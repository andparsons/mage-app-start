<?php
namespace Magento\UrlRewrite\Model;

/**
 * Url Persist Interface
 * @api
 * @since 100.0.2
 */
interface UrlPersistInterface
{
    /**
     * Save new url rewrites and remove old if exist
     *
     * @param \Magento\UrlRewrite\Service\V1\Data\UrlRewrite[] $urls
     * @return \Magento\UrlRewrite\Service\V1\Data\UrlRewrite[]
     * @throws \Magento\UrlRewrite\Model\Exception\UrlAlreadyExistsException|\Exception
     */
    public function replace(array $urls);

    /**
     * Remove rewrites that contains some rewrites data
     *
     * @param array $data
     * @return void
     * @api
     */
    public function deleteByData(array $data);
}
