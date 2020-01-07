<?php

namespace Magento\SharedCatalog\Test\Unit\Controller\Adminhtml\SharedCatalog\Company\MassAssign;

/**
 * Unit test for \Magento\SharedCatalog\Controller\Adminhtml\SharedCatalog\Company\MassAssign\Validate.
 */
class ValidateTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\SharedCatalog\Api\SharedCatalogManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $catalogManagement;

    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $logger;

    /**
     * @var \Magento\Ui\Component\MassAction\Filter|\PHPUnit_Framework_MockObject_MockObject
     */
    private $filter;

    /**
     * @var \Magento\SharedCatalog\Ui\DataProvider\Collection\Grid\CompanyFactory
     *      |\PHPUnit_Framework_MockObject_MockObject
     */
    private $collectionFactory;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultJsonFactory;

    /**
     * @var \Magento\SharedCatalog\Controller\Adminhtml\SharedCatalog\Company\MassAssign\Validate
     */
    private $validate;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->catalogManagement = $this
            ->getMockBuilder(\Magento\SharedCatalog\Api\SharedCatalogManagementInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->logger = $this->getMockBuilder(\Psr\Log\LoggerInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->filter = $this->getMockBuilder(\Magento\Ui\Component\MassAction\Filter::class)
            ->disableOriginalConstructor()->getMock();
        $this->collectionFactory = $this
            ->getMockBuilder(\Magento\SharedCatalog\Ui\DataProvider\Collection\Grid\CompanyFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()->getMock();
        $this->resultJsonFactory = $this
            ->getMockBuilder(\Magento\Framework\Controller\Result\JsonFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->validate = $objectManager->getObject(
            \Magento\SharedCatalog\Controller\Adminhtml\SharedCatalog\Company\MassAssign\Validate::class,
            [
                'catalogManagement' => $this->catalogManagement,
                'logger' => $this->logger,
                'filter' => $this->filter,
                'collectionFactory' => $this->collectionFactory,
                'resultJsonFactory' => $this->resultJsonFactory,
            ]
        );
    }

    /**
     * Test for execute method.
     *
     * @return void
     */
    public function testExecute()
    {
        $collection = $this
            ->getMockBuilder(\Magento\SharedCatalog\Ui\DataProvider\Collection\Grid\Company::class)
            ->disableOriginalConstructor()->getMock();
        $this->collectionFactory->expects($this->once())->method('create')->willReturn($collection);
        $this->filter->expects($this->once())->method('getCollection')->with($collection)->willReturn($collection);
        $sharedCatalog = $this
            ->getMockBuilder(\Magento\SharedCatalog\Api\Data\SharedCatalogInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->catalogManagement->expects($this->once())->method('getPublicCatalog')->willReturn($sharedCatalog);
        $company = $this->getMockBuilder(\Magento\Company\Api\Data\CompanyInterface::class)
            ->setMethods(['getSharedCatalogId'])
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $collection->expects($this->once())->method('getIterator')->willReturn(new \ArrayIterator([$company]));
        $sharedCatalog->expects($this->once())->method('getId')->willReturn(1);
        $company->expects($this->once())->method('getSharedCatalogId')->willReturn(2);
        $resultJson = $this->getMockBuilder(\Magento\Framework\Controller\Result\Json::class)
            ->disableOriginalConstructor()->getMock();
        $this->resultJsonFactory->expects($this->once())->method('create')->willReturn($resultJson);
        $resultJson->expects($this->once())->method('setJsonData')->with(
            json_encode(['is_custom_assigned' => true], JSON_NUMERIC_CHECK)
        )->willReturnSelf();
        $this->assertEquals($resultJson, $this->validate->execute());
    }
}
