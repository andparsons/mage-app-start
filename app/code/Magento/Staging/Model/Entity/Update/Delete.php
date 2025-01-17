<?php
namespace Magento\Staging\Model\Entity\Update;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Staging\Controller\Result\JsonFactory;
use Magento\Framework\Controller\Result\Json;
use Magento\Staging\Model\Entity\Update\Action\Pool;
use Psr\Log\LoggerInterface;

class Delete
{
    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var JsonFactory
     */
    protected $jsonFactory;

    /**
     * @var string
     */
    protected $entityName;

    /**
     * @var Pool
     */
    protected $actionPool;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param ManagerInterface $messageManager
     * @param JsonFactory $jsonFactory
     * @param Pool $actionPool
     * @param LoggerInterface $logger
     * @param string $entityName
     */
    public function __construct(
        ManagerInterface $messageManager,
        JsonFactory $jsonFactory,
        Pool $actionPool,
        LoggerInterface $logger,
        $entityName
    ) {
        $this->messageManager = $messageManager;
        $this->jsonFactory = $jsonFactory;
        $this->entityName = $entityName;
        $this->logger = $logger;
        $this->actionPool = $actionPool;
    }

    /**
     * Execute
     *
     * @param array $params
     * @return Json
     */
    public function execute(array $params)
    {
        $error = true;
        try {
            $action = $this->actionPool->getAction($this->entityName, 'delete', $this->getActionType($params));
            $executor = $this->actionPool->getExecutor($action);
            $error = !$executor->execute($params);
        } catch (LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
            $this->logger->critical($e);
        } catch (\Exception $e) {
            $this->messageManager->addException(
                $e,
                __('Something went wrong while removing the %1.', $this->entityName)
            );
            $this->logger->critical($e);
        }

        /** @var Json $resultJson */
        return $this->jsonFactory->create([], ['error' => $error]);
    }

    /**
     * Retrieve staging mode
     *
     * @param array $params
     * @return string
     * @throws LocalizedException
     */
    protected function getActionType(array $params)
    {
        if (!isset($params['stagingData']['mode'])) {
            throw new LocalizedException(__("The 'mode' value is unexpected."));
        }
        return $params['stagingData']['mode'];
    }
}
