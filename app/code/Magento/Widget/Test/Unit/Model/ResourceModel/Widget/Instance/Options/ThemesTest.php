<?php
namespace Magento\Widget\Test\Unit\Model\ResourceModel\Widget\Instance\Options;

use Magento\Widget\Model\ResourceModel\Widget\Instance\Options\Themes;
use Magento\Theme\Model\ResourceModel\Theme\Collection as ThemeCollection;
use Magento\Theme\Model\ResourceModel\Theme\CollectionFactory as ThemeCollectionFactory;

/**
 * Test class for \Magento\Widget\Model\ResourceModel\Widget\Instance\Options\Themes
 */
class ThemesTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Themes
     */
    private $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $themeCollectionFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $themeCollectionMock;

    protected function setUp()
    {
        $this->themeCollectionMock = $this->createMock(ThemeCollection::class);
        $this->themeCollectionFactoryMock = $this->createPartialMock(ThemeCollectionFactory::class, ['create']);
        $this->model = new Themes(
            $this->themeCollectionFactoryMock
        );
    }

    public function testToOptionArray()
    {
        $expectedResult = [
            1 => 'Theme Label',
        ];
        $this->themeCollectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->themeCollectionMock);

        $this->themeCollectionMock->expects($this->once())->method('loadRegisteredThemes')->willReturnSelf();
        $this->themeCollectionMock->expects($this->once())->method('toOptionHash')->willReturn($expectedResult);

        $this->assertEquals($expectedResult, $this->model->toOptionArray());
    }
}
