<?php

namespace Magento\RequisitionList\Model;

use Magento\Catalog\Model\Product\Configuration\Item\ItemInterface;
use Magento\Framework\Model\AbstractExtensibleModel as ExtensibleModel;

/**
 * Requisition List Item Options Model.
 */
class RequisitionListItemOptions extends ExtensibleModel implements ItemInterface
{
    const PRODUCT = 'product';
    const OPTIONS = 'options';

    /**
     * @inheritdoc
     */
    public function getProduct()
    {
        return $this->getData(self::PRODUCT);
    }

    /**
     * @inheritdoc
     */
    public function getOptionByCode($code)
    {
        $options = $this->getData(self::OPTIONS);

        return isset($options[$code]) ? $options[$code] : null;
    }

    /**
     * Get file download params. Special file download params are not needed.
     *
     * @return null
     */
    public function getFileDownloadParams()
    {
        return null;
    }
}
