<?php
declare(strict_types=1);

namespace Magento\Framework\App;

/**
 * Feed factory interface
 */
interface FeedFactoryInterface
{
    /**
     * RSS feed input format
     */
    const FORMAT_RSS = 'rss';

    /**
     * Returns FeedInterface object from a custom array
     *
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\RuntimeException
     * @param array $data
     * @param string $format
     * @return FeedInterface
     */
    public function create(array $data, string $format = self::FORMAT_RSS) : FeedInterface;
}
