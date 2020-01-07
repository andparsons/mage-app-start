<?php
declare(strict_types=1);

namespace Magento\Framework\GraphQl\Schema\Type;

/**
 * Wrapper for GraphQl StringType
 */
class StringType extends \GraphQL\Type\Definition\StringType implements InputTypeInterface, OutputTypeInterface
{
    /**
     * @var string
     */
    public $name = "Magento_String";
}
