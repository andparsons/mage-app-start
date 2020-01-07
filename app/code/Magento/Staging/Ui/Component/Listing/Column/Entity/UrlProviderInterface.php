<?php
namespace Magento\Staging\Ui\Component\Listing\Column\Entity;

/**
 * Interface \Magento\Staging\Ui\Component\Listing\Column\Entity\UrlProviderInterface
 *
 */
interface UrlProviderInterface
{
    /**
     * Get URL for data provider item
     *
     * @param array $item
     * @return string
     */
    public function getUrl(array $item);
}
