<?php
namespace Magento\Setup\Module\Dependency;

/**
 * Parser Interface
 */
interface ParserInterface
{
    /**
     * Parse files
     *
     * @param array $options
     * @return array
     */
    public function parse(array $options);
}
