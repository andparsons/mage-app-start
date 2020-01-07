<?php
namespace Magento\Company\Block\Company\Login;

use Magento\Customer\Model\Registration;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\App\ObjectManager;

/**
 * Company login info block
 *
 * @api
 * @since 100.0.0
 */
class Info extends \Magento\Framework\View\Element\Template
{
    /**
     * Registration
     *
     * @var Registration
     */
    private $registration;

    /**
     * @param Context $context
     * @param array $data
     * @param Registration|null $registration
     */
    public function __construct(
        Context $context,
        array $data = [],
        Registration $registration = null
    ) {
        parent::__construct($context, $data);
        $this->registration = $registration ?: ObjectManager::getInstance()->get(Registration::class);
    }

    /**
     * Retrieve registration object
     *
     * @return Registration
     * @since 100.1.1
     */
    public function getRegistration()
    {
        return $this->registration;
    }

    /**
     * Get create new company url
     *
     * @return string
     */
    public function getCreateCompanyAccountUrl()
    {
        return $this->getUrl('company/account/create');
    }
}
