<?php
namespace Magento\Framework\Acl\AclResource;

/**
 * Acl resources provider interface
 *
 * @api
 * @since 100.0.2
 */
interface ProviderInterface
{
    /**
     * Retrieve ACL resources
     *
     * @return array
     */
    public function getAclResources();
}
