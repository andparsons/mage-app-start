<?php
namespace Magento\Framework\View\Element\UiComponent;

use Magento\Framework\View\Element\UiComponent\DataProvider\DataProviderInterface;
use Magento\Framework\View\Element\UiComponentInterface;

/**
 * Interface DataSourceInterface
 */
interface DataSourceInterface extends UiComponentInterface
{
    /**
     * @return DataProviderInterface
     */
    public function getDataProvider();
}
