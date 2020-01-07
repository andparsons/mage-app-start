<?php

namespace Magento\NegotiableQuote\Model\Status;

/**
 * Class AbstractLabelProvider
 */
abstract class AbstractLabelProvider implements LabelProviderInterface
{
    /**
     * {@inheritdoc}
     */
    abstract public function getStatusLabels();

    /**
     * {@inheritdoc}
     */
    public function getLabelByStatus($status)
    {
        $labels = $this->getStatusLabels();

        return isset($labels[$status]) ? $labels[$status] : '';
    }

    /**
     * {@inheritdoc}
     */
    abstract public function getMessageLabels();

    /**
     * {@inheritdoc}
     */
    abstract public function getRemovedSkuMessageLabels();

    /**
     * {@inheritdoc}
     */
    public function getMessageByCode($code)
    {
        $labels = $this->getMessageLabels();

        return isset($labels[$code]) ? $labels[$code] : '';
    }

    /**
     * {@inheritdoc}
     */
    public function getRemovedSkuMessage(array $sku, $isNegotiable = false, $isLocked = false)
    {
        if (count($sku) == 0) {
            return '';
        }
        $messagesArray = $this->getRemovedSkuMessageLabels();
        $count = 'many';
        if (count($sku) == 1) {
            $count = 'one';
        }
        $locked = 'unlocked';
        if ($isLocked) {
            $locked = 'locked';
        }
        $message = $messagesArray[$locked][$count];
        if (!$isLocked) {
            $negotiable = $isNegotiable ? 'negotiable' : 'nonnegotiable';
            $message = $message[$negotiable];
        }

        if (count($sku) > 1) {
            $message = (string)__('Products %1', implode($sku, ', ') . ' ' . $message);
        } else {
            $message = (string)__('Product %1', implode($sku, ', ') . ' ' . $message);
        }

        return $message;
    }
}
