<?php

namespace Magento\Company\Model\Company;

use Magento\Company\Api\Data\CompanyCustomerInterface;
use Magento\Company\Api\Data\StructureInterface;
use Magento\Company\Api\Data\TeamInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * A model for a company tree structure entity. Used for work with company inner hierarchy.
 */
class Structure
{
    /**
     * @var \Magento\Company\Model\ResourceModel\Structure\Tree
     */
    private $tree;

    /**
     * @var \Magento\Company\Api\Data\StructureInterfaceFactory
     */
    private $structureFactory;

    /**
     * @var \Magento\Company\Model\StructureRepository
     */
    private $structureRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var \Magento\Company\Api\TeamRepositoryInterface
     */
    private $teamRepository;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    private $customerRepositoryInterface;

    /** @var \Magento\Framework\Data\Tree\Node[] */
    private $treesByIds = [];

    /**
     * @param \Magento\Company\Model\ResourceModel\Structure\Tree $tree
     * @param \Magento\Company\Api\Data\StructureInterfaceFactory $structureFactory
     * @param \Magento\Company\Model\StructureRepository $structureRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Company\Api\TeamRepositoryInterface $teamRepository
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface
     */
    public function __construct(
        \Magento\Company\Model\ResourceModel\Structure\Tree $tree,
        \Magento\Company\Api\Data\StructureInterfaceFactory $structureFactory,
        \Magento\Company\Model\StructureRepository $structureRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Company\Api\TeamRepositoryInterface $teamRepository,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface
    ) {
        $this->tree = $tree;
        $this->structureFactory = $structureFactory;
        $this->structureRepository = $structureRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->teamRepository = $teamRepository;
        $this->customerRepositoryInterface = $customerRepositoryInterface;
    }

    /**
     * Adds company member and team data to tree. Used for displaying company hierarchy tree.
     *
     * @param \Magento\Framework\Data\Tree\Node $tree
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function addDataToTree(\Magento\Framework\Data\Tree\Node $tree)
    {
        $customerIds = [];
        $teamIds = [];
        $this->treeWalk(
            $tree,
            function (\Magento\Framework\Data\Tree\Node $item) use (&$customerIds, &$teamIds) {
                if ($item->getData(StructureInterface::ENTITY_TYPE) == StructureInterface::TYPE_CUSTOMER) {
                    $customerIds[] = $item->getData(StructureInterface::ENTITY_ID);
                } elseif ($item->getData(StructureInterface::ENTITY_TYPE) == StructureInterface::TYPE_TEAM) {
                    $teamIds[] = $item->getData(StructureInterface::ENTITY_ID);
                }
            }
        );

        $builder = $this->searchCriteriaBuilder;

        $builder->addFilter('entity_id', $customerIds, 'in');
        $customers = $this->customerRepositoryInterface->getList($builder->create())->getItems();

        $builder->addFilter(TeamInterface::TEAM_ID, $teamIds, 'in');
        $teams = $this->teamRepository->getList($builder->create())->getItems();

        $this->treeWalk(
            $tree,
            function (\Magento\Framework\Data\Tree\Node $item) use ($customers, $teams) {
                if ($item->getData(StructureInterface::ENTITY_TYPE) == StructureInterface::TYPE_CUSTOMER) {
                    foreach ($customers as $key => $customer) {
                        /** @var CompanyCustomerInterface $companyAttributes */
                        $companyAttributes = $customer->getExtensionAttributes()->getCompanyAttributes();
                        if ($customer->getId() == $item->getData(StructureInterface::ENTITY_ID)) {
                            $item->addData($customer->__toArray());
                            $isActive = $companyAttributes->getStatus() == CompanyCustomerInterface::STATUS_ACTIVE;
                            $item->setIsActive($isActive);
                            unset($customers[$key]);
                            break;
                        }
                    }
                } elseif ($item->getData(StructureInterface::ENTITY_TYPE) == StructureInterface::TYPE_TEAM) {
                    foreach ($teams as $key => $team) {
                        if ($team->getId() == $item->getData(StructureInterface::ENTITY_ID)) {
                            $item->addData($team->getData());
                            $item->setIsActive(true);
                            unset($teams[$key]);
                            break;
                        }
                    }
                }
            }
        );
    }

    /**
     * Removes nodes from tree if node field value equals given ones.
     *
     * @param \Magento\Framework\Data\Tree\Node $tree
     * @param string $field
     * @param mixed $value
     * @return void
     */
    public function filterTree(\Magento\Framework\Data\Tree\Node $tree, $field, $value)
    {
        $this->treeWalk(
            $tree,
            function (\Magento\Framework\Data\Tree\Node $node) use ($tree, $field, $value) {
                if ($node->getData($field) !== $value) {
                    $node->getParent()->removeChild($node);
                }
            }
        );
    }

    /**
     * Walks the tree.
     *
     * @param \Magento\Framework\Data\Tree\Node $tree
     * @param callable $callback
     * @return mixed
     */
    private function treeWalk(
        \Magento\Framework\Data\Tree\Node $tree,
        callable $callback
    ) {
        if ($tree->hasChildren()) {
            /** @var \Magento\Framework\Data\Tree\Node $child */
            foreach ($tree->getChildren() as $child) {
                $this->treeWalk($child, $callback);
            }
        }
        return call_user_func($callback, $tree);
    }

    /**
     * Retrieves tree by customer id.
     *
     * @param int $id
     * @return \Magento\Framework\Data\Tree\Node|null
     */
    public function getTreeByCustomerId($id)
    {
        $customerStructure = $this->getStructureByCustomerId($id);
        if (!$customerStructure) {
            return null;
        }

        $rootItemId = $this->getFirstItemFromPath($customerStructure->getData(StructureInterface::PATH));
        return $this->getTreeById($rootItemId);
    }

    /**
     * Gets allowed ids of structures, teams and users.
     *
     * @param int $userId
     * @return array
     */
    public function getAllowedIds($userId)
    {
        $tree = $this->getTreeByCustomerId($userId);
        $allowed = [
            'structures' => [],
            'users' => [],
            'teams' => []
        ];

        if ($tree) {
            $this->treeWalk(
                $tree,
                function ($item) use (&$allowed) {
                    $allowed['structures'][] = $item->getData(StructureInterface::STRUCTURE_ID);
                    $entityType = ($item->getData(StructureInterface::ENTITY_TYPE) == StructureInterface::TYPE_TEAM) ?
                        'teams' :
                        'users';
                    $allowed[$entityType][] = $item->getData(StructureInterface::ENTITY_ID);
                }
            );
        }

        return $allowed;
    }

    /**
     * Moves teams and users within the company structure.
     *
     * @param int $id
     * @param int $newParentId
     * @return void
     * @throws LocalizedException
     */
    public function moveNode($id, $newParentId)
    {
        $this->executeMoveNode($id, $newParentId, false);
    }

    /**
     * Execute moving of teams and users within the company structure.
     *
     * @param int $id
     * @param int $newParentId
     * @param bool $changeSuperUser
     * @return void
     * @throws LocalizedException
     */
    private function executeMoveNode($id, $newParentId, $changeSuperUser)
    {
        $node = $this->getTreeById($id);
        $newParent = $this->getTreeById($newParentId);
        $this->checkIfNodeMoveIsPossible($node, $newParent, $changeSuperUser);
        $this->tree->move($node, $newParent);
    }

    /**
     * Checks if moving a node is possible.
     *
     * @param \Magento\Framework\Data\Tree\Node $node
     * @param \Magento\Framework\Data\Tree\Node $newParent
     * @param bool $changeSuperUser
     * @return void
     * @throws LocalizedException
     */
    private function checkIfNodeMoveIsPossible(
        \Magento\Framework\Data\Tree\Node $node,
        \Magento\Framework\Data\Tree\Node $newParent,
        $changeSuperUser
    ) {
        if ($changeSuperUser === false && !$node->getData(StructureInterface::PARENT_ID)) {
            throw new LocalizedException(__(
                'The company admin cannot be moved to a different location in the company structure.'
            ));
        }
        if ($node->getId() == $newParent->getId()) {
            throw new LocalizedException(__(
                'A user or a team cannot be moved under itself.'
            ));
        }
        $this->treeWalk($node, function (\Magento\Framework\Data\Tree\Node $childNode) use ($newParent) {
            if ($newParent->getId() == $childNode->getId()) {
                throw new LocalizedException(__(
                    'A user or a team cannot be moved under its child user or team.'
                ));
            }
        });

        if (!$changeSuperUser) {
            $rootItemId = $this->getFirstItemFromPath($node->getData(StructureInterface::PATH));
            $tree = $this->getTreeById($rootItemId);
            $isCompanyNode = false;
            $this->treeWalk(
                $tree,
                function (\Magento\Framework\Data\Tree\Node $childNode) use ($newParent, &$isCompanyNode) {
                    if ($newParent->getId() == $childNode->getId()) {
                        $isCompanyNode = true;
                    }
                }
            );
            if (!$isCompanyNode) {
                throw new LocalizedException(__(
                    'The specified parent ID belongs to a different company.'
                    . ' The specified entity (team or user) and its new parent must belong to the same company.'
                ));
            }
        }
    }

    /**
     * Retrieves tree by id.
     *
     * @param int $id
     * @return \Magento\Framework\Data\Tree\Node
     */
    public function getTreeById($id)
    {
        if (!isset($this->treesByIds[$id])) {
            $node = $this->tree->getNodeById($id);
            if (!$node) {
                $this->tree->setLoaded(false);
                $structure = $this->structureRepository->get($id);
                $rootId = $this->getFirstItemFromPath($structure->getPath());
                $tree = $this->tree->loadNode($rootId);
                $tree->loadChildren();
                $node = $this->tree->getNodeById($id);
            }
            $this->treesByIds[$id] = $node;
        }
        return $this->treesByIds[$id];
    }

    /**
     * Retrieves structure by customer id.
     *
     * @param int $id
     * @return StructureInterface|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getStructureByCustomerId($id)
    {
        $builder = $this->searchCriteriaBuilder;
        $builder->addFilter(StructureInterface::ENTITY_TYPE, StructureInterface::TYPE_CUSTOMER);
        $builder->addFilter(StructureInterface::ENTITY_ID, $id);
        $results = $this->structureRepository->getList($builder->create());
        if ($results->getTotalCount()) {
            $items = $results->getItems();
            return array_shift($items);
        } else {
            return null;
        }
    }

    /**
     * Retrieves structure by team id.
     *
     * @param int $id
     * @return StructureInterface|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getStructureByTeamId($id)
    {
        $builder = $this->searchCriteriaBuilder;
        $builder->addFilter(StructureInterface::ENTITY_TYPE, StructureInterface::TYPE_TEAM);
        $builder->addFilter(StructureInterface::ENTITY_ID, $id);
        $results = $this->structureRepository->getList($builder->create());
        if ($results->getTotalCount()) {
            $items = $results->getItems();
            return array_shift($items);
        } else {
            return null;
        }
    }

    /**
     * Gets first item from path.
     *
     * @param string $path
     * @return int
     */
    private function getFirstItemFromPath($path)
    {
        $pathArray = explode('/', $path);
        return $pathArray[0];
    }

    /**
     * Creates a new node and places it under parent with a given id.
     *
     * @param int $entityId
     * @param int $entityType
     * @param int $parentId
     * @return void
     */
    public function addNode($entityId, $entityType, $parentId)
    {
        $structure = $this->structureFactory->create();
        $structure->setEntityId($entityId);
        $structure->setEntityType($entityType);
        $structure->setParentId($parentId);
        $this->structureRepository->save($structure);
        if ($parentId) {
            $parent = $this->structureRepository->get($parentId);
            $path = $parent->getPath();
            $pathArray = explode('/', $path);
            $structure->setLevel(count($pathArray));
            $structure->setPath($path . '/' . $structure->getId());
            $this->structureRepository->save($structure);
        } else {
            $structure->setLevel(0);
            $structure->setPath($structure->getId());
            $this->structureRepository->save($structure);
        }
    }

    /**
     * Removes customer node. Used when a customer or a whole company is deleted.
     *
     * @param int $customerId
     * @return void
     */
    public function removeCustomerNode($customerId)
    {
        try {
            $structure = $this->getStructureByCustomerId($customerId);
            if ($structure) {
                $this->structureRepository->delete($structure);
            }
        } catch (LocalizedException $e) {
            //Do nothing as node might be already deleted.
        }
    }

    /**
     * Moves structure children to parent.
     *
     * @param int $customerId
     * @return $this
     */
    public function moveStructureChildrenToParent($customerId)
    {
        $structure = $this->getStructureByCustomerId($customerId);
        if ($structure) {
            $customerNode = $this->getTreeById($structure->getId());
            /** @var \Magento\Framework\Data\Tree\Node $childNode */
            foreach ($customerNode->getChildren() as $childNode) {
                $this->moveNode($childNode->getId(), $structure->getParentId());
            }
        }
        return $this;
    }

    /**
     * Moves customer structure.
     *
     * @param int $sourceCustomerId
     * @param int $targetCustomerId
     * @param bool $keepOld
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function moveCustomerStructure($sourceCustomerId, $targetCustomerId, $keepOld)
    {
        $sourceStructure = $this->getStructureByCustomerId($sourceCustomerId);
        $targetStructure = $this->getStructureByCustomerId($targetCustomerId);
        if ($sourceStructure && $targetStructure) {
            if (!$keepOld) {
                $builder = $this->searchCriteriaBuilder;
                $builder->addFilter(StructureInterface::PATH, $sourceStructure->getId() . '/%', 'like');
                $results = $this->structureRepository->getList($builder->create());
                foreach ($results->getItems() as $result) {
                    if ($result->getParentId() == $sourceStructure->getId()) {
                        $result->setParentId($targetStructure->getId());
                    }
                    $path = $result->getPath();
                    $path = preg_replace(
                        '/^' . $sourceStructure->getId() . '\//',
                        $targetStructure->getId() . '/',
                        $path
                    );
                    $result->setPath($path);
                    try {
                        $this->structureRepository->save($result);
                    } catch (LocalizedException $e) {
                        throw new LocalizedException(__(
                            'Unable to move customer structure.'
                        ));
                    }
                }
            }

            $sourceChildren = $this->getAdminUserChildren($sourceCustomerId);
            $this->executeMoveNode($sourceStructure->getId(), $targetStructure->getId(), true);
            $sourceNode = $this->getTreeById($sourceStructure->getId());
            foreach ($sourceChildren as $sourceChild) {
                $this->updateSourceChildren($sourceChild, $sourceNode->getId());
            }
        }
    }

    /**
     * Update source structure children.
     *
     * @param \Magento\Framework\Data\Tree\Node $child
     * @param int $parentId
     * @return void
     */
    private function updateSourceChildren(\Magento\Framework\Data\Tree\Node $child, $parentId)
    {
        $this->moveNode($child->getId(), $parentId);

        foreach ($child->getChildren() as $childNode) {
            $this->updateSourceChildren($childNode, $child->getId());
        }
    }

    /**
     * Get admin user children.
     *
     * @param int $sourceCustomerId
     * @return \Magento\Framework\Data\Tree\Node[]
     */
    private function getAdminUserChildren($sourceCustomerId)
    {
        $children = [];
        $node = $this->getTreeByCustomerId($sourceCustomerId);
        /** @var \Magento\Framework\Data\Tree\Node $childNode */
        foreach ($node->getChildren() as $childNode) {
            $children[] = $childNode;
        }

        return $children;
    }

    /**
     * Gets allowed children IDs of customer.
     *
     * @param int $parentId
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getAllowedChildrenIds($parentId)
    {
        $allChildrenIds = [];
        $searchCriteria = $this->searchCriteriaBuilder->addFilter('entity_id', $parentId, 'eq')
            ->addFilter('entity_type', StructureInterface::TYPE_CUSTOMER, 'eq')
            ->create();
        $items = $this->structureRepository->getList($searchCriteria)->getItems();

        if (!empty($items)) {
            $structure = array_shift($items);
            $searchCriteria = $this->searchCriteriaBuilder->addFilter('path', $structure->getPath() . '/%', 'like')
                ->addFilter('entity_type', StructureInterface::TYPE_CUSTOMER, 'eq')
                ->create();
            $subUsers = $this->structureRepository->getList($searchCriteria)->getItems();
            foreach ($subUsers as $child) {
                $allChildrenIds[] = $child->getEntityId();
            }
        }
        return $allChildrenIds;
    }

    /**
     * Gets list of teams under specified user.
     *
     * @param int $userId
     * @return array
     */
    public function getUserChildTeams($userId)
    {
        $structure = $this->getStructureByCustomerId($userId);
        $searchCriteria = $this->searchCriteriaBuilder->addFilter('path', $structure->getPath() . '/%', 'like')
            ->addFilter('entity_type', StructureInterface::TYPE_TEAM, 'eq')
            ->create();
        return $this->structureRepository->getList($searchCriteria)->getItems();
    }

    /**
     * Get team name by customer ID.
     *
     * @param int $customerId
     * @return null|string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getTeamNameByCustomerId($customerId)
    {
        $structure = $this->getStructureByCustomerId($customerId);
        $teamName = '';

        if ($structure) {
            $targetStructure = $this->getTargetStructure($structure);

            if ($targetStructure) {
                $teamId = $targetStructure->getEntityId();
                $team = $this->teamRepository->get($teamId);
                $teamName = $team->getName();
            }
        }

        return $teamName;
    }

    /**
     * Get target structure.
     *
     * @param \Magento\Company\Api\Data\StructureInterface $structure
     * @return \Magento\Company\Api\Data\StructureInterface|null
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getTargetStructure(\Magento\Company\Api\Data\StructureInterface $structure)
    {
        if ($structure) {
            $entityType = $structure->getEntityType();
            if ($entityType == \Magento\Company\Api\Data\StructureInterface::TYPE_TEAM) {
                return $structure;
            } elseif ($entityType == \Magento\Company\Api\Data\StructureInterface::TYPE_CUSTOMER &&
                $parentId = $structure->getParentId()) {
                return $this->getTargetStructure($this->structureRepository->get($parentId));
            }
        }

        return null;
    }
}
