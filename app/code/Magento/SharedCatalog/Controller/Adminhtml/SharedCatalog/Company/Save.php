<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SharedCatalog\Controller\Adminhtml\SharedCatalog\Company;

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\SharedCatalog\Controller\Adminhtml\SharedCatalog\AbstractAction;
use Magento\SharedCatalog\Model\Form\Storage\UrlBuilder;
use Magento\SharedCatalog\Api\Data\SharedCatalogInterface;

/**
 * Save shared catalog companies.
 */
class Save extends AbstractAction implements HttpPostActionInterface
{
    /**
     * @var \Magento\SharedCatalog\Api\CompanyManagementInterface
     */
    private $companySharedCatalogManagement;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \Magento\SharedCatalog\Model\Form\Storage\CompanyFactory
     */
    private $companyStorageFactory;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var \Magento\Company\Api\CompanyRepositoryInterface
     */
    private $companyRepository;

    /**
     * Save constructor.
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\SharedCatalog\Api\SharedCatalogRepositoryInterface $sharedCatalogRepository
     * @param \Magento\SharedCatalog\Api\CompanyManagementInterface $companySharedCatalogManagement
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\SharedCatalog\Model\Form\Storage\CompanyFactory $companyStorageFactory
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Company\Api\CompanyRepositoryInterface $companyRepository
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\SharedCatalog\Api\SharedCatalogRepositoryInterface $sharedCatalogRepository,
        \Magento\SharedCatalog\Api\CompanyManagementInterface $companySharedCatalogManagement,
        \Psr\Log\LoggerInterface $logger,
        \Magento\SharedCatalog\Model\Form\Storage\CompanyFactory $companyStorageFactory,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Company\Api\CompanyRepositoryInterface $companyRepository
    ) {
        parent::__construct($context, $resultPageFactory, $sharedCatalogRepository);
        $this->companySharedCatalogManagement = $companySharedCatalogManagement;
        $this->logger = $logger;
        $this->companyStorageFactory = $companyStorageFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->companyRepository = $companyRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        /** @var \Magento\SharedCatalog\Model\Form\Storage\Company $storage */
        $storage = $this->companyStorageFactory->create([
            'key' => $this->getRequest()->getParam(UrlBuilder::REQUEST_PARAM_CONFIGURE_KEY)
        ]);

        try {
            $sharedCatalog = $this->getSharedCatalog();

            $assignedCompaniesIds = $storage->getAssignedCompaniesIds();
            $assignedCompaniesSearchCriteriaBuilder = $this->searchCriteriaBuilder->addFilter(
                'entity_id',
                $assignedCompaniesIds,
                'in'
            );
            $assignedCompanies = $this->companyRepository->getList(
                $assignedCompaniesSearchCriteriaBuilder->create()
            )->getItems();
            $this->companySharedCatalogManagement->assignCompanies($sharedCatalog->getId(), $assignedCompanies);
            $unassignedCompaniesIds = $storage->getUnassignedCompaniesIds();
            $unassignedCompaniesSearchCriteriaBuilder = $this->searchCriteriaBuilder
                ->addFilter('customer_group_id', $sharedCatalog->getCustomerGroupId())
                ->addFilter('entity_id', $unassignedCompaniesIds, 'in');
            $unassignedCompanies = $this->companyRepository->getList(
                $unassignedCompaniesSearchCriteriaBuilder->create()
            )->getItems();
            $this->companySharedCatalogManagement->unassignCompanies($sharedCatalog->getId(), $unassignedCompanies);
            $this->messageManager->addSuccessMessage(__('The companies have been reassigned.'));
            $resultRedirect = $this->getSuccessRedirect($sharedCatalog->getId());
        } catch (\Exception $e) {
            $this->logger->critical($e);
            $this->messageManager->addErrorMessage(__('Error while reassigning the company.'));
            $resultRedirect = $this->getListRedirect();
        }

        return $resultRedirect;
    }

    /**
     * Get Edit Redirect.
     *
     * @param int $id
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    protected function getEditRedirect($id = null)
    {
        $id = !empty($id) ? $id : $this->getSharedCatalogId();
        return $this->resultRedirectFactory->create()
            ->setPath(
                'shared_catalog/sharedCatalog/companies',
                [SharedCatalogInterface::SHARED_CATALOG_ID_URL_PARAM => $id]
            );
    }

    /**
     * Get Success redirect.
     *
     * @param int $id
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    private function getSuccessRedirect($id = null)
    {
        $isContinue = $this->getRequest()->getParam('back');
        return $isContinue ? $this->getEditRedirect($id) : $this->getListRedirect();
    }

    /**
     * Get List redirect.
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    private function getListRedirect()
    {
        return $this->resultRedirectFactory->create()->setPath('shared_catalog/sharedCatalog/index');
    }
}
