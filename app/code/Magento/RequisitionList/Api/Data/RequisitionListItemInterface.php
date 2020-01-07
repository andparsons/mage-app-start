<?php

namespace Magento\RequisitionList\Api\Data;

/**
 * Interface RequisitionListItemInterface
 *
 * @api
 * @since 100.0.0
 */
interface RequisitionListItemInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**
     * Constants for keys of data array
     */
    const REQUISITION_LIST_ITEM_ID = 'item_id';
    const SKU = 'sku';
    const REQUISITION_LIST_ID = 'requisition_list_id';
    const QTY = 'qty';
    const OPTIONS = 'options';
    const STORE_ID = 'store_id';
    const ADDED_AT = 'added_at';
    const NO_PRODUCT = 'no_product';
    /**#@-*/

    /**
     * Set Requisition List ID.
     *
     * @param int $requisitionListItemId
     * @return $this
     */
    public function setId($requisitionListItemId);

    /**
     * Get Requisition List ID.
     *
     * @return int
     */
    public function getId();

    /**
     * Set Product SKU.
     *
     * @param string $sku
     * @return $this
     */
    public function setSku($sku);

    /**
     * Get Product SKU.
     *
     * @return int
     */
    public function getSku();

    /**
     * Set Requisition List ID.
     *
     * @param int $requisitionListId
     * @return $this
     */
    public function setRequisitionListId($requisitionListId);

    /**
     * Get Requisition List ID.
     *
     * @return int
     */
    public function getRequisitionListId();

    /**
     * Set Product Qty.
     *
     * @param float $qty
     * @return $this
     */
    public function setQty($qty);

    /**
     * Get Product Qty.
     *
     * @return float
     */
    public function getQty();

    /**
     * Set requisition list item options.
     *
     * @param array $options
     * @return $this
     */
    public function setOptions(array $options);

    /**
     * Get requisition list item options.
     *
     * @return mixed[]
     */
    public function getOptions();

    /**
     * Set store ID.
     *
     * @param int $storeId
     * @return $this
     */
    public function setStoreId($storeId);

    /**
     * Get store ID.
     *
     * @return int
     */
    public function getStoreId();

    /**
     * Set added_at value.
     *
     * @param string $date
     * @return $this
     */
    public function setAddedAt($date);

    /**
     * Get added_at value.
     *
     * @return string
     */
    public function getAddedAt();

    /**
     * Retrieve existing object extension attributes.
     *
     * @return \Magento\RequisitionList\Api\Data\RequisitionListItemExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set extension attributes for the object.
     *
     * @param \Magento\RequisitionList\Api\Data\RequisitionListItemExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magento\RequisitionList\Api\Data\RequisitionListItemExtensionInterface $extensionAttributes
    );
}
