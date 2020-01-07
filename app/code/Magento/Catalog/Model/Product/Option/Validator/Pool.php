<?php

namespace Magento\Catalog\Model\Product\Option\Validator;

class Pool
{
    /**
     * @var \Zend_Validate_Interface
     */
    protected $validators;

    /**
     * @param \Zend_Validate_Interface[] $validators
     */
    public function __construct(array $validators)
    {
        $this->validators = $validators;
    }

    /**
     * Get validator
     *
     * @param string $type
     * @return \Zend_Validate_Interface
     */
    public function get($type)
    {
        return $this->validators[$type] ?? $this->validators['default'];
    }
}
