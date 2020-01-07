<?php
namespace Magento\SharedCatalog\Controller\Adminhtml\SharedCatalog;

use Magento\Framework\App\RequestInterface;
use Magento\SharedCatalog\Api\Data\SharedCatalogInterface;

/**
 * Class for populating general setting in shared catalog from request.
 */
class SharedCatalogWizardData
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @param RequestInterface $request
     */
    public function __construct(
        RequestInterface $request
    ) {
        $this->request = $request;
    }

    /**
     * Populate data from request in shared catalog. Throw exception when request data is not valid.
     *
     * @param SharedCatalogInterface $sharedCatalog
     * @return void
     * @throws \UnexpectedValueException
     */
    public function populateDataFromRequest(SharedCatalogInterface $sharedCatalog)
    {
        $data = $this->request->getParam('catalog_details');
        $this->validateNameFromData($data);
        $sharedCatalog->setData($data);
        $sharedCatalogId = (int)$this->request->getParam(SharedCatalogInterface::SHARED_CATALOG_ID_URL_PARAM);
        if ($sharedCatalogId) {
            $sharedCatalog->setId($sharedCatalogId);
        }
    }

    /**
     * Validate that $data element 'name' is not empty otherwise throw an exception.
     *
     * @param array $data
     * @return void
     * @throws \UnexpectedValueException
     */
    private function validateNameFromData(array $data)
    {
        if (empty($data['name'])) {
            throw new \UnexpectedValueException(
                __('No shared catalog name is specified in request.')
            );
        }
    }
}
