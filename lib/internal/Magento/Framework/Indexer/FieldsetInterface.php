<?php
namespace Magento\Framework\Indexer;

/**
 * @api Implement custom Fieldset
 * @since 100.0.2
 */
interface FieldsetInterface
{
    /**
     * Add additional fields to fieldset
     *
     * @param array $data
     * @return array
     */
    public function addDynamicData(array $data);
}
