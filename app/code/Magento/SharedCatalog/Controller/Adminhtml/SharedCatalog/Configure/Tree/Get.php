<?php
namespace Magento\SharedCatalog\Controller\Adminhtml\SharedCatalog\Configure\Tree;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;

/**
 * Provide shared catalog category tree data for rendering that tree at shared catalog configuration step.
 */
class Get extends \Magento\SharedCatalog\Controller\Adminhtml\AbstractJsonAction implements HttpGetActionInterface
{
    /**
     * @var \Magento\SharedCatalog\Model\Configure\Category\Tree
     */
    protected $categoryTree;

    /**
     * @var \Magento\SharedCatalog\Model\Configure\Category\Tree\RendererInterface
     */
    protected $categoryTreeRenderer;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\SharedCatalog\Model\Form\Storage\WizardFactory
     */
    private $wizardStorageFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\SharedCatalog\Model\Configure\Category\Tree $categoryTree
     * @param \Magento\SharedCatalog\Model\Configure\Category\Tree\RendererInterface $treeRenderer
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\SharedCatalog\Model\Form\Storage\WizardFactory $wizardStorageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\SharedCatalog\Model\Configure\Category\Tree $categoryTree,
        \Magento\SharedCatalog\Model\Configure\Category\Tree\RendererInterface $treeRenderer,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\SharedCatalog\Model\Form\Storage\WizardFactory $wizardStorageFactory
    ) {
        parent::__construct($context, $resultJsonFactory);
        $this->categoryTree = $categoryTree;
        $this->categoryTreeRenderer = $treeRenderer;
        $this->storeManager = $storeManager;
        $this->wizardStorageFactory = $wizardStorageFactory;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        return $this->createJsonResponse(
            [
                'data'  => $this->getTreeData()
            ]
        );
    }

    /**
     * Get shared catalog category tree data for rendering that tree at shared catalog configuration step.
     *
     * @return array
     */
    protected function getTreeData()
    {
        /** @var \Magento\SharedCatalog\Model\Form\Storage\Wizard $storage */
        $storage = $this->wizardStorageFactory->create(
            [
                'key' => $this->_request->getParam(
                    \Magento\SharedCatalog\Model\Form\Storage\UrlBuilder::REQUEST_PARAM_CONFIGURE_KEY
                )
            ]
        );
        $storage->setStoreId($this->getStoreId());
        $rootNode = $this->categoryTree->getCategoryRootNode($storage);
        $treeData = $this->categoryTreeRenderer->render($rootNode);
        return $treeData;
    }

    /**
     * Get store.
     *
     * @return \Magento\Store\Api\Data\GroupInterface
     */
    protected function getStore()
    {
        return $this->storeManager->getGroup($this->getStoreId());
    }

    /**
     * Get requested store id.
     *
     * @return int
     */
    protected function getStoreId()
    {
        $filters = $this->getRequest()->getParam('filters');
        return isset($filters['store']['id'])
            ? $filters['store']['id']
            : $this->getDefaultStoreId();
    }

    /**
     * Get default store id.
     *
     * @return int
     */
    protected function getDefaultStoreId()
    {
        return $this->storeManager->getGroup()->getId();
    }
}
