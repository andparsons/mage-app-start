<?php
declare(strict_types=1);

namespace Magento\Framework\GraphQl\Schema\Type;

use Magento\Framework\GraphQl\Schema\TypeInterface;

/**
 * Interface for GraphQl InputType only used for input
 */
interface InputTypeInterface extends \GraphQL\Type\Definition\InputType, TypeInterface
{

}
