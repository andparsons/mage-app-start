<?php
namespace Magento\NegotiableQuote\Model\Quote\Source;

use Magento\NegotiableQuote\Model\Status\LabelProviderInterface;

/**
 * Class Status
 */
class Status implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @var LabelProviderInterface
     */
    protected $labelProvider;

    /**
     * Status constructor
     *
     * @param LabelProviderInterface $labelProvider
     */
    public function __construct(LabelProviderInterface $labelProvider)
    {
        $this->labelProvider = $labelProvider;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];

        foreach ($this->labelProvider->getStatusLabels() as $status => $label) {
            $options[] = ['label' => $label, 'value' => $status];
        }

        return $options;
    }
}
