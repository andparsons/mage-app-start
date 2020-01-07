<?php
declare(strict_types=1);

namespace Magento\GiftCardGraphQl\Model;

use Magento\Framework\GraphQl\Query\Resolver\TypeResolverInterface;

/**
 * {@inheritdoc}
 */
class GiftCardProductTypeResolver implements TypeResolverInterface
{
    /**
     * {@inheritdoc}
     */
    public function resolveType(array $data) : string
    {
        if (isset($data['type_id']) && $data['type_id'] == 'giftcard') {
            return 'GiftCardProduct';
        }
        return '';
    }
}
