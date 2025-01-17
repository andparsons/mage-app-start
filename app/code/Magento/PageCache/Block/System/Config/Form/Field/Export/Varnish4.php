<?php
namespace Magento\PageCache\Block\System\Config\Form\Field\Export;

/**
 * Class Export
 */
class Varnish4 extends \Magento\PageCache\Block\System\Config\Form\Field\Export
{
    /**
     * Return Varnish version to this class
     *
     * @return int
     */
    public function getVarnishVersion()
    {
        return 4;
    }
}
