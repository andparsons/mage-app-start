<?php
namespace Magento\Framework\Mview;

/**
 * Interface \Magento\Framework\Mview\ConfigInterface
 *
 */
interface ConfigInterface
{
    /**
     * Get views list
     *
     * @return array[]
     */
    public function getViews();

    /**
     * Get view by ID
     *
     * @param string $viewId
     * @return array
     */
    public function getView($viewId);
}
