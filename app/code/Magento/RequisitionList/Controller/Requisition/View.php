<?php

namespace Magento\RequisitionList\Controller\Requisition;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\RequisitionList\Model\Action\RequestValidator;

/**
 * Class View
 */
class View extends \Magento\Framework\App\Action\Action implements HttpGetActionInterface
{
    /**
     * @var RequestValidator
     */
    private $requestValidator;

    /**
     * @param Context $context
     * @param RequestValidator $requestValidator
     */
    public function __construct(
        Context $context,
        RequestValidator $requestValidator
    ) {
        parent::__construct($context);
        $this->requestValidator = $requestValidator;
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

        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->getConfig()->getTitle()->set(__('Requisition List'));

        /** @var \Magento\Framework\View\Element\Html\Links $navigationBlock */
        $navigationBlock = $resultPage->getLayout()->getBlock('customer_account_navigation');
        if ($navigationBlock) {
            $navigationBlock->setActive('requisition_list/requisition/index');
        }

        return $resultPage;
    }
}
