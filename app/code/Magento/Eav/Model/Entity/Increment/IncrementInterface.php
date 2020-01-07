<?php
namespace Magento\Eav\Model\Entity\Increment;

/**
 * @api
 * @since 100.0.2
 */
interface IncrementInterface
{
    /**
     * Get next id
     *
     * @return mixed
     */
    public function getNextId();
}
