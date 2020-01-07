<?php

namespace Magento\Company\Controller\Customer;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Company\Model\CompanyContext;
use \Magento\Customer\Api\Data\CustomerInterface;
use \Magento\Company\Api\Data\CompanyCustomerInterface;
use Magento\Company\Controller\AbstractAction;

/**
 * Class Check
 */
class Check extends AbstractAction implements HttpPostActionInterface, HttpGetActionInterface
{
    /**
     * Authorization level of a company session.
     */
    const COMPANY_RESOURCE = 'Magento_Company::users_edit';

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * Check constructor.
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param CompanyContext $companyContext
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Company\Model\CompanyContext $companyContext,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
    ) {
        parent::__construct($context, $companyContext, $logger);
        $this->customerRepository = $customerRepository;
    }

    /**
     * Check customer email.
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $email = $this->getRequest()->getParam('email');
        try {
            $customer = $this->customerRepository->get($email);
            $message = $this->getCustomerCompanyErrorMessage($customer);
            if ($message) {
                return $this->jsonError($message);
            }
            $message = __(
                'A user with this email address already exists in the system. '
                . 'If you proceed, the user will be linked to your company.'
            );
            return $this->jsonSuccess($this->getCustomerData($customer), $message);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            // Do not remove this handle as it used to check that customer with this email not registered in the system
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            return $this->handleJsonError($e->getMessage());
        } catch (\Exception $e) {
            $this->logger->critical($e);

            return $this->handleJsonError();
        }
        
        return $this->jsonSuccess([]);
    }

    /**
     * @param CustomerInterface $customer
     * @return CompanyCustomerInterface|null
     */
    private function getCompanyAttributes(CustomerInterface $customer)
    {
        if ($customer->getExtensionAttributes() && $customer->getExtensionAttributes()->getCompanyAttributes()) {
            return $customer->getExtensionAttributes()->getCompanyAttributes();
        }
        return null;
    }

    /**
     * @param CustomerInterface $customer
     * @return array
     */
    private function getCustomerData(CustomerInterface $customer)
    {
        $customerData = [
            'firstname' => $customer->getFirstname(),
            'lastname' => $customer->getLastname()
        ];
        $companyAttribute = $this->getCompanyAttributes($customer);
        if ($companyAttribute) {
            $customerData['extension_attributes[company_attributes][job_title]'] = $companyAttribute->getJobTitle()
                ?: '';
            $customerData['extension_attributes[company_attributes][telephone]'] = $companyAttribute->getTelephone()
                ?: '';
            $customerData['extension_attributes[company_attributes][status]'] = $companyAttribute->getStatus() ?: 1;
        }
        return $customerData;
    }

    /**
     * @param CustomerInterface $customer
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getCustomerCompanyErrorMessage(CustomerInterface $customer)
    {
        $companyAttribute = $this->getCompanyAttributes($customer);
        $currentCompany = $this->customerRepository->getById($this->companyContext->getCustomerId())
            ->getExtensionAttributes()->getCompanyAttributes();
        $message = '';
        if ($companyAttribute) {
            if ($companyAttribute->getCompanyId() == $currentCompany->getCompanyId()) {
                $message = __('A user with this email address is already a member of your company.');
            } elseif ((int)$companyAttribute->getCompanyId() > 0) {
                $message = __(
                    'A user with this email address already exists in the system. '
                    . 'Enter a different email address to create this user.'
                );
            }
        }
        return $message;
    }
}
