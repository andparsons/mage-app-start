<?php

namespace Magento\Framework\Model\Entity;

/**
 * Interface ScopeInterface
 */
interface ScopeInterface
{
    /**
     * @return string
     */
    public function getValue();

    /**
     * @return string
     */
    public function getIdentifier();

    /**
     * @return ScopeInterface|null
     */
    public function getFallback();
}
