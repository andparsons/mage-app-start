<?php
namespace Magento\Downloadable\Model\Sample;

use Magento\Downloadable\Api\SampleRepositoryInterface as SampleRepository;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;

/**
 * Class DeleteHandler
 */
class DeleteHandler implements ExtensionInterface
{
    /**
     * @var SampleRepository
     */
    protected $sampleRepository;

    /**
     * @param SampleRepository $sampleRepository
     */
    public function __construct(SampleRepository $sampleRepository)
    {
        $this->sampleRepository = $sampleRepository;
    }

    /**
     * @param object $entity
     * @param array $arguments
     * @return \Magento\Catalog\Api\Data\ProductInterface|object
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute($entity, $arguments = [])
    {
        /** @var $entity \Magento\Catalog\Api\Data\ProductInterface */
        if ($entity->getTypeId() != \Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE) {
            return $entity;
        }
        /** @var \Magento\Catalog\Api\Data\ProductInterface $entity */
        foreach ($this->sampleRepository->getList($entity->getSku()) as $sample) {
            $this->sampleRepository->delete($sample->getId());
        }
        return $entity;
    }
}
