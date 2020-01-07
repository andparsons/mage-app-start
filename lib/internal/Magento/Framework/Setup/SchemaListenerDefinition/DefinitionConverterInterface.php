<?php

namespace Magento\Framework\Setup\SchemaListenerDefinition;

/**
 * Definition formatting interface.
 */
interface DefinitionConverterInterface
{
    /**
     * Takes definition and convert to new format.
     *
     * @param array $definition
     * @return array
     */
    public function convertToDefinition(array $definition);
}
