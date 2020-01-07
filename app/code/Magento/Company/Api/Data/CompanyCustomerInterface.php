<?php
namespace Magento\Company\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Extended customer custom attributes interface.
 *
 * @api
 * @since 100.0.0
 */
interface CompanyCustomerInterface extends ExtensibleDataInterface
{
    /**
     * Customer id key.
     */
    const CUSTOMER_ID = 'customer_id';

    /**
     * Company id key.
     */
    const COMPANY_ID = 'company_id';

    /**
     * Job title key.
     */
    const JOB_TITLE = 'job_title';

    /**
     * Status key.
     */
    const STATUS = 'status';

    /**
     * Telephone key.
     */
    const TELEPHONE = 'telephone';

    /**
     * Status inactive value.
     */
    const STATUS_INACTIVE = 0;

    /**
     * Status active value.
     */
    const STATUS_ACTIVE = 1;

    /**
     * Company admin type value.
     */
    const TYPE_COMPANY_ADMIN = 0;

    /**
     * Company user type value.
     */
    const TYPE_COMPANY_USER = 1;

    /**
     * Individual user type value.
     */
    const TYPE_INDIVIDUAL_USER = 2;

    /**
     * Get customer ID.
     *
     * @return int|null
     */
    public function getCustomerId();

    /**
     * Get company ID.
     *
     * @return int|null
     */
    public function getCompanyId();

    /**
     * Get get job title.
     *
     * @return string|null
     */
    public function getJobTitle();

    /**
     * Get customer status.
     *
     * @return int|null
     */
    public function getStatus();

    /**
     * Get get telephone.
     *
     * @return string|null
     */
    public function getTelephone();

    /**
     * Set customer ID.
     *
     * @param int $id
     * @return \Magento\Company\Api\Data\CompanyCustomerInterface
     */
    public function setCustomerId($id);

    /**
     * Set company ID.
     *
     * @param int $companyId
     * @return \Magento\Company\Api\Data\CompanyCustomerInterface
     */
    public function setCompanyId($companyId);

    /**
     * Set job title.
     *
     * @param string $jobTitle
     * @return \Magento\Company\Api\Data\CompanyCustomerInterface
     */
    public function setJobTitle($jobTitle);

    /**
     * Set customer status.
     *
     * @param int $status
     * @return \Magento\Company\Api\Data\CompanyCustomerInterface
     */
    public function setStatus($status);

    /**
     * Set telephone.
     *
     * @param string $telephone
     * @return \Magento\Company\Api\Data\CompanyCustomerInterface
     */
    public function setTelephone($telephone);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Company\Api\Data\CompanyCustomerExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Company\Api\Data\CompanyCustomerExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magento\Company\Api\Data\CompanyCustomerExtensionInterface $extensionAttributes
    );
}
