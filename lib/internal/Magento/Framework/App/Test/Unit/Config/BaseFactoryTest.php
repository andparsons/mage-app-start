<?php
namespace Magento\Framework\App\Test\Unit\Config;

class BaseFactoryTest extends \Magento\Framework\TestFramework\Unit\AbstractFactoryTestCase
{
    protected function setUp()
    {
        $this->instanceClassName = \Magento\Framework\App\Config\Base::class;
        $this->factoryClassName = \Magento\Framework\App\Config\BaseFactory::class;
        parent::setUp();
    }
}
