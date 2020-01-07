<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SharedCatalog\Test\Unit\Ui\DataProvider\Modifier;

/**
 * Test for PriceByType modifier.
 */
class PriceByTypeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\SharedCatalog\Ui\DataProvider\Modifier\PriceByType
     */
    private $modifier;

    /**
     * @var \Magento\Ui\DataProvider\Modifier\ModifierInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $complexModifier;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->complexModifier = $this->getMockBuilder(\Magento\Ui\DataProvider\Modifier\ModifierInterface::class)
            ->disableOriginalConstructor()->getMock();
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->modifier = $objectManager->getObject(
            \Magento\SharedCatalog\Ui\DataProvider\Modifier\PriceByType::class,
            [
                'modifiers' => ['simple' => $this->complexModifier],
            ]
        );
    }

    /**
     * Test modifyData method without items.
     * @return void
     */
    public function testModifyDataWithoutItems()
    {
        $data = ['items' => null];
        $this->complexModifier->expects($this->never())->method('modifyData');
        $this->assertEquals($data, $this->modifier->modifyData($data));
    }

    /**
     * Test modifyData method with simple product items.
     * @return void
     */
    public function testModifyDataWithSimpleItems()
    {
        $data = ['items' => [['type_id' => 'simple']]];
        $dataExpect = ['items' => [['type_id' => 'simple', 'website_id' => 0]]];
        $this->complexModifier->expects($this->once())->method('modifyData')->willReturnArgument(0);
        $this->assertEquals($dataExpect, $this->modifier->modifyData($data));
    }

    /**
     * Test modifyData method without simple items.
     * @return void
     */
    public function testModifyDataWithOtherItems()
    {
        $data = ['items' => [['type_id' => 'other']]];
        $dataExpect = ['items' => [['type_id' => 'other', 'website_id' => 0]]];
        $this->complexModifier->expects($this->once())->method('modifyData')->willReturnArgument(0);
        $this->assertEquals($dataExpect, $this->modifier->modifyData($data));
    }

    /**
     * Test modifyMeta method.
     * @return void
     */
    public function testModifyMeta()
    {
        $data = ['modifyMeta'];
        $this->complexModifier->expects($this->once())->method('modifyMeta')->willReturnArgument(0);
        $this->assertEquals($data, $this->modifier->modifyMeta($data));
    }
}
