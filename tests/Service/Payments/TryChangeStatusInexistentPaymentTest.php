<?php

namespace Service\Payments;

use App\Database\Connection\SQLiteAdapter;
use App\Exceptions\PaymentStatusException;
use App\Gateway\GatewayOperation;
use App\Gateway\PagueFacil;
use App\Service\Payments\ConfirmPaymentService;
use App\Service\Payments\Dto\ProviderStatusInfo;
use App\Service\Payments\PaymentStatusService;
use App\Service\Providers\ProviderLogService;
use PHPUnit\Framework\TestCase;
use Phractico\Core\Infrastructure\Database\DatabaseConnection;

class TryChangeStatusInexistentPaymentTest extends TestCase
{
    /**
     * @before
     */
    public function init(): void
    {
        $connection = new SQLiteAdapter(__DIR__ . '/../../../database-test.sqlite');
        DatabaseConnection::setConnection($connection);
    }

    public function testExecute_ShouldThrowExceptionWhenPaymentDoesNotExist(): void
    {
        $paymentStatusService = new PaymentStatusService();
        $gateway = new PagueFacil();

        $providerStatus = new ProviderStatusInfo(
            provider: 'PagueFacil',
            operation: GatewayOperation::CREATE->value,
            paymentId: 777,
        );

        $providerLog = new ProviderLogService();
        $confirmPaymentService = new ConfirmPaymentService($gateway, $paymentStatusService, $providerLog);

        $this->expectException(PaymentStatusException::class);
        $this->expectExceptionMessage("Invalid ID");

        $confirmPaymentService->execute($providerStatus);
    }
}
