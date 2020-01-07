<?php
namespace Magento\NegotiableQuote\Block\Adminhtml\Quote;

use Magento\NegotiableQuote\Model\Restriction\RestrictionInterface;

/**
 * Adminhtml Quote view block.
 *
 * @api
 * @since 100.0.0
 */
class View extends \Magento\Backend\Block\Widget\Container
{
    /**
     * @var RestrictionInterface
     */
    protected $restriction;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param RestrictionInterface $restriction
     * @param array $data [optional]
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        RestrictionInterface $restriction,
        array $data = []
    ) {
        $this->restriction = $restriction;
        parent::__construct($context, $data);
    }

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_objectId = 'quote_id';
        $this->_controller = 'adminhtml_quote';
        $this->_mode = 'view';

        parent::_construct();

        $this->buttonList->remove('delete');
        $this->buttonList->remove('reset');
        $this->buttonList->remove('save');
        $this->buttonList->add(
            'back',
            [
                'label' => __('Back'),
                'onclick' => "window.location.href = '" . $this->getBackUrl() . "'",
                'class' => 'back'
            ]
        );
        $this->buttonList->add(
            'quote_print',
            [
                'label' => __('Print'),
                'class' => 'quote-print',
                'onclick' => "window.open('" . $this->getPrintUrl() . "', '_blank')",
            ]
        );
        $isDisabled = !$this->restriction->canSubmit();
        $this->buttonList->add(
            'quote_save',
            [
                'label' => __('Save as Draft'),
                'class' => 'save-as-draft',
                'disabled' => $isDisabled,
                'data_attribute' => [
                    'mage-init' => '{"Magento_NegotiableQuote/quote/create/save-as-draft":'
                        . '{"saveUrl": "' . $this->getSaveAsDraftUrl() . '"}}',
                    'role' => 'save-as-draft',
                ]
            ]
        );
        $this->buttonList->add(
            'quote_decline',
            [
                'label' => __('Decline'),
                'class' => 'decline action-secondary',
                'id' => 'quote-view-decline-button',
                'disabled' => $isDisabled,
                'data_attribute' => [
                    'mage-init' => '{"Magento_NegotiableQuote/quote/view/decline":'
                        . ' {"url": "' . $this->getDeclineUrl() . '"}}',
                ]
            ]
        );
        $this->buttonList->add(
            'quote_send',
            [
                'label' => __('Send'),
                'class' => 'send primary',
                'disabled' => $isDisabled,
                'data_attribute' => [
                    'mage-init' => '{"Magento_NegotiableQuote/quote/create/save-as-draft":'
                        . '{"saveUrl": "' . $this->getSendUrl() . '", "reload": "true"}}',
                ]
            ]
        );
        $this->setId('quotes_quote_view');
    }

    /**
     * Get URL for back (reset) button.
     *
     * @return string
     */
    protected function getBackUrl()
    {
        return $this->getUrl('*/*/');
    }

    /**
     * Get URL for print button.
     *
     * @return string
     */
    private function getPrintUrl()
    {
        return $this->getUrl('*/*/print/');
    }

    /**
     * Get URL for save as draft quote.
     *
     * @return string
     */
    protected function getSaveAsDraftUrl()
    {
        return $this->getUrl('*/*/save/');
    }

    /**
     * Get URL for decline quote.
     *
     * @return string
     */
    protected function getDeclineUrl()
    {
        return $this->getUrl('quotes/*/decline');
    }

    /**
     * Get URL for send quote.
     *
     * @return string
     */
    protected function getSendUrl()
    {
        return $this->getUrl('quotes/*/send');
    }

    /**
     * URL getter.
     *
     * @param string $params [optional]
     * @param array $params2 [optional]
     * @return string
     */
    public function getUrl($params = '', $params2 = [])
    {
        $params2['quote_id'] = $this->getQuoteId();
        return parent::getUrl($params, $params2);
    }

    /**
     * Retrieve Quote Identifier.
     *
     * @return int
     */
    protected function getQuoteId()
    {
        return $this->getRequest()->getParam('quote_id');
    }
}
