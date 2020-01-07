<?php

namespace Magento\SharedCatalog\Model\Customer\Source;

/**
 * Class GroupIncludeNotLogged.
 */
class GroupIncludeNotLogged extends Group
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            ['label' => __('Customer Groups'), 'value' => $this->getCustomerGroups(false, false)],
            ['label' => __('Shared Catalogs'), 'value' => $this->getCustomerGroups(true)],
        ];
    }
}
