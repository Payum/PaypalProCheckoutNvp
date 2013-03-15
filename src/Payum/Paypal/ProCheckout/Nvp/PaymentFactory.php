<?php
namespace Payum\Paypal\ProCheckout\Nvp;

use Payum\Payment;
use Payum\Extension\EndlessCycleDetectorExtension;
use Payum\Action\CapturePaymentInstructionAggregateAction;
use Payum\Action\SyncPaymentInstructionAggregateAction;
use Payum\Action\StatusPaymentInstructionAggregateAction;
use Payum\Paypal\ProCheckout\Nvp\Action\CaptureAction;
use Payum\Paypal\ProCheckout\Nvp\Action\StatusAction;
use Payum\Paypal\ProCheckout\Nvp\Api;

abstract class PaymentFactory
{
    /**
     * @param AuthorizeNetAIM $api
     *
     * @return Payment
     */
    public static function create(Api $api)
    {
        $payment = new Payment;

        $payment->addApi($api);
        
        $payment->addExtension(new EndlessCycleDetectorExtension);

        $payment->addAction(new CaptureAction);
        $payment->addAction(new StatusAction);

        return $payment;
    }

    /**
     */
    private function __construct()
    {
    }
}
