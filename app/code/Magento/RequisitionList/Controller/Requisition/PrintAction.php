<?php

namespace Magento\RequisitionList\Controller\Requisition;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\RequisitionList\Model\Action\RequestValidator;

/**
 * Class PrintAction
 */
class PrintAction extends \Magento\Framework\App\Action\Action
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

        return $resultPage;
    }
}
