<?php
namespace Magento\OfflineShipping\Test\Unit\Block\Adminhtml\Carrier\Tablerate;

class GridTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\OfflineShipping\Block\Adminhtml\Carrier\Tablerate\Grid
     */
    protected $model;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManagerMock;

    /**
     * @var \Magento\Backend\Helper\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $backendHelperMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $tablerateMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $context;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $collectionFactoryMock;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->storeManagerMock = $this->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->context = $objectManager->getObject(
            \Magento\Backend\Block\Template\Context::class,
            ['storeManager' => $this->storeManagerMock]
        );

        $this->backendHelperMock = $this->getMockBuilder(\Magento\Backend\Helper\Data::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->collectionFactoryMock =
            $this->getMockBuilder(
                \Magento\OfflineShipping\Model\ResourceModel\Carrier\Tablerate\CollectionFactory::class
            )->disableOriginalConstructor()
            ->getMock();

        $this->tablerateMock = $this->getMockBuilder(\Magento\OfflineShipping\Model\Carrier\Tablerate::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = new \Magento\OfflineShipping\Block\Adminhtml\Carrier\Tablerate\Grid(
            $this->context,
            $this->backendHelperMock,
            $this->collectionFactoryMock,
            $this->tablerateMock
        );
    }

    public function testSetWebsiteId()
    {
        $websiteId = 1;

        $websiteMock = $this->getMockBuilder(\Magento\Store\Model\Website::class)
            ->setMethods(['getId'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->storeManagerMock->expects($this->once())
            ->method('getWebsite')
            ->with($websiteId)
            ->willReturn($websiteMock);

        $websiteMock->expects($this->once())
            ->method('getId')
            ->willReturn($websiteId);

        $this->assertSame($this->model, $this->model->setWebsiteId($websiteId));
        $this->assertEquals($websiteId, $this->model->getWebsiteId());
    }

    public function testGetWebsiteId()
    {
        $websiteId = 10;

        $websiteMock = $this->getMockBuilder(\Magento\Store\Model\Website::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->getMock();

        $websiteMock->expects($this->once())
            ->method('getId')
            ->willReturn($websiteId);

        $this->storeManagerMock->expects($this->once())
            ->method('getWebsite')
            ->willReturn($websiteMock);

        $this->assertEquals($websiteId, $this->model->getWebsiteId());

        $this->storeManagerMock->expects($this->never())
            ->method('getWebsite')
            ->willReturn($websiteMock);

        $this->assertEquals($websiteId, $this->model->getWebsiteId());
    }

    public function testSetAndGetConditionName()
    {
        $conditionName = 'someName';
        $this->assertEquals($this->model, $this->model->setConditionName($conditionName));
        $this->assertEquals($conditionName, $this->model->getConditionName());
    }
}
