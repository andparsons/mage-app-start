<?php
namespace Magento\Rma\Model\Spi;

/**
 * Interface TrackResourceInterface
 */
interface TrackResourceInterface
{
    /**
     * Save object data
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    public function save(\Magento\Framework\Model\AbstractModel $object);

    /**
     * Load an object
     *
     * @param mixed $value
     * @param \Magento\Framework\Model\AbstractModel $object
     * @param string|null $field field to load by (defaults to model id)
     * @return mixed
     */
    public function load(\Magento\Framework\Model\AbstractModel $object, $value, $field = null);

    /**
     * Delete the object
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return mixed
     */
    public function delete(\Magento\Framework\Model\AbstractModel $object);
}
