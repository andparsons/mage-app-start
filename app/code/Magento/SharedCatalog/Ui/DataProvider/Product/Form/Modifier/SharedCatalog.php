<?php
namespace Magento\SharedCatalog\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\SharedCatalog\Model\ProductSharedCatalogsLoader;

/**
 * Class SharedCatalog
 */
class SharedCatalog extends AbstractModifier
{
    /**
     * @var LocatorInterface
     */
    private $locator;

    /**
     * @var ProductSharedCatalogsLoader
     */
    private $productSharedCatalogsLoader;

    /**
     * @param LocatorInterface $locator
     * @param ProductSharedCatalogsLoader $productSharedCatalogsLoader
     */
    public function __construct(
        LocatorInterface $locator,
        ProductSharedCatalogsLoader $productSharedCatalogsLoader
    ) {
        $this->locator = $locator;
        $this->productSharedCatalogsLoader = $productSharedCatalogsLoader;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyData(array $data)
    {
        $product = $this->locator->getProduct();
        $sharedCatalogs = $this->productSharedCatalogsLoader->getAssignedSharedCatalogs($product->getSku());

        if ($sharedCatalogs) {
            foreach ($sharedCatalogs as $sharedCatalog) {
                $data[$product->getId()][self::DATA_SOURCE_DEFAULT]['shared_catalog'][] = $sharedCatalog->getId();
            }
        }
        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyMeta(array $meta)
    {
        return $meta;
    }
}
