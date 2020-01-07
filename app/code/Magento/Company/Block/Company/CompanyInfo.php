<?php
namespace Magento\Company\Block\Company;

use Magento\Backend\Block\Template\Context;
use Magento\Company\Api\Data\CompanyCustomerInterface;
use Magento\Company\Api\Data\CompanyInterface;
use Magento\Company\Api\CompanyRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Adminhtml customer view extended personal information block
 *
 * @api
 * @since 100.0.0
 */
class CompanyInfo extends \Magento\Framework\View\Element\Template
{
    /**
     * @var CompanyCustomerInterface
     */
    protected $customerAttributes;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var CompanyRepositoryInterface
     */
    protected $companyRepository;

    /**
     * @var UserContextInterface
     */
    protected $customerContext;

    /**
     * @param Context $context
     * @param CustomerRepositoryInterface $customerRepository
     * @param CompanyRepositoryInterface $companyRepository
     * @param UserContextInterface $customerContext
     * @param array $data
     */
    public function __construct(
        Context $context,
        CustomerRepositoryInterface $customerRepository,
        CompanyRepositoryInterface $companyRepository,
        UserContextInterface $customerContext,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->customerRepository = $customerRepository;
        $this->companyRepository = $companyRepository;
        $this->customerContext = $customerContext;
    }

    /**
     * Retrieve customer extension attributes
     *
     * @return CompanyCustomerInterface|null
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCustomerAttributes()
    {
        if (!$this->customerAttributes && $this->customerContext->getUserId()) {
            $this->customerAttributes = $this->customerRepository->getById($this->customerContext->getUserId())
                ->getExtensionAttributes()->getCompanyAttributes();
        }
        return $this->customerAttributes;
    }

    /**
     * Retrieve job title
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
     * Retrieve company name
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
     * Get company
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
