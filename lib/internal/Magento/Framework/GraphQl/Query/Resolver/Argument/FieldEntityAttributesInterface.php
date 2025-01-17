<?php
declare(strict_types=1);

namespace Magento\Framework\GraphQl\Query\Resolver\Argument;

/**
 * Contract for the classes that retrieve attributes for a given entity configured in @see FieldEntityAttributesPool.
 */
interface FieldEntityAttributesInterface
{
    /**
     * Get the attributes for an entity
     *
     * @return array
     */
    public function getEntityAttributes() : array;
}
