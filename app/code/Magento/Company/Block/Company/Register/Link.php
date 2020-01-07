<?php
namespace Magento\Company\Block\Company\Register;

use Magento\Framework\App\ObjectManager;
use Magento\Customer\Model\Registration;

/**
 * Company register link
 *
 * @api
 * @since 100.0.0
 */
class Link extends \Magento\Framework\View\Element\Html\Link
{
    /**
     * @var \Magento\Framework\App\Http\Context
     */
    private $httpContext;

    /**
     * @var Registration
     */
    private $registration;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param array $data
     * @param Registration|null $registration
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\Http\Context $httpContext,
        array $data = [],
        Registration $registration = null
    ) {
        parent::__construct($context, $data);
        $this->httpContext = $httpContext;
        $this->registration = $registration ?: ObjectManager::getInstance()->get(Registration::class);
    }

    /**
     * @inheritdoc
     */
    protected function _toHtml()
    {
        if (!$this->registration->isAllowed()) {
            return '';
        }

        $isCustomerLoggedIn = (bool)$this->httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_AUTH);
        return $isCustomerLoggedIn ? '' : parent::_toHtml();
    }
}
