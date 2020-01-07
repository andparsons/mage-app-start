<?php
namespace Magento\Staging\Model\Entity;

use Magento\Framework\DataObject;

/**
 * Interface \Magento\Staging\Model\Entity\BuilderInterface
 *
 */
interface BuilderInterface
{
    /**
     * Build entity by prototype
     *
     * @param object $prototype
     * @return object
     */
    public function build($prototype);
}
