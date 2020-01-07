<?php

namespace Magento\CurrencySymbol\Test\Block\Adminhtml\System\Currency\Rate;

use Magento\Backend\Test\Block\FormPageActions as ParentFormPageActions;

/**
 * Form page actions on the SystemCurrencyIndex page.
 */
class FormPageActions extends ParentFormPageActions
{
    /**
     * "Save Currency Rates" button locator.
     *
     * @var string
     */
    protected $saveButton = '.save';
}
