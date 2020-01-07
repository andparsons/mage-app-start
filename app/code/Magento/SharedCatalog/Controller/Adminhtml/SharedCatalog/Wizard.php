<?php
namespace Magento\SharedCatalog\Controller\Adminhtml\SharedCatalog;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\SharedCatalog\Api\SharedCatalogRepositoryInterface;
use Magento\SharedCatalog\Model\Form\Storage\WizardFactory as WizardStorageFactory;
use Magento\SharedCatalog\Model\Form\Storage\Wizard\Builder as WizardStorageBuilder;
use Magento\SharedCatalog\Model\Form\Storage\UrlBuilder;

/**
 * Wizard configure shared catalog
 */
class Wizard extends AbstractAction implements HttpGetActionInterface
{
    /**
     * @var WizardStorageFactory
     */
    protected $wizardStorageFactory;

    /**
     * @var WizardStorageBuilder
     */
    protected $wizardStorageBuilder;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param SharedCatalogRepositoryInterface $sharedCatalogRepository
     * @param WizardStorageFactory $wizardStorageFactory
     * @param WizardStorageBuilder $wizardStorageBuilder
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        SharedCatalogRepositoryInterface $sharedCatalogRepository,
        WizardStorageFactory $wizardStorageFactory,
        WizardStorageBuilder $wizardStorageBuilder
    ) {
        parent::__construct($context, $resultPageFactory, $sharedCatalogRepository);
        $this->wizardStorageFactory = $wizardStorageFactory;
        $this->wizardStorageBuilder = $wizardStorageBuilder;
    }

    /**
     * Index action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\SharedCatalog\Model\Form\Storage\Wizard $wizardStorage */
        $wizardStorage = $this->wizardStorageFactory->create([
            'key' => $this->getRequest()->getParam(UrlBuilder::REQUEST_PARAM_CONFIGURE_KEY)
        ]);
        $this->wizardStorageBuilder->build($wizardStorage, $this->getSharedCatalog());

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
