<?php
declare(strict_types=1);

namespace Magento\InventoryReservationCli\Model\SalableQuantityInconsistency;

use Magento\InventoryReservationCli\Model\GetCompleteOrderStatusList;
use Magento\InventoryReservationCli\Model\SalableQuantityInconsistency;

/**
 * Remove all reservations with incomplete state
 */
class FilterIncompleteOrders
{
    /**
     * @var GetCompleteOrderStatusList
     */
    private $getCompleteOrderStatusList;

    /**
     * @param GetCompleteOrderStatusList $getCompleteOrderStatusList
     */
    public function __construct(
        GetCompleteOrderStatusList $getCompleteOrderStatusList
    ) {
        $this->getCompleteOrderStatusList = $getCompleteOrderStatusList;
    }

    /**
     * Remove all reservations with incomplete state
     *
     * @param SalableQuantityInconsistency[] $inconsistencies
     * @return SalableQuantityInconsistency[]
     */
    public function execute(array $inconsistencies): array
    {
        return array_filter(
            $inconsistencies,
            function (SalableQuantityInconsistency $inconsistency) {
                return !in_array($inconsistency->getOrder()->getStatus(), $this->getCompleteOrderStatusList->execute());
            }
        );
    }
}
