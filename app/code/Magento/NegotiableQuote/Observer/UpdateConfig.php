<?php
namespace Magento\NegotiableQuote\Observer;

use Magento\Company\Api\StatusServiceInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Event;
use Magento\Framework\Event\ObserverInterface;
use Magento\NegotiableQuote\Model\Config as NegotiableQuoteModuleConfig;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class UpdateConfig
 */
class UpdateConfig implements ObserverInterface
{
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var StatusServiceInterface
     */
    protected $companyStatusService;

    /**
     * @var NegotiableQuoteModuleConfig
     */
    protected $negotiableQuoteModuleConfig;

    /**
     * @param StoreManagerInterface $storeManager
     * @param StatusServiceInterface $companyStatusService
     * @param NegotiableQuoteModuleConfig $negotiableQuoteModuleConfig
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        StatusServiceInterface $companyStatusService,
        NegotiableQuoteModuleConfig $negotiableQuoteModuleConfig
    ) {
        $this->storeManager = $storeManager;
        $this->companyStatusService = $companyStatusService;
        $this->negotiableQuoteModuleConfig = $negotiableQuoteModuleConfig;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $scopeData = $this->getEventScopeData($observer->getEvent());

        $isCompanyActive = $this->companyStatusService->isActive(
            $scopeData->getScopeType(),
            $scopeData->getScopeCode()
        );
        $isNegotiableQuoteActive = $this->negotiableQuoteModuleConfig->isActive(
            $scopeData->getScopeType(),
            $scopeData->getScopeCode()
        );

        if (!$isCompanyActive && $isNegotiableQuoteActive) {
            $this->negotiableQuoteModuleConfig->setIsActive(
                false,
                $scopeData->getScopeType(),
                $scopeData->getScopeId()
            );
        }
    }

    /**
     * Get event scope data
     *
     * @param Event $event
     * @return DataObject
     */
    private function getEventScopeData(Event $event)
    {
        $scopeData = new DataObject();
        $scopeType = $event->getWebsite()
            ? ScopeInterface::SCOPE_WEBSITES
            : ScopeConfigInterface::SCOPE_TYPE_DEFAULT;
        $scopeData->setScopeType($scopeType);

        $scopeData->setScopeCode('');
        $scopeData->setScopeId(0);
        if ($scopeType === ScopeInterface::SCOPE_WEBSITES) {
            $website = $this->storeManager->getWebsite($event->getWebsite());
            $scopeData->setScopeCode($website->getCode());
            $scopeData->setScopeId($website->getId());
        }

        return $scopeData;
    }
}
