<?php
declare(strict_types=1);

namespace Magento\Framework\GraphQl\Schema\Type;

/**
 * Wrapper for GraphQl IdType
 */
class IdType extends \GraphQL\Type\Definition\IDType implements InputTypeInterface, OutputTypeInterface
{
    /**
     * @var string
     */
    public $name = "Magento_Id";
}
