<?php
namespace Magento\RequisitionList\Controller\Requisition;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\RequisitionList\Api\RequisitionListRepositoryInterface;
use Magento\RequisitionList\Model\Action\RequestValidator;
use Psr\Log\LoggerInterface;

/**
 * Class Delete
 */
class Delete extends \Magento\Framework\App\Action\Action
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
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @param Context $context
     * @param RequestValidator $requestValidator
     * @param RequisitionListRepositoryInterface $requisitionListRepository
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        RequestValidator $requestValidator,
        RequisitionListRepositoryInterface $requisitionListRepository,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->requestValidator = $requestValidator;
        $this->requisitionListRepository = $requisitionListRepository;
        $this->logger = $logger;
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

        $listId = $this->_request->getParam('requisition_id');

        try {
            $this->requisitionListRepository->deleteById($listId);
            $resultRedirect->setPath('requisition_list/requisition/index');
        } catch (LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addError(__('Something went wrong.'));
            $this->logger->critical($e);
        }

        return $resultRedirect;
    }
}
