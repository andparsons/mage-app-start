<?php
declare(strict_types=1);

namespace Magento\Framework\GraphQl\Schema\Type;

use Magento\Framework\GraphQl\Schema\TypeInterface;

/**
 * Interface for GraphQl WrappedType used to wrap other types like array or not null
 */
interface WrappedTypeInterface extends \GraphQL\Type\Definition\WrappingType, TypeInterface
{

}
