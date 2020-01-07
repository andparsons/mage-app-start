<?php
namespace Magento\RequisitionList\Controller\Item;

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\App\Action\Action;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Action\Context;
use Magento\RequisitionList\Api\RequisitionListManagementInterface;
use Magento\RequisitionList\Api\RequisitionListRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\RequisitionList\Model\Action\RequestValidator;

/**
 * Move requisition list items to the other list.
 */
class Move extends Action implements HttpPostActionInterface
{
    /**
     * @var \Magento\RequisitionList\Model\Action\RequestValidator
     */
    private $requestValidator;

    /**
     * @var \Magento\RequisitionList\Api\RequisitionListRepositoryInterface
     */
    private $requisitionListRepository;

    /**
     * @var \Magento\RequisitionList\Api\RequisitionListManagementInterface
     */
    private $requisitionListManagement;

    /**
     * @param Context $context
     * @param RequestValidator $requestValidator
     * @param RequisitionListRepositoryInterface $requisitionListRepository
     * @param RequisitionListManagementInterface $requisitionListManagement
     */
    public function __construct(
        Context $context,
        RequestValidator $requestValidator,
        RequisitionListRepositoryInterface $requisitionListRepository,
        RequisitionListManagementInterface $requisitionListManagement
    ) {
        parent::__construct($context);
        $this->requestValidator = $requestValidator;
        $this->requisitionListRepository = $requisitionListRepository;
        $this->requisitionListManagement = $requisitionListManagement;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $result = $this->requestValidator->getResult($this->getRequest());
        if ($result) {
            return $result;
        }

        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setRefererUrl();

        $listId = $this->getRequest()->getParam('list_id');
        $sourceListId = $this->getRequest()->getParam('source_list_id');
        $itemIds = explode(',', $this->getRequest()->getParam('selected'));

        try {
            $sourceList = $this->requisitionListRepository->get($sourceListId);

            $sourceListItems = [];
            $targetListItems = [];
            foreach ($sourceList->getItems() as $item) {
                if (in_array($item->getId(), $itemIds)) {
                    $targetListItems[] = $item;
                } else {
                    $sourceListItems[] = $item;
                }
            }

            $targetList = $this->requisitionListRepository->get($listId);
            foreach ($targetListItems as $item) {
                $this->requisitionListManagement->copyItemToList($targetList, $item);
            }
            $this->requisitionListRepository->save($targetList);

            $sourceList->setItems($sourceListItems);
            $this->requisitionListRepository->save($sourceList);

            $this->messageManager->addSuccessMessage(
                __("%1 item(s) were moved to %2", count($targetListItems), $targetList->getName())
            );
        } catch (NoSuchEntityException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (CouldNotSaveException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        return $resultRedirect;
    }
}
