<?php
namespace Magento\Catalog\Model;

use Magento\Catalog\Model\Config;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Helper\CacheCleaner;

class ConfigTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Config
     */
    private $config;
    
    /**
     * @var ObjectManager
     */
    private $objectManager;
    
    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->config = $this->objectManager->get(Config::class);
    }

    public function testGetEntityAttributeCodes()
    {
        $entityType = 'catalog_product';
        CacheCleaner::cleanAll();
        $this->assertEquals(
            $this->config->getEntityAttributeCodes($entityType),
            $this->config->getEntityAttributeCodes($entityType)
        );
    }

    public function testGetAttribute()
    {
        $entityType = 'catalog_product';
        $attributeCode = 'color';
        CacheCleaner::cleanAll();
        $this->assertEquals(
            $this->config->getAttribute($entityType, $attributeCode),
            $this->config->getAttribute($entityType, $attributeCode)
        );
    }

    public function testGetEntityType()
    {
        $entityType = 'catalog_product';
        CacheCleaner::cleanAll();
        $this->assertEquals(
            $this->config->getEntityType($entityType),
            $this->config->getEntityType($entityType)
        );
    }
}
