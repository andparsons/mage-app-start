<?php
namespace Magento\SharedCatalog\Block\Adminhtml\Company;

use Magento\Backend\Block\Widget\Form\Container;

/**
 * Catalog company form container
 *
 * @api
 * @since 100.0.0
 */
class Edit extends Container
{
    /**
     * @var string
     */
    protected $_objectId = 'id';

    /**
     * @var string
     */
    protected $_blockGroup = 'Magento_SharedCatalog';

    /**
     * @var string
     */
    protected $_controller = 'adminhtml_company';

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        parent::_construct();

        $this->addButton(
            'save_and_continue_edit',
            [
                'class' => 'save',
                'label' => __('Save and Continue Edit'),
                'data_attribute' => [
                    'mage-init' => ['button' => ['event' => 'saveAndContinueEdit', 'target' => '#edit_form']],
                ]
            ],
            1
        );

        $this->removeButton('reset');
        $this->removeButton('delete');
    }
}
