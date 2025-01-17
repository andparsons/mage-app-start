<?php
namespace Magento\Company\Ui\Component\Listing\Column;

use Magento\Company\Api\Data\ValidationRuleInterface;

class ValidationRules
{
    /**
     * @var array
     */
    protected $inputValidationMap = [
        'alpha' => 'validate-alpha',
        'numeric' => 'validate-number',
        'alphanumeric' => 'validate-alphanum',
        'url' => 'validate-url',
        'email' => 'validate-email',
    ];

    /**
     * Return list of validation rules with their value
     *
     * @param boolean $isRequired
     * @param array $validationRules
     * @return array
     */
    public function getValidationRules($isRequired, $validationRules)
    {
        $rules = [];
        if ($isRequired) {
            $rules['required-entry'] = true;
        }
        if (empty($validationRules)) {
            return $rules;
        }
        /** @var ValidationRuleInterface $rule */
        foreach ($validationRules as $rule) {
            if (!$rule instanceof ValidationRuleInterface) {
                continue;
            }
            $validationClass = $this->getValidationClass($rule);
            if ($validationClass) {
                $rules[$validationClass] = $this->getRuleValue($rule);
            }
        }

        return $rules;
    }

    /**
     * Return validation class based on rule name or value
     *
     * @param ValidationRuleInterface $rule
     * @return string
     */
    protected function getValidationClass(ValidationRuleInterface $rule)
    {
        $key = $rule->getName() == 'input_validation' ? $rule->getValue() : $rule->getName();
        return isset($this->inputValidationMap[$key])
            ? $this->inputValidationMap[$key]
            : $key;
    }

    /**
     * Return rule value
     *
     * @param ValidationRuleInterface $rule
     * @return bool|string
     */
    protected function getRuleValue(ValidationRuleInterface $rule)
    {
        return $rule->getName() != 'input_validation' ? $rule->getValue() : true;
    }
}
