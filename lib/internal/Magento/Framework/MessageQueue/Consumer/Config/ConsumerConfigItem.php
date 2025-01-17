<?php
namespace Magento\Framework\MessageQueue\Consumer\Config;

use Magento\Framework\MessageQueue\Consumer\Config\ConsumerConfigItem\Handler\Iterator as HandlerIterator;
use Magento\Framework\MessageQueue\Consumer\Config\ConsumerConfigItem\Handler\IteratorFactory as HandlerIteratorFactory;

/**
 * {@inheritdoc}
 */
class ConsumerConfigItem implements ConsumerConfigItemInterface
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $connection;

    /**
     * @var string
     */
    private $queue;

    /**
     * @var string
     */
    private $consumerInstance;

    /**
     * @var HandlerIterator
     */
    private $handlers;

    /**
     * @var string
     */
    private $maxMessages;

    /**
     * Initialize dependencies.
     *
     * @param HandlerIteratorFactory $handlerIteratorFactory
     */
    public function __construct(HandlerIteratorFactory $handlerIteratorFactory)
    {
        $this->handlers = $handlerIteratorFactory->create();
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * {@inheritdoc}
     */
    public function getQueue()
    {
        return $this->queue;
    }

    /**
     * {@inheritdoc}
     */
    public function getConsumerInstance()
    {
        return $this->consumerInstance;
    }

    /**
     * {@inheritdoc}
     */
    public function getHandlers()
    {
        return $this->handlers;
    }

    /**
     * {@inheritdoc}
     */
    public function getMaxMessages()
    {
        return $this->maxMessages;
    }

    /**
     * {@inheritdoc}
     */
    public function setData(array $data)
    {
        $this->name = $data['name'];
        $this->connection = $data['connection'];
        $this->queue = $data['queue'];
        $this->consumerInstance = $data['consumerInstance'];
        $this->maxMessages = $data['maxMessages'];
        $this->handlers->setData($data['handlers']);
    }
}
