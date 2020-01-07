<?php
namespace Magento\Company\Model\ResourceModel\Structure;

use Magento\Framework\Data\Tree\Dbp;

/**
 * Tree for company hierarchy.
 */
class Tree extends Dbp
{
    /**
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @throws \DomainException
     * @throws \Exception
     */
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource
    ) {
        $this->_coreResource = $resource;
        parent::__construct(
            $resource->getConnection(),
            $resource->getTableName('company_structure'),
            [
                self::ID_FIELD      => 'structure_id',
                self::PATH_FIELD    => 'path',
                self::ORDER_FIELD   => 'position',
                self::LEVEL_FIELD   => 'level'
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function move($node, $newParent, $prevNode = null)
    {
        parent::move($node, $newParent, $prevNode);
        $this->_conn->update(
            $this->_table,
            ['parent_id' => $newParent->getId()],
            $this->_conn->quoteInto("{$this->_idField} = ?", $node->getId())
        );
    }

    /**
     * Sets whether the tree is loaded from database.
     *
     * @param bool $loaded
     * @return void
     */
    public function setLoaded($loaded)
    {
        $this->_loaded = $loaded;
    }
}
