<?php
namespace Magento\Paypal\Block\Payflow\Advanced;

/**
 * Payflow Advanced iframe block
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Form extends \Magento\Paypal\Block\Payflow\Link\Form
{
    /**
     * @var string
     */
    protected $_template = 'Magento_Paypal::payflowadvanced/info.phtml';

    /**
     * Get frame action URL
     *
     * @return string
     */
    public function getFrameActionUrl()
    {
        return $this->getUrl('paypal/payflowadvanced/form', ['_secure' => true]);
    }
}
