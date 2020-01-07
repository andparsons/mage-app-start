<?php
namespace Magento\Framework\Filter\FilterManager;

/**
 * Filter plugin manager config
 */
class Config implements ConfigInterface
{
    /**
     * @var string[]
     */
    protected $factories = [\Magento\Framework\Filter\Factory::class, \Magento\Framework\Filter\ZendFactory::class];

    /**
     * @param string[] $factories
     */
    public function __construct(array $factories = [])
    {
        if (!empty($factories)) {
            $this->factories = array_merge($factories, $this->factories);
        }
    }

    /**
     * Get list of factories
     *
     * @return string[]
     */
    public function getFactories()
    {
        return $this->factories;
    }
}
