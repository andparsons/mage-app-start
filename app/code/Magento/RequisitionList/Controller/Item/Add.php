<?php

declare(strict_types=1);

namespace Magento\RequisitionList\Controller\Item;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\RequisitionList\Model\Action\RequestValidator;
use Magento\RequisitionList\Model\RequisitionListItem\Locator;
use Magento\RequisitionList\Model\RequisitionListItem\Options\Builder\ConfigurationException;
use Magento\RequisitionList\Model\RequisitionListItem\SaveHandler;
use Magento\RequisitionList\Model\RequisitionListProduct;
use Psr\Log\LoggerInterface;

/**
 * Add product to the requisition list.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Add extends Action implements HttpPostActionInterface
{
    /**
     * @var RequestValidator
     */
    private $requestValidator;

    /**
     * @var SaveHandler
     */
    private $requisitionListItemSaveHandler;

    /**
     * @var RequisitionListProduct
     */
    private $requisitionListProduct;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ProductInterface
     */
    private $product;

    /**
     * @var Locator
     */
    private $requisitionListItemLocator;

    /**
     * @param Context $context
     * @param RequestValidator $requestValidator
     * @param SaveHandler $requisitionListItemSaveHandler
     * @param RequisitionListProduct $requisitionListProduct
     * @param LoggerInterface $logger
     * @param Locator $requisitionListItemLocator
     */
    public function __construct(
        Context $context,
        RequestValidator $requestValidator,
        SaveHandler $requisitionListItemSaveHandler,
        RequisitionListProduct $requisitionListProduct,
        LoggerInterface $logger,
        Locator $requisitionListItemLocator
    ) {
        parent::__construct($context);
        $this->requestValidator = $requestValidator;
        $this->requisitionListItemSaveHandler = $requisitionListItemSaveHandler;
        $this->requisitionListProduct = $requisitionListProduct;
        $this->logger = $logger;
        $this->requisitionListItemLocator = $requisitionListItemLocator;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $redirect = $this->preExecute($resultRedirect);
        if ($redirect) {
            return $redirect;
        }
        $itemId = (int)$this->getRequest()->getParam('item_id');
        $listId = $this->findRequisitionListByItemId($itemId);

        try {
            $options = [];
            $productData = $this->requisitionListProduct->prepareProductData(
                $this->getRequest()->getParam('product_data')
            );
            if (is_array($productData->getOptions())) {
                $options = $productData->getOptions();
            }

            $redirect = $this->checkConfiguration($resultRedirect, $listId);

            if ($redirect) {
                return $redirect;
            }

            $message = $this->requisitionListItemSaveHandler->saveItem($productData, $options, $itemId, $listId);
            $this->messageManager->addSuccess($message);
        } catch (ConfigurationException $e) {
            $this->messageManager->addWarningMessage($e->getMessage());
            $resultRedirect->setUrl($this->getProductConfigureUrl());

            return $resultRedirect;
        } catch (LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            if ($itemId) {
                $this->messageManager->addError(__('We can\'t update your requisition list right now.'));
            } else {
                $this->messageManager->addErrorMessage(
                    __('We can\'t add the item to the Requisition List right now: %1.', $e->getMessage())
                );
            }
            $this->logger->critical($e);
        }

        if (!$itemId) {
            return $resultRedirect->setRefererUrl();
        }

        return $resultRedirect->setPath(
            'requisition_list/requisition/view',
            ['requisition_id' => $listId]
        );
    }

    /**
     * Check is product configuration correct and requisition list id exists.
     *
     * @param ResultInterface $resultRedirect
     * @param int $listId
     * @return ResultInterface|null
     */
    private function checkConfiguration(
        ResultInterface $resultRedirect,
        $listId
    ) {
        if (!$listId) {
            $this->messageManager->addError(__('We can\'t specify a requisition list.'));
            $resultRedirect->setPath('requisition_list/requisition/index');
            return $resultRedirect;
        }

        return null;
    }

    /**
     * Check is add to requisition list action allowed for the current user and product exists.
     *
     * @param ResultInterface $resultRedirect
     * @return ResultInterface|null
     */
    private function preExecute(ResultInterface $resultRedirect)
    {
        $result = $this->requestValidator->getResult($this->getRequest());
        if ($result) {
            return $result;
        }

        if (!$this->getProduct()) {
            $this->messageManager->addError(__('We can\'t specify a product.'));
            $resultRedirect->setPath('requisition_list/requisition/index');
            return $resultRedirect;
        }
        return null;
    }

    /**
     * Get product specified by product data.
     *
     * @return ProductInterface|bool
     */
    private function getProduct()
    {
        if ($this->product === null) {
            $productData = $this->requisitionListProduct->prepareProductData(
                $this->getRequest()->getParam('product_data')
            );
            $this->product = $this->requisitionListProduct->getProduct($productData->getSku());
        }
        return $this->product;
    }

    /**
     * Prepare product configure url.
     *
     * @return string
     */
    private function getProductConfigureUrl()
    {
        return $this->getProduct()->getUrlModel()->getUrl(
            $this->getProduct(),
            ['_fragment' => 'requisition_configure']
        );
    }

    /**
     * Find requisition list by item id.
     *
     * @param int $itemId
     * @return int|null
     */
    private function findRequisitionListByItemId($itemId)
    {
        $listId = $this->getRequest()->getParam('list_id');
        if (!$listId && $itemId) {
            $item = $this->requisitionListItemLocator->getItem($itemId);
            $listId = $item->getRequisitionListId();
        }

        return $listId;
    }
}
