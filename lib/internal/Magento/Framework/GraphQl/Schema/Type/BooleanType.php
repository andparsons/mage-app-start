<?php
declare(strict_types=1);

namespace Magento\Framework\GraphQl\Schema\Type;

/**
 * Wrapper for GraphQl BooleanType
 */
class BooleanType extends \GraphQL\Type\Definition\BooleanType implements InputTypeInterface, OutputTypeInterface
{
    /**
     * @var string
     */
    public $name = "Magento_Boolean";
}
