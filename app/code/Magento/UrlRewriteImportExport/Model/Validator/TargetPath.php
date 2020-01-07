<?php
declare(strict_types=1);

namespace Magento\UrlRewriteImportExport\Model\Validator;

use Magento\Framework\Validator\AbstractValidator;
use Magento\UrlRewriteImportExport\Model\Import;

/**
 * The validator for value of target_path column
 */
class TargetPath extends AbstractValidator
{
    /**
     * Check if a value of target_path column is valid
     *
     * @param mixed $value The value to check
     * @return bool Return true if the value is valid otherwise return false
     */
    public function isValid($value): bool
    {
        $this->_clearMessages();

        if (empty($value[Import::COLUMN_TARGET_PATH])) {
            $this->_addMessages([__('Column %1 is empty', Import::COLUMN_TARGET_PATH_TITLE)]);
            return false;
        }

        return true;
    }
}
