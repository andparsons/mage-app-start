<?php
namespace Magento\Sniffs\Less;

/**
 * Interface TokenizerSymbolsInterface
 */
interface TokenizerSymbolsInterface
{
    const TOKENIZER_CSS = 'CSS';

    /**#@+
     * Symbols for usage into Sniffers
     */
    const BITWISE_AND         = '&';
    const COLON               = ';';
    const OPEN_PARENTHESIS    = '(';
    const CLOSE_PARENTHESIS   = ')';
    const NEW_LINE            = "\n";
    const WHITESPACE          = ' ';
    const DOUBLE_WHITESPACE   = '  ';
    const INDENT_SPACES       = '    ';
    /**#@-*/
}
