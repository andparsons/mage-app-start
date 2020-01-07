<?php

namespace Magento\TestFramework\CodingStandard\Tool\CodeSniffer;

/**
 * Add HTML files extension to config.
 */
class HtmlWrapper extends Wrapper
{
    const FILE_EXTENSION = 'html';

    private const TOKENIZER = 'PHP';

    /**
     * @inheritDoc
     */
    public function init()
    {
        parent::init();

        $this->config->extensions += [self::FILE_EXTENSION => self::TOKENIZER];
    }
}
