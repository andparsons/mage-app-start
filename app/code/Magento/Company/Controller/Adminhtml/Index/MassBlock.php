<?php
namespace Magento\Company\Controller\Adminhtml\Index;

use Magento\Backend\App\Action\Context;
use Magento\Company\Model\ResourceModel\Company\CollectionFactory;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Company\Api\CompanyRepositoryInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Company\Model\Company;

/**
 * Controller for mass action.
 */
class MassBlock extends AbstractMassAction
{
    /**
     * @var CompanyRepositoryInterface
     */
    protected $companyRepository;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param CompanyRepositoryInterface $companyRepository
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        CompanyRepositoryInterface $companyRepository
    ) {
        parent::__construct($context, $filter, $collectionFactory);
        $this->companyRepository = $companyRepository;
    }

    /**
     * Performs mass change of status on company entities.
     *
     * @param AbstractCollection $collection
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \InvalidArgumentException
     */
    protected function massAction(AbstractCollection $collection)
    {
        $companiesUpdated = 0;
        foreach ($collection->getAllIds() as $companyId) {
            $company = $this->companyRepository->get($companyId);
            $company->setStatus(Company::STATUS_BLOCKED);
            $this->companyRepository->save($company);
            $companiesUpdated++;
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
