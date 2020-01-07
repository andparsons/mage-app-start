<?php
namespace Magento\Framework\Filter\Test\Unit;

class StripTagsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @covers \Magento\Framework\Filter\StripTags::filter
     */
    public function testStripTags()
    {
        $stripTags = new \Magento\Framework\Filter\StripTags(new \Magento\Framework\Escaper());
        $this->assertEquals('three', $stripTags->filter('<two>three</two>'));
    }
}
