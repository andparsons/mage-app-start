<?php
namespace Magento\SharedCatalog\Test\Unit\Model\Permissions;

use Magento\CatalogPermissions\App\ConfigInterface;
use Magento\Config\Model\Config;
use Magento\Config\Model\Config\Factory as ConfigFactory;
use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\SharedCatalog\Model\Permissions\Config as PermissionsConfig;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for Magento\SharedCatalog\Model\CategoryPermissions class.
 */
class ConfigTest extends TestCase
{
    /**
     * @var ReinitableConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $config;

    /**
     * @var ConfigFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configFactory;

    /**
     * @var PermissionsConfig
     */
    private $model;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->config = $this->createMock(ReinitableConfigInterface::class);
        $this->configFactory = $this->createMock(ConfigFactory::class);

        $objectManager = new ObjectManagerHelper($this);
        $this->model = $objectManager->getObject(
            PermissionsConfig::class,
            [
                'config' => $this->config,
                'configFactory' => $this->configFactory,
            ]
        );
    }

    /**
     * Test enable method.
     *
     * @return void
     */
    public function testEnable()
    {
        $scopeId = 2;

        $config1 = $this->createMock(Config::class);
        $config2 = $this->createMock(Config::class);
        $this->configFactory->expects($this->exactly(2))
            ->method('create')
            ->willReturnOnConsecutiveCalls($config1, $config2);

        $config1->expects($this->exactly(4))
            ->method('setDataByPath')
            ->withConsecutive(
                [ConfigInterface::XML_PATH_ENABLED, 1],
                [ConfigInterface::XML_PATH_GRANT_CATALOG_CATEGORY_VIEW, ConfigInterface::GRANT_ALL],
                [ConfigInterface::XML_PATH_GRANT_CATALOG_PRODUCT_PRICE, ConfigInterface::GRANT_ALL],
                [ConfigInterface::XML_PATH_GRANT_CHECKOUT_ITEMS, ConfigInterface::GRANT_ALL]
            );
        $config1->expects($this->once())
            ->method('save');

        $config2->expects($this->exactly(3))
            ->method('setDataByPath')
            ->withConsecutive(
                [ConfigInterface::XML_PATH_GRANT_CATALOG_CATEGORY_VIEW, ConfigInterface::GRANT_ALL],
                [ConfigInterface::XML_PATH_GRANT_CATALOG_PRODUCT_PRICE, ConfigInterface::GRANT_ALL],
                [ConfigInterface::XML_PATH_GRANT_CHECKOUT_ITEMS, ConfigInterface::GRANT_ALL]
            );
        $config2->expects($this->once())
            ->method('save');

        $this->model->enable($scopeId);
    }
}
