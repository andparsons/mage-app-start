<?php
namespace Magento\Company\Controller\Structure;

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;

/**
 * Controller for moving items in the company structure.
 */
class Manage extends \Magento\Company\Controller\AbstractAction implements HttpPostActionInterface
{
    /**
     * Authorization level of a company session.
     */
    const COMPANY_RESOURCE = 'Magento_Company::users_edit';

    /**
     * @var \Magento\Company\Model\Company\Structure
     */
    private $structureManager;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Company\Model\CompanyContext $companyContext
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Company\Model\Company\Structure $structureManager
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Company\Model\CompanyContext $companyContext,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Company\Model\Company\Structure $structureManager
    ) {
        parent::__construct($context, $companyContext, $logger);
        $this->structureManager = $structureManager;
    }

    /**
     * Move action.
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $request = $this->getRequest();

        $allowedIds = $this->structureManager->getAllowedIds($this->companyContext->getCustomerId());
        $structureId = $request->getParam('structure_id');
        $targetId = $request->getParam('target_id');

        if (!in_array($structureId, $allowedIds['structures'])
            || !in_array($targetId, $allowedIds['structures'])
        ) {
            return $this->jsonError(__('You are not allowed to do this.'));
        }

        try {
            $this->structureManager->moveNode($structureId, $targetId);
        } catch (\Exception $e) {
            $this->logger->critical($e);
            return $this->jsonError(__('Something went wrong.'));
        }

        return $this->jsonSuccess([], __('The item was successfully moved.'));
    }
}
