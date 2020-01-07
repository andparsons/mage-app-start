<?php
namespace Magento\SharedCatalog\Api;

/**
 * Shared catalog products actions.
 * @api
 * @since 100.0.0
 */
interface CategoryManagementInterface
{
    /**
     * Return the list of categories in the selected shared catalog.
     *
     * @param int $id
     * @return int[]
     */
    public function getCategories($id);

    /**
     * Add categories into the shared catalog.
     *
     * @param int $id
     * @param \Magento\Catalog\Api\Data\CategoryInterface[] $categories
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function assignCategories($id, array $categories);

    /**
     * Remove the specified categories from the shared catalog.
     *
     * @param int $id
     * @param \Magento\Catalog\Api\Data\CategoryInterface[] $categories
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function unassignCategories($id, array $categories);
}
