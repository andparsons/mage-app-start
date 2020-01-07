<?php
namespace Magento\NegotiableQuote\Model\Plugin\Checkout\Model;

use Magento\Checkout\Model\DefaultConfigProvider;
use Magento\Framework\App\Action\Context;

class DefaultConfigProviderPlugin
{

    /**
     * @var Context
     */
    protected $context;

    /**
     * DefaultConfigProviderPlugin constructor
     * @param Context $context
     */
    public function __construct(
        Context $context
    ) {
        $this->context = $context;
    }

    /**
     * @param DefaultConfigProvider $subject
     * @param string $result
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetCheckoutUrl(DefaultConfigProvider $subject, $result)
    {
        $id = $this->context->getRequest()->getParam('negotiableQuoteId');
        if ($id) {
            return $this->context->getUrl()->getUrl('checkout', ['negotiableQuoteId' => $id]);
        }
        return $result;
    }

    /**
     * @param DefaultConfigProvider $subject
     * @param string $result
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetDefaultSuccessPageUrl(DefaultConfigProvider $subject, $result)
    {
        $id = $this->context->getRequest()->getParam('negotiableQuoteId');
        if ($id) {
            return $this->context->getUrl()->getUrl('negotiable_quote/quote/order', ['quote_id' => $id]);
        }
        return $result;
    }
}
