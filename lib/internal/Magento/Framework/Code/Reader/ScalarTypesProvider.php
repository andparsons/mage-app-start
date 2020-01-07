<?php

namespace Magento\Framework\Code\Reader;

/**
 * Class ScalarTypesProvider returns array of supported scalar types.
 */
class ScalarTypesProvider
{
    /**
     * Return array of scalar types.
     *
     * @return array
     */
    public function getTypes()
    {
        return [
            'array',
            'string',
            'int',
            'integer',
            'float',
            'bool',
            'boolean',
            'mixed',
            'callable',
        ];
    }
}
