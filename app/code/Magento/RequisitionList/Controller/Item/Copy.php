<?php
namespace Magento\RequisitionList\Controller\Item;

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Action\Context;
use Magento\RequisitionList\Api\RequisitionListManagementInterface;
use Magento\RequisitionList\Api\RequisitionListRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\RequisitionList\Model\RequisitionList\Items;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\RequisitionList\Model\Action\RequestValidator;
use Magento\RequisitionList\Api\Data\RequisitionListItemInterfaceFactory;

/**
 * Class Copy
 */
class Copy extends \Magento\Framework\App\Action\Action implements HttpPostActionInterface
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
     * @var \Magento\RequisitionList\Model\RequisitionList\Items
     */
    private $requisitionListItemRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var \Magento\RequisitionList\Api\Data\RequisitionListItemInterfaceFactory
     */
    private $requisitionListItemFactory;

    /**
     * @param Context $context
     * @param RequestValidator $requestValidator
     * @param RequisitionListRepositoryInterface $requisitionListRepository
     * @param RequisitionListManagementInterface $requisitionListManagement
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param Items $requisitionListItemRepository
     * @param RequisitionListItemInterfaceFactory $requisitionListItemFactory
     */
    public function __construct(
        Context $context,
        RequestValidator $requestValidator,
        RequisitionListRepositoryInterface $requisitionListRepository,
        RequisitionListManagementInterface $requisitionListManagement,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        Items $requisitionListItemRepository,
        RequisitionListItemInterfaceFactory $requisitionListItemFactory
    ) {
        parent::__construct($context);
        $this->requestValidator = $requestValidator;
        $this->requisitionListRepository = $requisitionListRepository;
        $this->requisitionListManagement = $requisitionListManagement;
        $this->requisitionListItemRepository = $requisitionListItemRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->requisitionListItemFactory = $requisitionListItemFactory;
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

        $listId = $this->_request->getParam('list_id');
        $selectedItem = $this->_request->getParam('selected');

        $criteriaBuilder = $this->searchCriteriaBuilder->addFilter(
            'item_id',
            explode(',', $selectedItem),
            'IN'
        );
        $requisitionListItems = $this->requisitionListItemRepository->getList($criteriaBuilder->create())->getItems();

        try {
            $requisitionList = $this->requisitionListRepository->get($listId);
            foreach ($requisitionListItems as $item) {
                /** @var \Magento\RequisitionList\Api\Data\RequisitionListItemInterface $requisitionListItem */
                $requisitionListItem = $this->requisitionListItemFactory->create();
                $requisitionListItem->setQty($item->getQty());
                $requisitionListItem->setOptions((array)$item->getOptions());
                $requisitionListItem->setSku($item->getSku());
                $this->requisitionListManagement->addItemToList($requisitionList, $requisitionListItem);
            }
            $this->requisitionListRepository->save($requisitionList);
            $this->messageManager->addSuccess(
                __("%1 item(s) were copied to %2", count($requisitionListItems), $requisitionList->getName())
            );
        } catch (NoSuchEntityException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (CouldNotSaveException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        return $resultRedirect;
    }
}
