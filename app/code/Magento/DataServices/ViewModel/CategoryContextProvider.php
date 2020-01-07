<?php
declare(strict_types=1);

namespace Magento\DataServices\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Catalog\Model\Layer\Resolver;

/**
 * ViewModel for providing category information to the Data Services Data Layer
 */
class CategoryContextProvider implements ArgumentInterface
{
    /**
     * @var Resolver
     */
    private $layerResolver;

    /**
     * @param Resolver $layerResolver
     */
    public function __construct(
        Resolver $layerResolver
    ) {
        $this->layerResolver = $layerResolver;
    }

    /**
     * Return current category information
     *
     * @return array
     */
    public function getModelContext() : array
    {
        $currentCategory = $this->layerResolver->get()->getCurrentCategory();
        $category = [
            'name' => $currentCategory->getName(),
            'urlKey' => $currentCategory->getUrlKey(),
            'urlPath' => $currentCategory->getUrlPath()
        ];
        return $category;
    }
}
