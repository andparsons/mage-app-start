<?php
namespace Magento\Company\Model\Action\Validator;

use Magento\Framework\App\RequestInterface;

/**
 * Class Captcha
 */
class Captcha
{
    /**
     * @var \Magento\Captcha\Helper\Data
     */
    private $helper;

    /**
     * @var \Magento\Captcha\Observer\CaptchaStringResolver
     */
    private $stringResolver;

    /**
     * @param \Magento\Captcha\Helper\Data $helper
     * @param \Magento\Captcha\Observer\CaptchaStringResolver $stringResolver
     */
    public function __construct(
        \Magento\Captcha\Helper\Data $helper,
        \Magento\Captcha\Observer\CaptchaStringResolver $stringResolver
    ) {
        $this->helper = $helper;
        $this->stringResolver = $stringResolver;
    }

    /**
     * Validate request captcha
     *
     * @param string $formId
     * @param RequestInterface $request
     * @return bool
     */
    public function validate($formId, RequestInterface $request)
    {
        $captcha = $this->helper->getCaptcha($formId);
        return !$captcha->isRequired() || $captcha->isCorrect($this->stringResolver->resolve($request, $formId));
    }
}
