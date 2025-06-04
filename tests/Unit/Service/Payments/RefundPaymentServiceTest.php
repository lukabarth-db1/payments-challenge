<?php

namespace App\Tests\Unit\Service\Payments;

use App\Exceptions\PaymentStatusException;
use App\Gateway\Contracts\PaymentGateway;
use App\Gateway\GatewayOperation;
use App\Helpers\PaymentStatus;
use App\Service\Payments\Dto\ProviderStatusInfo;
use App\Service\Payments\PaymentStatusService;
use App\Service\Payments\RefundPaymentService;
use App\Service\Providers\ProviderLogService;
use DomainException;
use PHPUnit\Framework\TestCase;

class RefundPaymentServiceTest extends TestCase
{
    public function testExecute_testRefundAConfirmedPayment()
    {
        $paymentInfo = new ProviderStatusInfo(
            provider: 'PagueFacil',
            operation: GatewayOperation::CREATE->value,
            paymentId: 1,
        );

        /** @var \App\Gateway\Contracts\PaymentGateway&\PHPUnit\Framework\MockObject\MockObject $gateway */
        $gateway = $this->createMock(PaymentGateway::class);

        /** @var \App\Service\Payments\PaymentStatusService&\PHPUnit\Framework\MockObject\MockObject $statusService */
        $statusService = $this->createMock(PaymentStatusService::class);

        /** @var \App\Service\Providers\ProviderLogService&\PHPUnit\Framework\MockObject\MockObject $logService */
        $logService = $this->createMock(ProviderLogService::class);

        $gateway->expects($this->once())
            ->method('refund')
            ->with(PaymentStatus::CONFIRMED->value);

        $statusService->method('getStatus')
            ->with($paymentInfo->paymentId)
            ->willReturn(PaymentStatus::CONFIRMED->value);

        $statusService->expects($this->once())
            ->method('updatePaymentStatus')
            ->with($paymentInfo->paymentId, PaymentStatus::REFUND->value);

        $logService->expects($this->once())
            ->method('log')
            ->with(
                $paymentInfo->provider,
                $paymentInfo->operation,
                $paymentInfo->paymentId,
            );

        $service = new RefundPaymentService($gateway, $statusService, $logService);
        $service->execute($paymentInfo);
    }

    public function testExecute_throwsExceptionIfPaymentIsNotConfirmed()
    {
        $paymentInfo = new ProviderStatusInfo(
            provider: 'PagueFacil',
            operation: GatewayOperation::CREATE->value,
            paymentId: 2,
        );

        /** @var \App\Gateway\Contracts\PaymentGateway&\PHPUnit\Framework\MockObject\MockObject $gateway */
        $gateway = $this->createMock(PaymentGateway::class);

        /** @var \App\Service\Payments\PaymentStatusService&\PHPUnit\Framework\MockObject\MockObject $statusService */
        $statusService = $this->createMock(PaymentStatusService::class);

        /** @var \App\Service\Providers\ProviderLogService&\PHPUnit\Framework\MockObject\MockObject $logService */
        $logService = $this->createMock(ProviderLogService::class);

        $gateway->expects($this->once())
            ->method('refund')
            ->with(PaymentStatus::REFUND->value)
            ->willThrowException(new PaymentStatusException(PaymentStatus::REFUND->value));

        $statusService->method('getStatus')
            ->with($paymentInfo->paymentId)
            ->willReturn(PaymentStatus::REFUND->value);

        $statusService->expects($this->never())
            ->method('updatePaymentStatus');

        $service = new RefundPaymentService($gateway, $statusService, $logService);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage("Invalid payment status: " . PaymentStatus::REFUND->value);

        $service->execute($paymentInfo);
    }
}
