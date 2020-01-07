<?php
namespace Magento\SalesRuleStaging\Model;

use Magento\Staging\Model\StagingApplierInterface;
use Magento\Framework\Event\ManagerInterface as EventManager;

/**
 * Class RuleApplier
 */
class RuleApplier implements StagingApplierInterface
{
    /**
     * @var EventManager
     */
    protected $eventManager;

    /**
     * @param EventManager $eventManager
     */
    public function __construct(
        EventManager $eventManager
    ) {
        $this->eventManager = $eventManager;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(array $entityIds)
    {
        if (!empty($entityIds)) {
            $this->eventManager->dispatch('sales_rule_updated', ['entity_ids' => $entityIds]);
        }
    }
}
