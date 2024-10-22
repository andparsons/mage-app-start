<?php
declare(strict_types=1);
namespace Magento\Payment\Ui\Component\Listing\Column\Method;

/**
 * Class Options
 */
class Options implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @var array
     */
    protected $options;

    /**
     * @var \Magento\Payment\Helper\Data
     */
    protected $paymentHelper;

    /**
     * Constructor
     *
     * @param \Magento\Payment\Helper\Data $paymentHelper
     */
    public function __construct(
        \Magento\Payment\Helper\Data $paymentHelper
    ) {
        $this->paymentHelper = $paymentHelper;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        if ($this->options === null) {
            $this->options = $this->paymentHelper->getPaymentMethodList(true, true);
        }

        array_walk(
            $this->options,
            function (&$item) {
                $item['__disableTmpl'] = true;
            }
        );

        return $this->options;
    }
}
