<?php

namespace Magento\NegotiableQuote\Test\Unit\Observer;

/**
 * Class UpdateConfigTest
 */
class UpdateConfigTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManager;

    /**
     * @var \Magento\Company\Api\StatusServiceInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $companyStatusService;

    /**
     * @var \Magento\NegotiableQuote\Model\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $negotiableQuoteModuleConfig;

    /**
     * @var \Magento\Framework\Event\Observer|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $observer;

    /**
     * @var \Magento\Framework\Event|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $event;

    /**
     * @var \Magento\NegotiableQuote\Observer\UpdateConfig|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $updateConfig;

    /**
     * Set up
     */
    protected function setUp()
    {
        $this->storeManager = $this->createMock(\Magento\Store\Model\StoreManagerInterface::class);
        $this->companyStatusService =
            $this->createMock(\Magento\Company\Api\StatusServiceInterface::class);
        $this->negotiableQuoteModuleConfig =
            $this->createMock(\Magento\NegotiableQuote\Model\Config::class);

        $this->observer = $this->getMockBuilder(\Magento\Framework\Event\Observer::class)
            ->disableOriginalConstructor()->getMock();
        $this->event = $this->getMockBuilder(\Magento\Framework\Event::class)
            ->setMethods(['getWebsite'])
            ->disableOriginalConstructor()->getMock();
        $this->observer->expects($this->any())->method('getEvent')
            ->willReturn($this->event);

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->updateConfig = $objectManager->getObject(
            \Magento\NegotiableQuote\Observer\UpdateConfig::class,
            [
                'storeManager' => $this->storeManager,
                'companyStatusService' => $this->companyStatusService,
                'negotiableQuoteModuleConfig' => $this->negotiableQuoteModuleConfig,

            ]
        );
    }

    /**
     * @param int $eventWebsiteId
     * @param bool $isCompanyActive
     * @param bool $isQuoteActive
     * @return void
     * @dataProvider dataProviderExecute
     */
    public function testExecute($eventWebsiteId, $isCompanyActive, $isQuoteActive)
    {
        $this->event->expects($this->any())->method('getWebsite')->willReturn($eventWebsiteId);

        $website = $this->getMockBuilder(\Magento\Store\Api\Data\WebsiteInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->storeManager->expects($this->any())->method('getWebsite')
            ->willReturn($website);

        $this->companyStatusService->expects($this->any())->method('isActive')
            ->willReturn($isCompanyActive);

        $this->negotiableQuoteModuleConfig->expects($this->any())->method('isActive')
            ->willReturn($isQuoteActive);

        $isRequireModuleDisable = !$isCompanyActive && $isQuoteActive;
        $this->negotiableQuoteModuleConfig->expects($this->exactly(
            $isRequireModuleDisable ? 1 : 0
        ))->method('setIsActive');

        $this->updateConfig->execute($this->observer);
    }

    /**
     * @return array
     */
    public function dataProviderExecute()
    {
        return [
            [1, true, true],
            [0, false, true],
            [1, false, false],
            [0, true, false],
        ];
    }
}
