<?php
namespace Magento\SharedCatalog\Controller\Adminhtml\SharedCatalog;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\SharedCatalog\Model\Form\Storage\CompanyFactory as CompanyStorageFactory;
use Magento\SharedCatalog\Model\Form\Storage\Company\Builder as CompanyStorageBuilder;
use Magento\SharedCatalog\Model\Form\Storage\UrlBuilder;

/**
 * Shared catalog Companies
 */
class Companies extends AbstractAction implements HttpGetActionInterface
{
    /**
     * @var CompanyStorageFactory
     */
    protected $companyStorageFactory;

    /**
     * @var CompanyStorageBuilder
     */
    protected $companyStorageBuilder;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param \Magento\SharedCatalog\Api\SharedCatalogRepositoryInterface $sharedCatalogRepository
     * @param CompanyStorageFactory $companyStorageFactory
     * @param CompanyStorageBuilder $companyStorageBuilder
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Magento\SharedCatalog\Api\SharedCatalogRepositoryInterface $sharedCatalogRepository,
        CompanyStorageFactory $companyStorageFactory,
        CompanyStorageBuilder $companyStorageBuilder
    ) {
        parent::__construct($context, $resultPageFactory, $sharedCatalogRepository);
        $this->companyStorageFactory = $companyStorageFactory;
        $this->companyStorageBuilder = $companyStorageBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        /** @var \Magento\SharedCatalog\Model\Form\Storage\Company $companyStorage */
        $companyStorage = $this->companyStorageFactory->create([
            'key' => $this->getRequest()->getParam(UrlBuilder::REQUEST_PARAM_CONFIGURE_KEY)
        ]);
        $this->companyStorageBuilder->build($companyStorage, $this->getSharedCatalog());

        return $this->createResultPage();
    }

    /**
     * {@inheritdoc}
     */
    protected function createResultPage()
    {
        $resultPage = parent::createResultPage();
        $resultPage->getConfig()->getTitle()->prepend($this->getSharedCatalog()->getName());
        return $resultPage;
    }
}
