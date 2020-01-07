<?php
namespace Magento\Sales\Model\Config\Source\Order\Status;

/**
 * Order Statuses source model
 */
class NewStatus extends \Magento\Sales\Model\Config\Source\Order\Status
{
    /**
     * @var string
     */
    protected $_stateStatuses = \Magento\Sales\Model\Order::STATE_NEW;
}
