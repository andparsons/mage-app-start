<?php
namespace Magento\SharedCatalog\Test\Unit\Model\Source;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Unit tests for IsCurrent source model.
 */
class IsCurrentTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var \Magento\SharedCatalog\Model\Source\IsCurrent
     */
    private $isCurrent;

    /**
     * Set up.
     *
     * @return void
     */
    public function setUp()
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->isCurrent = $this->objectManagerHelper->getObject(
            \Magento\SharedCatalog\Model\Source\IsCurrent::class,
            []
        );
    }

    /**
     * @return void
     */
    public function testToOptionArray()
    {
        $result = [
            [
                'label' => __('Yes'),
                'value' => 1
            ],
            [
                'label' => __('No'),
                'value' => 0
            ]
        ];

        $this->assertEquals($result, $this->isCurrent->toOptionArray());
    }
}
