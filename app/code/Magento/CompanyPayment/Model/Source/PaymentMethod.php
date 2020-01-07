<?php

namespace Magento\CompanyPayment\Model\Source;

use Magento\Store\Model\StoreManagerInterface;

/**
 * Class PaymentMethod.
 */
class PaymentMethod implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * Payment method list.
     *
     * @var \Magento\Payment\Api\PaymentMethodListInterface
     */
    private $paymentMethodList;

    /**
     * @var \Magento\Framework\App\State
     */
    private $appState;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * Constructor.
     *
     * @param \Magento\Payment\Api\PaymentMethodListInterface $paymentMethodList
     * @param \Magento\Store\Api\StoreResolverInterface $storeResolver
     * @param \Magento\Framework\App\State $appState
     * @param StoreManagerInterface|null $storeManager
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        \Magento\Payment\Api\PaymentMethodListInterface $paymentMethodList,
        \Magento\Store\Api\StoreResolverInterface $storeResolver,
        \Magento\Framework\App\State $appState,
        StoreManagerInterface $storeManager = null
    ) {
        $this->paymentMethodList = $paymentMethodList;
        $this->appState = $appState;
        $this->storeManager = $storeManager ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(StoreManagerInterface::class);
    }

    /**
     * @inheritdoc
     */
    public function toOptionArray()
    {
        $options = [];
        $storeId = 0;

        if ($this->appState->getAreaCode() === \Magento\Framework\App\Area::AREA_FRONTEND) {
            $storeId = $this->storeManager->getStore()->getId();
        }

        $paymentMethodList = $this->paymentMethodList->getList($storeId);
        usort(
            $paymentMethodList,
            function ($comparedObject, $nextObject) {
                return strcmp($comparedObject->getTitle(), $nextObject->getTitle());
            }
        );
        $paymentMethodNames = array_map(
            function ($paymentMethod) {
                return $paymentMethod->getTitle();
            },
            $paymentMethodList
        );
        $duplicatedMethodNames = array_unique(array_diff_assoc($paymentMethodNames, array_unique($paymentMethodNames)));

        foreach ($paymentMethodList as $method) {
            if ($method->getCode() && $method->getTitle()) {
                $label = $method->getTitle();

                if (in_array($method->getTitle(), $duplicatedMethodNames)) {
                    $label .= ' ' . $method->getCode();
                }

                if (!$method->getIsActive()) {
                    $label .= __(' (disabled)');
                }

                $options[] = ['value' => $method->getCode(), 'label' => $label];
            }
        }

        return $options;
    }
}
