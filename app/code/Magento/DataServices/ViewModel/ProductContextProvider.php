<?php
declare(strict_types=1);

namespace Magento\DataServices\ViewModel;

use Magento\DataServices\Model\ProductContextInterface;
use Magento\Catalog\Helper\Data as CatalogHelper;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Block\ArgumentInterface;

/**
 * ViewModel for Product Context
 */
class ProductContextProvider implements ArgumentInterface
{
    /**
     * @var CatalogHelper
     */
    private $catalogHelper;

    /**
     * @var ProductContextInterface
     */
    private $productContext;

    /**
     * @param CatalogHelper $catalogHelper
     * @param ProductContextInterface $productContext
     */
    public function __construct(
        CatalogHelper $catalogHelper,
        ProductContextInterface $productContext
    ) {
        $this->catalogHelper = $catalogHelper;
        $this->productContext = $productContext;
    }

    /**
     * Return product context for data services events
     *
     * @return array
     * @throws NoSuchEntityException
     */
    public function getModelContext() : array
    {
        return $this->productContext->getContextData($this->catalogHelper->getProduct());
    }
}
