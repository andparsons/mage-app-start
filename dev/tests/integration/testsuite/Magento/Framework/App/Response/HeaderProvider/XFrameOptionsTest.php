<?php
namespace Magento\Framework\App\Response\HeaderProvider;

class XFrameOptionsTest extends AbstractHeaderTestCase
{
    public function testHeaderPresent()
    {
        $this->assertHeaderPresent('X-Frame-Options', 'SAMEORIGIN');
    }
}
