<?php
declare(strict_types=1);

namespace Magento\Downloadable\Controller\Download;

use Magento\Catalog\Model\Product\SalabilityChecker;
use Magento\Downloadable\Helper\Download as DownloadHelper;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;

/**
 * Class LinkSample executes download sample link action.
 *
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 */
class LinkSample extends \Magento\Downloadable\Controller\Download
{
    /**
     * @var SalabilityChecker
     */
    private $salabilityChecker;

    /**
     * @param Context $context
     * @param SalabilityChecker|null $salabilityChecker
     */
    public function __construct(
        Context $context,
        SalabilityChecker $salabilityChecker = null
    ) {
        parent::__construct($context);
        $this->salabilityChecker = $salabilityChecker ?: $this->_objectManager->get(SalabilityChecker::class);
    }

    /**
     * Download link's sample action.
     *
     * @return ResponseInterface
     */
    public function execute()
    {
        $linkId = $this->getRequest()->getParam('link_id', 0);
        /** @var \Magento\Downloadable\Model\Link $link */
        $link = $this->_objectManager->create(\Magento\Downloadable\Model\Link::class)->load($linkId);
        if ($link->getId() && $this->salabilityChecker->isSalable($link->getProductId())) {
            $resource = '';
            $resourceType = '';
            if ($link->getSampleType() == DownloadHelper::LINK_TYPE_URL) {
                $resource = $link->getSampleUrl();
                $resourceType = DownloadHelper::LINK_TYPE_URL;
            } elseif ($link->getSampleType() == DownloadHelper::LINK_TYPE_FILE) {
                $resource = $this->_objectManager->get(
                    \Magento\Downloadable\Helper\File::class
                )->getFilePath(
                    $this->_getLink()->getBaseSamplePath(),
                    $link->getSampleFile()
                );
                $resourceType = DownloadHelper::LINK_TYPE_FILE;
            }
            try {
                $this->_processDownload($resource, $resourceType);
                // phpcs:ignore Magento2.Security.LanguageConstruct.ExitUsage
                exit(0);
            } catch (\Exception $e) {
                $this->messageManager->addError(
                    __('Sorry, there was an error getting requested content. Please contact the store owner.')
                );
            }
        }

        return $this->getResponse()->setRedirect($this->_redirect->getRedirectUrl());
    }
}
