<?php
namespace Magento\Company\Controller\Adminhtml\Index;

use Magento\Backend\App\Action\Context;
use Magento\Company\Model\ResourceModel\Company\CollectionFactory;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Company\Api\CompanyRepositoryInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Company\Model\Company;
use Magento\Company\Api\CompanyManagementInterface;

/**
 * Controller for mass enabling companies via mass action block on backend.
 */
class MassEnable extends AbstractMassAction
{
    /**
     * @var CompanyRepositoryInterface
     */
    protected $companyRepository;

    /**
     * @var CompanyManagementInterface
     */
    protected $companyManagement;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param CompanyRepositoryInterface $companyRepository
     * @param CompanyManagementInterface $companyManagement
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        CompanyRepositoryInterface $companyRepository,
        CompanyManagementInterface $companyManagement
    ) {
        parent::__construct($context, $filter, $collectionFactory);
        $this->companyRepository = $companyRepository;
        $this->companyManagement = $companyManagement;
    }

    /**
     * Perform mass enabling.
     *
     * @param AbstractCollection $collection
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws CouldNotSaveException
     * @throws \InvalidArgumentException
     */
    protected function massAction(AbstractCollection $collection)
    {
        $companiesUpdated = 0;
        try {
            foreach ($collection->getAllIds() as $companyId) {
                $company = $this->companyRepository->get($companyId);
                $company->setStatus(Company::STATUS_APPROVED);
                $this->companyRepository->save($company);
                $companiesUpdated++;
            }
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__('Could not save company'), $e);
        }

        if ($companiesUpdated) {
            $this->messageManager->addSuccessMessage(__('A total of %1 record(s) were updated.', $companiesUpdated));
        }
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath($this->getComponentRefererUrl());

        return $resultRedirect;
    }
}
