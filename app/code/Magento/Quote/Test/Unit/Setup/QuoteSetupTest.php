<?php

namespace Magento\Quote\Test\Unit\Setup;

use Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Test for Quote module setup model.
 *
 * @package Magento\Quote\Test\Unit\Setup
 */
class QuoteSetupTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Quote\Setup\QuoteSetup
     */
    private $model;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var \Magento\Framework\Setup\ModuleDataSetupInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $moduleDataSetupMock;

    /**
     * @var \Magento\Eav\Model\Entity\Setup\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $contextMock;

    /**
     * @var \Magento\Framework\App\CacheInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cacheMock;

    /**
     * @var CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $collectionFactoryMock;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $scopeConfigMock;

    protected function setUp()
    {
        $this->moduleDataSetupMock = $this->getMockBuilder(\Magento\Framework\Setup\ModuleDataSetupInterface::class)
            ->getMockForAbstractClass();
        $this->contextMock = $this->getMockBuilder(\Magento\Eav\Model\Entity\Setup\Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->cacheMock = $this->getMockBuilder(\Magento\Framework\App\CacheInterface::class)
            ->getMockForAbstractClass();
        $this->collectionFactoryMock = $this->getMockBuilder(CollectionFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->scopeConfigMock = $this->getMockBuilder(\Magento\Framework\App\Config\ScopeConfigInterface::class)
            ->getMockForAbstractClass();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->model = $this->objectManagerHelper->getObject(
            \Magento\Quote\Setup\QuoteSetup::class,
            [
                'setup' => $this->moduleDataSetupMock,
                'context' => $this->contextMock,
                'cache' => $this->cacheMock,
                'attrGroupCollectionFactory' => $this->collectionFactoryMock,
                'config' => $this->scopeConfigMock
            ]
        );
    }

    public function testGetConnection()
    {
        $this->moduleDataSetupMock->expects($this->once())
            ->method('getConnection')
            ->with('checkout');
        $this->model->getConnection();
    }
}
