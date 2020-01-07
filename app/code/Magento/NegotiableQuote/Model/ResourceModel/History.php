<?php

namespace Magento\NegotiableQuote\Model\ResourceModel;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\NegotiableQuote\Api\Data\HistoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\NegotiableQuote\Model\History\Validator;

/**
 * Resource model for negotiable quote history log
 */
class History extends AbstractDb
{
    /**#@+
     * Negotiable quote history table
     */
    const NEGOTIABLE_QUOTE_HISTORY_TABLE = 'negotiable_quote_history';
    /**#@-*/

    /**
     * @var Validator
     */
    protected $validator;

    /**
     * Main table primary key field name
     *
     * @var string
     */
    protected $_idFieldName = 'history_id';

    /**
     * The list of possible statuses for history log
     * @var array $allowedStatuses
     */
    protected $allowedStatuses = [
        HistoryInterface::STATUS_CREATED,
        HistoryInterface::STATUS_UPDATED,
        HistoryInterface::STATUS_CLOSED,
        HistoryInterface::STATUS_UPDATED_BY_SYSTEM
    ];

    /**
     * @param Context $context
     * @param Validator $validator
     * @param string $connectionName
     */
    public function __construct(
        Context $context,
        Validator $validator,
        $connectionName = null
    ) {
        parent::__construct(
            $context,
            $connectionName
        );
        $this->validator = $validator;
    }

    /**
     * Model initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(self::NEGOTIABLE_QUOTE_HISTORY_TABLE, 'history_id');
    }

    /**
     * Perform actions before object save
     *
     * @param AbstractModel $object
     * @return $this
     * @throws LocalizedException
     */
    protected function _beforeSave(AbstractModel $object)
    {
        parent::_beforeSave($object);
        $warnings = $this->validator->validate($object);
        $currentStatus = $object->getData(HistoryInterface::STATUS);
        if (!in_array($currentStatus, $this->allowedStatuses)) {
            $warnings[] = sprintf('%s is not allowed value for status field', $currentStatus);
        }
        if (!empty($warnings)) {
            throw new LocalizedException(
                __("Cannot save comment:\n%1", implode("\n", $warnings))
            );
        }
        return $this;
    }
}
