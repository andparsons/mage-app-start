<?php
namespace Magento\Framework\App\Rss;

/**
 * Interface UrlBuilderInterface
 * @package Magento\Framework\App\Rss
 */
interface UrlBuilderInterface
{
    /**
     * @param array $queryParams
     * @return mixed
     */
    public function getUrl(array $queryParams = []);
}
