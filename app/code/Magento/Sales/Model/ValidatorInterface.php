<?php
namespace Magento\Sales\Model;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Exception\DocumentValidationException;

/**
 * Interface ValidatorInterface
 */
interface ValidatorInterface
{
    /**
     * @param object $entity
     * @return \Magento\Framework\Phrase[]
     * @throws DocumentValidationException
     * @throws NoSuchEntityException
     */
    public function validate($entity);
}
