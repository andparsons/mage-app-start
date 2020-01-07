<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SharedCatalog\Controller\Adminhtml\SharedCatalog;

/**
 * Class Delete
 */
class Delete extends AbstractAction
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * Delete Shared Catalog constructor.
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\SharedCatalog\Api\SharedCatalogRepositoryInterface $sharedCatalogRepository
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\SharedCatalog\Api\SharedCatalogRepositoryInterface $sharedCatalogRepository,
        \Psr\Log\LoggerInterface $logger
    ) {
        parent::__construct($context, $resultPageFactory, $sharedCatalogRepository);
        $this->logger = $logger;
    }

    /**
     * Delete shared catalog
     *
     * @return \Magento\Backend\Model\View\Result\Redirect|\Magento\Backend\Model\View\Result\Forward
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        try {
            $this->sharedCatalogRepository->delete($this->getSharedCatalog());
            $this->messageManager->addSuccessMessage(__('The shared catalog was deleted successfully.'));
            $resultRedirect->setPath('shared_catalog/sharedCatalog/index');
        } catch (\Exception $e) {
            $this->logger->critical($e);
            $this->messageManager->addErrorMessage($e->getMessage());
            $resultRedirect = $this->getEditRedirect();
        }

        return $resultRedirect;
    }
}
