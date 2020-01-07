<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SharedCatalog\Test\Unit\Ui\DataProvider\Modifier\PriceByType;

/**
 * Test for \Magento\SharedCatalog\Ui\DataProvider\Modifier\PriceByType\Simple class.
 */
class SimpleTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\SharedCatalog\Ui\DataProvider\Modifier\PriceByType\Simple
     */
    private $modifier;

    /**
     * @var \Magento\SharedCatalog\Model\Form\Storage\PriceCalculator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $calculator;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $request;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->calculator = $this->getMockBuilder(\Magento\SharedCatalog\Model\Form\Storage\PriceCalculator::class)
            ->disableOriginalConstructor()->getMock();
        $this->request = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->modifier = $objectManager->getObject(
            \Magento\SharedCatalog\Ui\DataProvider\Modifier\PriceByType\Simple::class,
            [
                'priceCalculator' => $this->calculator,
                'request' => $this->request,
            ]
        );
    }

    /**
     * Test modifyData method.
     *
     * @return void
     */
    public function testModifyData()
    {
        $data = [
            'entity_id' => 1,
            'price' => 150,
            'website_id' => 0,
            'sku' => 'sku_1'
        ];
        $result = [
            'entity_id' => 1,
            'new_price' => 100,
            'price' => 150,
            'website_id' => 0,
            'sku' => 'sku_1'
        ];
        $this->request->expects($this->once())
            ->method('getParam')
            ->with(\Magento\SharedCatalog\Model\Form\Storage\UrlBuilder::REQUEST_PARAM_CONFIGURE_KEY)
            ->willReturn('some_key');
        $this->calculator->expects($this->once())
            ->method('calculateNewPriceForProduct')
            ->with('some_key', 'sku_1', 150, 0)
            ->willReturn(100);

        $this->assertEquals($result, $this->modifier->modifyData($data));
    }

    /**
     * Test modifyData method without entity_id in data.
     *
     * @return void
     */
    public function testModifyDataWitoutItem()
    {
        $data = [
            'price' => 150,
            'website_id' => 0,
        ];
        $this->calculator->expects($this->never())->method('calculateNewPriceForProduct');

        $this->assertEquals($data, $this->modifier->modifyData($data));
    }

    /**
     * Test modifyMeta method.
     *
     * @return void
     */
    public function testModifyMeta()
    {
        $data = ['modifyMeta'];
        $this->assertEquals($data, $this->modifier->modifyMeta($data));
    }
}
