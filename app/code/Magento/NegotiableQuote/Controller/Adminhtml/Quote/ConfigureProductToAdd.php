<?php
namespace Magento\NegotiableQuote\Controller\Adminhtml\Quote;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Backend\App\Action;

/**
 * Controller for render configure product options.
 */
class ConfigureProductToAdd extends Action implements HttpPostActionInterface, HttpGetActionInterface
{
    /**
     * @var \Magento\Framework\DataObject
     */
    private $dataObject;

    /**
     * @var \Magento\Framework\Session\SessionManagerInterface
     */
    private $sessionQuote;

    /**
     * @var \Magento\Catalog\Helper\Product\Composite
     */
    private $compositeHelper;

    /**
     * @var \Magento\NegotiableQuote\Api\NegotiableQuoteManagementInterface
     */
    private $quoteManagement;

    /**
     * @var array
     */
    private $productTypesToReplace;

    /**
     * @param Action\Context $context
     * @param \Magento\Framework\DataObject $dataObject
     * @param \Magento\Framework\Session\SessionManagerInterface $sessionQuote
     * @param \Magento\Catalog\Helper\Product\Composite $compositeHelper
     * @param \Magento\NegotiableQuote\Api\NegotiableQuoteManagementInterface $quoteManagement
     * @param array $productTypesToReplace [optional]
     */
    public function __construct(
        Action\Context $context,
        \Magento\Framework\DataObject $dataObject,
        \Magento\Framework\Session\SessionManagerInterface $sessionQuote,
        \Magento\Catalog\Helper\Product\Composite $compositeHelper,
        \Magento\NegotiableQuote\Api\NegotiableQuoteManagementInterface $quoteManagement,
        array $productTypesToReplace = []
    ) {
        parent::__construct($context);
        $this->dataObject = $dataObject;
        $this->sessionQuote = $sessionQuote;
        $this->compositeHelper = $compositeHelper;
        $this->quoteManagement = $quoteManagement;
        $this->productTypesToReplace = $productTypesToReplace;
    }

    /**
     * Ajax handler to response configuration fieldset of composite product in order.
     *
     * @return \Magento\Framework\View\Result\Layout
     */
    public function execute()
    {
        // Prepare data
        $productId = (int)$this->getRequest()->getParam('id');

        $quote = $this->quoteManagement->getNegotiableQuote($this->getRequest()->getParam('quote_id'));
        $this->dataObject->setOk(true);
        $this->dataObject->setProductId($productId);
        $this->dataObject->setCurrentStoreId($this->sessionQuote->getStore()->getId());
        $this->dataObject->setCurrentCustomerId($quote->getCustomer()->getId());
        $config = $this->getRequest()->getParam('config');
        if ($config) {
            $configArray = [];
            parse_str(urldecode($config), $configArray);
            $configObject = new \Magento\Framework\DataObject($configArray);
            $this->dataObject->setBuyRequest($configObject);
        }
        /** @var \Magento\Framework\View\Result\Layout $resultLayout */
        $resultLayout = $this->compositeHelper->renderConfigureResult($this->dataObject);
        $this->updateLayoutHandles($resultLayout);

        return $resultLayout;
    }

    /**
     * Here we update default layout handles to Negotiable Quote module handles.
     *
     * @param \Magento\Framework\View\Result\Layout $resultLayout
     * @return void
     */
    private function updateLayoutHandles(\Magento\Framework\View\Result\Layout $resultLayout)
    {
        $layoutUpdate = $resultLayout->getLayout()->getUpdate();
        $layoutHandles = $layoutUpdate->getHandles();
        if (in_array('CATALOG_PRODUCT_COMPOSITE_CONFIGURE', $layoutHandles)) {
            $layoutUpdate->removeHandle('CATALOG_PRODUCT_COMPOSITE_CONFIGURE');
            $layoutUpdate->addHandle('negotiable_quote_catalog_product_composite_configure');
        }
        $productTypeHandlesToReplace = $this->getProductTypeHandlesToReplace();
        foreach ($layoutHandles as $layoutHandle) {
            if (in_array($layoutHandle, $productTypeHandlesToReplace)) {
                $layoutUpdate->removeHandle($layoutHandle);
                $layoutUpdate->addHandle('negotiablequote_' . $layoutHandle);
            }
        }
    }

    /**
     * Get product type handles to be replaced by negotiable quote handles.
     *
     * @return array
     */
    private function getProductTypeHandlesToReplace()
    {
        $handlesToReplace = [];

        foreach ($this->productTypesToReplace as $productTypeToReplace) {
            $handlesToReplace[] = 'catalog_product_view_type_' . $productTypeToReplace;
        }

        return $handlesToReplace;
    }
}
