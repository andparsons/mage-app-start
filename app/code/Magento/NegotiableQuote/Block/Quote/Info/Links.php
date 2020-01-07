<?php

namespace Magento\NegotiableQuote\Block\Quote\Info;

use Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface;
use Magento\NegotiableQuote\Block\Quote\AbstractQuote;
use Magento\NegotiableQuote\Model\Restriction\RestrictionInterface;
use Magento\Framework\View\Element\Template\Context as TemplateContext;
use Magento\NegotiableQuote\Helper\Quote as NegotiableQuoteHelper;
use Magento\Company\Api\CompanyManagementInterface;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Company\Api\Data\CompanyInterface;
use Magento\Framework\App\ActionInterface;

/**
 * Block for displaying quote links.
 *
 * @api
 * @since 100.0.0
 */
class Links extends AbstractQuote
{
    /**
     * @var CompanyManagementInterface
     */
    private $companyManagement;

    /**
     * @var UserContextInterface
     */
    private $userContext;

    /**
     * @var RestrictionInterface
     */
    private $restriction;

    /**
     * @var \Magento\Company\Api\AuthorizationInterface
     */
    private $authorization;

    /**
     * @var \Magento\Framework\Url\EncoderInterface
     */
    private $urlEncoder;

    /**
     * @param TemplateContext $context
     * @param \Magento\Framework\Data\Helper\PostHelper $postDataHelper
     * @param NegotiableQuoteHelper $negotiableQuoteHelper
     * @param RestrictionInterface $restriction
     * @param CompanyManagementInterface $companyManagement
     * @param UserContextInterface $userContext
     * @param \Magento\Company\Api\AuthorizationInterface $authorization
     * @param \Magento\Framework\Url\EncoderInterface $urlEncoder
     * @param array $data [optional]
     */
    public function __construct(
        TemplateContext $context,
        \Magento\Framework\Data\Helper\PostHelper $postDataHelper,
        NegotiableQuoteHelper $negotiableQuoteHelper,
        RestrictionInterface $restriction,
        CompanyManagementInterface $companyManagement,
        UserContextInterface $userContext,
        \Magento\Company\Api\AuthorizationInterface $authorization,
        \Magento\Framework\Url\EncoderInterface $urlEncoder,
        array $data = []
    ) {
        parent::__construct($context, $postDataHelper, $negotiableQuoteHelper, $data);
        $this->companyManagement = $companyManagement;
        $this->userContext = $userContext;
        $this->restriction = $restriction;
        $this->authorization = $authorization;
        $this->urlEncoder = $urlEncoder;
    }

    /**
     * Add UENC referer to params.
     *
     * @param array $params
     * @return array
     */
    private function addRefererToParams(array $params)
    {
        $params[ActionInterface::PARAM_NAME_URL_ENCODED] =
            $this->urlEncoder->encode($this->_request->getServer('HTTP_REFERER'));
        return $params;
    }

    /**
     * Gets link parameters.
     *
     * @param string $url
     * @param bool $addReferer [optional]
     * @return string
     */
    private function retrieveLinkParams($url, $addReferer = false)
    {
        $params = ['quote_id' => $this->getQuote()->getId()];
        if ($addReferer) {
            $params = $this->addRefererToParams($params);
        }
        return $this->postDataHelper->getPostData($url, $params);
    }

    /**
     * Get url for delete action.
     *
     * @return string
     */
    public function getDeleteUrl()
    {
        return $this->getUrl('negotiable_quote/quote/delete', ['quote_id' => $this->getQuote()->getId()]);
    }

    /**
     * Is delete available.
     *
     * @return bool
     */
    public function isDeleteAvailable()
    {
        return $this->restriction->canDelete();
    }

    /**
     * Is close available.
     *
     * @return bool
     */
    public function isCloseAvailable()
    {
        return $this->restriction->canClose();
    }

    /**
     * Get url for close action.
     *
     * @return string
     */
    public function getCloseUrl()
    {
        return $this->getUrl('negotiable_quote/quote/close');
    }

    /**
     * Retrieve params for closing negotiable quote.
     *
     * @param bool $addReferrer [optional]
     * @return string
     */
    public function getCloseParams($addReferrer = false)
    {
        return $this->retrieveLinkParams($this->getCloseUrl(), $addReferrer);
    }

    /**
     * Retrieve params for deleting negotiable quote.
     *
     * @param bool $addReferrer [optional]
     * @return string
     */
    public function getDeleteParams($addReferrer = false)
    {
        return $this->retrieveLinkParams($this->getDeleteUrl(), $addReferrer);
    }

    /**
     * Is proceed to checkout enabled.
     *
     * @return bool
     */
    public function isProceedToCheckoutAvailable()
    {
        return $this->restriction->canProceedToCheckout();
    }

    /**
     * Is expiration popup displayed.
     *
     * @return bool
     */
    public function isExpirationPopupDisplayed()
    {
        return $this->restriction->isExpiredMessageDisplayed();
    }

    /**
     * Get url for proceed to checkout action.
     *
     * @return string
     */
    public function getProceedToCheckoutUrl()
    {
        return $this->getUrl(
            '*/quote/checkout',
            ['negotiableQuoteId' => $this->getQuote()->getId()]
        );
    }

    /**
     * Retrieve params for closing negotiable quote.
     *
     * @param bool $addReferrer [optional]
     * @return string
     */
    public function getProceedToCheckoutParams($addReferrer = false)
    {
        return $this->retrieveLinkParams($this->getProceedToCheckoutUrl(), $addReferrer);
    }

    /**
     * Is proceed to checkout enabled.
     *
     * @return bool
     */
    public function isSendAvailable()
    {
        return $this->restriction->canSubmit();
    }

    /**
     * Get url for send action.
     *
     * @return string
     */
    public function getSendUrl()
    {
        return $this->getUrl('negotiable_quote/quote/send');
    }

    /**
     * Retrieve params for closing negotiable quote.
     *
     * @param bool $addReferrer [optional]
     * @return string
     */
    public function getSendParams($addReferrer = false)
    {
        return $this->retrieveLinkParams($this->getSendUrl(), $addReferrer);
    }

    /**
     * Get url for printing quote.
     *
     * @return string
     */
    public function getPrintUrl()
    {
        return $this->getUrl('negotiable_quote/quote/print', ['quote_id' => $this->getQuote()->getId()]);
    }

    /**
     * Get url for remove negotiation.
     *
     * @return string
     */
    public function getRemoveNegotiationUrl()
    {
        return $this->getUrl('negotiable_quote/quote/removeNegotiation');
    }

    /**
     * Returns if quote is new.
     *
     * @return bool
     */
    protected function isQuoteNew()
    {
        $quote = $this->getQuote();
        $negotiableQuote = $quote->getExtensionAttributes()->getNegotiableQuote();

        return $quote->getExtensionAttributes()
                && $negotiableQuote
                && ($negotiableQuote->getStatus() === NegotiableQuoteInterface::STATUS_CREATED);
    }

    /**
     * Check company status.
     *
     * @return bool
     */
    public function isCheckoutLinkVisible()
    {
        $isVisible = true;
        $customerId = $this->userContext->getUserId();
        if ($customerId) {
            $company = $this->companyManagement->getByCustomerId($customerId);
            if ($company && $company->getStatus() == CompanyInterface::STATUS_BLOCKED) {
                $isVisible = false;
            }
        }
        if (!$this->authorization->isAllowed('Magento_NegotiableQuote::checkout')) {
            $isVisible = false;
        }

        return $isVisible;
    }

    /**
     * Returns if current customer is allowed to manage quotes.
     *
     * @return bool
     */
    public function isAllowedManage()
    {
        return $this->authorization->isAllowed('Magento_NegotiableQuote::manage');
    }
}
