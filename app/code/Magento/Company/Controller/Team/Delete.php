<?php
namespace Magento\Company\Controller\Team;

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;

/**
 * Controller for deleting a team.
 */
class Delete extends \Magento\Company\Controller\AbstractAction implements HttpPostActionInterface
{
    /**
     * Authorization level of a company session.
     */
    const COMPANY_RESOURCE = 'Magento_Company::users_edit';

    /**
     * @var \Magento\Company\Api\TeamRepositoryInterface
     */
    private $teamRepository;

    /**
     * @var \Magento\Company\Model\Company\Structure
     */
    private $structureManager;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Company\Model\CompanyContext $companyContext
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Company\Api\TeamRepositoryInterface $teamRepository
     * @param \Magento\Company\Model\Company\Structure $structureManager
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Company\Model\CompanyContext $companyContext,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Company\Api\TeamRepositoryInterface $teamRepository,
        \Magento\Company\Model\Company\Structure $structureManager
    ) {
        parent::__construct($context, $companyContext, $logger);
        $this->teamRepository = $teamRepository;
        $this->structureManager = $structureManager;
    }

    /**
     * Delete team action.
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $allowedIds = $this->structureManager->getAllowedIds($this->companyContext->getCustomerId());
        $teamId = $this->getRequest()->getParam('team_id');
        if (!in_array($teamId, $allowedIds['teams'])) {
            return $this->jsonError(__('You are not allowed to do this.'));
        }

        try {
            $this->teamRepository->deleteById($teamId);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            return $this->jsonError($e->getMessage());
        } catch (\Exception $e) {
            $this->logger->critical($e);
            return $this->jsonError(__('Something went wrong.'));
        }

        return $this->jsonSuccess([], __('The team was successfully deleted.'));
    }
}
