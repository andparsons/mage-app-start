<?php
namespace Magento\RequisitionList\Controller\Item\Copy;

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\RequisitionList\Model\Action\RequestValidator;
use Magento\RequisitionList\Model\RequisitionList\Order\Converter;
use Magento\RequisitionList\Api\RequisitionListRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

/**
 * Add all products from order to a requisition list.
 */
class FromOrder extends \Magento\Framework\App\Action\Action implements HttpPostActionInterface
{
    /**
     * @var \Magento\RequisitionList\Model\Action\RequestValidator
     */
    private $requestValidator;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var \Magento\RequisitionList\Api\RequisitionListRepositoryInterface
     */
    private $requisitionListRepository;

    /**
     * @var \Magento\RequisitionList\Model\RequisitionList\Order\Converter
     */
    private $converter;

    /**
     * @param Context $context
     * @param RequestValidator $requestValidator
     * @param OrderRepositoryInterface $orderRepository
     * @param RequisitionListRepositoryInterface $requisitionListRepository
     * @param Converter $converter
     */
    public function __construct(
        Context $context,
        RequestValidator $requestValidator,
        OrderRepositoryInterface $orderRepository,
        RequisitionListRepositoryInterface $requisitionListRepository,
        Converter $converter
    ) {
        parent::__construct($context);
        $this->requestValidator = $requestValidator;
        $this->orderRepository = $orderRepository;
        $this->requisitionListRepository = $requisitionListRepository;
        $this->converter = $converter;
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

        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setRefererUrl();
        $orderId = $this->getRequest()->getParam('order_id');
        $requisitionListId = $this->getRequest()->getParam('list_id');

        try {
            $order = $this->orderRepository->get($orderId);
            $requisitionList = $this->requisitionListRepository->get($requisitionListId);
            $requisitionListItems = $this->converter->addItems($order, $requisitionList);
            $this->messageManager->addSuccessMessage(
                __('%1 item(s) were added to the "%2"', count($requisitionListItems), $requisitionList->getName())
            );
        } catch (NoSuchEntityException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (CouldNotSaveException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        return $resultRedirect;
    }
}
