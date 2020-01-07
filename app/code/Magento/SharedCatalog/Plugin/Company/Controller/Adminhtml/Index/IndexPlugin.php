<?php
namespace Magento\SharedCatalog\Plugin\Company\Controller\Adminhtml\Index;

/**
 * Class IndexPlugin
 */
class IndexPlugin
{
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var \Magento\SharedCatalog\Api\SharedCatalogManagementInterface
     */
    protected $sharedCatalogManagement;

    /**
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\SharedCatalog\Api\SharedCatalogManagementInterface $sharedCatalogManagement
     */
    public function __construct(
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\SharedCatalog\Api\SharedCatalogManagementInterface $sharedCatalogManagement
    ) {
        $this->messageManager = $messageManager;
        $this->urlBuilder = $urlBuilder;
        $this->sharedCatalogManagement = $sharedCatalogManagement;
    }

    /**
     * Before index controller execute
     *
     * @param \Magento\Company\Controller\Adminhtml\Index\Index $subject
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeExecute(\Magento\Company\Controller\Adminhtml\Index\Index $subject)
    {
        if (!$this->sharedCatalogManagement->isPublicCatalogExist()) {
            $this->messageManager->addError(
                __(
                    'Please <a href="%1">create</a> at least a public shared catalog to proceed.',
                    $this->urlBuilder->getUrl('shared_catalog/sharedCatalog/create')
                )
            );
        }
    }
}
