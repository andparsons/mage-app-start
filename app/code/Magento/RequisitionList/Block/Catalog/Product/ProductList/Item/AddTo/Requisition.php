<?php
namespace Magento\RequisitionList\Block\Catalog\Product\ProductList\Item\AddTo;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Block\Product\AwareInterface as ProductAwareInterface;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Catalog\Block\Product\Context;

/**
 * Add product to requisition
 *
 * @api
 * @since 100.0.0
 */
class Requisition extends \Magento\Framework\View\Element\Template implements ProductAwareInterface
{
    /**
     * @var ProductInterface
     */
    private $product;

    /**
     * @var HttpContext
     */
    private $httpContext;

    /**
     * @param Context $context
     * @param HttpContext $httpContext
     * @param array $data
     */
    public function __construct(
        Context $context,
        HttpContext $httpContext,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->httpContext = $httpContext;
    }

    /**
     * {@inheritdoc}
     */
    public function setProduct(ProductInterface $product)
    {
        $this->product = $product;
        return $this;
    }

    /**
     * Get product
     *
     * @return ProductInterface
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * Get Current Product ID.
     *
     * @return string
     */
    public function getComponentId()
    {
        return $this->getProduct()->getId();
    }

    /**
     * Get Current Product Sku
     *
     * @return string
     */
    public function getProductSku()
    {
        return $this->getProduct()->getSku();
    }

    /**
     * {@inheritdoc}
     */
    protected function _toHtml()
    {
        $isCustomerLoggedIn = $this->httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_AUTH);
        return $isCustomerLoggedIn ? parent::_toHtml() : '';
    }
}
