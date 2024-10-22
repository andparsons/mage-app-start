<?php
namespace Magento\Sitemap\Model;

use Magento\Sitemap\Model\EmailNotification as SitemapEmail;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Sitemap\Model\ResourceModel\Sitemap\CollectionFactory;
use Magento\Store\Model\ScopeInterface;

/**
 * Sitemap module observer
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Observer
{
    /**
     * Enable/disable configuration
     */
    const XML_PATH_GENERATION_ENABLED = 'sitemap/generate/enabled';

    /**
     * Cronjob expression configuration
     *
     * @deprecated Use \Magento\Cron\Model\Config\Backend\Sitemap::CRON_STRING_PATH instead.
     */
    const XML_PATH_CRON_EXPR = 'crontab/default/jobs/generate_sitemaps/schedule/cron_expr';

    /**
     * Error email template configuration
     */
    const XML_PATH_ERROR_TEMPLATE = 'sitemap/generate/error_email_template';

    /**
     * Error email identity configuration
     */
    const XML_PATH_ERROR_IDENTITY = 'sitemap/generate/error_email_identity';

    /**
     * 'Send error emails to' configuration
     */
    const XML_PATH_ERROR_RECIPIENT = 'sitemap/generate/error_email';

    /**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Magento\Sitemap\Model\ResourceModel\Sitemap\CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var $emailNotification
     */
    private $emailNotification;

    /**
     * Observer constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param CollectionFactory $collectionFactory
     * @param EmailNotification $emailNotification
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        CollectionFactory $collectionFactory,
        SitemapEmail $emailNotification
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->collectionFactory = $collectionFactory;
        $this->emailNotification = $emailNotification;
    }

    /**
     * Generate sitemaps
     *
     * @return void
     * @throws \Exception
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function scheduledGenerateSitemaps()
    {
        $errors = [];
        $recipient = $this->scopeConfig->getValue(
            Observer::XML_PATH_ERROR_RECIPIENT,
            ScopeInterface::SCOPE_STORE
        );
        // check if scheduled generation enabled
        if (!$this->scopeConfig->isSetFlag(
            self::XML_PATH_GENERATION_ENABLED,
            ScopeInterface::SCOPE_STORE
        )
        ) {
            return;
        }

        $collection = $this->collectionFactory->create();
        /* @var $collection \Magento\Sitemap\Model\ResourceModel\Sitemap\Collection */
        foreach ($collection as $sitemap) {
            /* @var $sitemap \Magento\Sitemap\Model\Sitemap */
            try {
                $sitemap->generateXml();
            } catch (\Exception $e) {
                $errors[] = $e->getMessage();
            }
        }
        if ($errors && $recipient) {
            $this->emailNotification->sendErrors($errors);
        }
    }
}
