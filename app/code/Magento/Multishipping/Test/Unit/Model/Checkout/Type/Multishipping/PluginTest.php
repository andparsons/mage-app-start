<?php
namespace Magento\Multishipping\Test\Unit\Model\Checkout\Type\Multishipping;

use Magento\Checkout\Model\Session;
use Magento\Multishipping\Model\Checkout\Type\Multishipping\State;

class PluginTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $checkoutSessionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $cartMock;

    /**
     * @var \Magento\Multishipping\Model\Checkout\Type\Multishipping\Plugin
     */
    protected $model;

    protected function setUp()
    {
        $this->checkoutSessionMock = $this->createPartialMock(
            \Magento\Checkout\Model\Session::class,
            ['getCheckoutState', 'setCheckoutState']
        );
        $this->cartMock = $this->createMock(\Magento\Checkout\Model\Cart::class);
        $this->model = new \Magento\Multishipping\Model\Checkout\Type\Multishipping\Plugin($this->checkoutSessionMock);
    }

    public function testBeforeInitCaseTrue()
    {
        $this->checkoutSessionMock->expects($this->once())->method('getCheckoutState')
            ->willReturn(State::STEP_SELECT_ADDRESSES);
        $this->checkoutSessionMock->expects($this->once())->method('setCheckoutState')
            ->with(Session::CHECKOUT_STATE_BEGIN);
        $this->model->beforeSave($this->cartMock);
    }

    public function testBeforeInitCaseFalse()
    {
        $this->checkoutSessionMock->expects($this->once())->method('getCheckoutState')
            ->willReturn('');
        $this->checkoutSessionMock->expects($this->never())->method('setCheckoutState');
        $this->model->beforeSave($this->cartMock);
    }
}
