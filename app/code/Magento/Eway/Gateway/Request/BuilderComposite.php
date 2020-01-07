<?php
namespace Magento\Eway\Gateway\Request;

use Magento\Payment\Gateway\Request\BuilderComposite as PaymentBuilderComposite;

/**
 * Class BuilderComposite
 *
 * @deprecated 100.3.3 Starting from Magento 2.3.3 eWay payment method core integration is deprecated
 *      in favor of official payment integration available on the marketplace
 */
class BuilderComposite extends PaymentBuilderComposite
{
    /**
     * @inheritdoc
     */
    protected function merge(array $result, array $builder)
    {
        return array_replace_recursive($result, $builder);
    }
}
