<?php

namespace Magento\SharedCatalog\Test\Unit\Controller\Adminhtml\SharedCatalog\Configure\Product;

use Magento\SharedCatalog\Controller\Adminhtml\SharedCatalog\Configure\Product\AbstractMassAction;

/**
 * Test for controller Adminhtml\SharedCatalog\Configure\Product\AbstractMassAction.
 */
class AbstractMassActionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var AbstractMassAction|\PHPUnit_Framework_MockObject_MockObject
     */
    private $abstractMassAction;

    /**
     * @var \Magento\Backend\App\Action\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $context;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultJsonFactory;

    /**
     * @var \Magento\Ui\Component\MassAction\Filter|\PHPUnit_Framework_MockObject_MockObject
     */
    private $filter;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $collectionFactory;

    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $logger;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->context = $this->getMockBuilder(\Magento\Backend\App\Action\Context::class)
            ->disableOriginalConstructor()->getMock();
        $this->resultJsonFactory = $this->getMockBuilder(\Magento\Framework\Controller\Result\JsonFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()->getMock();
        $this->filter = $this->getMockBuilder(\Magento\Ui\Component\MassAction\Filter::class)
            ->setMethods(['getCollection'])
            ->disableOriginalConstructor()->getMock();
        $this->collectionFactory = $this
            ->getMockBuilder(\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()->getMock();
        $this->logger = $this->getMockBuilder(\Psr\Log\LoggerInterface::class)
            ->setMethods(['critical'])
            ->disableOriginalConstructor()->getMockForAbstractClass();

        $this->abstractMassAction = $this->getMockBuilder(AbstractMassAction::class)
            ->setConstructorArgs([
                'context' => $this->context,
                'resultJsonFactory' => $this->resultJsonFactory,
                'filter' => $this->filter,
                'collectionFactory' => $this->collectionFactory,
                'logger' => $this->logger
            ])
            ->getMockForAbstractClass();
    }

    /**
     * Test for execute().
     *
     * @return void
     */
    public function testExecute()
    {
        $collection = $this->getMockBuilder(\Magento\Framework\Data\Collection\AbstractDb::class)
            ->disableOriginalConstructor()->getMock();
        $this->collectionFactory->expects($this->once())->method('create')->willReturn($collection);
        $filteredCollection = $this
            ->getMockBuilder(\Magento\Eav\Model\Entity\Collection\AbstractCollection::class)
            ->disableOriginalConstructor()->getMock();
        $this->filter->expects($this->once())->method('getCollection')->with($collection)
            ->willReturn($filteredCollection);
        $result = $this->getMockBuilder(\Magento\Framework\Controller\Result\Json::class)
            ->disableOriginalConstructor()->getMock();
        $this->abstractMassAction->expects($this->once())->method('massAction')->with($filteredCollection)
            ->willReturn($result);
        $this->assertEquals($result, $this->abstractMassAction->execute());
    }

    /**
     * Test for execute() with Exception.
     *
     * @return void
     */
    public function testExecuteWithException()
    {
        $exception = new \Exception();
        $collection = $this->getMockBuilder(\Magento\Framework\Data\Collection\AbstractDb::class)
            ->disableOriginalConstructor()->getMock();
        $this->collectionFactory->expects($this->once())->method('create')->willReturn($collection);
        $this->filter->expects($this->once())->method('getCollection')->with($collection)
            ->willThrowException($exception);
        $this->logger->expects($this->once())->method('critical')->with($exception);
        $resultJson = $this->getMockBuilder(\Magento\Framework\Controller\Result\Json::class)
            ->setMethods(['setJsonData'])
            ->disableOriginalConstructor()->getMock();
        $resultJson->expects($this->once())->method('setJsonData')->willReturnSelf();
        $this->resultJsonFactory->expects($this->once())->method('create')->willReturn($resultJson);
        $this->assertEquals($resultJson, $this->abstractMassAction->execute());
    }
}
