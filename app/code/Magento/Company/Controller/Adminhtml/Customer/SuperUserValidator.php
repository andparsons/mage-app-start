<?php
namespace Magento\Company\Controller\Adminhtml\Customer;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;

/**
 * Class that checks if any of customers is a company admin during the mass delete action in admin panel.
 */
class SuperUserValidator extends \Magento\Backend\App\Action implements HttpGetActionInterface
{
    /**
     * @var \Magento\Company\Model\Customer\CompanyAttributes
     */
    private $companyAttributes;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var \Magento\Company\Api\CompanyRepositoryInterface
     */
    private $companyRepository;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Company\Model\Customer\CompanyAttributes $companyAttributes
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Company\Api\CompanyRepositoryInterface $companyRepository
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Company\Model\Customer\CompanyAttributes $companyAttributes,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Company\Api\CompanyRepositoryInterface $companyRepository
    ) {
        parent::__construct($context);
        $this->companyAttributes = $companyAttributes;
        $this->customerRepository = $customerRepository;
        $this->companyRepository = $companyRepository;
    }

    /**
     * Checks if any of customers is a company admin during the mass delete action in admin panel.
     *
     * @return \Magento\Framework\Controller\ResultInterface
     * @throws \InvalidArgumentException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        $customerIds = (array)$this->getRequest()->getParam('customer_ids');
        $result = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON);
        try {
            foreach ($customerIds as $customerId) {
                $customer = $this->customerRepository->getById($customerId);
                $companyAttributes = $this->companyAttributes->getCompanyAttributesByCustomer($customer);
                $companyId = $companyAttributes->getCompanyId();
                if ($companyId) {
                    $company = $this->companyRepository->get($companyId);
                    if ($company->getSuperUserId() == $customerId) {
                        return $result->setData(['deletable' => false]);
                    }
                }
            }
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return $result->setData(['deletable' => false]);
        }
        return $result->setData(['deletable' => true]);
    }
}
