<?php
namespace Magento\Framework\Search\Test\Unit\Adapter\Mysql\Aggregation\Builder;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class ContainerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectManager;

    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);
    }

    public function testGet()
    {
        $bucketName = 'providerName';
        $bucketValue = 'dataProvider';
        /** @var \Magento\Framework\Search\Adapter\Mysql\Aggregation\Builder\Container $provider */
        $provider = $this->objectManager->getObject(
            \Magento\Framework\Search\Adapter\Mysql\Aggregation\Builder\Container::class,
            ['buckets' => [$bucketName => $bucketValue]]
        );
        $this->assertEquals($bucketValue, $provider->get($bucketName));
    }
}
