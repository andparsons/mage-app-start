<?php
namespace Magento\Company\Controller\Account;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Action\Action;

/**
 * class Create
 */
class Create extends Action implements HttpGetActionInterface
{
    /**
     * {@inheritdoc}
     * @throws \InvalidArgumentException
     */
    public function execute()
    {
        /** @var \Magento\Framework\View\Result\Page $result */
        $result = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $result->getConfig()->getTitle()->set(__('New Company'));
        return $result;
    }
}
