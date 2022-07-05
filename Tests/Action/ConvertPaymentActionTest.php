<?php

namespace Payum\Paypal\ProCheckout\Nvp\Tests\Action;

use Payum\Core\Model\Payment;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Request\Convert;
use Payum\Core\Request\GetCurrency;
use Payum\Core\Tests\GenericActionTest;
use Payum\Paypal\ProCheckout\Nvp\Action\ConvertPaymentAction;

class ConvertPaymentActionTest extends GenericActionTest
{
    protected $actionClass = \Payum\Paypal\ProCheckout\Nvp\Action\ConvertPaymentAction::class;

    protected $requestClass = \Payum\Core\Request\Convert::class;

    public function provideSupportedRequests(): \Iterator
    {
        yield [new $this->requestClass(new Payment(), 'array')];
        yield [new $this->requestClass($this->createMock(PaymentInterface::class), 'array')];
        yield [new $this->requestClass(new Payment(), 'array', $this->createMock(\Payum\Core\Security\TokenInterface::class))];
    }

    public function provideNotSupportedRequests(): \Iterator
    {
        yield ['foo'];
        yield [['foo']];
        yield [new \stdClass()];
        yield [$this->getMockForAbstractClass(\Payum\Core\Request\Generic::class, [[]])];
        yield [new $this->requestClass(new \stdClass(), 'array')];
        yield [new $this->requestClass(new Payment(), 'foobar')];
        yield [new $this->requestClass($this->createMock(PaymentInterface::class), 'foobar')];
    }

    public function testShouldCorrectlyConvertOrderToDetailsAndSetItBack()
    {
        $gatewayMock = $this->createMock(\Payum\Core\GatewayInterface::class);
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf(\Payum\Core\Request\GetCurrency::class))
            ->willReturnCallback(function (GetCurrency $request) {
                $request->name = 'US Dollar';
                $request->alpha3 = 'USD';
                $request->numeric = 123;
                $request->exp = 2;
                $request->country = 'US';
            })
        ;

        $order = new Payment();
        $order->setNumber('theNumber');
        $order->setCurrencyCode('USD');
        $order->setTotalAmount(123);
        $order->setDescription('the description');
        $order->setClientId('theClientId');
        $order->setClientEmail('theClientEmail');

        $action = new ConvertPaymentAction();
        $action->setGateway($gatewayMock);

        $action->execute($convert = new Convert($order, 'array'));

        $details = $convert->getResult();

        $this->assertNotEmpty($details);

        $this->assertArrayHasKey('AMT', $details);
        $this->assertSame(1.23, $details['AMT']);

        $this->assertArrayHasKey('CURRENCY', $details);
        $this->assertSame('USD', $details['CURRENCY']);
    }

    public function testShouldNotOverwriteAlreadySetExtraDetails()
    {
        $gatewayMock = $this->createMock(\Payum\Core\GatewayInterface::class);
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf(\Payum\Core\Request\GetCurrency::class))
            ->willReturnCallback(function (GetCurrency $request) {
                $request->name = 'US Dollar';
                $request->alpha3 = 'USD';
                $request->numeric = 123;
                $request->exp = 2;
                $request->country = 'US';
            })
        ;

        $order = new Payment();
        $order->setCurrencyCode('USD');
        $order->setTotalAmount(123);
        $order->setDescription('the description');
        $order->setDetails([
            'foo' => 'fooVal',
        ]);

        $action = new ConvertPaymentAction();
        $action->setGateway($gatewayMock);

        $action->execute($convert = new Convert($order, 'array'));

        $details = $convert->getResult();

        $this->assertNotEmpty($details);

        $this->assertArrayHasKey('foo', $details);
        $this->assertSame('fooVal', $details['foo']);
    }
}
