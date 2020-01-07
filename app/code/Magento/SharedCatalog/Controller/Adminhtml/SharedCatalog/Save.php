<?php
namespace Magento\SharedCatalog\Controller\Adminhtml\SharedCatalog;

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Save Shared Catalog.
 */
class Save extends AbstractAction implements HttpPostActionInterface
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
     * Save Shared Catalog constructor.
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\SharedCatalog\Api\SharedCatalogRepositoryInterface $sharedCatalogRepository
     * @param \Magento\SharedCatalog\Controller\Adminhtml\SharedCatalog\SharedCatalogWizardData $wizardData
     * @param \Magento\SharedCatalog\Model\SharedCatalogBuilder $sharedCatalogBuilder
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\SharedCatalog\Api\SharedCatalogRepositoryInterface $sharedCatalogRepository,
        \Magento\SharedCatalog\Controller\Adminhtml\SharedCatalog\SharedCatalogWizardData $wizardData,
        \Magento\SharedCatalog\Model\SharedCatalogBuilder $sharedCatalogBuilder
    ) {
        parent::__construct($context, $resultPageFactory, $sharedCatalogRepository);
        $this->wizardData = $wizardData;
        $this->sharedCatalogBuilder = $sharedCatalogBuilder;
    }

    /**
     * Create or save shared catalog.
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
            $this->messageManager->addSuccess(__('You saved the shared catalog.'));
            $resultRedirect = $this->getSuccessRedirect($sharedCatalog->getId());
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            if ($sharedCatalog && $sharedCatalog->getId()) {
                $resultRedirect = $this->getEditRedirect($sharedCatalog->getId());
            } else {
                $resultRedirect = $this->getCreateRedirect();
            }
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the shared catalog.'));
            $resultRedirect = $this->getListRedirect();
        }

        return $resultRedirect;
    }

    /**
     * Get success redirect.
     *
     * @param int|null $id [optional]
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    private function getSuccessRedirect($id = null)
    {
        $isContinue = $this->getRequest()->getParam('back');
        return $isContinue ? $this->getEditRedirect($id) : $this->getListRedirect();
    }

    /**
     * Get list redirect.
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    private function getListRedirect()
    {
        return $this->resultRedirectFactory->create()->setPath('shared_catalog/sharedCatalog/index');
    }
}
