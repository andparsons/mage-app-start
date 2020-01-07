<?php
declare(strict_types=1);

namespace Magento\Framework\GraphQl\Schema\Type;

/**
 * Wrapper for GraphQl IntType
 */
class IntType extends \GraphQL\Type\Definition\IntType implements InputTypeInterface, OutputTypeInterface
{
    /**
     * @var string
     */
    public $name = "Magento_Int";
}
