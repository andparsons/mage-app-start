<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SharedCatalog\Controller\Adminhtml\SharedCatalog;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\SharedCatalog\Api\Data\SharedCatalogInterface;

/**
 * Abstract controller for shared catalog actions.
 */
abstract class AbstractAction extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session.
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_SharedCatalog::list';

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\SharedCatalog\Api\SharedCatalogRepositoryInterface
     */
    protected $sharedCatalogRepository;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param \Magento\SharedCatalog\Api\SharedCatalogRepositoryInterface $sharedCatalogRepository
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Magento\SharedCatalog\Api\SharedCatalogRepositoryInterface $sharedCatalogRepository
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->sharedCatalogRepository = $sharedCatalogRepository;
    }

    /**
     * Create result page.
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function createResultPage()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Magento_SharedCatalog::shared_list');
        $resultPage->getConfig()->getTitle()->prepend(__('Shared Catalogs'));

        return $resultPage;
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
        $redirect = $this->resultRedirectFactory->create();
        $redirect->setPath(
            'shared_catalog/sharedCatalog/edit',
            [SharedCatalogInterface::SHARED_CATALOG_ID_URL_PARAM => $id]
        );

        return $redirect;
    }

    /**
     * Get Create Redirect.
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    protected function getCreateRedirect()
    {
        return $this->resultRedirectFactory->create()->setPath('shared_catalog/sharedCatalog/create');
    }

    /**
     * Get current requested shared catalog.
     *
     * @return \Magento\SharedCatalog\Api\Data\SharedCatalogInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getSharedCatalog()
    {
        return $this->sharedCatalogRepository->get($this->getSharedCatalogId());
    }

    /**
     * Get shared catalog id.
     *
     * @return int
     */
    protected function getSharedCatalogId()
    {
        return (int)$this->getRequest()->getParam(SharedCatalogInterface::SHARED_CATALOG_ID_URL_PARAM);
    }
}
