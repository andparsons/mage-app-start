<?php

namespace Magento\Company\Model;

/**
 * Company save validator interface.
 */
interface SaveValidatorInterface
{
    /**
     * Execute save validator.
     *
     * @return void
     */
    public function execute();
}
