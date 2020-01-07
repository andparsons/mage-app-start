<?php

namespace Magento\Company\Model\Team;

use Magento\Company\Api\Data\StructureInterface;

/**
 * Class for creating a team entity.
 */
class Create
{
    /**
     * @var \Magento\Company\Model\Company\Structure
     */
    private $structureManager;

    /**
     * @var \Magento\Company\Model\ResourceModel\Team
     */
    private $teamResource;

    /**
     * @var \Magento\Company\Api\CompanyRepositoryInterface
     */
    private $companyRepository;

    /**
     * @param \Magento\Company\Model\ResourceModel\Team $teamResource
     * @param \Magento\Company\Model\Company\Structure $structureManager
     * @param \Magento\Company\Api\CompanyRepositoryInterface $companyRepository
     */
    public function __construct(
        \Magento\Company\Model\ResourceModel\Team $teamResource,
        \Magento\Company\Model\Company\Structure $structureManager,
        \Magento\Company\Api\CompanyRepositoryInterface $companyRepository
    ) {
        $this->teamResource = $teamResource;
        $this->structureManager = $structureManager;
        $this->companyRepository = $companyRepository;
    }

    /**
     * Creates a team for a company which id is specified. Validates that the team is new and was not saved before.
     *
     * @param \Magento\Company\Api\Data\TeamInterface $team
     * @param int $companyId
     * @return void
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function create(\Magento\Company\Api\Data\TeamInterface $team, $companyId)
    {
        if ($team->getId()) {
            throw new \Magento\Framework\Exception\CouldNotSaveException(__('Could not create team'));
        }
        $company = $this->companyRepository->get($companyId);
        $companyTree = $this->structureManager->getTreeByCustomerId($company->getSuperUserId());
        $this->teamResource->save($team);
        $this->structureManager->addNode(
            $team->getId(),
            StructureInterface::TYPE_TEAM,
            $companyTree->getId()
        );
    }
}
