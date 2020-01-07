<?php
declare(strict_types=1);

namespace Magento\PageBuilderDataMigration\Setup\DataConverter\ChildrenExtractor;

use Magento\PageBuilderDataMigration\Setup\DataConverter\ChildrenExtractorInterface;

/**
 * Children data extractor that can be configured with path to children data
 */
class Configurable implements ChildrenExtractorInterface
{
    /**
     * @var string
     */
    private $path;

    /**
     * Configurable constructor.
     *
     * @param string $path
     */
    public function __construct(
        string $path
    ) {
        $this->path = $path;
    }

    /**
     * @inheritdoc
     */
    public function extract(array $data) : array
    {
        $parts = explode('/', $this->path);
        $children = [];
        foreach ($parts as $part) {
            if (isset($data[$part])) {
                $data = $children = $data[$part];
            }
        }
        return $children;
    }
}
