<?php
namespace Magento\NegotiableQuote\Block\Adminhtml\Quote\View;

/**
 * "Add by SKU" accordion.
 *
 * @api
 * @since 100.0.0
 */
class Sku extends \Magento\Backend\Block\Widget
{
    /**
     * Retrieve accordion header.
     *
     * @return \Magento\Framework\Phrase
     * @codeCoverageIgnore
     */
    public function getHeaderText()
    {
        return __('Add to Quote by SKU');
    }

    /**
     * Retrieve "Add to order" and 'Close' buttons.
     *
     * @return string
     */
    public function getButtonsHtml()
    {
        $addButtonData = [
            'label' => __('Add to Quote'),
            'class' => 'action-default action-add action-secondary',
            'disabled' => 'disabled'
        ];
        $addButtonDataAttribute = [
            'mage-init' => '{"Magento_NegotiableQuote/quote/actions/submit-form":{"formId":"sku-form"}}',
            'role' => 'add-to-quote'
        ];
        $html = $this->getLayout()->createBlock(
            \Magento\Backend\Block\Widget\Button::class
        )->setData(
            $addButtonData
        )->setDataAttribute(
            $addButtonDataAttribute
        )->toHtml();

        $cancelButtonData = [
            'label' => __('Cancel')
        ];
        $cancelButtonDataAttribute = [
            'mage-init' => '{"Magento_NegotiableQuote/js/quote/actions/toggle-show": '
                . '{"toggleBlockId": "order-additional_area",'
                . ' "showBlockId": "show-sku-form"}}'
        ];
        $html .= $this->getLayout()->createBlock(
            \Magento\Backend\Block\Widget\Button::class
        )->setData(
            $cancelButtonData
        )->setDataAttribute(
            $cancelButtonDataAttribute
        )->toHtml();

        return $html;
    }
}
