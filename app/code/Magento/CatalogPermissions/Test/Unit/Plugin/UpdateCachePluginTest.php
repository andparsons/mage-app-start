<?php
declare(strict_types=1);

namespace Magento\CatalogPermissions\Test\Unit\Plugin;

use Magento\Catalog\Model\Category;
use Magento\CatalogPermissions\App\ConfigInterface;
use Magento\CatalogPermissions\Plugin\UpdateCachePlugin;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Customer\Model\Session;

/**
 * Class UpdateCachePluginTest
 */
class UpdateCachePluginTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $permissionsConfigMock;

    /**
     * @var Registry|\PHPUnit_Framework_MockObject_MockObject
     */
    private $coreRegistryMock;

    /**
     * @var Session|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerSession;

    /**
     * @var UpdateCachePlugin
     */
    private $plugin;

    /**
     * Set up
     *
     * @return void
     */
    protected function setUp()
    {
        $this->permissionsConfigMock = $this->getMockBuilder(ConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->coreRegistryMock = $this->getMockBuilder(Registry::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->customerSession = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new ObjectManager($this);
        $this->plugin = $objectManager->getObject(
            UpdateCachePlugin::class,
            [
                'coreRegistry' => $this->coreRegistryMock,
                'customerSession' => $this->customerSession,
                'permissionsConfig' => $this->permissionsConfigMock,
            ]
        );
    }

    /**
     * @param bool $isEnabled
     * @param array $data
     * @param array $expected
     * @return void
     * @dataProvider afterGetDataDataProvider
     */
    public function testAfterGetData($isEnabled, $data, $expected)
    {
        $categoryId = 2;
        $customerGroupId = 3;
        $this->permissionsConfigMock->expects($this->once())
            ->method('isEnabled')
            ->willReturn($isEnabled);

        /** @var Category|\PHPUnit_Framework_MockObject_MockObject $categoryMock */
        $categoryMock = $this->getMockBuilder(Category::class)
            ->disableOriginalConstructor()
            ->getMock();
        $categoryMock->method('getId')
            ->willReturn($categoryId);

        $this->coreRegistryMock->method('registry')
            ->willReturn($categoryMock);

        $this->customerSession->method('getCustomerGroupId')
            ->willReturn($customerGroupId);

        /** @var HttpContext|\PHPUnit_Framework_MockObject_MockObject $httpContext */
        $httpContext = $this->getMockBuilder(HttpContext::class)
            ->disableOriginalConstructor()
            ->getMock();

        $data = $this->plugin->afterGetData($httpContext, $data);
        $this->assertEquals($expected, $data);
    }

    /**
     * Data provider
     *
     * @return array
     */
    public function afterGetDataDataProvider()
    {
        return [
            [
                true,
                ['string' => 'abc', 'int' => 42, 'bool' => true,],
                ['string' => 'abc', 'int' => 42, 'bool' => true, 'customer_group' => 3, 'category' => 2],
            ],
            [
                false,
                ['string' => 'abc', 'int' => 42, 'bool' => true,],
                ['string' => 'abc', 'int' => 42, 'bool' => true,],
            ]
        ];
    }
}
