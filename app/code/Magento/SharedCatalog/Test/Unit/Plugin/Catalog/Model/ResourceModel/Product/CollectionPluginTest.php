<?php

namespace Magento\SharedCatalog\Test\Unit\Plugin\Catalog\Model\ResourceModel\Product;

use Magento\Company\Model\CompanyContext;
use Magento\SharedCatalog\Model\Config as SharedCatalogConfig;
use Magento\SharedCatalog\Model\CustomerGroupManagement;
use Magento\SharedCatalog\Plugin\Catalog\Model\ResourceModel\Product\CollectionPlugin;
use Magento\SharedCatalog\Plugin\Catalog\Model\ResourceModel\Product\CollectionPlugin as ProductCollectionPlugin;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class CollectionPluginTest.
 */
class CollectionPluginTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var CompanyContext|\PHPUnit\Framework\MockObject\MockObject
     */
    private $companyContext;

    /**
     * @var SharedCatalogConfig|\PHPUnit_Framework_MockObject_MockObject
     */
    private $config;

    /**
     * @var CustomerGroupManagement|\PHPUnit\Framework\MockObject\MockObject
     */
    private $customerGroupManagement;

    /**
     * @var StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManager;

    /**
     * @var CollectionPlugin
     */
    private $collectionPlugin;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->companyContext = $this->createMock(CompanyContext::class);
        $this->config = $this->createPartialMock(SharedCatalogConfig::class, ['isActive']);
        $this->customerGroupManagement = $this->createMock(CustomerGroupManagement::class);
        $this->storeManager = $this->createMock(StoreManagerInterface::class);

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->collectionPlugin = $objectManager->getObject(
            ProductCollectionPlugin::class,
            [
                'companyContext' => $this->companyContext,
                'config' => $this->config,
                'customerGroupManagement' => $this->customerGroupManagement,
                'storeManager' => $this->storeManager,
            ]
        );
    }

    /**
     * Test for beforeLoad().
     *
     * @return void
     */
    public function testBeforeLoad()
    {
        $customerGroupId = 2;
        $this->companyContext->expects($this->once())
            ->method('getCustomerGroupId')
            ->willReturn($customerGroupId);
        $this->customerGroupManagement->expects($this->once())
            ->method('isMasterCatalogAvailable')
            ->with($customerGroupId)
            ->willReturn(false);

        $website = $this->createMock(\Magento\Store\Api\Data\WebsiteInterface::class);
        $this->storeManager->expects($this->atLeastOnce())->method('getWebsite')->willReturn($website);
        $subject = $this->createMock(\Magento\Catalog\Model\ResourceModel\Product\Collection::class);
        $subject->expects($this->any())->method('isLoaded')->willReturn(false);
        $this->config->expects($this->once())->method('isActive')->willReturn(true);
        $subject->expects($this->once())->method('joinTable')->will($this->returnSelf());
        $result = $this->collectionPlugin->beforeLoad($subject);
        $this->assertEquals($result, [false, false]);
    }
}
