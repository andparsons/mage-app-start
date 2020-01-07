<?php
declare(strict_types=1);

namespace Magento\ServicesId\Test\Unit\Model\Config\Source;

use Magento\ServicesId\Model\Config\Source\Environment;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * A unit test for the Environment store configuration dropdown source provider
 */
class EnvironmentTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Environment
     */
    private $environment;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->environment = $objectManager->getObject(Environment::class);
    }

    public function testToOptionArray()
    {
        $options =  [
            [
                'value' => Environment::NON_PRODUCTION_VALUE,
                'label' => __('Testing')
            ],
            [
                'value' => Environment::PRODUCTION_VALUE,
                'label' => __('Production')
            ],
        ];
        $this->assertEquals($options, $this->environment->toOptionArray());
    }
}
