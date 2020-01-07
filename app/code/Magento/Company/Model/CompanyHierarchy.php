<?php
namespace Magento\Company\Model;

use Magento\Company\Api\Data\StructureInterface;

/**
 * Company hierarchy management class.
 */
class CompanyHierarchy implements \Magento\Company\Api\CompanyHierarchyInterface
{
    /**
     * @var \Magento\Company\Api\Data\HierarchyInterfaceFactory
     */
    private $hierarchyFactory;

    /**
     * @var \Magento\Company\Model\Company\Structure
     */
    private $structure;

    /**
     * @var \Magento\Company\Api\CompanyRepositoryInterface
     */
    private $companyRepository;

    /**
     * @param \Magento\Company\Api\Data\HierarchyInterfaceFactory $hierarchyFactory
     * @param \Magento\Company\Model\Company\Structure $structure
     * @param \Magento\Company\Api\CompanyRepositoryInterface $companyRepository
     */
    public function __construct(
        \Magento\Company\Api\Data\HierarchyInterfaceFactory $hierarchyFactory,
        \Magento\Company\Model\Company\Structure $structure,
        \Magento\Company\Api\CompanyRepositoryInterface $companyRepository
    ) {
        $this->hierarchyFactory = $hierarchyFactory;
        $this->structure = $structure;
        $this->companyRepository = $companyRepository;
    }

    /**
     * @inheritdoc
     */
    public function moveNode($id, $newParentId)
    {
        $this->structure->moveNode($id, $newParentId);
    }

    /**
     * @inheritdoc
     */
    public function getCompanyHierarchy($id)
    {
        $company = $this->companyRepository->get($id);
        $tree = $this->structure->getTreeByCustomerId($company->getSuperUserId());
        if (!$tree) {
            return [];
        }
        $data = $this->getTreeAsFlatObjectArray($tree);
        return $data;
    }

    /**
     * Gets tree as flat array of objects.
     *
     * @param \Magento\Framework\Data\Tree\Node $tree
     * @return array
     */
    private function getTreeAsFlatObjectArray(\Magento\Framework\Data\Tree\Node $tree)
    {
        $data = [];
        if ($tree->hasChildren()) {
            foreach ($tree->getChildren() as $child) {
                $data = array_merge($data, $this->getTreeAsFlatObjectArray($child));
            }
        }
        $data[] = $this->hierarchyFactory->create([
            'data' => [
                'structure_id' => $tree->getData('structure_id'),
                'structure_parent_id' => $tree->getData('parent_id'),
                'entity_id' => $tree->getData('entity_id'),
                'entity_type' => ($tree->getData('entity_type') == StructureInterface::TYPE_CUSTOMER)
                    ? \Magento\Company\Api\Data\HierarchyInterface::TYPE_CUSTOMER
                    : \Magento\Company\Api\Data\HierarchyInterface::TYPE_TEAM
            ]
        ]);
        return $data;
    }
}
