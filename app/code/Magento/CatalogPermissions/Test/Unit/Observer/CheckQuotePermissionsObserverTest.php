<?php
namespace Magento\CatalogPermissions\Test\Unit\Observer;

use Magento\CatalogPermissions\Observer\CheckQuotePermissionsObserver;

/**
 * Test for \Magento\CatalogPermissions\Observer\CheckQuotePermissionsObserver
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CheckQuotePermissionsObserverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\CatalogPermissions\Observer\CheckQuotePermissionsObserver
     */
    protected $observer;

    /**
     * @var \Magento\CatalogPermissions\App\ConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $permissionsConfig;

    /**
     * @var \Magento\CatalogPermissions\Model\Permission\Index|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $permissionIndex;

    /**
     * @var \Magento\Store\Model\StoreRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeRepositoryMock;

    /**
     * @var \Magento\CatalogPermissions\Helper\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    private $catalogPermData;

    /**
     * @var \Magento\Customer\Model\Session|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerSession;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->permissionsConfig = $this->getMockBuilder(\Magento\CatalogPermissions\App\ConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->permissionIndex = $this->getMockBuilder(\Magento\CatalogPermissions\Model\Permission\Index::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->customerSession = $this->getMockBuilder(\Magento\Customer\Model\Session::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->catalogPermData = $this->getMockBuilder(\Magento\CatalogPermissions\Helper\Data::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeRepositoryMock = $this->getMockBuilder(\Magento\Store\Model\StoreRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->observer = $objectManager->getObject(
            CheckQuotePermissionsObserver::class,
            [
                'permissionsConfig' => $this->permissionsConfig,
                'customerSession' => $this->customerSession,
                'permissionIndex' => $this->permissionIndex,
                'catalogPermData' => $this->catalogPermData,
                'storeRepository' => $this->storeRepositoryMock,
            ]
        );
    }

    /**
     * @param int $step
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function preparationData($step = 0)
    {
        $quoteMock = $this->createMock(\Magento\Quote\Model\Quote::class);

        if ($step == 0) {
            $quoteMock->expects($this->exactly(2))
                ->method('getAllItems')
                ->will($this->returnValue([]));
        } else {
            $quoteItems = $this->createPartialMock(
                \Magento\Eav\Model\Entity\Collection\AbstractCollection::class,
                ['setDisableAddToCart', 'getParentItem', 'getDisableAddToCart', 'getProduct']
            );

            $productMock = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
                ->disableOriginalConstructor()
                ->getMock();
            $productMock->expects($this->any())
                ->method('getCategoryIds')
                ->willReturn([1]);

            $quoteItems->expects($this->once())
                ->method('getParentItem')
                ->will($this->returnValue(0));

            $quoteItems->expects($this->once())
                ->method('getDisableAddToCart')
                ->will($this->returnValue(0));

            $quoteItems->expects($this->any())
                ->method('getProduct')
                ->will($this->returnValue($productMock));

            $quoteMock->expects($this->exactly(2))
                ->method('getAllItems')
                ->will($this->returnValue([$quoteItems]));
        }

        if ($step == 1) {
            $this->permissionIndex->expects($this->exactly(1))
                ->method('getIndexForCategory')
                ->will($this->returnValue([]));
        } elseif ($step == 2) {
            $this->permissionIndex->expects($this->exactly(1))
                ->method('getIndexForCategory')
                ->will($this->returnValue([1 => true]));
        }

        $cartMock = $this->createMock(\Magento\AdvancedCheckout\Model\Cart::class);
        $cartMock->expects($this->once())
            ->method('getQuote')
            ->will($this->returnValue($quoteMock));

        $eventMock = $this->createPartialMock(\Magento\Framework\Event::class, ['getCart']);
        $eventMock->expects($this->once())
            ->method('getCart')
            ->will($this->returnValue($cartMock));

        $observerMock = $this->createPartialMock(\Magento\Framework\Event\Observer::class, ['getEvent']);
        $observerMock->expects($this->once())
            ->method('getEvent')
            ->will($this->returnValue($eventMock));

        $quoteMock->expects($this->any())
            ->method('getStoreId')
            ->will($this->returnValue(1));

        $this->customerSession->expects($this->any())
            ->method('getCustomerGroupId')
            ->willReturn(1);

        $storeMock = $this->getMockBuilder(\Magento\Store\Model\Store::class)
            ->disableOriginalConstructor()
            ->getMock();
        $storeMock->expects($this->any())
            ->method('getWebsiteId')
            ->willReturn(1);
        $this->storeRepositoryMock->expects($this->any())
            ->method('getById')
            ->will($this->returnValue($storeMock));

        return $observerMock;
    }

    /**
     * @return void
     */
    public function testCheckQuotePermissionsPermissionsConfigDisabled()
    {
        $this->permissionsConfig->expects($this->once())
            ->method('isEnabled')
            ->will($this->returnValue(false));

        $observerMock = $this->createMock(\Magento\Framework\Event\Observer::class);
        $this->assertEquals($this->observer, $this->observer->execute($observerMock));
    }

    /**
     * @param int $step
     * @dataProvider dataSteps
     * @return void
     */
    public function testCheckQuotePermissionsPermissionsConfigEnabled($step)
    {
        $this->permissionsConfig->expects($this->once())
            ->method('isEnabled')
            ->will($this->returnValue(true));

        $observer = $this->preparationData($step);
        $this->assertEquals($this->observer, $this->observer->execute($observer));
    }

    /**
     * @return array
     */
    public function dataSteps()
    {
        return [[0], [1], [2]];
    }
}
