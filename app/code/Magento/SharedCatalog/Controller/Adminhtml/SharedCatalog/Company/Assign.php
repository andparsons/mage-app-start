<?php
namespace Magento\SharedCatalog\Controller\Adminhtml\SharedCatalog\Company;

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\SharedCatalog\Model\Form\Storage\CompanyFactory as CompanyStorageFactory;
use Magento\SharedCatalog\Model\Form\Storage\UrlBuilder;

/**
 * Assign company to shared catalog
 */
class Assign extends \Magento\SharedCatalog\Controller\Adminhtml\AbstractJsonAction implements HttpPostActionInterface
{
    /**
     * @var CompanyStorageFactory
     */
    protected $companyStorageFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param CompanyStorageFactory $companyStorageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        CompanyStorageFactory $companyStorageFactory
    ) {
        parent::__construct($context, $resultJsonFactory);
        $this->companyStorageFactory = $companyStorageFactory;
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

        $companyId = (int)$this->getRequest()->getParam('company_id');
        $isAssign = (int)$this->getRequest()->getParam('is_assign');

        if ($isAssign) {
            $storage->assignCompanies([$companyId]);
        } else {
            $storage->unassignCompanies([$companyId]);
        }

        return $this->createJsonResponse([
            'data'  => [
                'status' => 1,
                'company' => $companyId,
                'is_assign' => $storage->isCompanyAssigned($companyId)
            ]
        ]);
    }
}
