<?php
namespace Magento\Ui\Config\Argument;

/**
 * Convert node to array
 */
interface ParserInterface
{
    /**
     * Parse xml node to array
     *
     * @param array $data
     * @param \DOMNode $node
     * @return array
     */
    public function parse(array $data, \DOMNode $node);
}
