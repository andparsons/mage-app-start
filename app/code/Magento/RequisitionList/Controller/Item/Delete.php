<?php
namespace Magento\RequisitionList\Controller\Item;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\RequisitionList\Api\RequisitionListRepositoryInterface;
use Magento\RequisitionList\Model\Action\RequestValidator;
use Magento\RequisitionList\Model\RequisitionList\Items;
use Psr\Log\LoggerInterface;

/**
 * Class Delete
 */
class Delete extends \Magento\Framework\App\Action\Action
{
    /**
     * @var RequestValidator
     */
    private $requestValidator;

    /**
     * @var RequisitionListRepositoryInterface
     */
    private $requisitionListRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Items
     */
    private $requisitionListItemRepository;

    /**
     * @param Context $context
     * @param RequestValidator $requestValidator
     * @param RequisitionListRepositoryInterface $requisitionListRepository
     * @param LoggerInterface $logger
     * @param Items $requisitionListItemRepository
     */
    public function __construct(
        Context $context,
        RequestValidator $requestValidator,
        RequisitionListRepositoryInterface $requisitionListRepository,
        LoggerInterface $logger,
        Items $requisitionListItemRepository
    ) {
        parent::__construct($context);
        $this->requestValidator = $requestValidator;
        $this->requisitionListRepository = $requisitionListRepository;
        $this->logger = $logger;
        $this->requisitionListItemRepository = $requisitionListItemRepository;
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

        $id = $this->_request->getParam('requisition_id');
        $itemIds = explode(',', $this->_request->getParam('selected'));

        try {
            $requisitionList = $this->requisitionListRepository->get($id);

            foreach ($requisitionList->getItems() as $item) {
                if (in_array($item->getId(), $itemIds)) {
                    $this->requisitionListItemRepository->delete($item);
                }
            }

            $this->requisitionListRepository->save($requisitionList);

            $resultRedirect->setPath(
                'requisition_list/requisition/view',
                ['requisition_id' => $requisitionList->getId()]
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
