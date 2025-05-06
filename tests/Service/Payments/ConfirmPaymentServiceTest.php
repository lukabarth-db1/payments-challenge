<?php

declare(strict_types=1);

namespace App\Service\Payments;

use App\Database\Connection\SQLiteAdapter;
use App\Gateway\GatewayOperation;
use App\Helpers\PaymentStatus;
use App\Service\Payments\Dto\CreatePaymentInfo;
use App\Service\Payments\Dto\ProviderStatusInfo;
use App\Service\Providers\ProviderLogService;
use PHPUnit\Framework\TestCase;
use Phractico\Core\Facades\Database;
use Phractico\Core\Infrastructure\Database\DatabaseConnection;
use Phractico\Core\Infrastructure\Database\Query\Statement;

class ConfirmPaymentServiceTest extends TestCase
{
    /**
     * @before
     */
    public function init(): void
    {
        $connection = new SQLiteAdapter(__DIR__ . '/../../../database-test.sqlite');
        DatabaseConnection::setConnection($connection);
    }

    public function testExecute_ShouldConfirmPaymentInDatabase(): void
    {
        // arrange
        $createPayment = new CreatePaymentInfo(
            amount: 12.559,
            type: 'creditcard',
            country: 'br',
            customerId: 1,
        );

        $createPaymentService = new CreatePaymentService();
        $paymentStatusService = new PaymentStatusService();

        // act - criação do pagamento
        $createPaymentService->execute($createPayment);

        $lastInsertedPayment = $this->retrieveLastInsertedPayment();
        $paymentId = $lastInsertedPayment['id'];

        $providerStatus = new ProviderStatusInfo(
            provider: 'PagueFacil',
            operation: GatewayOperation::CREATE->value,
            paymentId: $paymentId,
        );

        $providerLog = new ProviderLogService();
        $providerLog->log(
            provider: $providerStatus->provider,
            operation: $providerStatus->operation,
            paymentId: $providerStatus->paymentId,
        );

        $confirmPaymentService = new ConfirmPaymentService($paymentStatusService, $providerLog);
        $confirmPaymentService->execute($providerStatus);

        // assert - validações
        $confirmedPayment = $this->retrieveLastInsertedPayment();

        $this->assertEquals(PaymentStatus::CONFIRMED->value, $confirmedPayment['status']);
        $this->assertEquals($createPayment->amount, $confirmedPayment['amount']);
        $this->assertEquals($createPayment->type, $confirmedPayment['type']);
        $this->assertEquals($createPayment->country, $confirmedPayment['country']);
    }

    private function retrieveLastInsertedPayment(): array
    {
        $statement = new Statement("SELECT * FROM payments ORDER BY id DESC LIMIT 1");
        $statement->returningResults();

        return Database::execute($statement)->getRows()[0];
    }
}
