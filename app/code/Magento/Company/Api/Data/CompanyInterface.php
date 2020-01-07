<?php
namespace Magento\Company\Api\Data;

/**
 * Interface for Company entity.
 *
 * @api
 * @since 100.0.0
 */
interface CompanyInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    const COMPANY_ID = 'entity_id';
    const STATUS = 'status';
    const NAME = 'company_name';
    const LEGAL_NAME = 'legal_name';
    const COMPANY_EMAIL = 'company_email';
    const EMAIL = 'email';
    const VAT_TAX_ID = 'vat_tax_id';
    const RESELLER_ID = 'reseller_id';
    const COMMENT = 'comment';
    const STREET = 'street';
    const CITY = 'city';
    const COUNTRY_ID = 'country_id';
    const REGION = 'region';
    const REGION_ID = 'region_id';
    const POSTCODE = 'postcode';
    const TELEPHONE = 'telephone';
    const JOB_TITLE = 'job_title';
    const PREFIX = 'prefix';
    const FIRSTNAME = 'firstname';
    const MIDDLENAME = 'middlename';
    const LASTNAME = 'lastname';
    const SUFFIX = 'suffix';
    const GENDER = 'gender';
    const CUSTOMER_GROUP_ID = 'customer_group_id';
    const SALES_REPRESENTATIVE_ID = 'sales_representative_id';
    const REJECT_REASON = 'reject_reason';
    const REJECTED_AT = 'rejected_at';
    const SUPER_USER_ID = 'super_user_id';

    const STATUS_PENDING = 0;
    const STATUS_APPROVED = 1;
    const STATUS_REJECTED = 2;
    const STATUS_BLOCKED = 3;

    /**
     * Get Id.
     *
     * @return int|null
     */
    public function getId();

    /**
     * Get status.
     *
     * @return int|null
     */
    public function getStatus();

    /**
     * Get company name.
     *
     * @return string|null
     */
    public function getCompanyName();

    /**
     * Get legal name.
     *
     * @return string|null
     */
    public function getLegalName();

    /**
     * Get company email.
     *
     * @return string|null
     */
    public function getCompanyEmail();

    /**
     * Get vat tax id.
     *
     * @return string|null
     */
    public function getVatTaxId();

    /**
     * Get reseller Id.
     *
     * @return string|null
     */
    public function getResellerId();

    /**
     * Get comment.
     *
     * @return string|null
     */
    public function getComment();

    /**
     * Get street.
     *
     * @return string[]
     */
    public function getStreet();

    /**
     * Get city.
     *
     * @return string|null
     */
    public function getCity();

    /**
     * Get country.
     *
     * @return string|null
     */
    public function getCountryId();

    /**
     * Get region.
     *
     * @return string|null
     */
    public function getRegion();

    /**
     * Get region Id.
     *
     * @return string|null
     */
    public function getRegionId();

    /**
     * Get postcode.
     *
     * @return string|null
     */
    public function getPostcode();

    /**
     * Get telephone.
     *
     * @return string|null
     */
    public function getTelephone();

    /**
     * Set Id.
     *
     * @param int $id
     * @return \Magento\Company\Api\Data\CompanyInterface
     */
    public function setId($id);

    /**
     * Set status.
     *
     * @param int $status
     * @return \Magento\Company\Api\Data\CompanyInterface
     */
    public function setStatus($status);

    /**
     * Set company name.
     *
     * @param string $companyName
     * @return \Magento\Company\Api\Data\CompanyInterface
     */
    public function setCompanyName($companyName);

    /**
     * Set legal name.
     *
     * @param string $legalName
     * @return \Magento\Company\Api\Data\CompanyInterface
     */
    public function setLegalName($legalName);

    /**
     * Set company email.
     *
     * @param string $companyEmail
     * @return \Magento\Company\Api\Data\CompanyInterface
     */
    public function setCompanyEmail($companyEmail);

    /**
     * Set vat tax id.
     *
     * @param string $vatTaxId
     * @return \Magento\Company\Api\Data\CompanyInterface
     */
    public function setVatTaxId($vatTaxId);

    /**
     * Set reseller id.
     *
     * @param string $resellerId
     * @return \Magento\Company\Api\Data\CompanyInterface
     */
    public function setResellerId($resellerId);

    /**
     * Set comment.
     *
     * @param string $comment
     * @return \Magento\Company\Api\Data\CompanyInterface
     */
    public function setComment($comment);

    /**
     * Set street.
     *
     * @param array|string $street
     * @return \Magento\Company\Api\Data\CompanyInterface
     */
    public function setStreet($street);

    /**
     * Set city.
     *
     * @param string $city
     * @return \Magento\Company\Api\Data\CompanyInterface
     */
    public function setCity($city);

    /**
     * Set country.
     *
     * @param string $country
     * @return \Magento\Company\Api\Data\CompanyInterface
     */
    public function setCountryId($country);

    /**
     * Set region.
     *
     * @param string $region
     * @return \Magento\Company\Api\Data\CompanyInterface
     */
    public function setRegion($region);

    /**
     * Set region Id.
     *
     * @param string $regionId
     * @return \Magento\Company\Api\Data\CompanyInterface
     */
    public function setRegionId($regionId);

    /**
     * Set postcode.
     *
     * @param string $postcode
     * @return \Magento\Company\Api\Data\CompanyInterface
     */
    public function setPostcode($postcode);

    /**
     * Set telephone.
     *
     * @param string $telephone
     * @return \Magento\Company\Api\Data\CompanyInterface
     */
    public function setTelephone($telephone);

    /**
     * Set Customer Group Id.
     *
     * @param int $customerGroupId
     * @return \Magento\Company\Api\Data\CompanyInterface
     */
    public function setCustomerGroupId($customerGroupId);

    /**
     * Get Customer Group Id.
     *
     * @return int
     */
    public function getCustomerGroupId();

    /**
     * Set Sales Representative Id.
     *
     * @param int $salesRepresentativeId
     * @return \Magento\Company\Api\Data\CompanyInterface
     */
    public function setSalesRepresentativeId($salesRepresentativeId);

    /**
     * Get Sales Representative Id.
     *
     * @return int
     */
    public function getSalesRepresentativeId();

    /**
     * Set Reject Reason.
     *
     * @param string $rejectReason
     * @return \Magento\Company\Api\Data\CompanyInterface
     */
    public function setRejectReason($rejectReason);

    /**
     * Get Reject Reason.
     *
     * @return string
     */
    public function getRejectReason();

    /**
     * Set rejected at time.
     *
     * @param string $rejectedAt
     * @return \Magento\Company\Api\Data\CompanyInterface
     */
    public function setRejectedAt($rejectedAt);

    /**
     * Get rejected at time.
     *
     * @return string
     */
    public function getRejectedAt();

    /**
     * Set company admin customer id.
     *
     * @param int $superUserId
     * @return \Magento\Company\Api\Data\CompanyInterface
     */
    public function setSuperUserId($superUserId);

    /**
     * Get company admin customer id.
     *
     * @return int
     */
    public function getSuperUserId();

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Company\Api\Data\CompanyExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Company\Api\Data\CompanyExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(\Magento\Company\Api\Data\CompanyExtensionInterface $extensionAttributes);
}
