<?php
declare(strict_types=1);

namespace Magento\Reports\Test\Unit\Model\ResourceModel\Product;

use Magento\Catalog\Model\Indexer\Product\Flat\State;
use Magento\Catalog\Model\Product\Attribute\DefaultAttributes;
use Magento\Catalog\Model\Product\OptionFactory;
use Magento\Catalog\Model\Product\Type as ProductType;
use Magento\Catalog\Model\ResourceModel\Helper;
use Magento\Catalog\Model\ResourceModel\Product as ResourceProduct;
use Magento\Catalog\Model\ResourceModel\Product\Collection\ProductLimitation;
use Magento\Catalog\Model\ResourceModel\Product\Collection\ProductLimitationFactory;
use Magento\Catalog\Model\ResourceModel\Url;
use Magento\Customer\Api\GroupManagementInterface;
use Magento\Customer\Model\Session;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Context;
use Magento\Eav\Model\Entity\Type;
use Magento\Eav\Model\EntityFactory as EavEntityFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactory;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Module\Manager as Manager;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Validator\UniversalFactory;
use Magento\Quote\Model\ResourceModel\Quote\Collection;
use Magento\Reports\Model\Event\TypeFactory;
use Magento\Reports\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Test for Magento\Reports\Model\ResourceModel\Product\Collection.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 *
 */
class CollectionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ProductCollection
     */
    private $collection;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $eventTypeFactoryMock;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $connectionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $resourceMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $selectMock;

    /**
     * SetUp method
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);
        $context = $this->createPartialMock(Context::class, ['getResource', 'getEavConfig']);
        $entityFactoryMock = $this->createMock(EntityFactory::class);
        $loggerMock = $this->createMock(LoggerInterface::class);
        $fetchStrategyMock = $this->createMock(FetchStrategyInterface::class);
        $eventManagerMock = $this->createMock(ManagerInterface::class);
        $eavConfigMock = $this->createMock(Config::class);
        $this->resourceMock = $this->createPartialMock(ResourceConnection::class, ['getTableName', 'getConnection']);
        $eavEntityFactoryMock = $this->createMock(EavEntityFactory::class);
        $resourceHelperMock = $this->createMock(Helper::class);
        $universalFactoryMock = $this->createMock(UniversalFactory::class);
        $storeManagerMock = $this->createPartialMockForAbstractClass(
            StoreManagerInterface::class,
            ['getStore', 'getId']
        );
        $moduleManagerMock = $this->createMock(Manager::class);
        $productFlatStateMock = $this->createMock(State::class);
        $scopeConfigMock = $this->createMock(ScopeConfigInterface::class);
        $optionFactoryMock = $this->createMock(OptionFactory::class);
        $catalogUrlMock = $this->createMock(Url::class);
        $localeDateMock = $this->createMock(TimezoneInterface::class);
        $customerSessionMock = $this->createMock(Session::class);
        $dateTimeMock = $this->createMock(DateTime::class);
        $groupManagementMock = $this->createMock(GroupManagementInterface::class);
        $eavConfig = $this->createPartialMock(Config::class, ['getEntityType']);
        $entityType = $this->createMock(Type::class);

        $eavConfig->expects($this->atLeastOnce())->method('getEntityType')->willReturn($entityType);
        $context->expects($this->atLeastOnce())->method('getResource')->willReturn($this->resourceMock);
        $context->expects($this->atLeastOnce())->method('getEavConfig')->willReturn($eavConfig);

        $defaultAttributes = $this->createPartialMock(DefaultAttributes::class, ['_getDefaultAttributes']);
        $productMock = $this->objectManager->getObject(
            ResourceProduct::class,
            ['context' => $context, 'defaultAttributes' => $defaultAttributes]
        );

        $this->eventTypeFactoryMock = $this->createMock(TypeFactory::class);
        $productTypeMock = $this->createMock(ProductType::class);
        $quoteResourceMock = $this->createMock(Collection::class);
        $this->connectionMock = $this->createPartialMockForAbstractClass(AdapterInterface::class, ['select']);
        $this->selectMock = $this->createPartialMock(
            Select::class,
            [
                'reset',
                'from',
                'join',
                'where',
                'group',
                'order',
                'having',
            ]
        );

        $storeManagerMock->expects($this->atLeastOnce())->method('getStore')->willReturn($storeManagerMock);
        $storeManagerMock->expects($this->atLeastOnce())->method('getId')->willReturn(1);
        $universalFactoryMock->expects($this->atLeastOnce())->method('create')->willReturn($productMock);
        $this->resourceMock->expects($this->atLeastOnce())->method('getTableName')->willReturn('test_table');
        $this->resourceMock->expects($this->atLeastOnce())->method('getConnection')->willReturn($this->connectionMock);
        $this->connectionMock->expects($this->atLeastOnce())->method('select')->willReturn($this->selectMock);

        $productLimitationFactoryMock = $this->createPartialMock(
            ProductLimitationFactory::class,
            ['create']
        );
        $productLimitation = $this->createMock(ProductLimitation::class);
        $productLimitationFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($productLimitation);

        $this->collection = $this->objectManager->getObject(
            ProductCollection::class,
            [
                'entityFactory' => $entityFactoryMock,
                'logger' => $loggerMock,
                'fetchStrategy' => $fetchStrategyMock,
                'eventManager' => $eventManagerMock,
                'eavConfig' => $eavConfigMock,
                'resource' => $this->resourceMock,
                'eavEntityFactory' => $eavEntityFactoryMock,
                'resourceHelper' => $resourceHelperMock,
                'universalFactory' => $universalFactoryMock,
                'storeManager' => $storeManagerMock,
                'moduleManager' => $moduleManagerMock,
                'catalogProductFlatState' => $productFlatStateMock,
                'scopeConfig' => $scopeConfigMock,
                'productOptionFactory' => $optionFactoryMock,
                'catalogUrl' => $catalogUrlMock,
                'localeDate' => $localeDateMock,
                'customerSession' => $customerSessionMock,
                'dateTime' => $dateTimeMock,
                'groupManagement' => $groupManagementMock,
                'product' => $productMock,
                'eventTypeFactory' => $this->eventTypeFactoryMock,
                'productType' => $productTypeMock,
                'quoteResource' => $quoteResourceMock,
                'connection' => $this->connectionMock,
                'productLimitationFactory' => $productLimitationFactoryMock
            ]
        );
    }

    /**
     * Test addViewsCount behavior.
     */
    public function testAddViewsCount()
    {
        $context = $this->createPartialMock(
            \Magento\Framework\Model\ResourceModel\Db\Context::class,
            ['getResources']
        );
        $context->expects($this->atLeastOnce())
            ->method('getResources')
            ->willReturn($this->resourceMock);
        $abstractResourceMock = $this->getMockForAbstractClass(
            \Magento\Framework\Model\ResourceModel\Db\AbstractDb::class,
            ['context' => $context],
            '',
            true,
            true,
            true,
            [
                'getTableName',
                'getConnection',
                'getMainTable',
            ]
        );

        $abstractResourceMock->expects($this->atLeastOnce())
            ->method('getConnection')
            ->willReturn($this->connectionMock);
        $abstractResourceMock->expects($this->atLeastOnce())
            ->method('getMainTable')
            ->willReturn('catalog_product');

        /** @var \Magento\Reports\Model\ResourceModel\Event\Type\Collection $eventTypesCollection */
        $eventTypesCollection = $this->objectManager->getObject(
            \Magento\Reports\Model\ResourceModel\Event\Type\Collection::class,
            ['resource' => $abstractResourceMock]
        );
        $eventTypeMock = $this->createPartialMock(
            \Magento\Reports\Model\Event\Type::class,
            [
                'getEventName',
                'getId',
                'getCollection',
            ]
        );

        $eventTypesCollection->addItem($eventTypeMock);

        $this->eventTypeFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($eventTypeMock);
        $eventTypeMock->expects($this->atLeastOnce())
            ->method('getCollection')
            ->willReturn($eventTypesCollection);
        $eventTypeMock->expects($this->atLeastOnce())
            ->method('getEventName')
            ->willReturn('catalog_product_view');
        $eventTypeMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn(1);

        $this->selectMock->expects($this->atLeastOnce())
            ->method('reset')
            ->willReturn($this->selectMock);
        $this->selectMock->expects($this->atLeastOnce())
            ->method('from')
            ->with(
                ['report_table_views' => 'test_table'],
                ['views' => 'COUNT(report_table_views.event_id)']
            )->willReturn($this->selectMock);
        $this->selectMock->expects($this->atLeastOnce())
            ->method('join')
            ->with(
                ['e' => 'test_table'],
                'e.entity_id = report_table_views.object_id'
            )->willReturn($this->selectMock);
        $this->selectMock->expects($this->atLeastOnce())
            ->method('where')
            ->with('report_table_views.event_type_id = ?', 1)
            ->willReturn($this->selectMock);
        $this->selectMock->expects($this->atLeastOnce())
            ->method('group')
            ->with('e.entity_id')
            ->willReturn($this->selectMock);
        $this->selectMock->expects($this->atLeastOnce())
            ->method('order')
            ->with('views DESC')
            ->willReturn($this->selectMock);
        $this->selectMock->expects($this->atLeastOnce())
            ->method('having')
            ->with('COUNT(report_table_views.event_id) > ?', 0)
            ->willReturn($this->selectMock);

        $this->collection->addViewsCount();
    }

    /**
     * Get mock for abstract class with methods.
     *
     * @param string $className
     * @param array $methods
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function createPartialMockForAbstractClass($className, $methods)
    {
        return $this->getMockForAbstractClass(
            $className,
            [],
            '',
            true,
            true,
            true,
            $methods
        );
    }
}
