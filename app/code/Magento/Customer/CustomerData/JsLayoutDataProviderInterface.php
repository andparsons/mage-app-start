<?php

namespace Magento\Customer\CustomerData;

/**
 * Js layout data provider interface
 *
 * @api
 * @since 100.0.2
 */
interface JsLayoutDataProviderInterface
{
    /**
     * Get data
     *
     * @return array
     */
    public function getData();
}
