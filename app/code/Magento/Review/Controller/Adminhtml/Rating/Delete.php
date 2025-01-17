<?php
namespace Magento\Review\Controller\Adminhtml\Rating;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Review\Controller\Adminhtml\Rating as RatingController;
use Magento\Framework\Controller\ResultFactory;

class Delete extends RatingController implements HttpPostActionInterface
{
    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        if ($this->getRequest()->getParam('id') > 0) {
            try {
                /** @var \Magento\Review\Model\Rating $model */
                $model = $this->_objectManager->create(\Magento\Review\Model\Rating::class);
                $model->load($this->getRequest()->getParam('id'))->delete();
                $this->messageManager->addSuccess(__('You deleted the rating.'));
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                $resultRedirect->setPath('review/rating/edit', ['id' => $this->getRequest()->getParam('id')]);
                return $resultRedirect;
            }
        }
        $resultRedirect->setPath('review/rating/');
        return $resultRedirect;
    }
}
