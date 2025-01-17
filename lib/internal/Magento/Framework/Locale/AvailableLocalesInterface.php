<?php
namespace Magento\Framework\Locale;

use Magento\Framework\View\DesignInterface;

/**
 * Interface for classes that fetching codes of available locales for the concrete theme.
 */
interface AvailableLocalesInterface
{
    /**
     * Returns array of codes of deployed locales for the theme by given theme code and area.
     *
     * @param string $code theme code identifier
     * @param string $area area in which theme can be applied
     * @return array of locale codes, for example: ['en_US', 'en_GB']
     */
    public function getList($code, $area = DesignInterface::DEFAULT_AREA);
}
