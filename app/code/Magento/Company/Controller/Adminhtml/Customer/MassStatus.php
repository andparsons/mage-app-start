<?php
namespace Magento\Company\Controller\Adminhtml\Customer;

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Backend\App\Action\Context;
use Magento\Company\Api\Data\CompanyCustomerInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory;
use Magento\Eav\Model\Entity\Collection\AbstractCollection;
use Magento\Customer\Controller\Adminhtml\Index\AbstractMassAction;

/**
 * Class MassStatus
 */
class MassStatus extends AbstractMassAction implements HttpPostActionInterface
{
    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        CustomerRepositoryInterface $customerRepository
    ) {
        parent::__construct($context, $filter, $collectionFactory);
        $this->customerRepository = $customerRepository;
    }

    /**
     * @inheritDoc
     *
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\State\InputMismatchException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function massAction(AbstractCollection $collection)
    {
        $status = (int)$this->getRequest()->getParam('status');
        $customersUpdated = 0;
        foreach ($collection->getAllIds() as $customerId) {
            $customer = $this->customerRepository->getById($customerId);
            /** @var CompanyCustomerInterface $companyCustomerAttributes */
            $companyCustomerAttributes = $customer->getExtensionAttributes()->getCompanyAttributes();
            if ($companyCustomerAttributes) {
                $companyCustomerAttributes->setStatus($status);
                try {
                    $this->customerRepository->save($customer);
                    $customersUpdated++;
                } catch (CouldNotSaveException $e) {
                    $this->messageManager->addError($e->getMessage());
                }
            }
        }

        if ($customersUpdated) {
            $this->messageManager->addSuccess(__('A total of %1 record(s) were updated.', $customersUpdated));
        }

        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('customer/index/index');

        return $resultRedirect;
    }
}
