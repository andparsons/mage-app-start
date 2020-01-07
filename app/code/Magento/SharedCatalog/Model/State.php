<?php
declare(strict_types=1);

namespace Magento\SharedCatalog\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\SharedCatalog\Api\StatusInfoInterface;
use Magento\Store\Api\Data\WebsiteInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Shared catalog state information.
 */
class State
{
    /**
     * @var StatusInfoInterface
     */
    private $statusInfo;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param StatusInfoInterface $statusInfo
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        StatusInfoInterface $statusInfo,
        StoreManagerInterface $storeManager
    ) {
        $this->statusInfo = $statusInfo;
        $this->storeManager = $storeManager;
    }

    /**
     * Is shared catalog enabled.
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        $result = false;

        $websites = $this->storeManager->getWebsites();
        foreach ($websites as $website) {
            if ($this->statusInfo->isActive(ScopeInterface::SCOPE_WEBSITE, $website->getCode())) {
                $result = true;
                break;
            }
        }

        return $result;
    }

    /**
     * Get websites on which shared catalog is enabled.
     *
     * @return WebsiteInterface[]
     */
    public function getActiveWebsites(): array
    {
        $websites = $this->storeManager->getWebsites();
        $websites = array_filter(
            $websites,
            function (WebsiteInterface $website) {
                return $this->statusInfo->isActive(ScopeInterface::SCOPE_WEBSITE, $website->getCode());
            }
        );

        return $websites;
    }

    /**
     * Check is shared catalog enabled globally.
     *
     * @return bool
     */
    public function isGlobal(): bool
    {
        return $this->statusInfo->isActive(ScopeConfigInterface::SCOPE_TYPE_DEFAULT, null);
    }
}
