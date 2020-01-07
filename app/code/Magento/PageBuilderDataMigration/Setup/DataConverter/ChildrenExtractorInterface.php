<?php
declare(strict_types=1);

namespace Magento\PageBuilderDataMigration\Setup\DataConverter;

/**
 * Extract children data from content type data
 *
 * @api
 */
interface ChildrenExtractorInterface
{
    /**
     * Extract children for an element
     *
     * @param array $data
     * @return array
     */
    public function extract(array $data) : array;
}
