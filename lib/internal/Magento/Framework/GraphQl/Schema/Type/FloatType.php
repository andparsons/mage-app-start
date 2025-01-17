<?php
declare(strict_types=1);

namespace Magento\Framework\GraphQl\Schema\Type;

/**
 * Wrapper for GraphQl FloatType
 */
class FloatType extends \GraphQL\Type\Definition\FloatType implements InputTypeInterface, OutputTypeInterface
{
    /**
     * @var string
     */
    public $name = "Magento_Float";
}
