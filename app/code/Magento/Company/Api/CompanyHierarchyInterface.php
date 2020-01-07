<?php

namespace Magento\Company\Api;

/**
 * Interface for working with company hierarchy.
 *
 * @api
 * @since 100.0.0
 */
interface CompanyHierarchyInterface
{
    /**
     * Returns the list of teams and company users in the company structure.
     *
     * @param int $id
     * @return \Magento\Company\Api\Data\HierarchyInterface[]
     */
    public function getCompanyHierarchy($id);

    /**
     * Moves teams and users within the company structure.
     *
     * @param int $id
     * @param int $newParentId
     * @return void
     */
    public function moveNode($id, $newParentId);
}
