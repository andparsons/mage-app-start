<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Solr\Controller\Adminhtml\Search;

class RelatedGrid extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Backend::global_search';
    
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * Query factory
     *
     * @var \Magento\Search\Model\QueryFactory
     */
    protected $_queryFactory;

    /**
     * Construct
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Search\Model\QueryFactory $queryFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Search\Model\QueryFactory $queryFactory
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->_queryFactory = $queryFactory;
        parent::__construct($context);
    }

    /**
     * Ajax grid action
     *
     * @return void
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        /** @var \Magento\Search\Model\Query $model */
        $model = $this->_queryFactory->get();

        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addError(__('This search no longer exists.'));
                $this->_redirect('adminhtml/*');
                return;
            }
        }

        // set entered data if was error when we do save
        $data = $this->_session->getPageData(true);
        if (!empty($data)) {
            $model->addData($data);
        }

        $this->_coreRegistry->register('current_catalog_search', $model);

        $this->_view->loadLayout(false);
        $this->_view->renderLayout();
    }
}
