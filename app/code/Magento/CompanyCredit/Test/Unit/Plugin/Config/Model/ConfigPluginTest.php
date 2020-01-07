<?php

namespace Magento\CompanyCredit\Test\Unit\Plugin\Config\Model;

/**
 * Class ConfigPluginTest.
 */
class ConfigPluginTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Store\Api\WebsiteRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $websiteRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchCriteriaBuilder;

    /**
     * @var \Magento\CompanyCredit\Api\CreditLimitRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $creditLimitRepository;

    /**
     * @var \Magento\Framework\Message\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $messageManager;

    /**
     * @var \Magento\Framework\UrlInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $urlBuilder;

    /**
     * @var \Magento\CompanyCredit\Plugin\Config\Model\ConfigPlugin
     */
    private $configPlugin;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->websiteRepository = $this->createMock(
            \Magento\Store\Api\WebsiteRepositoryInterface::class
        );
        $this->searchCriteriaBuilder = $this->createMock(
            \Magento\Framework\Api\SearchCriteriaBuilder::class
        );
        $this->creditLimitRepository = $this->createMock(
            \Magento\CompanyCredit\Api\CreditLimitRepositoryInterface::class
        );
        $this->messageManager = $this->createMock(
            \Magento\Framework\Message\ManagerInterface::class
        );
        $this->urlBuilder = $this->createMock(
            \Magento\Framework\UrlInterface::class
        );

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->configPlugin = $objectManager->getObject(
            \Magento\CompanyCredit\Plugin\Config\Model\ConfigPlugin::class,
            [
                'websiteRepository' => $this->websiteRepository,
                'searchCriteriaBuilder' => $this->searchCriteriaBuilder,
                'creditLimitRepository' => $this->creditLimitRepository,
                'messageManager' => $this->messageManager,
                'urlBuilder' => $this->urlBuilder,
            ]
        );
    }

    /**
     * Test method for aroundSave.
     *
     * @return void
     */
    public function testAroundSave()
    {
        $websiteId = 1;
        $websiteName = 'Website Name';
        $currencyCode = 'USD';
        $newCurrencyCode = 'EUR';
        $url = '/admin/company/index';
        $config = $this->createPartialMock(\Magento\Config\Model\Config::class, ['getSection']);
        $config->expects($this->once())->method('getSection')->willReturn('currency');
        $website = $this->createMock(\Magento\Store\Model\Website::class);
        $this->websiteRepository->expects($this->exactly(2))->method('getList')->willReturn([$website]);
        $website->expects($this->once())->method('getCode')->willReturn('website_code');
        $website->expects($this->exactly(2))->method('getId')->willReturn($websiteId);
        $website->expects($this->exactly(2))
            ->method('getBaseCurrencyCode')->willReturnOnConsecutiveCalls($currencyCode, $newCurrencyCode);
        $website->expects($this->once())->method('getName')->willReturn($websiteName);
        $this->websiteRepository->expects($this->once())->method('clean');
        $this->searchCriteriaBuilder->expects($this->once())->method('addFilter')->with(
            \Magento\CompanyCredit\Api\Data\CreditLimitInterface::CURRENCY_CODE,
            $currencyCode
        )->willReturnSelf();
        $this->searchCriteriaBuilder->expects($this->once())->method('setPageSize')->with(0)->willReturnSelf();
        $searchCriteria = $this->createMock(\Magento\Framework\Api\SearchCriteriaInterface::class);
        $this->searchCriteriaBuilder->expects($this->once())->method('create')->willReturn($searchCriteria);
        $searchResults = $this->createMock(\Magento\Framework\Api\SearchResultsInterface::class);
        $this->creditLimitRepository->expects($this->once())
            ->method('getList')->with($searchCriteria)->willReturn($searchResults);
        $searchResults->expects($this->once())->method('getTotalCount')->willReturn(1);
        $this->urlBuilder->expects($this->once())->method('getUrl')->with('company/index')->willReturn($url);
        $this->messageManager->expects($this->once())->method('addComplexWarningMessage')->with(
            'baseCurrencyChangeWarning',
            [
                'websiteName' => $websiteName,
                'currencyCode' => $currencyCode,
                'url' => $url,
            ]
        )->willReturnSelf();
        $this->assertSame(
            $config,
            $this->configPlugin->aroundSave(
                $config,
                function () use ($config) {
                    return $config;
                }
            )
        );
    }
}
