<?php

namespace Magento\NegotiableQuote\Plugin\Checkout\Controller\Index;

/**
 * Class IndexPlugin.
 */
class IndexPlugin
{
    /**
     * @var \Magento\Framework\App\Action\Context
     */
    private $context;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var \Magento\NegotiableQuote\Model\Restriction\RestrictionInterface
     */
    private $restriction;

    /**
     * @var \Magento\Company\Model\CompanyUserPermission
     */
    private $companyUserPermission;

    /**
     * @var \Magento\Authorization\Model\UserContextInterface
     */
    private $customerContext;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param \Magento\NegotiableQuote\Model\Restriction\RestrictionInterface $restriction
     * @param \Magento\Company\Model\CompanyUserPermission $companyUserPermission
     * @param \Magento\Authorization\Model\UserContextInterface $customerContext
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\NegotiableQuote\Model\Restriction\RestrictionInterface $restriction,
        \Magento\Company\Model\CompanyUserPermission $companyUserPermission,
        \Magento\Authorization\Model\UserContextInterface $customerContext
    ) {
        $this->context = $context;
        $this->quoteRepository = $quoteRepository;
        $this->restriction = $restriction;
        $this->companyUserPermission = $companyUserPermission;
        $this->customerContext = $customerContext;
    }

    /**
     * Plugin for checking restriction for negotiable quote.
     *
     * @param \Magento\Checkout\Controller\Index\Index $subject
     * @param \Closure $proceed
     * @return \Magento\Framework\Controller\ResultInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundExecute(
        \Magento\Checkout\Controller\Index\Index $subject,
        \Closure $proceed
    ) {
        $negotiableQuoteId = (int) $this->context->getRequest()->getParam('negotiableQuoteId');

        if ($negotiableQuoteId) {
            $quoteExists = true;

            try {
                $quote = $this->quoteRepository->get($negotiableQuoteId);
                $restriction = $this->restriction->setQuote($quote);
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                $quoteExists = false;
                $restriction = $this->restriction;
            }

            if (!$restriction->canProceedToCheckout() && $quoteExists) {
                $resultRedirect = $this->context->getResultRedirectFactory()->create();

                $userType = $this->customerContext->getUserType();
                $userId = $this->customerContext->getUserId();
                if (empty($userId)
                    || $userType != \Magento\Authorization\Model\UserContextInterface::USER_TYPE_CUSTOMER
                ) {
                    $resultRedirect->setPath('customer/account/login');
                } elseif ($this->companyUserPermission->isCurrentUserCompanyUser()) {
                    $resultRedirect->setPath('company/accessdenied');
                } else {
                    $resultRedirect->setPath('noroute');
                }

                return $resultRedirect;
            }
        }

        return $proceed();
    }
}
