<?php

namespace Magento\PageCache\Model;

/**
 * @api
 * @since 100.2.0
 */
interface VclGeneratorInterface
{
    /**
     * Return generated varnish.vcl configuration file
     *
     * @param int $version
     * @return string
     * @since 100.2.0
     */
    public function generateVcl($version);
}
