<?php

namespace Magento\RequisitionList\Model\Action;

use Magento\Framework\App\RequestInterface;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;
use Magento\RequisitionList\Model\Config as ModuleConfig;
use Magento\RequisitionList\Api\RequisitionListRepositoryInterface;

/**
 * Controller action request validator.
 */
class RequestValidator
{
    /**
     * @var \Magento\RequisitionList\Model\Config
     */
    private $moduleConfig;

    /**
     * @var \Magento\Authorization\Model\UserContextInterface
     */
    private $userContext;

    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    private $formKeyValidator;

    /**
     * @var \Magento\Framework\Controller\ResultFactory
     */
    private $resultFactory;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $urlBuilder;

    /**
     * @var \Magento\RequisitionList\Api\RequisitionListRepositoryInterface
     */
    private $requisitionListRepository;

    /**
     * @param ModuleConfig $moduleConfig
     * @param UserContextInterface $userContext
     * @param Validator $formKeyValidator
     * @param ResultFactory $resultFactory
     * @param UrlInterface $urlBuilder
     * @param RequisitionListRepositoryInterface $requisitionListRepository
     * @internal param RedirectInterface $redirect
     */
    public function __construct(
        ModuleConfig $moduleConfig,
        UserContextInterface $userContext,
        Validator $formKeyValidator,
        ResultFactory $resultFactory,
        UrlInterface $urlBuilder,
        RequisitionListRepositoryInterface $requisitionListRepository
    ) {
        $this->moduleConfig = $moduleConfig;
        $this->userContext = $userContext;
        $this->formKeyValidator = $formKeyValidator;
        $this->resultFactory = $resultFactory;
        $this->urlBuilder = $urlBuilder;
        $this->requisitionListRepository = $requisitionListRepository;
    }

    /**
     * Get validator result.
     *
     * @param RequestInterface $request
     * @return ResultInterface
     */
    public function getResult(RequestInterface $request)
    {
        $result = null;

        if ($this->userContext->getUserType() !== UserContextInterface::USER_TYPE_CUSTOMER) {
            /** @var \Magento\Framework\Controller\Result\Redirect $result */
            $result = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $result->setPath('customer/account/login');
        } elseif (!$this->isActionAllowed($request)) {
            /** @var \Magento\Framework\Controller\Result\Redirect $result */
            $result = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $result->setRefererUrl();
        }

        return $result;
    }

    /**
     * Is action allowed.
     *
     * @param RequestInterface $request
     * @return bool
     */
    private function isActionAllowed(RequestInterface $request)
    {
        return $this->moduleConfig->isActive() &&
               $this->isListAllowed($request) &&
               $this->isPostValid($request);
    }

    /**
     * Is action allowed for customer.
     *
     * @param RequestInterface $request
     * @return bool
     */
    private function isListAllowed(RequestInterface $request)
    {
        $listId = $request->getParam('requisition_id') ? : $request->getParam('list_id');
        if ($listId) {
            $customerId = $this->userContext->getUserId();
            try {
                $list = $this->requisitionListRepository->get($listId);
            } catch (NoSuchEntityException $e) {
                return false;
            }
            return $list->getCustomerId() == $customerId;
        }
        return true;
    }

    /**
     * Is post request valid.
     *
     * @param RequestInterface $request
     * @return bool
     */
    private function isPostValid(RequestInterface $request)
    {
        return !$request->isPost() || $this->formKeyValidator->validate($request);
    }
}
