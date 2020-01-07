<?php
namespace Magento\RequisitionList\Controller\Item;

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\CartManagementInterface;
use Magento\RequisitionList\Api\RequisitionListManagementInterface;
use Magento\RequisitionList\Model\Action\RequestValidator;
use Psr\Log\LoggerInterface;

/**
 * Add specified items from requisition list to cart.
 */
class AddToCart extends \Magento\Framework\App\Action\Action implements HttpPostActionInterface
{
    /**
     * @var \Magento\RequisitionList\Model\Action\RequestValidator
     */
    private $requestValidator;

    /**
     * @var \Magento\Authorization\Model\UserContextInterface
     */
    private $userContext;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \Magento\RequisitionList\Api\RequisitionListManagementInterface
     */
    private $listManagement;

    /**
     * @var \Magento\Quote\Api\CartManagementInterface
     */
    private $cartManagement;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\RequisitionList\Model\RequisitionList\ItemSelector
     */
    private $itemSelector;

    /**
     * @param Context $context
     * @param RequestValidator $requestValidator
     * @param UserContextInterface $userContext
     * @param LoggerInterface $logger
     * @param RequisitionListManagementInterface $listManagement
     * @param CartManagementInterface $cartManagement
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\RequisitionList\Model\RequisitionList\ItemSelector $itemSelector
     */
    public function __construct(
        Context $context,
        RequestValidator $requestValidator,
        UserContextInterface $userContext,
        LoggerInterface $logger,
        RequisitionListManagementInterface $listManagement,
        CartManagementInterface $cartManagement,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\RequisitionList\Model\RequisitionList\ItemSelector $itemSelector
    ) {
        parent::__construct($context);
        $this->requestValidator = $requestValidator;
        $this->userContext = $userContext;
        $this->logger = $logger;
        $this->listManagement = $listManagement;
        $this->cartManagement = $cartManagement;
        $this->storeManager = $storeManager;
        $this->itemSelector = $itemSelector;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $result = $this->requestValidator->getResult($this->getRequest());
        if ($result) {
            return $result;
        }

        try {
            /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        } catch (\InvalidArgumentException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return $this->_redirect(
                'requisition_list/requisition/view',
                ['requisition_id' => $this->getRequest()->getParam('requisition_id')]
            );
        }
        $resultRedirect->setRefererUrl();

        $isReplace = $this->getRequest()->getParam('is_replace', false);

        try {
            $cartId = $this->cartManagement->createEmptyCartForCustomer($this->userContext->getUserId());
            $listId = $this->getRequest()->getParam('requisition_id');
            $itemIds = explode(',', $this->_request->getParam('selected'));
            $items = $this->itemSelector->selectItemsFromRequisitionList(
                $listId,
                $itemIds,
                $this->storeManager->getWebsite()->getId()
            );
            $addedItems = $this->listManagement->placeItemsInCart($cartId, $items, $isReplace);

            $this->messageManager->addSuccess(
                __('You added %1 item(s) to your shopping cart.', count($addedItems))
            );
        } catch (LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addError(__('Something went wrong.'));
            $this->logger->critical($e);
        }

        return $resultRedirect;
    }
}
