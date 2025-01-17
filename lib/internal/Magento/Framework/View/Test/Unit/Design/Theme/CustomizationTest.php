<?php

/**
 * Test of theme customization model
 */
namespace Magento\Framework\View\Test\Unit\Design\Theme;

use \Magento\Framework\View\Design\Theme\Customization;

class CustomizationTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Customization
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $fileProvider;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $customizationPath;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $theme;

    protected function setUp()
    {
        $this->fileProvider = $this->createMock(\Magento\Framework\View\Design\Theme\FileProviderInterface::class);
        $collectionFactory = $this->createPartialMock(
            \Magento\Theme\Model\ResourceModel\Theme\File\CollectionFactory::class,
            ['create']
        );
        $collectionFactory->expects($this->any())->method('create')->will($this->returnValue($this->fileProvider));
        $this->customizationPath = $this->createMock(\Magento\Framework\View\Design\Theme\Customization\Path::class);
        $this->theme = $this->createPartialMock(\Magento\Theme\Model\Theme::class, ['__wakeup', 'save', 'load']);

        $this->model = new Customization($this->fileProvider, $this->customizationPath, $this->theme);
    }

    protected function tearDown()
    {
        $this->model = null;
        $this->fileProvider = null;
        $this->customizationPath = null;
        $this->theme = null;
    }

    /**
     * @covers \Magento\Framework\View\Design\Theme\Customization::getFiles
     * @covers \Magento\Framework\View\Design\Theme\Customization::__construct
     */
    public function testGetFiles()
    {
        $this->fileProvider->expects(
            $this->once()
        )->method(
            'getItems'
        )->with(
            $this->theme
        )->will(
            $this->returnValue([])
        );
        $this->assertEquals([], $this->model->getFiles());
    }

    /**
     * @covers \Magento\Framework\View\Design\Theme\Customization::getFilesByType
     */
    public function testGetFilesByType()
    {
        $type = 'sample-type';
        $this->fileProvider->expects(
            $this->once()
        )->method(
            'getItems'
        )->with(
            $this->theme,
            ['file_type' => $type]
        )->will(
            $this->returnValue([])
        );
        $this->assertEquals([], $this->model->getFilesByType($type));
    }

    /**
     * @covers \Magento\Framework\View\Design\Theme\Customization::generateFileInfo
     */
    public function testGenerationOfFileInfo()
    {
        $file = $this->createPartialMock(\Magento\Theme\Model\Theme\File::class, ['__wakeup', 'getFileInfo']);
        $file->expects($this->once())->method('getFileInfo')->will($this->returnValue(['sample-generation']));
        $this->assertEquals([['sample-generation']], $this->model->generateFileInfo([$file]));
    }

    /**
     * @covers \Magento\Framework\View\Design\Theme\Customization::getCustomizationPath
     */
    public function testGetCustomizationPath()
    {
        $this->customizationPath->expects(
            $this->once()
        )->method(
            'getCustomizationPath'
        )->with(
            $this->theme
        )->will(
            $this->returnValue('path')
        );
        $this->assertEquals('path', $this->model->getCustomizationPath());
    }

    /**
     * @covers \Magento\Framework\View\Design\Theme\Customization::getThemeFilesPath
     * @dataProvider getThemeFilesPathDataProvider
     * @param string $type
     * @param string $expectedMethod
     */
    public function testGetThemeFilesPath($type, $expectedMethod)
    {
        $this->theme->setData(['id' => 123, 'type' => $type, 'area' => 'area51', 'theme_path' => 'theme_path']);
        $this->customizationPath->expects(
            $this->once()
        )->method(
            $expectedMethod
        )->with(
            $this->theme
        )->will(
            $this->returnValue('path')
        );
        $this->assertEquals('path', $this->model->getThemeFilesPath());
    }

    /**
     * @return array
     */
    public function getThemeFilesPathDataProvider()
    {
        return [
            'physical' => [\Magento\Framework\View\Design\ThemeInterface::TYPE_PHYSICAL, 'getThemeFilesPath'],
            'virtual' => [\Magento\Framework\View\Design\ThemeInterface::TYPE_VIRTUAL, 'getCustomizationPath'],
            'staging' => [\Magento\Framework\View\Design\ThemeInterface::TYPE_STAGING, 'getCustomizationPath']
        ];
    }

    /**
     * @covers \Magento\Framework\View\Design\Theme\Customization::getCustomViewConfigPath
     */
    public function testGetCustomViewConfigPath()
    {
        $this->customizationPath->expects(
            $this->once()
        )->method(
            'getCustomViewConfigPath'
        )->with(
            $this->theme
        )->will(
            $this->returnValue('path')
        );
        $this->assertEquals('path', $this->model->getCustomViewConfigPath());
    }

    /**
     * @covers \Magento\Framework\View\Design\Theme\Customization::reorder
     * @dataProvider customFileContent
     */
    public function testReorder($sequence, $filesContent)
    {
        $files = [];
        $type = 'sample-type';
        foreach ($filesContent as $fileContent) {
            $file = $this->createPartialMock(\Magento\Theme\Model\Theme\File::class, ['__wakeup', 'save']);
            $file->expects($fileContent['isCalled'])->method('save')->will($this->returnSelf());
            $file->setData($fileContent['content']);
            $files[] = $file;
        }
        $this->fileProvider->expects(
            $this->once()
        )->method(
            'getItems'
        )->with(
            $this->theme,
            ['file_type' => $type]
        )->will(
            $this->returnValue($files)
        );
        $this->assertInstanceOf(
            \Magento\Framework\View\Design\Theme\CustomizationInterface::class,
            $this->model->reorder($type, $sequence)
        );
    }

    /**
     * Reorder test content
     *
     * @return array
     */
    public function customFileContent()
    {
        return [
            [
                'sequence' => [3, 2, 1],
                'filesContent' => [
                    [
                        'isCalled' => $this->once(),
                        'content' => [
                            'id' => 1,
                            'theme_id' => 123,
                            'file_path' => 'css/custom_file1.css',
                            'content' => 'css content',
                            'sort_order' => 1,
                        ],
                    ],
                    [
                        'isCalled' => $this->never(),
                        'content' => [
                            'id' => 2,
                            'theme_id' => 123,
                            'file_path' => 'css/custom_file2.css',
                            'content' => 'css content',
                            'sort_order' => 1,
                        ]
                    ],
                    [
                        'isCalled' => $this->once(),
                        'content' => [
                            'id' => 3,
                            'theme_id' => 123,
                            'file_path' => 'css/custom_file3.css',
                            'content' => 'css content',
                            'sort_order' => 5,
                        ]
                    ],
                ],
            ]
        ];
    }

    /**
     * @covers \Magento\Framework\View\Design\Theme\Customization::delete
     */
    public function testDelete()
    {
        $file = $this->createPartialMock(\Magento\Theme\Model\Theme\File::class, ['__wakeup', 'delete']);
        $file->expects($this->once())->method('delete')->will($this->returnSelf());
        $file->setData(
            [
                'id' => 1,
                'theme_id' => 123,
                'file_path' => 'css/custom_file1.css',
                'content' => 'css content',
                'sort_order' => 1,
            ]
        );
        $this->fileProvider->expects(
            $this->once()
        )->method(
            'getItems'
        )->with(
            $this->theme
        )->will(
            $this->returnValue([$file])
        );

        $this->assertInstanceOf(
            \Magento\Framework\View\Design\Theme\CustomizationInterface::class,
            $this->model->delete([1])
        );
    }
}
