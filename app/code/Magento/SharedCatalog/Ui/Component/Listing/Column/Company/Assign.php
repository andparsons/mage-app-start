<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SharedCatalog\Ui\Component\Listing\Column\Company;

use Magento\SharedCatalog\Ui\Component\Listing\Column\Configure\AbstractColumn;

/**
 * Assign column component
 */
class Assign extends AbstractColumn
{
    /**
     * {@inheritdoc}
     */
    public function prepare()
    {
        $this->prepareAssignUrl();
        parent::prepare();
    }

    /**
     * Prepares assign url
     *
     * @return $this
     */
    protected function prepareAssignUrl()
    {
        return $this->prepareClientSaveUrl(
            'assignClientConfig',
            'shared_catalog/sharedCatalog/company_assign'
        );
    }
}
