<?php
declare(strict_types=1);

namespace Magento\ImportExport\Model\Export\Entity;

use Magento\Framework\Serialize\SerializerInterface;
use Magento\ImportExport\Api\Data\ExportInfoInterface;
use Magento\Framework\ObjectManagerInterface;
use \Psr\Log\LoggerInterface;
use Magento\ImportExport\Model\Export\ConfigInterface;
use Magento\ImportExport\Model\Export\Entity\Factory as EntityFactory;
use Magento\ImportExport\Model\Export\Adapter\Factory as AdapterFactory;
use Magento\ImportExport\Model\Export\AbstractEntity;

/**
 * Factory for Export Info
 */
class ExportInfoFactory
{
    /**
     * Object Manager
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var \Magento\ImportExport\Model\Export\ConfigInterface
     */
    private $exportConfig;

    /**
     * @var AdapterFactory
     */
    private $exportAdapterFac;

    /**
     * @var EntityFactory
     */
    private $entityFactory;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * ExportInfoFactory constructor.
     * @param ObjectManagerInterface $objectManager
     * @param ConfigInterface $exportConfig
     * @param Factory $entityFactory
     * @param AdapterFactory $exportAdapterFac
     * @param SerializerInterface $serializer
     * @param LoggerInterface $logger
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        ConfigInterface $exportConfig,
        EntityFactory $entityFactory,
        AdapterFactory $exportAdapterFac,
        SerializerInterface $serializer,
        LoggerInterface $logger
    ) {
        $this->objectManager = $objectManager;
        $this->exportConfig = $exportConfig;
        $this->entityFactory = $entityFactory;
        $this->exportAdapterFac = $exportAdapterFac;
        $this->serializer = $serializer;
        $this->logger = $logger;
    }

    /**
     * Create ExportInfo object.
     *
     * @param string $fileFormat
     * @param string $entity
     * @param string $exportFilter
     * @return ExportInfoInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function create($fileFormat, $entity, $exportFilter)
    {
        $writer = $this->getWriter($fileFormat);
        $entityAdapter = $this->getEntityAdapter($entity, $fileFormat, $exportFilter, $writer->getContentType());
        $fileName = $this->generateFileName($entity, $entityAdapter, $writer->getFileExtension());
        /** @var ExportInfoInterface $exportInfo */
        $exportInfo = $this->objectManager->create(ExportInfoInterface::class);
        $exportInfo->setExportFilter($this->serializer->serialize($exportFilter));
        $exportInfo->setFileName($fileName);
        $exportInfo->setEntity($entity);
        $exportInfo->setFileFormat($fileFormat);
        $exportInfo->setContentType($writer->getContentType());

        return $exportInfo;
    }

    /**
     * Generate file name
     *
     * @param string $entity
     * @param AbstractEntity $entityAdapter
     * @param string $fileExtensions
     * @return string
     */
    private function generateFileName($entity, $entityAdapter, $fileExtensions)
    {
        $fileName = null;
        if ($entityAdapter instanceof AbstractEntity) {
            $fileName = $entityAdapter->getFileName();
        }
        if (!$fileName) {
            $fileName = $entity;
        }

        return $fileName . '_' . date('Ymd_His') . '.' . $fileExtensions;
    }

    /**
     * Create instance of entity adapter and return it.
     *
     * @param string $entity
     * @param string $fileFormat
     * @param string $exportFilter
     * @param string $contentType
     * @return \Magento\ImportExport\Model\Export\AbstractEntity|AbstractEntity
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getEntityAdapter($entity, $fileFormat, $exportFilter, $contentType)
    {
        $entities = $this->exportConfig->getEntities();
        if (isset($entities[$entity])) {
            try {
                $entityAdapter = $this->entityFactory->create($entities[$entity]['model']);
            } catch (\Exception $e) {
                $this->logger->critical($e);
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Please enter a correct entity model.')
                );
            }
            if (!$entityAdapter instanceof \Magento\ImportExport\Model\Export\Entity\AbstractEntity &&
                !$entityAdapter instanceof \Magento\ImportExport\Model\Export\AbstractEntity
            ) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __(
                        'The entity adapter object must be an instance of %1 or %2.',
                        \Magento\ImportExport\Model\Export\Entity\AbstractEntity::class,
                        \Magento\ImportExport\Model\Export\AbstractEntity::class
                    )
                );
            }
            // check for entity codes integrity
            if ($entity != $entityAdapter->getEntityTypeCode()) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('The input entity code is not equal to entity adapter code.')
                );
            }
        } else {
            throw new \Magento\Framework\Exception\LocalizedException(__('Please enter a correct entity.'));
        }
        $entityAdapter->setParameters([
            'fileFormat' => $fileFormat,
            'entity' => $entity,
            'exportFilter' => $exportFilter,
            'contentType' => $contentType,
        ]);
        return $entityAdapter;
    }

    /**
     * Returns writer for a file format
     *
     * @param string $fileFormat
     * @return \Magento\ImportExport\Model\Export\Adapter\AbstractAdapter
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getWriter($fileFormat)
    {
        $fileFormats = $this->exportConfig->getFileFormats();
        if (isset($fileFormats[$fileFormat])) {
            try {
                $writer = $this->exportAdapterFac->create($fileFormats[$fileFormat]['model']);
            } catch (\Exception $e) {
                $this->logger->critical($e);
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Please enter a correct entity model.')
                );
            }
            if (!$writer instanceof \Magento\ImportExport\Model\Export\Adapter\AbstractAdapter) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __(
                        'The adapter object must be an instance of %1.',
                        \Magento\ImportExport\Model\Export\Adapter\AbstractAdapter::class
                    )
                );
            }
        } else {
            throw new \Magento\Framework\Exception\LocalizedException(__('Please correct the file format.'));
        }
        return $writer;
    }
}
