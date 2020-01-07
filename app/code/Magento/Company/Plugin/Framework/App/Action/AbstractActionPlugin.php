<?php

namespace Magento\Company\Plugin\Framework\App\Action;

use Magento\Framework\App\Action\AbstractAction;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\UrlInterface;

/**
 * Class AbstractActionPlugin
 */
class AbstractActionPlugin
{
    /**
     * @var \Magento\Framework\Controller\ResultFactory
     */
    private $resultFactory;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $urlBuilder;

    /**
     * @var CustomerLoginChecker
     */
    private $customerLoginChecker;

    /**
     * @param ResultFactory $resultFactory
     * @param UrlInterface $urlBuilder
     * @param CustomerLoginChecker $customerLoginChecker
     */
    public function __construct(
        ResultFactory $resultFactory,
        UrlInterface $urlBuilder,
        CustomerLoginChecker $customerLoginChecker
    ) {
        $this->resultFactory = $resultFactory;
        $this->urlBuilder = $urlBuilder;
        $this->customerLoginChecker = $customerLoginChecker;
    }

    /**
     * Around dispatch plugin.
     *
     * @param AbstractAction $subject
     * @param \Closure $proceed
     * @param RequestInterface $request
     * @return \Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \InvalidArgumentException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundDispatch(AbstractAction $subject, \Closure $proceed, RequestInterface $request)
    {
        if ($request->isPost() && $this->customerLoginChecker->isLoginAllowed()) {
            return $this->getLogoutResult($request);
        }
        return $proceed($request);
    }

    /**
     * Get logout result.
     *
     * @param RequestInterface $request
     * @return \Magento\Framework\Controller\ResultInterface
     * @throws \InvalidArgumentException
     */
    private function getLogoutResult(RequestInterface $request)
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $result */
        $result = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $result->setPath('customer/account/logout');
        if ($request->isAjax()) {
            $result = $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData([
                'backUrl' => $this->urlBuilder->getUrl('customer/account/logout')
            ]);
        }
        return $result;
    }
}
