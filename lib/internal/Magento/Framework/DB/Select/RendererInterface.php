<?php

namespace Magento\Framework\DB\Select;

use Magento\Framework\DB\Select;

/**
 * Interface RendererInterface
 */
interface RendererInterface
{
    /**
     * Render Select part
     *
     * @param Select $select
     * @param string $sql
     * @return string
     */
    public function render(Select $select, $sql = '');
}
