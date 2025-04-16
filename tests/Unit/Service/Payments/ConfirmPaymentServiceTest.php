<?php

namespace App\Tests\Unit\Service\Payments;

use App\Service\Payments\ConfirmPaymentService;
use App\Service\Payments\PaymentStatusService;
use DomainException;
use PHPUnit\Framework\TestCase;

class ConfirmPaymentServiceTest extends TestCase
{
    public function testExecute_testConfirmAPendingPayment()
    {
        $paymentId = 1;

        /** @var PaymentStatusService&\PHPUnit\Framework\MockObject\MockObject $statusService */
        $statusService = $this->createMock(PaymentStatusService::class);

        $statusService->method('getStatus')
            ->with($paymentId)
            ->willReturn('pending');

        $statusService->expects($this->once())
            ->method('updatePaymentStatus')
            ->with($paymentId, 'confirmed');

        $service = new ConfirmPaymentService($statusService);
        $service->execute($paymentId);
    }

    public function testExecute_throwsExceptionIfPaymentIsNotPending()
    {
        $paymentId = 2;

        /** @var PaymentStatusService&\PHPUnit\Framework\MockObject\MockObject $statusService */
        $statusService = $this->createMock(PaymentStatusService::class);

        $statusService->method('getStatus')
            ->with($paymentId)
            ->willReturn('confirmed');

        $statusService->expects($this->never())
            ->method('updatePaymentStatus');

        $service = new ConfirmPaymentService($statusService);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage("payment id {$paymentId} cannot be confirm");

        $service->execute($paymentId);
    }
}
