<?php

namespace Magento\Framework\Webapi\CustomAttribute;

interface ServiceTypeListInterface
{
    /**
     * Get list of all Data Interface corresponding to complex custom attribute types
     *
     * @return string[] array of Data Interface class names
     */
    public function getDataTypes();
}
