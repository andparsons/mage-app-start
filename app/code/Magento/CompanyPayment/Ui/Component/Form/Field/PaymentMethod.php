<?php

namespace Magento\CompanyPayment\Ui\Component\Form\Field;

/**
 * Class PaymentMethod.
 */
class PaymentMethod extends \Magento\Ui\Component\Form\Field
{
    /**
     * Constructor.
     *
     * @param \Magento\Framework\View\Element\UiComponent\ContextInterface $context
     * @param \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory
     * @param \Magento\CompanyPayment\Model\Config $config
     * @param array|\Magento\Framework\View\Element\UiComponentInterface[] $components
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory,
        \Magento\CompanyPayment\Model\Config $config,
        array $components,
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function prepare()
    {
        $this->setData(
            'config',
            array_replace_recursive(
                (array) $this->getData('config'),
                [
                    'b2bPaymentMethods' => $this->config->getAvailablePaymentMethods(),
                ]
            )
        );

        parent::prepare();
    }
}
