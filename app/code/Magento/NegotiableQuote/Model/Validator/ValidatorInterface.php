<?php

namespace Magento\NegotiableQuote\Model\Validator;

/**
 * Interface for data validation.
 */
interface ValidatorInterface
{
    /**
     * Validate $data.
     *
     * @param array $data
     * @return \Magento\NegotiableQuote\Model\Validator\ValidatorResult
     */
    public function validate(array $data);
}
