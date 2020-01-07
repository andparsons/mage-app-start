<?php
namespace Magento\SharedCatalog\Block\Adminhtml\SharedCatalog\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Magento\SharedCatalog\Api\Data\SharedCatalogInterface;

/**
 * Button for duplicate shared catalog.
 */
class DuplicateSharedCatalogButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * Get button configuration.
     *
     * @return array
     */
    public function getButtonData()
    {
        $data = [];
        if ($this->request->getActionName() == 'edit') {
            $data = [
                'label' => __('Duplicate'),
                'class' => 'duplicate',
                'data_attribute' => [
                    'mage-init' => [
                        'redirectionUrl' => ['url' => $this->getDuplicateUrl()],
                    ]
                ],
                'sort_order' => 50,
            ];
        }

        return $data;
    }

    /**
     * Get ulr for duplicate catalog.
     *
     * @return string
     */
    private function getDuplicateUrl()
    {
        $sharedCatalogId = $this->request->getParam(SharedCatalogInterface::SHARED_CATALOG_ID_URL_PARAM);
        return $this->getUrl(
            '*/*/duplicate',
            [SharedCatalogInterface::SHARED_CATALOG_ID_URL_PARAM => $sharedCatalogId]
        );
    }
}
