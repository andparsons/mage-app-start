<?php
namespace Magento\Indexer\Model\Source;

use Magento\Framework\Exception\NotFoundException;

/**
 * Interface \Magento\Indexer\Model\Source\DataInterface
 *
 */
interface DataInterface
{
    /**
     * @param array $fieldsData
     * @return array
     * @throws NotFoundException
     */
    public function getData(array $fieldsData);
}
