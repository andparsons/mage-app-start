<?php
namespace Magento\Framework\App\Test\Unit\Config;

class DataFactoryTest extends \Magento\Framework\TestFramework\Unit\AbstractFactoryTestCase
{
    protected function setUp()
    {
        $this->instanceClassName = \Magento\Framework\App\Config\Data::class;
        $this->factoryClassName = \Magento\Framework\App\Config\DataFactory::class;
        parent::setUp();
    }
}
