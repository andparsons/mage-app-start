<?php

namespace Magento\Company\Model\Team;

use Magento\Framework\Exception\LocalizedException;

/**
 * Class for deleting a team entity.
 */
class Delete
{
    /**
     * @var \Magento\Company\Model\StructureRepository
     */
    private $structureRepository;

    /**
     * @var \Magento\Company\Model\Company\Structure
     */
    private $structureManager;

    /**
     * @var \Magento\Company\Model\ResourceModel\Team
     */
    protected $teamResource;

    /**
     * @param \Magento\Company\Model\ResourceModel\Team $teamResource
     * @param \Magento\Company\Model\StructureRepository $structureRepository
     * @param \Magento\Company\Model\Company\Structure $structureManager
     */
    public function __construct(
        \Magento\Company\Model\ResourceModel\Team $teamResource,
        \Magento\Company\Model\StructureRepository $structureRepository,
        \Magento\Company\Model\Company\Structure $structureManager
    ) {
        $this->teamResource = $teamResource;
        $this->structureRepository = $structureRepository;
        $this->structureManager = $structureManager;
    }

    /**
     * Deletes a team.
     *
     * @param \Magento\Company\Api\Data\TeamInterface $team
     * @return void
     * @throws LocalizedException
     */
    public function delete(\Magento\Company\Api\Data\TeamInterface $team)
    {
        $structure = $this->structureManager->getStructureByTeamId($team->getId());
        if ($structure) {
            $structureNode = $this->structureManager->getTreeById($structure->getId());
            if ($structureNode && $structureNode->hasChildren()) {
                throw new LocalizedException(
                    __(
                        'This team has child users or teams aligned to it and cannot be deleted.'
                        . ' Please re-align the child users or teams first.'
                    )
                );
            }
            $this->structureRepository->deleteById($structure->getId());
        }
        $this->teamResource->delete($team);
    }
}
