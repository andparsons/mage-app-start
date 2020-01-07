<?php
namespace Magento\Framework\Indexer\SaveHandler;

class Batch
{
    /**
     * @param \Traversable $documents
     * @param int $size
     * @return \Generator
     */
    public function getItems(\Traversable $documents, $size)
    {
        $i = 0;
        $batch = [];

        foreach ($documents as $documentName => $documentValue) {
            $batch[$documentName] = $documentValue;
            if (++$i == $size) {
                yield $batch;
                $i = 0;
                $batch = [];
            }
        }
        if (count($batch) > 0) {
            yield $batch;
        }
    }
}
