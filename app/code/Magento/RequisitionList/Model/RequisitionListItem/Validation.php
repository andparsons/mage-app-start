<?php

namespace Magento\RequisitionList\Model\RequisitionListItem;

use Magento\RequisitionList\Api\Data\RequisitionListItemInterface;

/**
 * Requisition List Item validation service.
 *
 * @api
 * @since 100.0.0
 */
class Validation
{
    /**
     * @var ValidatorInterface[]
     */
    private $validators;

    /**
     * @param ValidatorInterface[] $validators [optional]
     */
    public function __construct(
        $validators = []
    ) {
        $this->validators = $validators;
    }

    /**
     * Validate list item.
     *
     * @param RequisitionListItemInterface $item
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function validate(RequisitionListItemInterface $item)
    {
        $errors = [];
        foreach ($this->validators as $validator) {
            $errors = array_merge($errors, $validator->validate($item));
            if (count($errors)) {
                break;
            }
        }
        return $errors;
    }

    /**
     * Is list item valid.
     *
     * @param RequisitionListItemInterface $item
     * @return boolean
     */
    public function isValid(RequisitionListItemInterface $item)
    {
        return !count($this->validate($item));
    }
}
