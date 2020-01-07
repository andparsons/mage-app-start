<?php
namespace Magento\NegotiableQuote\Test\Unit\Model\Quote;

use Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface;
use Magento\NegotiableQuote\Api\NegotiableQuoteRepositoryInterface;
use Magento\NegotiableQuote\Model\Discount\StateChanges\Applier;
use Magento\NegotiableQuote\Model\HistoryManagementInterface;

/**
 * Test for Magento\NegotiableQuote\Model\Quote\ItemRemove class.
 */
class ItemRemoveTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\NegotiableQuote\Model\Quote\ItemRemove
     */
    private $itemRemove;

    /**
     * @var NegotiableQuoteRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $negotiableQuoteRepository;

    /**
     * @var Applier|\PHPUnit_Framework_MockObject_MockObject
     */
    private $messageApplier;

    /**
     * @var HistoryManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $historyManagement;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json|\PHPUnit_Framework_MockObject_MockObject
     */
    private $serializerMock;

    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $loggerMock;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->negotiableQuoteRepository = $this->getMockBuilder(NegotiableQuoteRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->messageApplier = $this->getMockBuilder(Applier::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->historyManagement = $this->getMockBuilder(HistoryManagementInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->serializerMock = $this->getMockBuilder(\Magento\Framework\Serialize\Serializer\Json::class)
            ->disableOriginalConstructor()
            ->setMethods(['serialize', 'unserialize'])
            ->getMock();

        $this->loggerMock = $this->getMockBuilder(\Psr\Log\LoggerInterface::class)->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->itemRemove = $objectManager->getObject(
            \Magento\NegotiableQuote\Model\Quote\ItemRemove::class,
            [
                'negotiableQuoteRepository' => $this->negotiableQuoteRepository,
                'messageApplier' => $this->messageApplier,
                'historyManagement' => $this->historyManagement,
                'serializer' => $this->serializerMock,
                'logger' => $this->loggerMock
            ]
        );
    }

    /**
     * Test setNotificationRemove method without SKUs.
     *
     * @return void
     */
    public function testSetNotificationRemoveWithoutSkus()
    {
        $valueId = 42;

        $this->prepareSerializerMock();
        $negotiableQuote = $this->getMockBuilder(NegotiableQuoteInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getSnapshot', 'setSnapshot'])
            ->getMockForAbstractClass();
        $this->negotiableQuoteRepository->expects($this->once())->method('getById')->willReturn($negotiableQuote);
        $negotiableQuote->expects($this->once())->method('getIsRegularQuote')->willReturn(true);
        $addSku = ['adminhtml' => 'sku'];
        $negotiableQuote->expects($this->atLeastOnce())->method('getDeletedSku')->willReturn(null);
        $negotiableQuote->expects($this->atLeastOnce())->method('setDeletedSku')->willReturnSelf();
        $jsonSnapshot = json_encode([
            'items' => [
                $valueId => [
                    'product_id' => $valueId,
                    'sku' => $valueId,
                    'name' => $valueId,
                ]
            ]
        ]);
        $negotiableQuote->expects($this->once())->method('getSnapshot')->willReturn($jsonSnapshot);
        $negotiableQuote->expects($this->once())->method('setSnapshot')->willReturnSelf();
        $negotiableQuote->expects($this->once())->method('getNegotiatedPriceValue')->willReturn(true);
        $negotiableQuote->expects($this->once())->method('setHasUnconfirmedChanges')->willReturnSelf();
        $negotiableQuote->expects($this->once())->method('setIsCustomerPriceChanged')->willReturnSelf();
        $this->negotiableQuoteRepository->expects($this->once())
            ->method('save')
            ->with($negotiableQuote)
            ->willReturn(true);
        $this->serializerMock->expects($this->never())->method('unserialize');

        $this->assertInstanceOf(
            get_class($this->itemRemove),
            $this->itemRemove->setNotificationRemove($valueId, $valueId, $addSku)
        );
    }

    /**
     * Test setNotificationRemove method with SKUs.
     *
     * @return void
     */
    public function testSetNotificationRemoveWithSkus()
    {
        $valueId = 42;

        $this->prepareSerializerMock();
        $negotiableQuote = $this->getMockBuilder(NegotiableQuoteInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getSnapshot', 'setSnapshot'])
            ->getMockForAbstractClass();
        $this->negotiableQuoteRepository->expects($this->once())->method('getById')->willReturn($negotiableQuote);
        $negotiableQuote->expects($this->once())->method('getIsRegularQuote')->willReturn(true);
        $productSkus = [
            \Magento\Framework\App\Area::AREA_ADMINHTML => ['sku'],
            \Magento\Framework\App\Area::AREA_FRONTEND => ['sku']
        ];
        $negotiableQuote->expects($this->atLeastOnce())->method('getDeletedSku')->willReturn(json_encode($productSkus));
        $negotiableQuote->expects($this->atLeastOnce())->method('setDeletedSku')->willReturnSelf();
        $jsonSnapshot = json_encode([
            'items' => [
                $valueId => [
                    'product_id' => $valueId,
                    'sku' => $valueId,
                    'name' => $valueId,
                ]
            ]
        ]);
        $negotiableQuote->expects($this->once())->method('getSnapshot')->willReturn($jsonSnapshot);
        $negotiableQuote->expects($this->once())->method('setSnapshot')->willReturnSelf();
        $negotiableQuote->expects($this->once())->method('getNegotiatedPriceValue')->willReturn(true);
        $negotiableQuote->expects($this->once())->method('setHasUnconfirmedChanges')->willReturnSelf();
        $negotiableQuote->expects($this->once())->method('setIsCustomerPriceChanged')->willReturnSelf();
        $this->negotiableQuoteRepository->expects($this->once())->method('save')
            ->with($negotiableQuote)->willReturn(true);
        $addSku = ['adminhtml' => 'sku'];

        $this->assertInstanceOf(
            get_class($this->itemRemove),
            $this->itemRemove->setNotificationRemove($valueId, $valueId, $addSku)
        );
    }

    /**
     * Test setNotificationRemove method with invalid snapshot.
     *
     * @return void
     */
    public function testSetNotificationRemoveInvalidSnapshot()
    {
        $valueId = 42;

        $this->prepareSerializerMock();
        $negotiableQuote = $this->getMockBuilder(NegotiableQuoteInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getSnapshot'])
            ->getMockForAbstractClass();
        $this->negotiableQuoteRepository->expects($this->once())->method('getById')->willReturn($negotiableQuote);
        $negotiableQuote->expects($this->once())->method('getIsRegularQuote')->willReturn(true);
        $productSkus = [
            \Magento\Framework\App\Area::AREA_ADMINHTML => ['sku'],
            \Magento\Framework\App\Area::AREA_FRONTEND => ['sku']
        ];
        $negotiableQuote->expects($this->atLeastOnce())->method('getDeletedSku')->willReturn(json_encode($productSkus));
        $negotiableQuote->expects($this->atLeastOnce())->method('setDeletedSku')->willReturnSelf();
        $jsonInvalidSnapshot = json_encode('notArray');
        $negotiableQuote->expects($this->once())->method('getSnapshot')->willReturn($jsonInvalidSnapshot);
        $addSku = ['adminhtml' => 'sku'];

        $this->assertInstanceOf(
            get_class($this->itemRemove),
            $this->itemRemove->setNotificationRemove($valueId, $valueId, $addSku)
        );
    }

    /**
     * Test setNotificationRemove method with Admin notifications only.
     *
     * @return void
     */
    public function testSetNotificationRemoveForAdminOnly()
    {
        $valueId = 42;

        $this->prepareSerializerMock();
        $negotiableQuote = $this->getMockBuilder(NegotiableQuoteInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getSnapshot'])
            ->getMockForAbstractClass();
        $this->negotiableQuoteRepository->expects($this->once())->method('getById')->willReturn($negotiableQuote);
        $negotiableQuote->expects($this->once())->method('getIsRegularQuote')->willReturn(true);
        $productSkus = [\Magento\Framework\App\Area::AREA_ADMINHTML => ['sku']];
        $negotiableQuote->expects($this->atLeastOnce())->method('getDeletedSku')->willReturn(json_encode($productSkus));
        $negotiableQuote->expects($this->once())->method('setDeletedSku')
            ->with(json_encode($productSkus))
            ->willReturnSelf();
        $jsonInvalidSnapshot = json_encode('notArray');
        $negotiableQuote->expects($this->once())->method('getSnapshot')->willReturn($jsonInvalidSnapshot);
        $addSku = ['sku'];

        $this->assertInstanceOf(
            get_class($this->itemRemove),
            $this->itemRemove->setNotificationRemove($valueId, $valueId, $addSku)
        );
    }

    /**
     * Test setNotificationRemove method with not regular snapshot.
     *
     * @return void
     */
    public function testSetNotificationRemoveNotRegularSnapshot()
    {
        $valueId = 42;

        $this->prepareSerializerMock();
        $negotiableQuote = $this->getMockBuilder(NegotiableQuoteInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->negotiableQuoteRepository->expects($this->once())->method('getById')->willReturn($negotiableQuote);
        $negotiableQuote->expects($this->once())->method('getIsRegularQuote')->willReturn(false);
        $addSku = ['adminhtml' => 'sku'];

        $this->assertInstanceOf(
            get_class($this->itemRemove),
            $this->itemRemove->setNotificationRemove($valueId, $valueId, $addSku)
        );
    }

    /**
     * Prepare Serializer mock for tests.
     *
     * @return void
     */
    private function prepareSerializerMock()
    {
        $this->serializerMock->expects($this->any())->method('serialize')
            ->will(
                $this->returnCallback(
                    function ($value) {
                        return json_encode($value);
                    }
                )
            );
        $this->serializerMock->expects($this->any())->method('unserialize')
            ->will(
                $this->returnCallback(
                    function ($value) {
                        return json_decode($value, true);
                    }
                )
            );
    }

    /**
     * Test setNotificationRemove method with Exception on quote save.
     *
     * @return void
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Cannot save removed quote item notification
     */
    public function testSetNotificationRemoveWithExceptionOnQuoteSave()
    {
        $valueId = 42;

        $this->prepareSerializerMock();
        $negotiableQuote = $this->getMockBuilder(NegotiableQuoteInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getSnapshot', 'setSnapshot'])
            ->getMockForAbstractClass();
        $this->negotiableQuoteRepository->expects($this->once())->method('getById')->willReturn($negotiableQuote);
        $negotiableQuote->expects($this->once())->method('getIsRegularQuote')->willReturn(true);
        $productSkus = [
            \Magento\Framework\App\Area::AREA_ADMINHTML => ['sku'],
            \Magento\Framework\App\Area::AREA_FRONTEND => ['sku']
        ];
        $negotiableQuote->expects($this->atLeastOnce())->method('getDeletedSku')->willReturn(json_encode($productSkus));
        $exception = new \Magento\Framework\Exception\LocalizedException(__('exception message'));
        $this->negotiableQuoteRepository->expects($this->once())->method('save')
            ->with($negotiableQuote)->willThrowException($exception);
        $this->loggerMock->expects($this->once())->method('critical')->with('exception message');
        $addSku = ['adminhtml' => 'sku'];

        $this->itemRemove->setNotificationRemove($valueId, $valueId, $addSku);
    }
}
