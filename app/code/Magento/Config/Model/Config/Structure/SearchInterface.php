<?php
namespace Magento\Config\Model\Config\Structure;

/**
 * @api
 * @since 100.0.2
 */
interface SearchInterface
{
    /**
     * Find element by path
     *
     * @param string $path
     * @return ElementInterface|null
     */
    public function getElement($path);
}
