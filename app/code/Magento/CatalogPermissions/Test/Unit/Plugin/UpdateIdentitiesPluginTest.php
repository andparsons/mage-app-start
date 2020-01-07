<?php
declare(strict_types=1);

namespace Magento\CatalogPermissions\Test\Unit\Plugin;

use Magento\Catalog\Block\Product\View;
use Magento\Catalog\Model\Category;
use Magento\CatalogPermissions\App\ConfigInterface;
use Magento\CatalogPermissions\Plugin\UpdateIdentitiesPlugin;
use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Class UpdateIdentitiesPluginTest
 */
class UpdateIdentitiesPluginTest extends \PHPUnit\Framework\TestCase
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
     * @var UpdateIdentitiesPlugin
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

        $objectManager = new ObjectManager($this);
        $this->plugin = $objectManager->getObject(
            UpdateIdentitiesPlugin::class,
            [
                'coreRegistry' => $this->coreRegistryMock,
                'permissionsConfig' => $this->permissionsConfigMock,
            ]
        );
    }

    /**
     * @param bool $isEnabled
     * @param array $identities
     * @param array $expected
     * @return void
     * @dataProvider afterGetIdentitiesDataProvider
     */
    public function testAfterGetIdentities(
        bool $isEnabled,
        array $identities,
        array $expected
    ) {
        $categoryId = 2;
        /** @var View|\PHPUnit_Framework_MockObject_MockObject $viewMock */
        $viewMock = $this->getMockBuilder(View::class)
            ->disableOriginalConstructor()
            ->getMock();

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
            ->with('current_category')
            ->willReturn($categoryMock);

        $actual = $this->plugin->afterGetIdentities($viewMock, $identities);
        $this->assertEquals($expected, $actual);
    }

    /**
     * Data provider
     *
     * @return array
     */
    public function afterGetIdentitiesDataProvider()
    {
        return [
            [true, ['cat_p_1', 'cat_p_2'], ['cat_p_1', 'cat_p_2', 'cat_c_2']],
            [false, ['cat_p_1', 'cat_p_2'], ['cat_p_1', 'cat_p_2']],
        ];
    }
}
