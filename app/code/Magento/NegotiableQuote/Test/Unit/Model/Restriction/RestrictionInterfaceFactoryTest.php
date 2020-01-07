<?php
namespace Magento\NegotiableQuote\Test\Unit\Model\Restriction;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\NegotiableQuote\Model\Restriction\RestrictionInterfaceFactory;

/**
 * Unit tests for RestrictionInterfaceFactory.
 */
class RestrictionInterfaceFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var RestrictionInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $restrictionInterfaceFactory;

    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $objectManagerMock;

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        $this->objectManagerMock = $this->getMockBuilder(\Magento\Framework\ObjectManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->restrictionInterfaceFactory = $this->objectManagerHelper->getObject(
            \Magento\NegotiableQuote\Model\Restriction\RestrictionInterfaceFactory::class,
            [
                'objectManager' => $this->objectManagerMock
            ]
        );
    }

    /**
     * Test for create() method
     *
     * @return void
     */
    public function testCreate()
    {
        $restrictionMock = $this->getMockBuilder(\Magento\NegotiableQuote\Model\Restriction\RestrictionInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $quoteMock = $this->getMockBuilder(\Magento\Quote\Api\Data\CartInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $quoteMock->expects($this->atLeastOnce())->method('getId')->willReturn(1);
        $this->objectManagerMock->expects($this->once())->method('create')->with(
            \Magento\NegotiableQuote\Model\Restriction\RestrictionInterface::class,
            ['quote' => $quoteMock]
        )
            ->willReturn($restrictionMock);

        $this->assertSame($restrictionMock, $this->restrictionInterfaceFactory->create($quoteMock));
    }
}
