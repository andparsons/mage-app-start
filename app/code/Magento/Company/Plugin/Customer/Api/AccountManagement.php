<?php
namespace Magento\Company\Plugin\Customer\Api;

use Magento\Company\Api\Data\CompanyInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Company\Api\CompanyManagementInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class AccountManagement
 */
class AccountManagement
{
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    private $request;

    /**
     * @var \Magento\Company\Model\Email\Sender
     */
    private $companyEmailSender;

    /**
     * @var \Magento\Backend\Model\UrlInterface
     */
    private $urlBuilder;

    /**
     * @var \Magento\Company\Model\Customer\Company
     */
    private $customerCompany;

    /**
     * AccountManagement constructor
     *
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Company\Model\Email\Sender $companyEmailSender
     * @param \Magento\Backend\Model\UrlInterface $urlBuilder
     * @param \Magento\Company\Model\Customer\Company $customerCompany
     * @param CompanyManagementInterface $companyManagement
     * @param CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        \Magento\Company\Model\Email\Sender $companyEmailSender,
        \Magento\Backend\Model\UrlInterface $urlBuilder,
        \Magento\Company\Model\Customer\Company $customerCompany,
        CompanyManagementInterface $companyManagement,
        CustomerRepositoryInterface $customerRepository
    ) {
        $this->request = $request;
        $this->companyEmailSender = $companyEmailSender;
        $this->urlBuilder = $urlBuilder;
        $this->customerCompany = $customerCompany;
        $this->companyManagement = $companyManagement;
        $this->customerRepository = $customerRepository;
    }

    /**
     * Additional auth logic for company customers.
     *
     * @param \Magento\Customer\Api\AccountManagementInterface $subject
     * @param string $username
     * @param string $password
     * @return \Magento\Customer\Api\Data\CustomerInterface
     * @throws \Magento\Framework\Exception\InvalidEmailOrPasswordException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeAuthenticate(\Magento\Customer\Api\AccountManagementInterface $subject, $username, $password)
    {
        try {
            $customer = $this->customerRepository->get($username);
        } catch (NoSuchEntityException $e) {
            throw new \Magento\Framework\Exception\InvalidEmailOrPasswordException(__('Invalid login or password.'));
        }
        if ($customer->getExtensionAttributes()
            && $customer->getExtensionAttributes()->getCompanyAttributes()
            && $customer->getExtensionAttributes()->getCompanyAttributes()->getStatus() == 0
        ) {
            throw new \Magento\Framework\Exception\LocalizedException(__('This account is locked.'));
        }
        $company = $this->companyManagement->getByCustomerId($customer->getId());
        if ($company) {
            switch ($company->getStatus()) {
                case CompanyInterface::STATUS_REJECTED:
                    throw new \Magento\Framework\Exception\LocalizedException(__('This account is locked.'));
                case CompanyInterface::STATUS_PENDING:
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __('Your account is not yet approved. If you have questions, please contact the seller.')
                    );
                default:
                    break;
            }
        }

        return [$username, $password];
    }

    /**
     * Creating company profile after finished creating regular customer account.
     *
     * @param \Magento\Customer\Api\AccountManagementInterface $subject
     * @param \Magento\Customer\Api\Data\CustomerInterface $result
     * @return \Magento\Customer\Api\Data\CustomerInterface
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterCreateAccount(
        \Magento\Customer\Api\AccountManagementInterface $subject,
        \Magento\Customer\Api\Data\CustomerInterface $result
    ) {
        $companyData = $this->request->getPost('company');

        if (isset($companyData['status'])) {
            unset($companyData['status']);
        }

        if (is_array($companyData) && !empty($companyData)) {
            $jobTitle = $this->request->getPost('job_title');
            $companyDataObject = $this->customerCompany->createCompany($result, $companyData, $jobTitle);
            $companyUrl = $this->urlBuilder->getUrl('company/index/edit', ['id' => $companyDataObject->getId()]);
            $this->companyEmailSender->sendAdminNotificationEmail(
                $result,
                $companyDataObject->getCompanyName(),
                $companyUrl
            );
        }
        return $result;
    }
}
