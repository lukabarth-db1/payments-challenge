<?php

namespace App\Tests\Service;

use App\Database\Connection\SQLiteAdapter;
use App\Exceptions\GatewayException;
use App\Gateway\Contracts\Dto\GatewayPaymentInfo;
use App\Gateway\Contracts\GatewayFactory;
use App\Gateway\GatewayOperation;
use App\Service\Payments\CreatePaymentService;
use App\Service\Payments\Dto\CreatePaymentInfo;
use App\Service\Payments\Dto\ProviderStatusInfo;
use App\Service\Payments\Repository\ProviderRepository;
use App\Service\Providers\ProviderLogService;
use PHPUnit\Framework\TestCase;
use Phractico\Core\Facades\Database;
use Phractico\Core\Infrastructure\Database\DatabaseConnection;
use Phractico\Core\Infrastructure\Database\Query\Statement;

class GatewayPagueFacilTest extends TestCase
{
    /**
     * @before
     */
    public function init(): void
    {
        $connection = new SQLiteAdapter(__DIR__ . '/../../../database-test.sqlite');
        DatabaseConnection::setConnection($connection);
    }

    public function testExecute_PaymentInBrazilShouldHavePagueFacilAsProvider(): void
    {
        // arrange - prepare test
        $paymentInfo = new CreatePaymentInfo(
            amount: 2.550,
            type: 'creditcard',
            country: 'br',
            customerId: 1,
        );

        $createPaymentService = new CreatePaymentService();
        $providerRepository = new ProviderRepository();

        // act - run test
        $createPaymentService->execute($paymentInfo);

        $lastInsertedPayment = $this->retrieveLastInsertedPayment();
        $paymentId = $lastInsertedPayment['id'];

        $providerStatus = new ProviderStatusInfo(
            paymentId: $paymentId,
            provider: 'PagueFacil',
            operation: GatewayOperation::CREATE->value,
        );

        $providerLog = new ProviderLogService();
        $providerLog->log(
            provider: $providerStatus->provider,
            operation: $providerStatus->operation,
            paymentId: $providerStatus->paymentId,
        );

        // assert - check assert
        $lastInsertedPayment = $this->retrieveLastInsertedPayment();

        $this->assertEquals('PagueFacil', $providerRepository->FindById($paymentId));
        $this->assertEquals($lastInsertedPayment['amount'], $paymentInfo->amount);
        $this->assertEquals($lastInsertedPayment['type'], $paymentInfo->type);
        $this->assertEquals($lastInsertedPayment['country'], $paymentInfo->country);
    }

    public function testExecute_PaymentInBrazilIfProviderIsNotPagueFacil(): void
    {
        // arrange
        $paymentInfo = new CreatePaymentInfo(
            amount: 1589.45,
            type: 'creditcard',
            country: 'ar',
            customerId: 1,
        );

        $gatewayFactory = new GatewayFactory();
        $pagueDificil = $gatewayFactory->gatewayHandler('br');

        $gatewayDto = new GatewayPaymentInfo(
            amount: $paymentInfo->amount,
            country: $paymentInfo->country,
            type: $paymentInfo->type,
        );

        // assert
        $this->expectException(GatewayException::class);
        $this->expectExceptionMessage('PagueFacil only accepts payments from Brazil');

        // act
        $pagueDificil->create($gatewayDto, 'PENDING');
    }

    private function retrieveLastInsertedPayment(): array
    {
        $statement = new Statement("SELECT * FROM payments ORDER BY id DESC LIMIT 1");
        $statement->returningResults();

        return Database::execute($statement)->getRows()[0];
    }
}
