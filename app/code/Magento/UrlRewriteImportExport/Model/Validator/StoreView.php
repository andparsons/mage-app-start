<?php
declare(strict_types=1);

namespace Magento\UrlRewriteImportExport\Model\Validator;

use Magento\Framework\Validator\AbstractValidator;
use Magento\UrlRewriteImportExport\Model\Import;
use Magento\UrlRewriteImportExport\Model\StoreViewResolver;

/**
 * The validator for value of store_code column
 */
class StoreView extends AbstractValidator
{
    /**
     * The store view resolver
     *
     * @var StoreViewResolver
     */
    private $storeViewResolver;

    /**
     * @param StoreViewResolver $storeViewResolver The store view resolver
     */
    public function __construct(StoreViewResolver $storeViewResolver)
    {
        $this->storeViewResolver = $storeViewResolver;
    }

    /**
     * Check if a value of store_code column is valid and exists
     *
     * @param mixed $value The value to check
     * @return bool Return true if the value is valid otherwise return false
     */
    public function isValid($value): bool
    {
        $this->_clearMessages();

        if (empty($value[Import::COLUMN_STORE_VIEW_CODE])) {
            $this->_addMessages([__('Column %1 is empty', Import::COLUMN_STORE_VIEW_CODE_TITLE)]);
            return false;
        }

        $code = $value[Import::COLUMN_STORE_VIEW_CODE];
        if (null === $this->storeViewResolver->getIdByCode($code)) {
            $this->_addMessages([__('Store View with %1 code does not exist', $code)]);
            return false;
        }

        return true;
    }
}
