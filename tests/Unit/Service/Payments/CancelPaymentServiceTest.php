<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Payments;

use App\Exceptions\PaymentStatusException;
use App\Gateway\GatewayOperation;
use App\Gateway\Contracts\PaymentGateway;
use App\Helpers\PaymentStatus;
use App\Service\Payments\CancelPaymentService;
use App\Service\Payments\Dto\ProviderStatusInfo;
use App\Service\Payments\PaymentStatusService;
use App\Service\Providers\ProviderLogService;
use DomainException;
use PHPUnit\Framework\TestCase;

class CancelPaymentServiceTest extends TestCase
{
    public function testCancelAPendingPayment()
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

        $statusService->method('getStatus')
            ->with($paymentInfo->paymentId)
            ->willReturn(PaymentStatus::PENDING->value);

        $statusService->expects($this->once())
            ->method('updatePaymentStatus')
            ->with($paymentInfo->paymentId, PaymentStatus::CANCELED->value);

        $logService->expects($this->once())
            ->method('log')
            ->with(
                $paymentInfo->provider,
                $paymentInfo->operation,
                $paymentInfo->paymentId,
            );

        $gateway->expects($this->once())
            ->method('cancel')
            ->with(PaymentStatus::PENDING->value);

        $service = new CancelPaymentService($gateway, $statusService, $logService);
        $service->execute($paymentInfo);
    }

    public function testThrowsExceptionIfPaymentIsNotPending()
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
            ->method('cancel')
            ->with(PaymentStatus::CANCELED->value)
            ->willThrowException(new PaymentStatusException(PaymentStatus::CANCELED->value));

        $statusService->method('getStatus')
            ->with($paymentInfo->paymentId)
            ->willReturn(PaymentStatus::CANCELED->value);

        $statusService->expects($this->never())
            ->method('updatePaymentStatus');

        $logService->expects($this->never())
            ->method('log');

        $service = new CancelPaymentService($gateway, $statusService, $logService);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage("Invalid payment status: " . PaymentStatus::CANCELED->value);

        $service->execute($paymentInfo);
    }
}
