<?php

namespace Magento\NegotiableQuote\Test\Unit\Model\History;

/**
 * Unit test for Magento\NegotiableQuote\Model\History\LogInformation class.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class LogInformationTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\NegotiableQuote\Model\Restriction\RestrictionInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $restriction;

    /**
     * @var \Magento\NegotiableQuote\Helper\Quote|\PHPUnit_Framework_MockObject_MockObject
     */
    private $negotiableQuoteHelper;

    /**
     * @var \Magento\NegotiableQuote\Model\HistoryManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $historyManagement;

    /**
     * @var \Magento\Customer\Model\Address\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $addressConfig;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $localeResolver;

    /**
     * @var \Magento\NegotiableQuote\Model\History\LogInformation
     */
    private $model;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->restriction = $this->getMockBuilder(
            \Magento\NegotiableQuote\Model\Restriction\RestrictionInterface::class
        )
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->negotiableQuoteHelper = $this->getMockBuilder(\Magento\NegotiableQuote\Helper\Quote::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->historyManagement = $this->getMockBuilder(
            \Magento\NegotiableQuote\Model\HistoryManagementInterface::class
        )
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->addressConfig = $this->getMockBuilder(\Magento\Customer\Model\Address\Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->localeResolver = $this->getMockBuilder(\Magento\Framework\Locale\ResolverInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectManagerHelper->getObject(
            \Magento\NegotiableQuote\Model\History\LogInformation::class,
            [
                'restriction' => $this->restriction,
                'negotiableQuoteHelper' => $this->negotiableQuoteHelper,
                'historyManagement' => $this->historyManagement,
                'addressConfig' => $this->addressConfig,
                'localeResolver' => $this->localeResolver,
            ]
        );
    }

    /**
     * Test isCanSubmit method.
     *
     * @return void
     */
    public function testIsCanSubmit()
    {
        $canSubmit = true;
        $this->restriction->expects($this->once())->method('canSubmit')->willReturn(true);

        $this->assertEquals($canSubmit, $this->model->isCanSubmit());
    }

    /**
     * Test getQuoteHistory method.
     *
     * @return void
     */
    public function testGetQuoteHistory()
    {
        $quoteId = 1;
        $quote = $this->getMockBuilder(\Magento\Quote\Api\Data\CartInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getEntityId'])
            ->getMockForAbstractClass();
        $historyCollection = $this->getMockBuilder(
            \Magento\NegotiableQuote\Model\ResourceModel\History\Collection::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $this->negotiableQuoteHelper->expects($this->atLeastOnce())->method('resolveCurrentQuote')->willReturn($quote);
        $quote->expects($this->atLeastOnce())->method('getEntityId')->willReturn($quoteId);
        $this->historyManagement->expects($this->once())->method('updateSystemLogsStatus')->with($quoteId);
        $this->historyManagement->expects($this->once())
            ->method('getQuoteHistory')
            ->with($quoteId)
            ->willReturn($historyCollection);

        $this->assertEquals($historyCollection, $this->model->getQuoteHistory());
    }

    /**
     * Test getQuoteUpdates method.
     *
     * @return void
     */
    public function testGetQuoteUpdates()
    {
        $logId = 1;
        $this->historyManagement->expects($this->once())
            ->method('getLogUpdatesList')
            ->with($logId)
            ->willReturn(
                [
                    'negotiated_price' => 12,
                    'negotiated_price_type' => 'fixed',
                ]
            );
        $updates = new \Magento\Framework\DataObject();
        $updates->setData(['negotiated_price_type' => 'fixed']);

        $this->assertEquals($updates, $this->model->getQuoteUpdates($logId));
    }

    /**
     * Test isSetPostcode method.
     *
     * @return void
     */
    public function testIsSetPostcode()
    {
        $flatAddressArray = [
            \Magento\Quote\Api\Data\AddressInterface::KEY_POSTCODE => '222222'
        ];

        $this->assertTrue($this->model->isSetPostcode($flatAddressArray));
    }

    /**
     * Test getLogAddressRenderer method.
     *
     * @return void
     */
    public function testGetLogAddressRenderer()
    {
        $format = $this->getMockBuilder(\Magento\Framework\DataObject::class)
            ->disableOriginalConstructor()
            ->setMethods(['getRenderer'])
            ->getMock();
        $renderer = $this->getMockBuilder(\Magento\Customer\Block\Address\Renderer\RendererInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->addressConfig->expects($this->once())->method('getFormatByCode')->with('html')->willReturn($format);
        $format->expects($this->once())->method('getRenderer')->willReturn($renderer);

        $this->assertEquals($renderer, $this->model->getLogAddressRenderer());
    }

    /**
     * Test formatDate method.
     *
     * @return void
     */
    public function testFormatDate()
    {
        $dateType = \IntlDateFormatter::LONG;
        $date = date('Y-m-d H:i:s');
        $this->localeResolver->expects($this->once())->method('getLocale')->willReturn('US');

        $this->assertEquals(date('F j, Y'), $this->model->formatDate($date, $dateType));
    }
}
