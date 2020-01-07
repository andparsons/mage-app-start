<?php

namespace Magento\NegotiableQuote\Model\ResourceModel;

/**
 * Negotiable Quote Item resource model.
 */
class NegotiableQuoteItem extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**#@+*/
    const NEGOTIABLE_QUOTE_ITEM_TABLE = 'negotiable_quote_item';
    /**#@-*/

    /**
     * @inheritdoc
     */
    protected $_useIsObjectNew = true;

    /**
     * @inheritdoc
     */
    protected $_isPkAutoIncrement = false;

    /**
     * Define main table.
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(self::NEGOTIABLE_QUOTE_ITEM_TABLE, 'quote_item_id');
    }

    /**
     * Save list of negotiable quote items in bulk via single query.
     *
     * @param \Magento\NegotiableQuote\Api\Data\NegotiableQuoteItemInterface[] $items
     * @return void
     */
    public function saveList(array $items)
    {
        $data = [];
        foreach ($items as $item) {
            $data[] = $item->getData();
        }
        
        if (!empty($data)) {
            $this->getConnection()->insertOnDuplicate(
                $this->getMainTable(),
                $data,
                [
                    \Magento\NegotiableQuote\Api\Data\NegotiableQuoteItemInterface::ORIGINAL_PRICE,
                    \Magento\NegotiableQuote\Api\Data\NegotiableQuoteItemInterface::ORIGINAL_TAX_AMOUNT,
                    \Magento\NegotiableQuote\Api\Data\NegotiableQuoteItemInterface::ORIGINAL_DISCOUNT_AMOUNT,
                ]
            );
        }
    }
}
