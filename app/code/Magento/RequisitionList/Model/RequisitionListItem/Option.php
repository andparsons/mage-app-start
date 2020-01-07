<?php
namespace Magento\RequisitionList\Model\RequisitionListItem;

use Magento\RequisitionList\Model\RequisitionListItem;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Api\ProductRepositoryInterface;

/**
 * Item option model
 */
class Option extends \Magento\Framework\Model\AbstractModel implements
    \Magento\Catalog\Model\Product\Configuration\Item\Option\OptionInterface
{
    /**
     * @var RequisitionListItem
     */
    private $item;

    /**
     * @var Product|null
     */
    private $product;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param ProductRepositoryInterface $productRepository
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        ProductRepositoryInterface $productRepository,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->productRepository = $productRepository;
    }

    /**
     * Get option value
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->_getData('value');
    }

    /**
     * Set option item
     *
     * @param RequisitionListItem $item
     * @return $this
     */
    public function setItem(RequisitionListItem $item)
    {
        $this->item = $item;
        return $this;
    }

    /**
     * Get option item
     *
     * @return RequisitionListItem
     */
    public function getItem()
    {
        return $this->item;
    }

    /**
     * Set option product
     *
     * @param   Product $product
     * @return  $this
     */
    public function setProduct($product)
    {
        $this->setProductId($product->getId());
        $this->product = $product;
        return $this;
    }

    /**
     * Get option product
     *
     * @return Product
     */
    public function getProduct()
    {
        //In some cases product_id is present instead product instance
        if (null === $this->product && $this->getProductId()) {
            $this->product = $this->productRepository->getById($this->getProductId());
        }
        return $this->product;
    }

    /**
     * Clone option object
     *
     * @return $this
     */
    public function __clone()
    {
        $this->setId(null);
        $this->item = null;
        return $this;
    }
}
