<?php
namespace Magento\Downloadable\Model\Sample;

use Magento\Downloadable\Api\SampleRepositoryInterface as SampleRepository;
use Magento\Downloadable\Model\Product\Type;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;

/**
 * Class UpdateHandler
 */
class UpdateHandler implements ExtensionInterface
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
        if ($entity->getTypeId() != Type::TYPE_DOWNLOADABLE) {
            return $entity;
        }

        /** @var \Magento\Downloadable\Api\Data\SampleInterface[] $samples */
        $samples = $entity->getExtensionAttributes()->getDownloadableProductSamples() ?: [];
        $updatedSamples = [];
        $oldSamples = $this->sampleRepository->getList($entity->getSku());
        foreach ($samples as $sample) {
            if ($sample->getId()) {
                $updatedSamples[$sample->getId()] = true;
            }
            $this->sampleRepository->save($entity->getSku(), $sample, !(bool)$entity->getStoreId());
        }
        /** @var \Magento\Catalog\Api\Data\ProductInterface $entity */
        foreach ($oldSamples as $sample) {
            if (!isset($updatedSamples[$sample->getId()])) {
                $this->sampleRepository->delete($sample->getId());
            }
        }

        return $entity;
    }
}
