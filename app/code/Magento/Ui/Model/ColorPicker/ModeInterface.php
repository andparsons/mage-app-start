<?php

declare(strict_types=1);

namespace Magento\Ui\Model\ColorPicker;

/**
 * Mode interface for color modes
 */
interface ModeInterface
{
    /**
     * Returns config parameters for spectrum library
     *
     * @return array
     */
    public function getConfig() : array ;
}
