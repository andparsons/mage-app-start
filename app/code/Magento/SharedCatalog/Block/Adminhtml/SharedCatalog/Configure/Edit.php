<?php
namespace Magento\SharedCatalog\Block\Adminhtml\SharedCatalog\Configure;

use Magento\Backend\Block\Widget\Form\Container;

/**
 * Catalog configure form container
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
    protected $_controller = 'adminhtml_sharedCatalog_configure';

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        parent::_construct();

        $this->removeButton('reset');
        $this->removeButton('delete');
    }
}
