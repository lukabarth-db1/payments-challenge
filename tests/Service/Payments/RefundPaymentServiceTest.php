<?php

declare(strict_types=1);

namespace App\Service\Payments;

use App\Database\Connection\SQLiteAdapter;
use App\Gateway\GatewayOperation;
use App\Gateway\PagueFacil;
use App\Helpers\PaymentStatus;
use App\Service\Payments\Dto\CreatePaymentInfo;
use App\Service\Payments\Dto\ProviderStatusInfo;
use App\Service\Providers\ProviderLogService;
use PHPUnit\Framework\TestCase;
use Phractico\Core\Facades\Database;
use Phractico\Core\Infrastructure\Database\DatabaseConnection;
use Phractico\Core\Infrastructure\Database\Query\Statement;

class RefundPaymentServiceTest extends TestCase
{
    /**
     * @before
     */
    public function init(): void
    {
        $connection = new SQLiteAdapter(__DIR__ . '/../../../database-test.sqlite');
        DatabaseConnection::setConnection($connection);
    }

    public function testExecute_ShouldRefundPaymentInDatabase(): void
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
        $gateway = new PagueFacil();

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

        $confirmPaymentService = new ConfirmPaymentService($gateway, $paymentStatusService, $providerLog);
        $confirmPaymentService->execute($providerStatus);

        $refundPaymentService = new RefundPaymentService($gateway, $paymentStatusService, $providerLog);
        $refundPaymentService->execute($providerStatus);

        // assert - validações
        $refundPayment = $this->retrieveLastInsertedPayment();

        $this->assertEquals(PaymentStatus::REFUND->value, $refundPayment['status']);
        $this->assertEquals($createPayment->amount, $refundPayment['amount']);
        $this->assertEquals($createPayment->type, $refundPayment['type']);
        $this->assertEquals($createPayment->country, $refundPayment['country']);
    }

    private function retrieveLastInsertedPayment(): array
    {
        $statement = new Statement("SELECT * FROM payments ORDER BY id DESC LIMIT 1");
        $statement->returningResults();

        return Database::execute($statement)->getRows()[0];
    }
}
