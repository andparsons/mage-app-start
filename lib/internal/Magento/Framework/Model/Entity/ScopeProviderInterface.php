<?php

namespace Magento\Framework\Model\Entity;

/**
 * Class ScopeProviderInterface
 */
interface ScopeProviderInterface
{
    /**
     * @param string $entityType
     * @param array $entityData
     * @return mixed
     */
    public function getContext($entityType, $entityData = []);
}
