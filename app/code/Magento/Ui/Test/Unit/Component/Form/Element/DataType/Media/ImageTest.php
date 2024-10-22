<?php

declare(strict_types=1);

namespace Magento\Ui\Test\Unit\Component\Form\Element\DataType\Media;

use Magento\Ui\Component\Form\Element\DataType\Media\Image;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Framework\File\Size;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class ImageTest extends \Magento\Ui\Test\Unit\Component\Form\Element\DataType\MediaTest
{
    /**
     * @var StoreInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $store;

    /**
     * @var StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManager;

    /**
     * @var Size|\PHPUnit_Framework_MockObject_MockObject
     */
    private $fileSize;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var Image
     */
    private $image;

    public function setUp()
    {
        parent::setUp();

        $this->processor = $this->getMockBuilder(\Magento\Framework\View\Element\UiComponent\Processor::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->context->expects($this->atLeastOnce())->method('getProcessor')->willReturn($this->processor);

        $this->storeManager = $this->getMockForAbstractClass(StoreManagerInterface::class);

        $this->store = $this->getMockBuilder(StoreInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->store->expects($this->any())->method('getId')->willReturn(0);

        $this->storeManager->expects($this->any())->method('getStore')->willReturn($this->store);

        $this->fileSize = $this->getMockBuilder(Size::class)->getMock();

        $this->objectManager = new ObjectManager($this);

        $this->image = $this->objectManager->getObject(Image::class, [
            'context' => $this->context,
            'storeManager' => $this->storeManager,
            'fileSize' => $this->fileSize
        ]);

        $this->image->setData([
            'config' => [
                'initialMediaGalleryOpenSubpath' => 'open/sesame',
            ],
        ]);
    }

    /**
     * @dataProvider prepareDataProvider
     */
    public function testPrepare()
    {
        $this->assertExpectedPreparedConfiguration(...func_get_args());
    }

    /**
     * Data provider for testPrepare
     * @return array
     */
    public function prepareDataProvider(): array
    {
        return [
            [['maxFileSize' => 10], 10, ['maxFileSize' => 10]],
            [['maxFileSize' => null], 10, ['maxFileSize' => 10]],
            [['maxFileSize' => 10], 5, ['maxFileSize' => 5]],
            [['maxFileSize' => 10], 20, ['maxFileSize' => 10]],
            [['maxFileSize' => 0], 10, ['maxFileSize' => 10]],
        ];
    }

    /**
     * @param array $initialConfig
     * @param int $maxFileSizeSupported
     * @param array $expectedPreparedConfig
     */
    private function assertExpectedPreparedConfiguration(
        array $initialConfig,
        int $maxFileSizeSupported,
        array $expectedPreparedConfig
    ) {
        $this->image->setData(array_merge_recursive(['config' => $initialConfig], $this->image->getData()));

        $this->fileSize->expects($this->any())->method('getMaxFileSize')->willReturn($maxFileSizeSupported);

        $this->image->prepare();

        $actualRelevantPreparedConfig = array_intersect_key($this->image->getConfiguration(), $initialConfig);

        $this->assertEquals(
            $expectedPreparedConfig,
            $actualRelevantPreparedConfig
        );
    }
}
