<?php
namespace Magento\Company\Controller\Adminhtml\Index;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Controller\ResultFactory;
use Magento\Customer\Api\Data\CustomerInterface;

/**
 * Class for add user to the company in admin panel on company edit page.
 */
class AddUser extends \Magento\Backend\App\Action implements HttpGetActionInterface
{
    /**
     * @var \Magento\Company\Model\CustomerRetriever
     */
    private $customerRetriever;

    /**
     * @var \Magento\Company\Api\CompanyManagementInterface
     */
    protected $companyManagement;

    /** @var \Psr\Log\LoggerInterface */
    protected $logger;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Company\Model\CustomerRetriever $customerRetriever
     * @param \Magento\Company\Api\CompanyManagementInterface $companyManagement
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Company\Model\CustomerRetriever $customerRetriever,
        \Magento\Company\Api\CompanyManagementInterface $companyManagement,
        \Psr\Log\LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->customerRetriever = $customerRetriever;
        $this->companyManagement = $companyManagement;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     * @throws \InvalidArgumentException
     */
    public function execute()
    {
        try {
            $email = $this->getRequestedEmail();
            $websiteId = $this->getRequestedWebsiteId();

            $customer = $this->customerRetriever->retrieveForWebsite(
                $email,
                $websiteId
            );
            if ($customer) {
                $result = $this->getCustomerData($customer);
            } else {
                $result = [
                    'is_new_customer' => true
                ];
            }
        } catch (LocalizedException $e) {
            $result = [
                'error' => $e->getMessage()
            ];
        } catch (\Exception $e) {
            $result = [
                'error' => __('Something went wrong.')
            ];
        }

        /** @var \Magento\Framework\Controller\Result\Json $response */
        $response = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $response->setData($result);
        return $response;
    }

    /**
     * Get requested email.
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Zend_Validate_Exception
     */
    private function getRequestedEmail()
    {
        $email = $this->getRequest()->getParam('email');
        $isValidEmail = \Zend_Validate::is(
            $email,
            'EmailAddress'
        );
        if (!$isValidEmail) {
            throw new LocalizedException(
                __('Invalid value of "%value" provided for the email field.', ['value' => $email])
            );
        }
        return $email;
    }

    /**
     * Retrieve requested website ID.
     *
     * @return int|null
     * @throws LocalizedException
     */
    private function getRequestedWebsiteId()
    {
        /** @var string|null $websiteId */
        $websiteId = $this->getRequest()->getParam('website_id');
        if ($websiteId !== null) {
            if (!\Zend_Validate::is($websiteId, 'Int')) {
                throw new LocalizedException(
                    __(
                        'Invalid value "%value" given for the website ID field.',
                        ['value' => $websiteId]
                    )
                );
            }
            $websiteId = (int)$websiteId;
        }

        return $websiteId;
    }

    /**
     * Get customer data.
     *
     * @param CustomerInterface $customer
     * @return array
     */
    private function getCustomerData(CustomerInterface $customer)
    {
        $isCompanyUser = false;

        try {
            $company = $this->companyManagement->getByCustomerId($customer->getId());
            if ($company) {
                $isCompanyUser = $company->getId()
                    && ($company->getId() != $this->getRequest()->getParam('companyId'));
            }
        } catch (NoSuchEntityException $e) {
            $this->logger->critical($e);
        }

        $jobTitle = '';
        $isActive = "0";

        if ($customer->getExtensionAttributes()
            && $customer->getExtensionAttributes()->getCompanyAttributes()
        ) {
            $jobTitle = $customer->getExtensionAttributes()->getCompanyAttributes()->getJobTitle();
            $isActive = $customer->getExtensionAttributes()->getCompanyAttributes()->getStatus();
        }

        return [
            'customer' => $customer->getId(),
            'firstname' => $customer->getFirstname(),
            'prefix' => $customer->getPrefix(),
            'middlename' => $customer->getMiddlename(),
            'lastname' => $customer->getLastname(),
            'suffix' => $customer->getSuffix(),
            'gender' => $customer->getGender(),
            'is_company_user' => $isCompanyUser,
            'job_title' => $jobTitle,
            'is_active' => boolval($isActive),
            'website_id' => $customer->getWebsiteId()
        ];
    }
}
