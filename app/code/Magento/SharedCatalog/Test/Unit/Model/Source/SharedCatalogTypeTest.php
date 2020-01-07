<?php

namespace Magento\SharedCatalog\Test\Unit\Model\Source;

/**
 * Class SharedCatalogTypeTest
 */
class SharedCatalogTypeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\SharedCatalog\Model\SharedCatalog|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $sharedCatalog;

    /**
     * @var \Magento\SharedCatalog\Model\Source\SharedCatalogType|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $sharedCatalogTypeMock;

    /**
     * Set up
     */
    protected function setUp()
    {
        $this->sharedCatalog = $this->createMock(\Magento\SharedCatalog\Model\SharedCatalog::class);
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->sharedCatalogTypeMock = $objectManager->getObject(
            \Magento\SharedCatalog\Model\Source\SharedCatalogType::class,
            [
                'sharedCatalog' => $this->sharedCatalog,
            ]
        );
    }

    /**
     * Test for method toOptionArray
     */
    public function testToOptionArray()
    {
        $result = [
            [
                'label' => __('Public'),
                'value' => \Magento\SharedCatalog\Api\Data\SharedCatalogInterface::TYPE_PUBLIC,
            ],
            [
                'label' => __('Custom'),
                'value' => \Magento\SharedCatalog\Api\Data\SharedCatalogInterface::TYPE_CUSTOM,
            ]
        ];
        $availableTypes = [
            \Magento\SharedCatalog\Api\Data\SharedCatalogInterface::TYPE_PUBLIC => __('Public'),
            \Magento\SharedCatalog\Api\Data\SharedCatalogInterface::TYPE_CUSTOM => __('Custom')
        ];
        $this->sharedCatalog
            ->expects($this->any())
            ->method('getAvailableTypes')
            ->will($this->returnValue($availableTypes));
        $this->assertEquals($result, $this->sharedCatalogTypeMock->toOptionArray());
    }
}
