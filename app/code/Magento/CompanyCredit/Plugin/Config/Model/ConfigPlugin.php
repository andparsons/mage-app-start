<?php
namespace Magento\CompanyCredit\Plugin\Config\Model;

/**
 * Plugin to track base currency changes and display notification about company credit currency.
 */
class ConfigPlugin
{
    /**
     * Config sections that can affect base currency settings.
     *
     * @var array
     */
    private $sections = ['currency', 'catalog'];

    /**
     * @var \Magento\Store\Api\WebsiteRepositoryInterface
     */
    private $websiteRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var \Magento\CompanyCredit\Api\CreditLimitRepositoryInterface
     */
    private $creditLimitRepository;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    private $messageManager;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * @param \Magento\Store\Api\WebsiteRepositoryInterface $websiteRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\CompanyCredit\Api\CreditLimitRepositoryInterface $creditLimitRepository
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Framework\UrlInterface $urlBuilder
     */
    public function __construct(
        \Magento\Store\Api\WebsiteRepositoryInterface $websiteRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\CompanyCredit\Api\CreditLimitRepositoryInterface $creditLimitRepository,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\UrlInterface $urlBuilder
    ) {
        $this->websiteRepository = $websiteRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->creditLimitRepository = $creditLimitRepository;
        $this->messageManager = $messageManager;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * Add notification about company credit currency if base currency is changed.
     *
     * @param \Magento\Config\Model\Config $config
     * @param \Closure $method
     * @return \Magento\Config\Model\Config
     */
    public function aroundSave(
        \Magento\Config\Model\Config $config,
        \Closure $method
    ) {
        if (in_array($config->getSection(), $this->sections)) {
            $websites = $this->websiteRepository->getList();
            $data = [];
            foreach ($websites as $website) {
                if ($website->getCode() !== \Magento\Store\Model\Store::ADMIN_CODE) {
                    $data[$website->getId()] = [
                        'currencyCode' => $website->getBaseCurrencyCode(),
                        'websiteName' => $website->getName(),
                    ];
                }
            }
            $result = $method();
            $data = $this->processBaseCurrencyChanges($data);
            $url = $this->urlBuilder->getUrl('company/index');
            foreach ($data as $currencyCode => $websiteNames) {
                $this->messageManager->addComplexWarningMessage(
                    'baseCurrencyChangeWarning',
                    [
                        'websiteName' => implode(', ', $websiteNames),
                        'currencyCode' => $currencyCode,
                        'url' => $url,
                    ]
                );
            }
        } else {
            $result = $method();
        }
        return $result;
    }

    /**
     * Check if base currency is changed for at least one website.
     *
     * @param array $oldData
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function processBaseCurrencyChanges(array $oldData)
    {
        $this->websiteRepository->clean();
        $websites = $this->websiteRepository->getList();
        $newData = [];
        foreach ($websites as $website) {
            $newData[$website->getId()] = $website->getBaseCurrencyCode();
        }
        $data = [];
        foreach ($oldData as $websiteId => $item) {
            if ($item['currencyCode'] != $newData[$websiteId]) {
                $searchCriteria = $this->searchCriteriaBuilder
                    ->addFilter(
                        \Magento\CompanyCredit\Api\Data\CreditLimitInterface::CURRENCY_CODE,
                        $item['currencyCode']
                    )
                    ->setPageSize(0)
                    ->create();
                $searchResults = $this->creditLimitRepository->getList($searchCriteria);
                if ($searchResults->getTotalCount()) {
                    $data[$item['currencyCode']][] = $item['websiteName'];
                }
            }
        }
        return $data;
    }
}
