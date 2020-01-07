<?php

/**
 * Test class for \Magento\Customer\Block\Widget\Dob
 */
namespace Magento\Customer\Block\Widget;

class DobTest extends \PHPUnit\Framework\TestCase
{
    public function testGetDateFormat()
    {
        $block = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Customer\Block\Widget\Dob::class
        );
        $this->assertNotEmpty($block->getDateFormat());
    }
}
