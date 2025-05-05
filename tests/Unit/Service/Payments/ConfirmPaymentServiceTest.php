<?php

namespace App\Tests\Unit\Service\Payments;

use App\Gateway\GatewayOperation;
use App\Helpers\PaymentStatus;
use App\Service\Payments\ConfirmPaymentService;
use App\Service\Payments\Dto\ProviderStatusInfo;
use App\Service\Payments\PaymentStatusService;
use App\Service\Providers\ProviderLogService;
use DomainException;
use PHPUnit\Framework\TestCase;

class ConfirmPaymentServiceTest extends TestCase
{
    public function testExecute_testConfirmAPendingPayment()
    {
        $paymentInfo = new ProviderStatusInfo(
            provider: 'PagueFacil',
            operation: GatewayOperation::CREATE->value,
            paymentId: 1,
        );

        /** @var \App\Service\Payments\PaymentStatusService&\PHPUnit\Framework\MockObject\MockObject $statusService */
        $statusService = $this->createMock(PaymentStatusService::class);

        /** @var \App\Service\Providers\ProviderLogService&\PHPUnit\Framework\MockObject\MockObject $logService */
        $logService = $this->createMock(ProviderLogService::class);

        $statusService->method('getStatus')
            ->with($paymentInfo->paymentId)
            ->willReturn(PaymentStatus::PENDING->value);

        $statusService->expects($this->once())
            ->method('updatePaymentStatus')
            ->with($paymentInfo->paymentId, PaymentStatus::CONFIRMED->value);

        $logService->expects($this->once())
            ->method('log')
            ->with(
                $paymentInfo->provider,
                $paymentInfo->operation,
                $paymentInfo->paymentId,
            );

        $service = new ConfirmPaymentService($statusService, $logService);
        $service->execute($paymentInfo);
    }

    public function testExecute_throwsExceptionIfPaymentIsNotPending()
    {
        $paymentInfo = new ProviderStatusInfo(
            provider: 'PagueFacil',
            operation: GatewayOperation::CREATE->value,
            paymentId: 2,
        );

        /** @var \App\Service\Payments\PaymentStatusService&\PHPUnit\Framework\MockObject\MockObject $statusService */
        $statusService = $this->createMock(PaymentStatusService::class);

        /** @var \App\Service\Providers\ProviderLogService&\PHPUnit\Framework\MockObject\MockObject $logService */
        $logService = $this->createMock(ProviderLogService::class);

        $statusService->method('getStatus')
            ->with($paymentInfo->paymentId)
            ->willReturn(PaymentStatus::CONFIRMED->value);

        $statusService->expects($this->never())
            ->method('updatePaymentStatus');

        $service = new ConfirmPaymentService($statusService, $logService);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage("Invalid payment status: " . PaymentStatus::CONFIRMED->value);

        $service->execute($paymentInfo);
    }
}
