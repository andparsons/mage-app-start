<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SharedCatalog\Controller\Adminhtml\SharedCatalog;

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\SharedCatalog\Api\Data\SharedCatalogInterface;

/**
 * Controller for duplicating shared catalog.
 */
class DuplicatePost extends AbstractAction implements HttpPostActionInterface
{
    /**
     * @var \Magento\SharedCatalog\Model\SharedCatalogBuilder
     */
    private $sharedCatalogBuilder;

    /**
     * @var \Magento\SharedCatalog\Controller\Adminhtml\SharedCatalog\SharedCatalogWizardData
     */
    private $wizardData;

    /**
     * @var \Magento\SharedCatalog\Model\Duplicator
     */
    private $duplicateManager;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\SharedCatalog\Api\SharedCatalogRepositoryInterface $sharedCatalogRepository
     * @param \Magento\SharedCatalog\Controller\Adminhtml\SharedCatalog\SharedCatalogWizardData $wizardData
     * @param \Magento\SharedCatalog\Model\SharedCatalogBuilder $sharedCatalogBuilder
     * @param \Magento\SharedCatalog\Model\Duplicator $duplicateManager
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\SharedCatalog\Api\SharedCatalogRepositoryInterface $sharedCatalogRepository,
        \Magento\SharedCatalog\Controller\Adminhtml\SharedCatalog\SharedCatalogWizardData $wizardData,
        \Magento\SharedCatalog\Model\SharedCatalogBuilder $sharedCatalogBuilder,
        \Magento\SharedCatalog\Model\Duplicator $duplicateManager
    ) {
        parent::__construct($context, $resultPageFactory, $sharedCatalogRepository);
        $this->wizardData = $wizardData;
        $this->sharedCatalogBuilder = $sharedCatalogBuilder;
        $this->duplicateManager = $duplicateManager;
    }

    /**
     * Action for duplicate catalog. Return redirect on edit or grid page when duplicating is success.
     * Return to duplicate page when duplicating is failed.
     *
     * @return \Magento\Framework\Controller\Result\Redirect|\Magento\Backend\Model\View\Result\Forward
     */
    public function execute()
    {
        $sharedCatalog = null;
        try {
            $sharedCatalog = $this->sharedCatalogBuilder->build($this->getSharedCatalogId());
            $this->wizardData->populateDataFromRequest($sharedCatalog);
            $this->sharedCatalogRepository->save($sharedCatalog);
            $duplicatedId = $this->getRequest()->getParam('duplicate_id');
            $this->duplicateManager->duplicateCatalog($duplicatedId, $sharedCatalog->getId());
            $this->messageManager->addSuccess(__('You saved the shared catalog.'));
            $resultRedirect = $this->getSuccessRedirect($sharedCatalog->getId());
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            if ($sharedCatalog && $sharedCatalog->getId()) {
                $resultRedirect = $this->getEditRedirect($sharedCatalog->getId());
            } else {
                $resultRedirect = $this->getDuplicateRedirect();
            }
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage(
                $e,
                __('Something went wrong while saving the shared catalog.')
            );
            $resultRedirect = $this->getDuplicateRedirect();
        }

        return $resultRedirect;
    }

    /**
     * Retrieve redirect object when duplicating is success (redirect on edit of grid page).
     *
     * @param int|null $id [optional]
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    private function getSuccessRedirect($id = null)
    {
        $isContinue = $this->getRequest()->getParam('back');
        return $isContinue
            ? $this->getEditRedirect($id)
            : $this->resultRedirectFactory->create()->setPath('shared_catalog/sharedCatalog/index');
    }

    /**
     * Retrieve redirect object to duplicate page.
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    private function getDuplicateRedirect()
    {
        $duplicatedId = $this->getRequest()->getParam('duplicate_id');
        $redirect = $this->resultRedirectFactory->create();
        $redirect->setPath(
            'shared_catalog/sharedCatalog/duplicate',
            [SharedCatalogInterface::SHARED_CATALOG_ID_URL_PARAM => $duplicatedId]
        );

        return $redirect;
    }
}
