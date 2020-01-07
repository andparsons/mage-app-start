<?php
namespace Magento\Company\Block\Adminhtml\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * Class DeleteButton
 * @package Magento\Customer\Block\Adminhtml\Edit
 */
class DeleteButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * @return array
     */
    public function getButtonData()
    {
        if (!$this->getRequest()->getParam('id')) {
            return [];
        }
        $data = [
            'label' => __('Delete Company'),
            'class' => 'delete',
            'id' => 'company-edit-delete-button',
            'data_attribute' => [
                'mage-init' => [
                    'Magento_Company/edit/post-wrapper' => ['url' => $this->getDeleteUrl()],
                ]
            ],
            'on_click' => '',
            'sort_order' => 20,
        ];
        return $data;
    }

    /**
     * @return string
     */
    public function getDeleteUrl()
    {
        $companyId = $this->getRequest()->getParam('id');
        return $this->getUrl('*/*/delete', ['id' => $companyId]);
    }
}
