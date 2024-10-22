<?php

/**
 * Product attribute add form variations main tab
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\ConfigurableProduct\Block\Adminhtml\Product\Attribute\Edit\Tab\Variations;

/**
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 */
class Main extends \Magento\Eav\Block\Adminhtml\Attribute\Edit\Main\AbstractMain
{
    /**
     * Adding product form elements for editing attribute
     *
     * @return \Magento\ConfigurableProduct\Block\Adminhtml\Product\Attribute\Edit\Tab\Variations\Main
     */
    protected function _prepareForm()
    {
        parent::_prepareForm();
        /* @var $form \Magento\Framework\Data\Form */
        $form = $this->getForm();
        /* @var $fieldset \Magento\Framework\Data\Form\Element\Fieldset */
        $fieldset = $form->getElement('base_fieldset');
        $fieldsToRemove = ['attribute_code', 'is_unique', 'frontend_class'];

        foreach ($fieldset->getElements() as $element) {
            /** @var \Magento\Framework\Data\Form\AbstractForm $element  */
            if (substr($element->getId(), 0, strlen('default_value')) == 'default_value') {
                $fieldsToRemove[] = $element->getId();
            }
        }
        foreach ($fieldsToRemove as $id) {
            $fieldset->removeField($id);
        }
        return $this;
    }
}
