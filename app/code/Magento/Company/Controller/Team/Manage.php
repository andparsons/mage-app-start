<?php
namespace Magento\Company\Controller\Team;

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;

/**
 * Controller for managing teams from the storefront.
 */
class Manage extends \Magento\Company\Controller\AbstractAction implements HttpPostActionInterface
{
    /**
     * Authorization level of a company session.
     */
    const COMPANY_RESOURCE = 'Magento_Company::users_edit';

    /** @var \Magento\Company\Api\TeamRepositoryInterface */
    private $teamRepository;

    /** @var \Magento\Company\Api\Data\TeamInterfaceFactory */
    private $teamFactory;

    /** @var \Magento\Framework\Api\DataObjectHelper */
    private $objectHelper;

    /**
     * @var \Magento\Company\Model\Company\Structure
     */
    private $structureManager;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Company\Model\CompanyContext $companyContext
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Company\Model\Company\Structure $structureManager
     * @param \Magento\Company\Api\TeamRepositoryInterface $teamRepository
     * @param \Magento\Company\Api\Data\TeamInterfaceFactory $teamFactory
     * @param \Magento\Framework\Api\DataObjectHelper $objectHelper
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Company\Model\CompanyContext $companyContext,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Company\Model\Company\Structure $structureManager,
        \Magento\Company\Api\TeamRepositoryInterface $teamRepository,
        \Magento\Company\Api\Data\TeamInterfaceFactory $teamFactory,
        \Magento\Framework\Api\DataObjectHelper $objectHelper,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
    ) {
        parent::__construct($context, $companyContext, $logger);
        $this->structureManager = $structureManager;
        $this->teamRepository = $teamRepository;
        $this->teamFactory = $teamFactory;
        $this->objectHelper = $objectHelper;
        $this->customerRepository = $customerRepository;
    }

    /**
     * Add/Edit team action.
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $request = $this->getRequest();

        $allowedIds = $this->structureManager->getAllowedIds($this->companyContext->getCustomerId());

        $teamId = $request->getParam('team_id');

        if ((int)$teamId) {
            return $this->edit($allowedIds, $teamId);
        } else {
            return $this->create($allowedIds);
        }
    }

    /**
     * Edit team.
     *
     * @param array $allowedIds
     * @param int $teamId
     * @return \Magento\Framework\Controller\Result\Json
     */
    private function edit(array $allowedIds, $teamId)
    {
        $request = $this->getRequest();

        if (!in_array($teamId, $allowedIds['teams'])) {
            return $this->jsonError(__('You are not allowed to do this.'));
        }
        try {
            $team = $this->teamFactory->create();
            $this->objectHelper->populateWithArray(
                $team,
                $request->getParams(),
                \Magento\Company\Api\Data\TeamInterface::class
            );
            $team->setId($teamId);
            $this->teamRepository->save($team);
            $team = $this->teamRepository->get($teamId);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            return $this->jsonError($e->getMessage());
        } catch (\Exception $e) {
            $this->logger->critical($e);
            return $this->jsonError(__('Something went wrong.'));
        }
        $message = __('The team was successfully updated.');
        return $this->jsonSuccess($team->getData(), $message);
    }

    /**
     * Create team.
     *
     * @param array $allowedIds
     * @return \Magento\Framework\Controller\Result\Json
     */
    private function create(array $allowedIds)
    {
        $request = $this->getRequest();
        $targetId = $request->getParam('target_id');
        if ($targetId && !in_array($targetId, $allowedIds['structures'])) {
            return $this->jsonError(__('You are not allowed to do this.'));
        }
        try {
            $team = $this->teamFactory->create();
            $this->objectHelper->populateWithArray(
                $team,
                $request->getParams(),
                \Magento\Company\Api\Data\TeamInterface::class
            );
            $customer = $this->customerRepository->getById($this->companyContext->getCustomerId());
            $companyId = $customer->getExtensionAttributes()->getCompanyAttributes()->getCompanyId();
            $this->teamRepository->create($team, $companyId);
            if ($targetId) {
                $teamStructure = $this->structureManager->getStructureByTeamId($team->getId());
                $this->structureManager->moveNode($teamStructure->getId(), $targetId);
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            return $this->jsonError($e->getMessage());
        } catch (\Exception $e) {
            $this->logger->critical($e);
            return $this->jsonError(__('Something went wrong.'));
        }
        $message = __('The team was successfully created.');
        return $this->jsonSuccess($team->getData(), $message);
    }
}
