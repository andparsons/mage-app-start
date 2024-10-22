<?php

declare(strict_types=1);

namespace Magento\AuthorizenetAcceptjs\Gateway\Http\Payload\Filter;

use Magento\AuthorizenetAcceptjs\Gateway\Http\Payload\FilterInterface;

/**
 * Removes a set of fields from the payload
 */
class RemoveFieldsFilter implements FilterInterface
{
    /**
     * @var array
     */
    private $fields;

    /**
     * @param array $fields
     */
    public function __construct(array $fields)
    {
        $this->fields = $fields;
    }

    /**
     * @inheritdoc
     */
    public function filter(array $data): array
    {
        foreach ($this->fields as $field) {
            unset($data[$field]);
        }

        return $data;
    }
}
