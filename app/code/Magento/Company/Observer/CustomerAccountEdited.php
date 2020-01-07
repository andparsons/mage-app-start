<?php
namespace Magento\Company\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Company\Model\ResourceModel\Customer as CustomerResource;

/**
 * Class CustomerAccountEdited
 */
class CustomerAccountEdited implements ObserverInterface
{
    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var CustomerResource
     */
    protected $customerResource;

    /**
     * CustomerAccountEdited constructor
     *
     * @param CustomerRepositoryInterface $customerRepository
     * @param RequestInterface $request
     * @param CustomerResource $customerResource
     */
    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        RequestInterface $request,
        CustomerResource $customerResource
    ) {
        $this->customerRepository = $customerRepository;
        $this->request = $request;
        $this->customerResource = $customerResource;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $email = $observer->getEmail();
        $customer = $this->customerRepository->get($email);
        $customerData = $this->request->getParam('customer');
        if ($customer->getExtensionAttributes() !== null
            && $customer->getExtensionAttributes()->getCompanyAttributes() !== null
            && !empty($customerData['extension_attributes']['company_attributes']['job_title'])
        ) {
            $companyAttributes = $customer->getExtensionAttributes()->getCompanyAttributes();
            $companyAttributes->setCustomerId($customer->getId())
                ->setJobTitle($customerData['extension_attributes']['company_attributes']['job_title']);
            $this->customerResource->saveAdvancedCustomAttributes($companyAttributes);
        }
        return $this;
    }
}
