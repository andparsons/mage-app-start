<?php
namespace Magento\CompanyCredit\Plugin\Store\Model;

/**
 * Displays notification about company credit currency when a website deleted.
 */
class WebsitePlugin
{
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
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\CompanyCredit\Api\CreditLimitRepositoryInterface $creditLimitRepository
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Framework\UrlInterface $urlBuilder
     */
    public function __construct(
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\CompanyCredit\Api\CreditLimitRepositoryInterface $creditLimitRepository,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\UrlInterface $urlBuilder
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->creditLimitRepository = $creditLimitRepository;
        $this->messageManager = $messageManager;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * Add notification if exists at least one company with credit currency equal to website base currency.
     *
     * @param \Magento\Store\Model\Website $website
     * @return \Magento\Store\Model\Website
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function afterAfterDelete(
        \Magento\Store\Model\Website $website
    ) {
        $currencyCode = $website->getBaseCurrencyCode();
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(
                \Magento\CompanyCredit\Api\Data\CreditLimitInterface::CURRENCY_CODE,
                $currencyCode
            )
            ->setPageSize(0)
            ->create();
        $searchResults = $this->creditLimitRepository->getList($searchCriteria);
        if ($searchResults->getTotalCount()) {
            $url = $this->urlBuilder->getUrl('company/index');
            $this->messageManager->addComplexWarningMessage(
                'baseCurrencyChangeWarning',
                [
                    'websiteName' => $website->getName(),
                    'currencyCode' => $currencyCode,
                    'url' => $url,
                ]
            );
        }
        return $website;
    }
}
