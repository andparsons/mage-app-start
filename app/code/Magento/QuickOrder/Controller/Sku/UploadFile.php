<?php

namespace Magento\QuickOrder\Controller\Sku;

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\QuickOrder\Model\Config as ModuleConfig;

/**
 * Class for processing file upload.
 */
class UploadFile extends \Magento\QuickOrder\Controller\AbstractAction implements HttpPostActionInterface
{
    /**
     * @var \Magento\AdvancedCheckout\Helper\Data
     */
    protected $advancedCheckoutHelper;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param ModuleConfig $moduleConfig
     * @param \Magento\AdvancedCheckout\Helper\Data $advancedCheckoutHelper
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        ModuleConfig $moduleConfig,
        \Magento\AdvancedCheckout\Helper\Data $advancedCheckoutHelper
    ) {
        parent::__construct($context, $moduleConfig);
        $this->advancedCheckoutHelper= $advancedCheckoutHelper;
    }

    /**
     * Enable functionality not for only logged in customers but for not logged in also.
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @return \Magento\Framework\App\ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function dispatch(\Magento\Framework\App\RequestInterface $request)
    {
        if (!$this->advancedCheckoutHelper->isSkuEnabled() || !$this->advancedCheckoutHelper->isSkuApplied()) {
            return $this->_redirect('customer/account');
        }

        return parent::dispatch($request);
    }

    /**
     * Upload file action.
     *
     * @return void
     */
    public function execute()
    {
        $rows = $this->advancedCheckoutHelper->isSkuFileUploaded($this->getRequest())
            ? $this->advancedCheckoutHelper->processSkuFileUploading()
            : [];

        $items = $this->getRequest()->getPost('items');
        if (!is_array($items)) {
            $items = [];
        }

        if (is_array($rows) && count($rows)) {
            foreach ($rows as $row) {
                $items[] = $row;
            }
        }

        $this->getRequest()->setParam('items', $items);
        $this->_forward('advancedAdd', 'cart', 'customer_order');
    }
}
