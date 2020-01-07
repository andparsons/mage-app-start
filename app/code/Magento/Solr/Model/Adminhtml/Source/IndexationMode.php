<?php
namespace Magento\Solr\Model\Adminhtml\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Search engine indexation modes
 */
class IndexationMode implements ArrayInterface
{
    /**
     * Indexation mode that provide commit after all documents are added to index.
     * Products are not visible at front before indexation is not completed.
     */
    const MODE_FINAL = 0;

    /**
     * Indexation mode that provide commit after defined amount of products.
     * Products become visible after products bunch is indexed.
     * This is not auto commit using search engine feature.
     *
     * @see \Magento\CatalogSearch\Model\Indexer\Fulltext\Action\Full::getSearchableProducts() limitation
     */
    const MODE_PARTIAL = 1;

    /**
     * Indexation mode when commit is not provided by Magento at all.
     * Changes will be applied after third party search engine autocommit will be called.
     *
     * @see e.g. /app/code/Solr/conf/solrconfig.xml : <luceneAutoCommit/>, <autoCommit/>
     */
    const MODE_ENGINE = 2;

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        $modes = [
            self::MODE_FINAL => __('Final commit'),
            self::MODE_PARTIAL => __('Partial commit'),
            self::MODE_ENGINE => __('Engine autocommit'),
        ];

        $options = [];
        foreach ($modes as $value => $label) {
            $options[] = ['value' => $value, 'label' => $label];
        }

        return $options;
    }
}
