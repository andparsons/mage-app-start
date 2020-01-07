<?php

namespace Magento\CompanyCredit\Block\Adminhtml\Company\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Magento\Company\Block\Adminhtml\Edit\GenericButton;

/**
 * Class ReimburseButton.
 */
class ReimburseButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * Get reimburse button data.
     *
     * @return array
     */
    public function getButtonData()
    {
        if (!$this->getRequest()->getParam('id')) {
            return [];
        }
        $targetName  = 'company_form.company_form.modalContainer.company_credit_form_modal';
        $amountField = $targetName . '.reimburse_balance.amount';
        $data = [
            'label' => __('Reimburse Balance'),
            'class' => 'reimburse action-secondary',
            'id' => 'company-edit-reimburse-button',
            'data_attribute' => [
                'mage-init' => [
                    'Magento_Ui/js/form/button-adapter' => [
                        'actions' => [
                            [
                                'targetName' => $targetName,
                                'actionName' => 'openModal',
                                'params' => [
                                    [
                                        'url' => $this->getReimburseUrl(),
                                        'title' => __('Reimburse Balance'),
                                    ],
                                ],
                            ],
                            [
                                'targetName' => $amountField,
                                'actionName' => 'enable'
                            ]
                        ],
                    ],
                ]
            ],
            'on_click' => '',
            'sort_order' => 85,
        ];
        return $data;
    }

    /**
     * Get url fot reimburse.
     *
     * @return string
     */
    public function getReimburseUrl()
    {
        $companyId = $this->getRequest()->getParam('id');
        return $this->getUrl('credit/index/reimburse', ['id' => $companyId]);
    }
}
