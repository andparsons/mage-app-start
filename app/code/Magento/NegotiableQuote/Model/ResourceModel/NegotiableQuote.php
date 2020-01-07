<?php
namespace Magento\NegotiableQuote\Model\ResourceModel;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface;

/**
 * Negotiable Quote resource model
 */
class NegotiableQuote extends AbstractDb
{
    /**#@+
     * Negotiable quote table
     */
    const NEGOTIABLE_QUOTE_TABLE = 'negotiable_quote';
    /**#@-*/

    /**
     * Define main table
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(self::NEGOTIABLE_QUOTE_TABLE, 'quote_id');
    }

    /**
     * Assign quote negotiated data
     *
     * @param NegotiableQuoteInterface $negotiableQuote
     * @return $this
     * @throws CouldNotSaveException
     */
    public function saveNegotiatedQuoteData(
        NegotiableQuoteInterface $negotiableQuote
    ) {
        $negotiableData = $negotiableQuote->getData();

        if ($negotiableData) {
            try {
                $this->getConnection()->insertOnDuplicate(
                    $this->getTable(self::NEGOTIABLE_QUOTE_TABLE),
                    $negotiableData,
                    array_keys($negotiableData)
                );
            } catch (\Exception $e) {
                throw new CouldNotSaveException(
                    __('Changes to the negotiated quote were not saved. Please try again.')
                );
            }
        }

        return $this;
    }
}
