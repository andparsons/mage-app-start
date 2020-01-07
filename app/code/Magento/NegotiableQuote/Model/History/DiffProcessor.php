<?php

namespace Magento\NegotiableQuote\Model\History;

/**
 * Class for processing diff for old and new quote shapshots.
 */
class DiffProcessor
{
    /**
     * @var \Magento\NegotiableQuote\Model\Expiration
     */
    private $expiration;

    /**
     * @var array
     */
    private $oldSnapshot = [];

    /**
     * @var array
     */
    private $currentSnapshot = [];

    /**
     * @var array
     */
    private $diff = [];

    /**
     * @param \Magento\NegotiableQuote\Model\Expiration $expiration
     */
    public function __construct(
        \Magento\NegotiableQuote\Model\Expiration $expiration
    ) {
        $this->expiration = $expiration;
    }

    /**
     * @param array $oldSnapshot
     * @param array $currentSnapshot
     * @return array
     */
    public function processDiff(array $oldSnapshot, array $currentSnapshot)
    {
        $this->setData($oldSnapshot, $currentSnapshot);
        $this->processCommentDiff();
        $this->processCartDiff();
        $this->processPriceDiff();
        $this->processShippingDiff();
        $this->processExpirationDateDiff();
        $this->processSimpleData('status');
        $this->processAddressData();
        if (!empty($this->diff['negotiated_price'])) {
            $this->processSimpleData('subtotal');
        }

        return $this->diff;
    }

    /**
     * Process difference between comments.
     *
     * @return void
     */
    private function processCommentDiff()
    {
        if (!isset($this->currentSnapshot['comments'])) {
            return;
        } else {
            if (isset($this->oldSnapshot['comments']) && is_array($this->oldSnapshot['comments'])) {
                if (count($this->oldSnapshot['comments']) == count($this->currentSnapshot['comments'])) {
                    return;
                }
                if (count($this->currentSnapshot['comments']) > count($this->oldSnapshot['comments'])) {
                    $this->diff['comment'] = array_pop($this->currentSnapshot['comments']);
                }
            } else {
                $this->diff['comment'] = array_pop($this->currentSnapshot['comments']);
            }
        }
    }

    /**
     * Process difference between cart snapshots.
     *
     * @return void
     */
    private function processCartDiff()
    {
        if (isset($this->oldSnapshot['cart']) && isset($this->currentSnapshot['cart'])) {
            if (!empty($this->oldSnapshot['cart']) && is_array($this->oldSnapshot['cart'])) {
                if ($this->oldSnapshot['cart'] == $this->currentSnapshot['cart']) {
                    return;
                }
                $this->processRemovedAndChangedProductsInCart();
                $this->processAddedToCart();
            }
        }
    }

    /**
     * Figure out difference between two cart snapshots with qty and removed items.
     *
     * @return void
     */
    private function processRemovedAndChangedProductsInCart()
    {
        $this->diff['removed_from_cart'] = [];
        foreach ($this->oldSnapshot['cart'] as $cartItemId => $product) {
            $newQty = $this->findProductQty($this->currentSnapshot['cart'], $product);
            $removedIds = [];
            if ($newQty === 0) {
                $this->diff['removed_from_cart'][$cartItemId] = $product;
                $removedIds[] = $product['product_id'];
            }
            if (!in_array($product['product_id'], $removedIds)) {
                if (isset($product['options'])) {
                    $newOptions = $this->findProductOptions($this->currentSnapshot['cart'], $product);
                    if ($newOptions) {
                        $optionsDiff = $this->processOptionsDiff($product['options'], $newOptions);
                        if ($optionsDiff) {
                            $this->diff['updated_in_cart'][$product['sku']]['options_changed'] = $optionsDiff;
                            $this->diff['updated_in_cart'][$product['sku']]['product_id'] = $product['product_id'];
                        }
                    }
                }
                if ($newQty != (int)$product['qty']) {
                    $this->diff['updated_in_cart'][$product['sku']]['qty_changed'] = [
                        'old_value' => $product['qty'],
                        'new_value' => $newQty
                    ];
                    $this->diff['updated_in_cart'][$product['sku']]['product_id'] = $product['product_id'];
                }
            }
        }
        if ($this->diff['removed_from_cart'] === []) {
            unset($this->diff['removed_from_cart']);
        }
    }

    /**
     * Process difference in product options.
     *
     * @param array $oldOptions
     * @param array $newOptions
     * @return array
     */
    private function processOptionsDiff(array $oldOptions, array $newOptions)
    {
        $optionsDiff = [];
        foreach ($oldOptions as $key => $option) {
            if (isset($newOptions[$key]) && ($newOptions[$key]['value'] != $option['value'])) {
                $optionsDiff[$newOptions[$key]['option']] = [
                    'old_value' => $option['value'],
                    'new_value' => $newOptions[$key]['value']
                ];
            }
        }
        return $optionsDiff;
    }

    /**
     * Added to cart.
     *
     * @return void
     */
    private function processAddedToCart()
    {
        foreach ($this->currentSnapshot['cart'] as $product) {
            $newQty = $this->findProductQty($this->oldSnapshot['cart'], $product);
            // If qty of product in old cart is 0 => this product was added to new cart
            if ($newQty === 0) {
                if (isset($product['product_id'])) {
                    $this->diff['added_to_cart'][$product['sku']]['product_id'] = $product['product_id'];
                }
                // if added product has options
                if (isset($product['options'])) {
                    $this->diff['added_to_cart'][$product['sku']]['options'] = $product['options'];
                }
                $this->diff['added_to_cart'][$product['sku']]['qty'] = $product['qty'];
            }
        }
    }

    /**
     * Process negotiated price.
     *
     * @return void
     */
    private function processPriceDiff()
    {
        $priceDiff = [];
        if (isset($this->currentSnapshot['price']) && isset($this->currentSnapshot['price']['type'])) {
            $priceDiff['new_value'] = [
                $this->currentSnapshot['price']['type'] => $this->currentSnapshot['price']['value']
            ];
        }
        if (isset($this->oldSnapshot['price']) && isset($this->oldSnapshot['price']['type'])) {
            $oldPrice = $this->oldSnapshot['price'];
            if (!empty($priceDiff['new_value'])
                && isset($priceDiff['new_value'][$oldPrice['type']])
                && $priceDiff['new_value'][$oldPrice['type']] == $oldPrice['value']
            ) {
                unset($priceDiff['new_value']);
            } else {
                $priceDiff['old_value'] = [
                    $this->oldSnapshot['price']['type'] => $this->oldSnapshot['price']['value']
                ];
            }
        }
        if (!empty($priceDiff)) {
            $this->diff['negotiated_price'] = $priceDiff;
        }
    }

    /**
     * Process negotiated shipping price and type.
     *
     * @return void
     */
    private function processShippingDiff()
    {
        $shippingDiff = [];
        if (isset($this->currentSnapshot['shipping']) && isset($this->currentSnapshot['shipping']['method'])) {
            $shippingDiff['new_value'] = [
                'method' => $this->currentSnapshot['shipping']['method'],
                'price' => $this->currentSnapshot['shipping']['price']
            ];
        }
        if (isset($this->oldSnapshot['shipping']) && isset($this->oldSnapshot['shipping']['method'])) {
            if (!empty($shippingDiff['new_value'])
                && !array_diff_assoc($shippingDiff['new_value'], $this->oldSnapshot['shipping'])
            ) {
                unset($shippingDiff['new_value']);
            } else {
                $shippingDiff['old_value'] = [
                    'method' => $this->oldSnapshot['shipping']['method'],
                    'price' => $this->oldSnapshot['shipping']['price']
                ];
            }
        }
        if (!empty($shippingDiff)) {
            $this->diff['shipping'] = $shippingDiff;
        }
    }

    /**
     * Process address data.
     *
     * @return void
     */
    private function processAddressData()
    {
        if (isset($this->currentSnapshot['address'])) {
            if (isset($this->oldSnapshot['address'])) {
                if ($this->oldSnapshot['address']['id'] !== $this->currentSnapshot['address']['id']) {
                    $this->diff['address']['old_value'] = $this->oldSnapshot['address'];
                    $this->diff['address']['new_value'] = $this->currentSnapshot['address'];
                }
            } else {
                $this->diff['address']['new_value'] = $this->currentSnapshot['address'];
            }
        }
    }

    /**
     * Process diff for expiration date from quote snapshots.
     *
     * @return void
     */
    private function processExpirationDateDiff()
    {
        $this->processSimpleData('expiration_date');
        if (empty($this->oldSnapshot['expiration_date']) && !empty($this->diff['expiration_date']['new_value'])) {
            $dateDefault = $this->expiration->retrieveDefaultExpirationDate()->format('Y-m-d');
            if ($dateDefault == $this->diff['expiration_date']['new_value']) {
                unset($this->diff['expiration_date']);
            }
        }
    }

    /**
     * Process simple data.
     *
     * @param string $dataKey
     * @return void
     */
    private function processSimpleData($dataKey)
    {
        if (isset($this->currentSnapshot[$dataKey])) {
            if (isset($this->oldSnapshot[$dataKey])) {
                if ($this->oldSnapshot[$dataKey] !== $this->currentSnapshot[$dataKey]) {
                    $this->diff[$dataKey]['old_value'] = $this->oldSnapshot[$dataKey];
                    $this->diff[$dataKey]['new_value'] = $this->currentSnapshot[$dataKey];
                }
            } else {
                $this->diff[$dataKey]['new_value'] = $this->currentSnapshot[$dataKey];
            }
        }
    }

    /**
     * Find product qty in array by sku.
     *
     * @param array $cart
     * @param array $product
     * @return int
     */
    private function findProductQty(array $cart, array $product)
    {
        $qty = 0;
        foreach ($cart as $cartProduct) {
            if ($cartProduct['product_id'] == $product['product_id']) {
                if (isset($cartProduct['qty'])) {
                    $qty = $cartProduct['qty'];
                }
            }
        }
        return $qty;
    }

    /**
     * Get product options.
     *
     * @param array $cart
     * @param array $product
     * @return array
     */
    private function findProductOptions(array $cart, array $product)
    {
        $options = [];
        foreach ($cart as $cartProduct) {
            if (isset($product['options'])) {
                if ($cartProduct['product_id'] == $product['product_id']) {
                    if (isset($cartProduct['options'])) {
                        $options = $cartProduct['options'];
                    }
                }
            }
        }
        return $options;
    }

    /**
     * Init snapshots data.
     *
     * @param array $oldSnapshot
     * @param array $currentSnapshot
     * @return void
     */
    private function setData(array $oldSnapshot, array $currentSnapshot)
    {
        $this->oldSnapshot = $oldSnapshot;
        $this->currentSnapshot = $currentSnapshot;
    }
}
