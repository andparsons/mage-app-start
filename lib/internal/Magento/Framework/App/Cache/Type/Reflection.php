<?php
namespace Magento\Framework\App\Cache\Type;

/**
 * System / Cache Management / Cache type "Reflection Data"
 */
class Reflection extends \Magento\Framework\Cache\Frontend\Decorator\TagScope
{
    /**
     * Cache type code unique among all cache types
     */
    const TYPE_IDENTIFIER = 'reflection';

    /**
     * Cache tag used to distinguish the cache type from all other cache
     */
    const CACHE_TAG = 'REFLECTION';

    /**
     * @param \Magento\Framework\App\Cache\Type\FrontendPool $cacheFrontendPool
     */
    public function __construct(\Magento\Framework\App\Cache\Type\FrontendPool $cacheFrontendPool)
    {
        parent::__construct($cacheFrontendPool->get(self::TYPE_IDENTIFIER), self::CACHE_TAG);
    }
}
