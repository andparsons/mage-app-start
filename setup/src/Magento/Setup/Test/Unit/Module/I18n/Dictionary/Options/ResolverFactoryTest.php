<?php
namespace Magento\Setup\Test\Unit\Module\I18n\Dictionary\Options;

/**
 * Class ResolverTest
 */
class ResolverFactoryTest extends \PHPUnit\Framework\TestCase
{
    public function testCreate()
    {
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        /** @var \Magento\Setup\Module\I18n\Dictionary\Options\ResolverFactory $resolverFactory */
        $resolverFactory = $objectManagerHelper
            ->getObject(\Magento\Setup\Module\I18n\Dictionary\Options\ResolverFactory::class);
        $this->assertInstanceOf(
            \Magento\Setup\Module\I18n\Dictionary\Options\ResolverFactory::DEFAULT_RESOLVER,
            $resolverFactory->create('some_dir', true)
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage stdClass doesn't implement ResolverInterface
     */
    public function testCreateException()
    {
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        /** @var \Magento\Setup\Module\I18n\Dictionary\Options\ResolverFactory $resolverFactory */
        $resolverFactory = $objectManagerHelper->getObject(
            \Magento\Setup\Module\I18n\Dictionary\Options\ResolverFactory::class,
            [
                'resolverClass' => 'stdClass'
            ]
        );
        $resolverFactory->create('some_dir', true);
    }
}
