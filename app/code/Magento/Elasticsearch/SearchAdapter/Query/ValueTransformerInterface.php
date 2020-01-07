<?php
declare(strict_types=1);

namespace Magento\Elasticsearch\SearchAdapter\Query;

/**
 * Value transformer of search term for matching with ES field types.
 */
interface ValueTransformerInterface
{
    /**
     * Transform value according to field type.
     *
     * @param string $value
     * @return mixed
     */
    public function transform(string $value);
}
