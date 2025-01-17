<?php
namespace Magento\Swatches\Test\Constraint;

use Magento\Catalog\Test\Page\Category\CatalogCategoryView;
use Magento\Cms\Test\Page\CmsIndex;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Assert swatches are visible on category page
 */
class AssertSwatchesAreVisibleOnCategoryPage extends AbstractConstraint
{
    /**
     * @param CatalogCategoryView $catalogCategoryView
     * @param CmsIndex $cmsIndex
     * @param FixtureInterface $product
     * @return void
     */
    public function processAssert(
        CatalogCategoryView $catalogCategoryView,
        CmsIndex $cmsIndex,
        FixtureInterface $product
    ) {
        $this->openCategoryPage($cmsIndex, $product);

        $isSwatchesBlockVisible = $catalogCategoryView->getListSwatchesProductBlock()
            ->getProductItem($product)->isSwatchesBlockVisible();

        \PHPUnit\Framework\Assert::assertTrue(
            $isSwatchesBlockVisible,
            'Swatches are absent on category page.'
        );
    }

    /**
     * Open category view page
     *
     * @param CmsIndex $cmsIndex
     * @param FixtureInterface $product
     * @return void
     */
    private function openCategoryPage(
        CmsIndex $cmsIndex,
        FixtureInterface $product
    ) {
        $categoryIds = $product->getCategoryIds();
        $categoryName = reset($categoryIds);
        $cmsIndex->open();
        $cmsIndex->getTopmenu()->selectCategoryByName($categoryName);
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'Swatches visibility is correct.';
    }
}
