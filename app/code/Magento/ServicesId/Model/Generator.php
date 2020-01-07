<?php
declare(strict_types=1);

namespace Magento\ServicesId\Model;

use Magento\ServicesId\Exception\InstanceIdGenerationException;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Exception\UnsatisfiedDependencyException;
use Ramsey\Uuid\Uuid;

/**
 * UUID generator for Instance ID
 */
class Generator implements GeneratorInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(
        LoggerInterface $logger
    ) {
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public function generateInstanceId() : string
    {
        try {
            $uuid = Uuid::uuid4();
            return $uuid->toString();
        } catch (UnsatisfiedDependencyException $e) {
            $this->logger->error($e->getMessage());
            throw new InstanceIdGenerationException(__('Failed to generate Instance ID'));
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            throw new InstanceIdGenerationException(__('Failed to generate Instance ID'));
        }
    }
}
