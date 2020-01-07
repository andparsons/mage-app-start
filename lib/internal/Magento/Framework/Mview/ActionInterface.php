<?php
namespace Magento\Framework\Mview;

/**
 * Interface \Magento\Framework\Mview\ActionInterface
 *
 */
interface ActionInterface
{
    /**
     * Execute materialization on ids entities
     *
     * @param int[] $ids
     * @return void
     * @api
     */
    public function execute($ids);
}
