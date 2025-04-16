<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Payments;

use App\Service\Payments\CancelPaymentService;
use App\Service\Payments\PaymentStatusService;
use DomainException;
use PHPUnit\Framework\TestCase;

class CancelPaymentServiceTest extends TestCase
{
    public function testCancelAPendingPayment()
    {
        $paymentId = 1;

        /** @var PaymentStatusService&\PHPUnit\Framework\MockObject\MockObject $statusService */
        $statusService = $this->createMock(PaymentStatusService::class);

        $statusService->method('getStatus')
            ->with($paymentId)
            ->willReturn('pending');

        $statusService->expects($this->once())
            ->method('updatePaymentStatus')
            ->with($paymentId, 'canceled');

        $service = new CancelPaymentService($statusService);

        $service->execute($paymentId);
    }

    public function testThrowsExceptionIfPaymentIsNotPending()
    {
        $paymentId = 2;

        /** @var PaymentStatusService&\PHPUnit\Framework\MockObject\MockObject $statusService */
        $statusService = $this->createMock(PaymentStatusService::class);

        $statusService->method('getStatus')
            ->with($paymentId)
            ->willReturn('confirmed');

        $statusService->expects($this->never())
            ->method('updatePaymentStatus');

        $service = new CancelPaymentService($statusService);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage("payment id {$paymentId} cannot be cancel");

        $service->execute($paymentId);
    }
}
