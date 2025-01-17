<?php

namespace Magento\Setup\Test\Unit\Fixtures;

use \Magento\Setup\Fixtures\CouponCodesFixture;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CouponCodesFixtureTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Setup\Fixtures\CartPriceRulesFixture
     */
    private $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Setup\Fixtures\FixtureModel
     */
    private $fixtureModelMock;

    /**
     * @var \Magento\SalesRule\Model\RuleFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $ruleFactoryMock;

    /**
     * @var \Magento\SalesRule\Model\CouponFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $couponCodeFactoryMock;

    /**
     * setUp
     */
    public function setUp()
    {
        $this->fixtureModelMock = $this->createMock(\Magento\Setup\Fixtures\FixtureModel::class);
        $this->ruleFactoryMock = $this->createPartialMock(\Magento\SalesRule\Model\RuleFactory::class, ['create']);
        $this->couponCodeFactoryMock = $this->createPartialMock(
            \Magento\SalesRule\Model\CouponFactory::class,
            ['create']
        );
        $this->model = new CouponCodesFixture(
            $this->fixtureModelMock,
            $this->ruleFactoryMock,
            $this->couponCodeFactoryMock
        );
    }

    /**
     * testExecute
     */
    public function testExecute()
    {
        $websiteMock = $this->createMock(\Magento\Store\Model\Website::class);
        $websiteMock->expects($this->once())
            ->method('getId')
            ->will($this->returnValue('website_id'));

        $storeManagerMock = $this->createMock(\Magento\Store\Model\StoreManager::class);
        $storeManagerMock->expects($this->once())
            ->method('getWebsites')
            ->will($this->returnValue([$websiteMock]));

        $objectManagerMock = $this->createMock(\Magento\Framework\ObjectManager\ObjectManager::class);
        $objectManagerMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($storeManagerMock));

        $valueMap = [
            ['coupon_codes', 0, 1]
        ];

        $this->fixtureModelMock
            ->expects($this->exactly(1))
            ->method('getValue')
            ->will($this->returnValueMap($valueMap));
        $this->fixtureModelMock
            ->expects($this->exactly(1))
            ->method('getObjectManager')
            ->will($this->returnValue($objectManagerMock));

        $ruleMock = $this->createMock(\Magento\SalesRule\Model\Rule::class);
        $this->ruleFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($ruleMock);

        $couponMock = $this->createMock(\Magento\SalesRule\Model\Coupon::class);
        $couponMock->expects($this->once())
            ->method('setRuleId')
            ->willReturnSelf();
        $couponMock->expects($this->once())
            ->method('setCode')
            ->willReturnSelf();
        $couponMock->expects($this->once())
            ->method('setIsPrimary')
            ->willReturnSelf();
        $couponMock->expects($this->once())
            ->method('setType')
            ->willReturnSelf();
        $couponMock->expects($this->once())
            ->method('save')
            ->willReturnSelf();
        $this->couponCodeFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($couponMock);

        $this->model->execute();
    }

    /**
     * testNoFixtureConfigValue
     */
    public function testNoFixtureConfigValue()
    {
        $ruleMock = $this->createMock(\Magento\SalesRule\Model\Rule::class);
        $ruleMock->expects($this->never())->method('save');

        $objectManagerMock = $this->createMock(\Magento\Framework\ObjectManager\ObjectManager::class);
        $objectManagerMock->expects($this->never())
            ->method('get')
            ->with($this->equalTo(\Magento\SalesRule\Model\Rule::class))
            ->willReturn($ruleMock);

        $this->fixtureModelMock
            ->expects($this->never())
            ->method('getObjectManager')
            ->willReturn($objectManagerMock);
        $this->fixtureModelMock
            ->expects($this->once())
            ->method('getValue')
            ->willReturn(false);

        $this->model->execute();
    }

    /**
     * testGetActionTitle
     */
    public function testGetActionTitle()
    {
        $this->assertSame('Generating coupon codes', $this->model->getActionTitle());
    }

    /**
     * testIntroduceParamLabels
     */
    public function testIntroduceParamLabels()
    {
        $this->assertSame(['coupon_codes' => 'Coupon Codes'], $this->model->introduceParamLabels());
    }
}
