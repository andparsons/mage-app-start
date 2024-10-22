<?php

namespace Magento\Wishlist\Test\Unit\Block\Adminhtml\Widget\Grid\Column\Filter;

use \Magento\Wishlist\Block\Adminhtml\Widget\Grid\Column\Filter\Text;

class TextTest extends \PHPUnit\Framework\TestCase
{
    /** @var Text | \PHPUnit_Framework_MockObject_MockObject */
    private $textFilterBlock;

    protected function setUp()
    {
        $this->textFilterBlock = (new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this))->getObject(
            \Magento\Wishlist\Block\Adminhtml\Widget\Grid\Column\Filter\Text::class
        );
    }

    public function testGetCondition()
    {
        $value = "test";
        $this->textFilterBlock->setValue($value);
        $this->assertSame(["like" => $value], $this->textFilterBlock->getCondition());
    }
}
