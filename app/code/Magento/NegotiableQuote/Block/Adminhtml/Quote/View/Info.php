<?php

namespace Magento\NegotiableQuote\Block\Adminhtml\Quote\View;

use Magento\Authorization\Model\UserContextInterface;

/**
 * Quote block represents quote details on quote view page.
 *
 * @api
 * @since 100.0.0
 */
class Info extends \Magento\NegotiableQuote\Block\Quote\Info
{
    /**
     * @var \Magento\NegotiableQuote\Model\Creator
     */
    private $creator;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\NegotiableQuote\Helper\Quote $negotiableQuoteHelper
     * @param \Magento\NegotiableQuote\Model\Status\LabelProviderInterface $labelProvider
     * @param \Magento\NegotiableQuote\Model\Expiration $expiration
     * @param \Magento\Company\Api\AuthorizationInterface $authorization
     * @param \Magento\NegotiableQuote\Model\Company\DetailsProviderFactory $companyDetailsProviderFactory
     * @param \Magento\NegotiableQuote\Model\Customer\AddressProviderFactory $addressProviderFactory
     * @param \Magento\NegotiableQuote\Model\Creator $creator
     * @param array $data [optional]
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\NegotiableQuote\Helper\Quote $negotiableQuoteHelper,
        \Magento\NegotiableQuote\Model\Status\LabelProviderInterface $labelProvider,
        \Magento\NegotiableQuote\Model\Expiration $expiration,
        \Magento\Company\Api\AuthorizationInterface $authorization,
        \Magento\NegotiableQuote\Model\Company\DetailsProviderFactory $companyDetailsProviderFactory,
        \Magento\NegotiableQuote\Model\Customer\AddressProviderFactory $addressProviderFactory,
        \Magento\NegotiableQuote\Model\Creator $creator,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $negotiableQuoteHelper,
            $labelProvider,
            $expiration,
            $authorization,
            $companyDetailsProviderFactory,
            $addressProviderFactory,
            $data
        );
        $this->creator = $creator;
    }

    /**
     * Get company name.
     *
     * @return string
     */
    public function getCompanyName()
    {
        return $this->getCompanyDetailsProvider()->getCompanyName();
    }

    /**
     * Company exists.
     *
     * @return bool
     */
    public function existsCompany()
    {
        return (bool)$this->getCompanyDetailsProvider()->getCompany();
    }

    /**
     * Get company admin email.
     *
     * @return string
     */
    public function getCompanyAdminEmail()
    {
        return $this->getCompanyDetailsProvider()->getCompanyAdminEmail();
    }

    /**
     * Retrieve company edit url.
     *
     * @return string
     */
    public function getCompanyUrl()
    {
        $companyUrl = '';
        if ($this->getCompanyDetailsProvider()->getCompany() !== null) {
            $companyUrl = $this->getUrl(
                'company/index/edit',
                ['id' => $this->getCompanyDetailsProvider()->getCompany()->getId()]
            );
        }

        return $companyUrl;
    }

    /**
     * Retrieve company admin user url.
     *
     * @return string
     */
    public function getCompanyAdminUrl()
    {
        $companyAdminUrl = '';
        $customer = $this->getCompanyDetailsProvider()->getCompanyAdmin();

        if (!empty($customer['customer_id'])) {
            $companyAdminUrl = $this->getUrl(
                'customer/index/edit',
                ['id' => $customer['customer_id']]
            );
        }

        return $companyAdminUrl;
    }

    /**
     * Retrieve sales representative url.
     *
     * @return string
     */
    public function getSalesRepUrl()
    {
        $salesRepUrl = '';

        if ($this->getCompanyDetailsProvider()->getCompany()) {
            $id = $this->getCompanyDetailsProvider()->getCompany()->getSalesRepresentativeId();
            $salesRepUrl = $this->getUrl(
                'adminhtml/user/edit',
                ['user_id' => $id]
            );
        }

        return $salesRepUrl;
    }

    /**
     * Sales Representative exists.
     *
     * @return bool
     */
    public function existsSalesRepresentative()
    {
        return $this->getCompanyDetailsProvider()->existsSalesRepresentative();
    }

    /**
     * @inheritdoc
     */
    public function getQuoteOwnerFullName()
    {
        $name = parent::getQuoteOwnerFullName();
        $adminTypes = [UserContextInterface::USER_TYPE_ADMIN, UserContextInterface::USER_TYPE_INTEGRATION];
        $negotiableQuote = $this->getNegotiableQuote();
        if ($negotiableQuote !== null && in_array($negotiableQuote->getCreatorType(), $adminTypes)) {
            $creatorName = $this->creator->retrieveCreatorName(
                $this->getNegotiableQuote()->getCreatorType(),
                $this->getNegotiableQuote()->getCreatorId(),
                $this->getNegotiableQuote()->getQuoteId()
            );
            $name = __(
                '%creator (for %customer)',
                ['creator' => $creatorName, 'customer' => $name]
            );
        }
        return $name;
    }
}
