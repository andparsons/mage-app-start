<?php
declare(strict_types=1);

namespace Magento\PageBuilderDataMigration\Setup\DataConverter\ChildrenExtractor;

use Magento\PageBuilderDataMigration\Setup\DataConverter\ChildrenExtractorInterface;

/**
 * Children data extractor for elements that don't have children
 */
class Dummy implements ChildrenExtractorInterface
{
    /**
     * @inheritdoc
     */
    public function extract(array $data) : array
    {
        return [];
    }
}
