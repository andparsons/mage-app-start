<?php
namespace Magento\Eway\Model\Adminhtml\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class ConnectionType provides source for backend connection_type selector
 *
 * @deprecated 100.3.3 Starting from Magento 2.3.3 eWay payment method core integration is deprecated
 *      in favor of official payment integration available on the marketplace
 */
class ConnectionType implements ArrayInterface
{
    const CONNECTION_TYPE_DIRECT = 'direct';
    const CONNECTION_TYPE_SHARED = 'shared';

    /**
     * @inheritdoc
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::CONNECTION_TYPE_DIRECT,
                'label' => 'Direct connection',
            ],
            [
                'value' => self::CONNECTION_TYPE_SHARED,
                'label' => 'Responsive shared page'
            ]
        ];
    }
}
