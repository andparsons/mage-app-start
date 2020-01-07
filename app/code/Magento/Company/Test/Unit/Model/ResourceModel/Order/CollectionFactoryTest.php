<?php

namespace Magento\Company\Test\Unit\Model\ResourceModel\Order;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class CollectionFactoryTest.
 */
class CollectionFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var \Magento\Company\Model\ResourceModel\Order\CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $objectManagerMock;

    /**
     * @var \Magento\Company\Model\Company\Structure|\PHPUnit_Framework_MockObject_MockObject
     */
    private $structureMock;

    /**
     * @var \Magento\Company\Api\StatusServiceInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $moduleConfig;

    /**
     * Set up.
     *
     * @return void
     */
    public function setUp()
    {
        $this->objectManagerMock = $this->getMockBuilder(\Magento\Framework\ObjectManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->structureMock = $this->getMockBuilder(\Magento\Company\Model\Company\Structure::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAllowedChildrenIds'])
            ->getMock();

        $this->moduleConfig = $this->getMockBuilder(\Magento\Company\Api\StatusServiceInterface::class)
            ->setMethods(['isActive'])
            ->disableOriginalConstructor()->getMockForAbstractClass();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->collectionFactory = $this->objectManagerHelper->getObject(
            \Magento\Company\Model\ResourceModel\Order\CollectionFactory::class,
            [
                'objectManager' => $this->objectManagerMock,
                'structure' => $this->structureMock,
                'moduleConfig' => $this->moduleConfig
            ]
        );
    }

    /**
     * Test for create() method.
     *
     * @return void
     */
    public function testCreate()
    {
        $collectionMock = $this->getMockBuilder(\Magento\Sales\Model\ResourceModel\Order\Collection::class)
            ->disableOriginalConstructor()
            ->setMethods(['addFieldToFilter'])
            ->getMock();

        $isActive = true;
        $this->moduleConfig->expects($this->exactly(1))->method('isActive')->willReturn($isActive);

        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->willReturn($collectionMock);

        $this->structureMock->expects($this->once())
            ->method('getAllowedChildrenIds')
            ->with(1)
            ->willReturn([1]);
        $collectionMock->expects($this->once())
            ->method('addFieldToFilter');

        $this->assertSame($collectionMock, $this->collectionFactory->create(1));
    }
}
