<?php

namespace Magento\CompanyCredit\Test\Unit\Block\Adminhtml\Order;

/**
 * Unit test for Cancel button.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CancelButtonTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\CompanyCredit\Model\CompanyStatus|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyStatus;

    /**
     * @var \Magento\CompanyCredit\Model\CompanyOrder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyOrder;

    /**
     * @var \Magento\Company\Api\CompanyRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyRepository;

    /**
     * @var \Magento\Backend\Block\Widget\Button\ButtonList|\PHPUnit_Framework_MockObject_MockObject
     */
    private $buttonList;

    /**
     * @var \Magento\Framework\Registry|\PHPUnit_Framework_MockObject_MockObject
     */
    private $coreRegistry;

    /**
     * @var \Magento\Framework\UrlInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $urlBuilder;

    /**
     * @var \Magento\CompanyCredit\Block\Adminhtml\Order\CancelButton
     */
    private $cancelButton;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->companyStatus = $this->createMock(
            \Magento\CompanyCredit\Model\CompanyStatus::class
        );
        $this->companyOrder = $this->createMock(
            \Magento\CompanyCredit\Model\CompanyOrder::class
        );
        $this->companyRepository = $this->createMock(
            \Magento\Company\Api\CompanyRepositoryInterface::class
        );
        $this->buttonList = $this->createMock(
            \Magento\Backend\Block\Widget\Button\ButtonList::class
        );
        $this->coreRegistry = $this->createMock(
            \Magento\Framework\Registry::class
        );
        $this->urlBuilder = $this->createMock(
            \Magento\Framework\UrlInterface::class
        );

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->cancelButton = $objectManager->getObject(
            \Magento\CompanyCredit\Block\Adminhtml\Order\CancelButton::class,
            [
                'companyStatus' => $this->companyStatus,
                'companyOrder' => $this->companyOrder,
                'companyRepository' => $this->companyRepository,
                'buttonList' => $this->buttonList,
                '_coreRegistry' => $this->coreRegistry,
                '_urlBuilder' => $this->urlBuilder,
            ]
        );
    }

    /**
     * Test for method checkCompanyStatus.
     *
     * @return void
     */
    public function testCheckCompanyStatus()
    {
        $companyId = 1;
        $orderId = 2;
        $companyName = 'Company Name';
        $confirmationMessage = __(
            'Are you sure you want to cancel this order? '
            . 'The order amount will not be reverted to %1 because the company is not active.',
            'Company Name'
        );
        $url = '/sales/order/cancel/order_id/' . $orderId;
        $order = $this->getMockForAbstractClass(
            \Magento\Sales\Api\Data\OrderInterface::class,
            [],
            '',
            false,
            false,
            true,
            ['getId']
        );
        $this->coreRegistry->expects($this->atLeastOnce())->method('registry')->with('sales_order')->willReturn($order);
        $order->expects($this->atLeastOnce())->method('getId')->willReturn($orderId);
        $orderPayment = $this->createMock(\Magento\Sales\Api\Data\OrderPaymentInterface::class);
        $order->expects($this->once())->method('getPayment')->willReturn($orderPayment);
        $orderPayment->expects($this->once())
            ->method('getMethod')
            ->willReturn(\Magento\CompanyCredit\Model\CompanyCreditPaymentConfigProvider::METHOD_NAME);
        $this->companyOrder->expects($this->once())
            ->method('getCompanyIdByOrder')->with($order)->willReturn($companyId);
        $this->companyStatus->expects($this->once())->method('isRevertAvailable')->with($companyId)->willReturn(false);
        $company = $this->createMock(\Magento\Company\Api\Data\CompanyInterface::class);
        $this->companyRepository->expects($this->once())->method('get')->with($companyId)->willReturn($company);
        $company->expects($this->once())->method('getCompanyName')->willReturn($companyName);
        $this->buttonList->expects($this->once())->method('update')->with(
            'order_cancel',
            'data_attribute',
            [
                'mage-init' => '{"Magento_CompanyCredit/js/cancel-order-button": '
                    . '{"message": "' . $confirmationMessage .  '", "url": "' . $url . '"}}',
            ]
        );
        $this->urlBuilder->expects($this->once())
            ->method('getUrl')->with('sales/*/cancel', ['order_id' => $orderId])->willReturn($url);
        $this->cancelButton->checkCompanyStatus();
    }

    /**
     * Test for method checkCompanyStatus with deleted company.
     *
     * @return void
     */
    public function testCheckCompanyStatusWithDeletedCompany()
    {
        $companyId = 1;
        $orderId = 2;
        $companyName = 'Company Name';
        $confirmationMessage = __(
            'Are you sure you want to cancel this order? The order amount will not be reverted '
            . 'to %1 because the company associated with this customer does not exist.',
            'Company Name'
        );
        $url = '/sales/order/cancel/order_id/' . $orderId;
        $order = $this->getMockForAbstractClass(
            \Magento\Sales\Api\Data\OrderInterface::class,
            [],
            '',
            false,
            false,
            true,
            ['getId']
        );
        $this->coreRegistry->expects($this->atLeastOnce())->method('registry')->with('sales_order')->willReturn($order);
        $order->expects($this->atLeastOnce())->method('getId')->willReturn($orderId);
        $orderPayment = $this->createMock(\Magento\Sales\Api\Data\OrderPaymentInterface::class);
        $order->expects($this->atLeastOnce())->method('getPayment')->willReturn($orderPayment);
        $orderPayment->expects($this->once())
            ->method('getMethod')
            ->willReturn(\Magento\CompanyCredit\Model\CompanyCreditPaymentConfigProvider::METHOD_NAME);
        $orderPayment->expects($this->once())
            ->method('getAdditionalInformation')->with('company_name')->willReturn($companyName);
        $this->companyOrder->expects($this->once())
            ->method('getCompanyIdByOrder')->with($order)->willReturn($companyId);
        $this->companyStatus->expects($this->once())->method('isRevertAvailable')->with($companyId)->willReturn(false);
        $this->companyRepository->expects($this->once())->method('get')->with($companyId)
            ->willThrowException(new \Magento\Framework\Exception\NoSuchEntityException(__('Exception Message')));
        $this->buttonList->expects($this->once())->method('update')->with(
            'order_cancel',
            'data_attribute',
            [
                'mage-init' => '{"Magento_CompanyCredit/js/cancel-order-button": '
                    . '{"message": "' . $confirmationMessage .  '", "url": "' . $url . '"}}',
            ]
        );
        $this->urlBuilder->expects($this->once())
            ->method('getUrl')->with('sales/*/cancel', ['order_id' => $orderId])->willReturn($url);
        $this->cancelButton->checkCompanyStatus();
    }
}
