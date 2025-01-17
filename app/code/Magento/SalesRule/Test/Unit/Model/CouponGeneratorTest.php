<?php
declare(strict_types=1);

namespace Magento\SalesRule\Test\Unit\Model;

use Magento\SalesRule\Api\Data\CouponGenerationSpecInterface;
use Magento\SalesRule\Api\Data\CouponGenerationSpecInterfaceFactory;
use Magento\SalesRule\Model\CouponGenerator;
use Magento\SalesRule\Model\Service\CouponManagementService;

/**
 * @covers \Magento\SalesRule\Model\CouponGenerator
 */
class CouponGeneratorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Testable Object
     *
     * @var CouponGenerator
     */
    private $couponGenerator;

    /**
     * @var CouponManagementService|\PHPUnit_Framework_MockObject_MockObject
     */
    private $couponManagementServiceMock;

    /**
     * @var CouponGenerationSpecInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $generationSpecFactoryMock;

    /**
     * @var CouponGenerationSpecInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $generationSpecMock;

    /**
     * Set Up
     *
     * @return void
     */
    protected function setUp()
    {
        $this->generationSpecFactoryMock = $this->getMockBuilder(CouponGenerationSpecInterfaceFactory::class)
            ->disableOriginalConstructor()->setMethods(['create'])->getMock();
        $this->couponManagementServiceMock = $this->createMock(CouponManagementService::class);
        $this->generationSpecMock = $this->createMock(CouponGenerationSpecInterface::class);
        $this->couponGenerator = new CouponGenerator(
            $this->couponManagementServiceMock,
            $this->generationSpecFactoryMock
        );
    }

    /**
     * Test beforeSave method
     *
     * @return void
     */
    public function testBeforeSave()
    {
        $expected = ['test'];
        $this->generationSpecFactoryMock->expects($this->once())->method('create')
            ->willReturn($this->generationSpecMock);
        $this->couponManagementServiceMock->expects($this->once())->method('generate')
            ->with($this->generationSpecMock)->willReturn($expected);
        $actual = $this->couponGenerator->generateCodes([]);
        self::assertEquals($expected, $actual);
    }
}
