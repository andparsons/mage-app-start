<?php
namespace Magento\Company\Test\Unit\Block\Adminhtml\Customer\Edit;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Unit test for \Magento\Company\Block\Adminhtml\Customer\Edit\DeleteButton class.
 */
class DeleteButtonTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var \Magento\Company\Block\Adminhtml\Customer\Edit\DeleteButton|\PHPUnit_Framework_MockObject_MockObject
     */
    private $deleteButton;

    /**
     * @var \Magento\Framework\UrlInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $urlBuilderMock;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestMock;

    /**
     * @var \Magento\Customer\Api\AccountManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $accountManagementMock;

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        $this->urlBuilderMock = $this->getMockBuilder(\Magento\Framework\UrlInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->requestMock = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->accountManagementMock = $this->getMockBuilder(\Magento\Customer\Api\AccountManagementInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->deleteButton = $this->objectManagerHelper->getObject(
            \Magento\Company\Block\Adminhtml\Customer\Edit\DeleteButton::class,
            [
                'urlBuilder' => $this->urlBuilderMock,
                'request' => $this->requestMock,
                'accountManagement' => $this->accountManagementMock
            ]
        );
    }

    /**
     * Test for method getButtonData.
     *
     * @param array $result
     * @return void
     * @dataProvider dataProviderGetButtonData
     */
    public function testGetButtonData(array $result)
    {
        $this->requestMock->expects($this->atLeastOnce())
            ->method('getParam')
            ->willReturn(1);
        $this->urlBuilderMock->expects($this->atLeastOnce())
            ->method('getUrl')
            ->willReturn('*/*/test');
        $this->accountManagementMock->expects($this->once())
            ->method('isReadonly')
            ->willReturn(false);
        $this->assertEquals($result, $this->deleteButton->getButtonData());
    }

    /**
     * Test for method getButtonData with readonly.
     *
     * @return void
     */
    public function testGetButtonDataWithReadonly()
    {
        $this->requestMock->expects($this->atLeastOnce())
            ->method('getParam')
            ->willReturn(1);
        $this->urlBuilderMock->expects($this->never())
            ->method('getUrl');
        $this->accountManagementMock->expects($this->once())
            ->method('isReadonly')
            ->willReturn(true);
        $this->assertEquals([], $this->deleteButton->getButtonData());
    }

    /**
     * Data provider for getButtonData.
     *
     * @return array
     */
    public function dataProviderGetButtonData()
    {
        return [
            [
                [
                    'label' => new \Magento\Framework\Phrase('Delete Customer'),
                    'class' => 'delete',
                    'id' => 'customer-delete-button',
                    'data_attribute' => [
                        'mage-init' => '{"Magento_Company/js/actions/delete-customer":'
                            . '{"url": "*/*/test",
                                        "validate": "*/*/test"}}',
                    ],
                    'on_click' => '',
                    'sort_order' => 20,
                ]
            ]
        ];
    }
}
