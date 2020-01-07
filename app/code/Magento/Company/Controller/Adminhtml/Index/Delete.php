<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Company\Controller\Adminhtml\Index;

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Company\Api\CompanyRepositoryInterface;

/**
 * Controller for deleting a company in admin panel.
 */
class Delete extends \Magento\Company\Controller\Adminhtml\Index implements HttpPostActionInterface
{
    /**
     * @var \Magento\Framework\Controller\Result\RawFactory
     */
    protected $resultRawFactory;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param CompanyRepositoryInterface $companyRepository
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        CompanyRepositoryInterface $companyRepository,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->resultRawFactory = $resultRawFactory;
        $this->urlBuilder = $context->getUrl();
        $this->logger = $logger;
        parent::__construct(
            $context,
            $resultForwardFactory,
            $resultPageFactory,
            $companyRepository
        );
    }

    /**
     * Delete company.
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $result = [];
        $id = $this->getRequest()->getParam('id');
        if ($id) {
            try {
                $company = $this->companyRepository->get($id);
                $this->companyRepository->deleteById($id);
                $this->messageManager->addSuccessMessage(
                    __(
                        'You have deleted company %companyName.',
                        ['companyName' => $company ? $company->getCompanyName() : '']
                    )
                );
                $result = ['url' => $this->urlBuilder->getUrl('company/index')];
            } catch (NoSuchEntityException $e) {
                $this->messageManager->addErrorMessage(__('The company no longer exists.'));
                $result = ['url' => $this->urlBuilder->getUrl('company/*/')];
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage(__($e->getMessage()));
                $result = ['url' => $this->urlBuilder->getUrl('company/index/edit', ['id' => $id])];
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__('Something went wrong. Please try again later.'));
                $result = ['url' => $this->urlBuilder->getUrl('company/index/edit', ['id' => $id])];
                $this->logger->critical($e);
            }
        }

        /** @var \Magento\Framework\Controller\Result\Raw $response */
        $response = $this->resultRawFactory->create();
        $response->setHeader('Content-type', 'text/plain');
        $response->setContents(json_encode($result));
        return $response;
    }
}
