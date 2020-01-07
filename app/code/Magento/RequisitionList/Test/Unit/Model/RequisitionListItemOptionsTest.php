<?php

namespace Magento\RequisitionList\Test\Unit\Model;

/**
 * Unit test for RequisitionListItemOptions model.
 */
class RequisitionListItemOptionsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Api\Data\ProductInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $product;

    /**
     * @var \Magento\Catalog\Model\Product\Configuration\Item\Option\OptionInterface
     *      |\PHPUnit_Framework_MockObject_MockObject
     */
    private $option;

    /**
     * @var string
     */
    private $optionCode = 'option_code';

    /**
     * @var \Magento\RequisitionList\Model\RequisitionListItemOptions
     */
    private $requisitionListItemOptions;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->product = $this->getMockBuilder(\Magento\Catalog\Api\Data\ProductInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->option = $this
            ->getMockBuilder(\Magento\Catalog\Model\Product\Configuration\Item\Option\OptionInterface::class)
            ->disableOriginalConstructor()->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->requisitionListItemOptions = $objectManager->getObject(
            \Magento\RequisitionList\Model\RequisitionListItemOptions::class,
            [
                '_data' => [
                    'product' => $this->product,
                    'options' => [$this->optionCode => $this->option],
                ],
            ]
        );
    }

    /**
     * Test for getProduct method.
     *
     * @return void
     */
    public function testGetProduct()
    {
        $this->assertEquals($this->product, $this->requisitionListItemOptions->getProduct());
    }

    /**
     * Test for getOptionByCode method.
     *
     * @return void
     */
    public function testGetOptionByCode()
    {
        $this->assertEquals($this->option, $this->requisitionListItemOptions->getOptionByCode($this->optionCode));
    }

    /**
     * Test for getFileDownloadParams method.
     *
     * @return void
     */
    public function testGetFileDownloadParams()
    {
        $this->assertNull($this->requisitionListItemOptions->getFileDownloadParams());
    }
}
