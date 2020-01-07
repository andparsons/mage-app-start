<?php
namespace Magento\Setup\Module\I18n\Parser;

/**
 * Adapter Interface
 */
interface AdapterInterface
{
    /**
     * Parse file
     *
     * @param string $file
     * @return array
     */
    public function parse($file);

    /**
     * Get parsed phrases
     *
     * @return array
     */
    public function getPhrases();
}
