<?php
declare(strict_types=1);

namespace Magento\Framework\GraphQl\Query\Resolver;

use Magento\Framework\GraphQl\Query\ResolverInterface;

/**
 * Resolver Context is used as a shared data extensible object in all resolvers that implement @see ResolverInterface.
 *
 * GraphQL will pass the same instance of this interface to each field resolver, so these resolvers could have
 * shared access to the same data for ease of implementation purposes.
 */
interface ContextInterface
{
}
