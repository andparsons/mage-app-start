<?php
namespace Magento\SharedCatalog\Block\Adminhtml\SharedCatalog\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Magento\SharedCatalog\Api\Data\SharedCatalogInterface;

/**
 * Render delete shared catalog button.
 */
class DeleteSharedCatalogButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * @var string
     */
    private $actionName = 'edit';

    /**
     * Get delete button data.
     *
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getButtonData()
    {
        $data = [];
        if ($this->request->getActionName() == $this->actionName) {
            $data = [
                'label' => __('Delete'),
                'class' => 'delete',
                'id' => 'shared-catalog-edit-delete-button',
                'on_click' => 'deleteConfirm(\'' . __(
                    'This action cannot be undone. Are you sure you want to delete this catalog?'
                ) . '\', \'' . $this->getDeleteUrl() . '\')',
                'sort_order' => 50,
            ];
        }

        return $data;
    }

    /**
     * Get delete URL.
     *
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getDeleteUrl()
    {
        $sharedCatalogId = $this->request->getParam(SharedCatalogInterface::SHARED_CATALOG_ID_URL_PARAM);
        return $this->getUrl(
            '*/*/delete',
            [SharedCatalogInterface::SHARED_CATALOG_ID_URL_PARAM => $sharedCatalogId]
        );
    }
}
