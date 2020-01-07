<?php

namespace Magento\Company\Model;

use Magento\Company\Api\Data\CompanyInterface;
use Magento\Framework\Model\AbstractExtensibleModel;
use Magento\Framework\Api\AttributeValueFactory;

/**
 * Class that implements interface for data transfer object of company entity.
 *
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 */
class Company extends AbstractExtensibleModel implements CompanyInterface
{
    /**
     * Cache tag.
     */
    const CACHE_TAG = 'company';

    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = 'company';

    /**
     * Initialize resource model.
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magento\Company\Model\ResourceModel\Company::class);
    }

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource [optional]
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection [optional]
     * @param array $data [optional]
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->getData(self::COMPANY_ID);
    }

    /**
     * @inheritdoc
     */
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    /**
     * @inheritdoc
     */
    public function getCompanyName()
    {
        return $this->getData(self::NAME);
    }

    /**
     * @inheritdoc
     */
    public function getLegalName()
    {
        return $this->getData(self::LEGAL_NAME);
    }

    /**
     * @inheritdoc
     */
    public function getCompanyEmail()
    {
        return $this->getData(self::COMPANY_EMAIL);
    }

    /**
     * @inheritdoc
     */
    public function getVatTaxId()
    {
        return $this->getData(self::VAT_TAX_ID);
    }

    /**
     * @inheritdoc
     */
    public function getResellerId()
    {
        return $this->getData(self::RESELLER_ID);
    }

    /**
     * @inheritdoc
     */
    public function getComment()
    {
        return $this->getData(self::COMMENT);
    }

    /**
     * @inheritdoc
     */
    public function getCity()
    {
        return $this->getData(self::CITY);
    }

    /**
     * @inheritdoc
     */
    public function getCountryId()
    {
        return $this->getData(self::COUNTRY_ID);
    }

    /**
     * @inheritdoc
     */
    public function getRegion()
    {
        return $this->getData(self::REGION);
    }

    /**
     * @inheritdoc
     */
    public function getRegionId()
    {
        return $this->getData(self::REGION_ID);
    }

    /**
     * @inheritdoc
     */
    public function getPostcode()
    {
        return $this->getData(self::POSTCODE);
    }

    /**
     * @inheritdoc
     */
    public function getTelephone()
    {
        return $this->getData(self::TELEPHONE);
    }

    /**
     * @inheritdoc
     */
    public function setId($id)
    {
        return $this->setData(self::COMPANY_ID, $id);
    }

    /**
     * @inheritdoc
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * @inheritdoc
     */
    public function setCompanyName($companyName)
    {
        return $this->setData(self::NAME, $companyName);
    }

    /**
     * @inheritdoc
     */
    public function setLegalName($legalName)
    {
        return $this->setData(self::LEGAL_NAME, $legalName);
    }

    /**
     * @inheritdoc
     */
    public function setCompanyEmail($companyEmail)
    {
        return $this->setData(self::COMPANY_EMAIL, $companyEmail);
    }

    /**
     * @inheritdoc
     */
    public function setVatTaxId($vatTaxId)
    {
        return $this->setData(self::VAT_TAX_ID, $vatTaxId);
    }

    /**
     * @inheritdoc
     */
    public function setResellerId($resellerId)
    {
        return $this->setData(self::RESELLER_ID, $resellerId);
    }

    /**
     * @inheritdoc
     */
    public function setComment($comment)
    {
        return $this->setData(self::COMMENT, $comment);
    }

    /**
     * @inheritdoc
     */
    public function setStreet($street)
    {
        if (is_array($street) && count($street)) {
            $isScalar = false;
            foreach ($street as $val) {
                if (is_scalar($val)) {
                    $isScalar = true;
                }
            }
            if ($isScalar) {
                $street = trim(implode("\n", $street));
            }
        }
        return $this->setData(self::STREET, $street);
    }

    /**
     * @inheritdoc
     */
    public function getStreet()
    {
        if (is_array($this->getStreetFull())) {
            return $this->getStreetFull();
        }
        return explode("\n", $this->getStreetFull());
    }

    /**
     * @inheritdoc
     */
    public function getStreetLine($number)
    {
        $lines = $this->getStreet();
        return isset($lines[$number - 1]) ? $lines[$number - 1] : '';
    }

    /**
     * @inheritdoc
     */
    public function getStreetFull()
    {
        return $this->getData(self::STREET);
    }

    /**
     * @inheritdoc
     */
    public function setStreetFull($street)
    {
        return $this->setStreet($street);
    }

    /**
     * @inheritdoc
     */
    public function explodeStreetAddress()
    {
        $streetLines = $this->getStreet();
        foreach ($streetLines as $i => $line) {
            $this->setData('street' . ($i + 1), $line);
        }
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setCity($city)
    {
        return $this->setData(self::CITY, $city);
    }

    /**
     * @inheritdoc
     */
    public function setCountryId($country)
    {
        return $this->setData(self::COUNTRY_ID, $country);
    }

    /**
     * @inheritdoc
     */
    public function setRegion($region)
    {
        return $this->setData(self::REGION, $region);
    }

    /**
     * @inheritdoc
     */
    public function setRegionId($regionId)
    {
        return $this->setData(self::REGION_ID, $regionId);
    }

    /**
     * @inheritdoc
     */
    public function setPostcode($postcode)
    {
        return $this->setData(self::POSTCODE, $postcode);
    }

    /**
     * @inheritdoc
     */
    public function setTelephone($telephone)
    {
        return $this->setData(self::TELEPHONE, $telephone);
    }

    /**
     * @inheritdoc
     */
    public function setCustomerGroupId($customerGroupId)
    {
        return $this->setData(self::CUSTOMER_GROUP_ID, $customerGroupId);
    }

    /**
     * @inheritdoc
     */
    public function getCustomerGroupId()
    {
        return $this->getData(self::CUSTOMER_GROUP_ID);
    }

    /**
     * @inheritdoc
     */
    public function setSalesRepresentativeId($salesRepresentativeId)
    {
        return $this->setData(self::SALES_REPRESENTATIVE_ID, $salesRepresentativeId);
    }

    /**
     * @inheritdoc
     */
    public function getSalesRepresentativeId()
    {
        return $this->getData(self::SALES_REPRESENTATIVE_ID);
    }

    /**
     * @inheritdoc
     */
    public function setSuperUserId($superUserId)
    {
        return $this->setData(self::SUPER_USER_ID, $superUserId);
    }

    /**
     * @inheritdoc
     */
    public function getSuperUserId()
    {
        return $this->getData(self::SUPER_USER_ID);
    }

    /**
     * @inheritdoc
     */
    public function setRejectReason($rejectReason)
    {
        return $this->setData(self::REJECT_REASON, $rejectReason);
    }

    /**
     * @inheritdoc
     */
    public function getRejectReason()
    {
        return $this->getData(self::REJECT_REASON);
    }

    /**
     * @inheritdoc
     */
    public function setRejectedAt($rejectedAt)
    {
        return $this->setData(self::REJECTED_AT, $rejectedAt);
    }

    /**
     * @inheritdoc
     */
    public function getRejectedAt()
    {
        return $this->getData(self::REJECTED_AT);
    }

    /**
     * @inheritdoc
     */
    public function getExtensionAttributes()
    {
        if (!$this->_getExtensionAttributes()) {
            $this->setExtensionAttributes(
                $this->extensionAttributesFactory->create(get_class($this))
            );
        }
        return $this->_getExtensionAttributes();
    }

    /**
     * @inheritdoc
     */
    public function setExtensionAttributes(\Magento\Company\Api\Data\CompanyExtensionInterface $extensionAttributes)
    {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
