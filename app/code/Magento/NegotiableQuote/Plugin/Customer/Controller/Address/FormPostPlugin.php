<?php

namespace Magento\NegotiableQuote\Plugin\Customer\Controller\Address;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;

/**
 * Class FormPostPlugin
 */
class FormPostPlugin
{
    /**
     * @var \Magento\Framework\Controller\ResultFactory
     */
    protected $resultFactory;

    /**
     * Redirect constructor.
     *
     * @param Context $context
     */
    public function __construct(
        Context $context
    ) {
        $this->resultFactory = $context->getResultFactory();
    }

    /**
     * @param \Magento\Customer\Controller\Address\FormPost $subject
     * @param ResultInterface $resultRedirect
     * @return ResultInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterExecute(
        \Magento\Customer\Controller\Address\FormPost $subject,
        \Magento\Framework\Controller\ResultInterface $resultRedirect
    ) {
        $quoteId = $subject->getRequest()->getParam('quoteId');
        if ($quoteId) {
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            return $resultRedirect->setPath(
                'negotiable_quote/quote/view',
                ['quote_id' => $quoteId]
            );
        }
        return $resultRedirect;
    }
}
