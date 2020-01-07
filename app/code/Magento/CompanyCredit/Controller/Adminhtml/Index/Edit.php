<?php

namespace Magento\CompanyCredit\Controller\Adminhtml\Index;

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Controller\ResultFactory;
use Magento\CompanyCredit\Model\HistoryInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Class Edit.
 */
class Edit extends \Magento\Backend\App\Action implements HttpPostActionInterface
{
    /**
     * Authorization level of a basic admin session.
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Company::index';

    /**
     * @var \Magento\CompanyCredit\Model\HistoryRepositoryInterface
     */
    private $historyRepository;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var Json
     */
    private $serializer;

    /**
     * Edit controller constructor.
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\CompanyCredit\Model\HistoryRepositoryInterface $historyRepository
     * @param \Psr\Log\LoggerInterface $logger
     * @param Json|null $serializer
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\CompanyCredit\Model\HistoryRepositoryInterface $historyRepository,
        \Psr\Log\LoggerInterface $logger,
        Json $serializer = null
    ) {
        parent::__construct($context);
        $this->historyRepository = $historyRepository;
        $this->logger = $logger;
        $this->serializer = $serializer ?: ObjectManager::getInstance()->get(Json::class);
    }

    /**
     * Edit history record.
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     * @throws \InvalidArgumentException
     */
    public function execute()
    {
        $request = $this->getRequest();
        $reimburseBalance = $this->getRequest()->getParam('reimburse_balance');
        $id = $request->getParam('history_id') ? $request->getParam('history_id') : null;
        $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        try {
            $historyRepository = $this->historyRepository->get($id);
            $historyRepository->setPurchaseOrder($reimburseBalance['purchase_order']);
            $this->changeComment($historyRepository, $reimburseBalance['credit_comment']);
            $this->historyRepository->save($historyRepository);
            $result->setData(['status' => 'success']);
        } catch (NoSuchEntityException $e) {
            $result->setData(['status' => 'error', 'error' => __('History record no longer exists.')]);
        } catch (CouldNotSaveException $e) {
            $result->setData(['status' => 'error', 'error' => $e->getMessage()]);
        } catch (\Exception $e) {
            $result->setData(['status' => 'error', 'error' => __('Something went wrong. Please try again later.')]);
            $this->logger->critical($e);
        }

        return $result;
    }

    /**
     * Change comment in history.
     *
     * @param HistoryInterface $historyLog
     * @param string $comment
     * @return void
     */
    private function changeComment(HistoryInterface $historyLog, $comment)
    {
        $commentArray = $historyLog->getComment() ? $this->serializer->unserialize($historyLog->getComment()) : [];
        $commentArray['custom'] = $comment;
        $historyLog->setComment($this->serializer->serialize($commentArray));
    }
}
