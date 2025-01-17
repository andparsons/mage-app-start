<?php

/**
 * Reward action for reverting points
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Reward\Model\Action;

class OrderRevert extends \Magento\Reward\Model\Action\AbstractAction
{
    /**
     * Check whether rewards can be added for action
     *
     * @return bool
     * @codeCoverageIgnore
     */
    public function canAddRewardPoints()
    {
        return true;
    }

    /**
     * Return action message for history log
     *
     * @param   array $args Additional history data
     * @return \Magento\Framework\Phrase
     */
    public function getHistoryMessage($args = [])
    {
        $incrementId = isset($args['increment_id']) ? $args['increment_id'] : '';
        return __('Reverted from incomplete order #%1', $incrementId);
    }

    /**
     * Setter for $_entity and add some extra data to history
     *
     * @param   \Magento\Framework\DataObject $entity
     * @return  \Magento\Reward\Model\Action\AbstractAction
     * @codeCoverageIgnore
     */
    public function setEntity($entity)
    {
        parent::setEntity($entity);
        $this->getHistory()->addAdditionalData(['increment_id' => $this->getEntity()->getIncrementId()]);

        return $this;
    }
}
