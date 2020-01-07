<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SharedCatalog\Ui\Component\Listing\Column\Configure;

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
        $this->prepareAssignUrl()
            ->prepareMassAssignUrl();
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
            'shared_catalog/sharedCatalog/configure_product_assign'
        );
    }

    /**
     * Prepares mass assign url
     *
     * @return $this
     */
    protected function prepareMassAssignUrl()
    {
        return $this->prepareClientSaveUrl(
            'massAssignClientConfig',
            'shared_catalog/sharedCatalog/configure_product_massAssign'
        );
    }
}
