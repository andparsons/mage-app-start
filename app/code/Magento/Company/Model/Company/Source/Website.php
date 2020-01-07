<?php
declare(strict_types=1);

namespace Magento\Company\Model\Company\Source;

use Magento\Company\Model\Company\Source\Provider\CustomerAttributeOptions;
use Magento\Framework\Data\OptionSourceInterface;

/**
 * Websites where to look for customers.
 */
class Website implements OptionSourceInterface
{
    /**
     * @var CustomerAttributeOptions
     */
    private $provider;

    /**
     * @param CustomerAttributeOptions $provider
     */
    public function __construct(CustomerAttributeOptions $provider)
    {
        $this->provider = $provider;
    }

    /**
     * @inheritDoc
     */
    public function toOptionArray()
    {
        return $this->provider->loadOptions('website_id');
    }
}
