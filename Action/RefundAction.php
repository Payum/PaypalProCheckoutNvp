<?php
namespace Payum\Paypal\ProCheckout\Nvp\Action;

use Payum\Core\Action\PaymentAwareAction;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Core\Exception\LogicException;
use Payum\Core\Request\ObtainCreditCard;
use Payum\Core\Security\SensitiveValue;
use Payum\Paypal\ProCheckout\Nvp\Api;
use Payum\Paypal\ProCheckout\Nvp\Bridge\Buzz\Request;
use Payum\Core\Request\Refund;

/**
 * @author Ton Sharp <Forma-PRO@66ton99.org.ua>
 */
class RefundAction extends PaymentAwareAction implements ApiAwareInterface
{
    /**
     * @var Api
     */
    protected $api;

    /**
     * {@inheritDoc}
     */
    public function setApi($api)
    {
        if (false == $api instanceof Api) {
            throw new UnsupportedApiException('Not supported.');
        }

        $this->api = $api;
    }

    /**
     * {@inheritDoc}
     */
    public function execute($request)
    {
        \Log::info("DUDE WE ARE STUCK ON RefundAction execute");
        /** @var $request Refund */
        RequestNotSupportedException::assertSupports($this, $request);

        $model = new ArrayObject($request->getModel());

        \Log::info("THE MODEL ---> ");
        \Log::info($model['RESULT']);

        if (is_numeric($model['RESULT'])) {
            return;
        }

        // $cardFields = array('AMT');
        // if (false == $model->validateNotEmpty($cardFields, false)) {
        //     try {
        //         $this->payment->execute($obtainCreditCard = new ObtainCreditCard);

        //         $card = $obtainCreditCard->obtain();

        //         $model['EXPDATE'] = new SensitiveValue($card->getExpireAt()->format('my'));
        //         $model['ACCT'] = new SensitiveValue($card->getNumber());
        //         $model['CVV2'] = new SensitiveValue($card->getSecurityCode());
        //     } catch (RequestNotSupportedException $e) {
        //         throw new LogicException('Credit card details has to be set explicitly or there has to be an action that supports ObtainCreditCard request.');
        //     }
        // }

        $buzzRequest = new Request();
        $buzzRequest->setFields($model->toUnsafeArray());
        $response = $this->api->doRefund($buzzRequest);

        $model->replace($response);
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof Refund &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
