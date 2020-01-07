<?php
namespace Magento\Company\Block\Adminhtml\Customer\Edit\Tab\View;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Company\Api\CompanyRepositoryInterface;
use Magento\Company\Api\Data\CompanyCustomerInterface;
use Magento\Company\Api\Data\CompanyInterface;
use Magento\Company\Model\Customer\Source\CustomerType as CustomerTypeSource;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Adminhtml customer view extended personal information block.
 *
 * @api
 * @since 100.0.0
 */
class PersonalInfo extends Template
{
    /**
     * @var CompanyCustomerInterface
     */
    private $customerAttributes;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var CompanyRepositoryInterface
     */
    private $companyRepository;

    /**
     * @var CustomerTypeSource
     */
    private $customerTypeSource;

    /**
     * @param Context $context
     * @param CustomerRepositoryInterface $customerRepository
     * @param CompanyRepositoryInterface $companyRepository
     * @param CustomerTypeSource $customerTypeSource
     * @param array $data [optional]
     */
    public function __construct(
        Context $context,
        CustomerRepositoryInterface $customerRepository,
        CompanyRepositoryInterface $companyRepository,
        CustomerTypeSource $customerTypeSource,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->customerRepository = $customerRepository;
        $this->companyRepository = $companyRepository;
        $this->customerTypeSource = $customerTypeSource;
    }

    /**
     * Retrieve customer extension attributes.
     *
     * @return CompanyCustomerInterface|null
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCustomerAttributes()
    {
        if (!$this->customerAttributes) {
            if (isset($this->_backendSession->getCustomerData()['account']['id'])) {
                $customer = $this->_backendSession->getCustomerData()['account'];
                $this->customerAttributes = $this->customerRepository->getById($customer['id'])
                    ->getExtensionAttributes()->getCompanyAttributes();
            }
        }
        return $this->customerAttributes;
    }

    /**
     * Retrieve job title.
     *
     * @return string
     */
    public function getJobTitle()
    {
        $jobTitle = '';
        if ($this->getCustomerAttributes()) {
            $jobTitle = $this->getCustomerAttributes()->getJobTitle();
        }
        return $jobTitle;
    }

    /**
     * Retrieve customer type.
     *
     * @return int
     */
    public function getCustomerType()
    {
        $customerType = CompanyCustomerInterface::TYPE_INDIVIDUAL_USER;
        if ($this->getCustomerAttributes() && $this->getCustomerAttributes()->getCompanyId()) {
            $company = $this->getCompany();
            $customer = $this->_backendSession->getCustomerData()['account'];
            $customerType = ($company->getSuperUserId() == $customer['id'])
                ? CompanyCustomerInterface::TYPE_COMPANY_ADMIN
                : CompanyCustomerInterface::TYPE_COMPANY_USER;
        }

        return $customerType;
    }

    /**
     * Get label by customer type value.
     *
     * @param int $type
     * @return null|string
     */
    public function getCustomerTypeLabel($type)
    {
        return $this->customerTypeSource->getLabel($type);
    }

    /**
     * Retrieve company name.
     *
     * @return string
     */
    public function getCompanyName()
    {
        $companyName = '';
        if ($this->getCompany()) {
            $companyName = $this->getCompany()->getCompanyName();
        }
        return $companyName;
    }

    /**
     * Get company.
     *
     * @return CompanyInterface|null
     */
    public function getCompany()
    {
        $company = null;
        if ($this->getCustomerAttributes()) {
            $companyId = $this->getCustomerAttributes()->getCompanyId();
            try {
                $company = $this->companyRepository->get($companyId);
            } catch (NoSuchEntityException $e) {
                $company = null;
            }
        }
        return $company;
    }
}
