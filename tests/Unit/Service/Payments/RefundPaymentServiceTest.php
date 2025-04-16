<?php

namespace App\Tests\Unit\Service\Payments;

use App\Service\Payments\PaymentStatusService;
use App\Service\Payments\RefundPaymentService;
use DomainException;
use PHPUnit\Framework\TestCase;

class RefundPaymentServiceTest extends TestCase
{
    public function testExecute_testRefundAConfirmedPayment()
    {
        $paymentId = 1;

        /** @var PaymentStatusService&\PHPUnit\Framework\MockObject\MockObject $statusService */
        $statusService = $this->createMock(PaymentStatusService::class);

        $statusService->method('getStatus')
            ->with($paymentId)
            ->willReturn('confirmed');

        $statusService->expects($this->once())
            ->method('updatePaymentStatus')
            ->with($paymentId, 'refund');

        $service = new RefundPaymentService($statusService);

        $service->execute($paymentId);
    }

    public function testExecute_throwsExceptionIfPaymentIsNotConfirmed()
    {
        $paymentId = 2;

        /** @var PaymentStatusService&\PHPUnit\Framework\MockObject\MockObject $statusService */
        $statusService = $this->createMock(PaymentStatusService::class);

        $statusService->method('getStatus')
            ->with($paymentId)
            ->willReturn('refund');

        $statusService->expects($this->never())
            ->method('updatePaymentStatus');

        $service = new RefundPaymentService($statusService);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('payment id 2 cannot be refunded');

        $service->execute($paymentId);
    }
}
