<?php
namespace Magento\SharedCatalog\Block\Adminhtml\SharedCatalog\Wizard\Step;

/**
 * Shared catalog Pricing step
 *
 * @api
 * @since 100.0.0
 */
class Pricing extends \Magento\Ui\Block\Component\StepsWizard\StepAbstract
{
    /**
     * {@inheritdoc}
     */
    public function getCaption()
    {
        return __('Pricing');
    }
}
