<?php
declare(strict_types=1);

namespace Magento\ServicesId\Model\Config\Backend;

use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\ServicesId\Exception\InstanceIdGenerationException;
use Magento\ServicesId\Model\GeneratorInterface;
use Magento\ServicesId\Model\ServicesConfig;

/**
 * Instance Id config variable processor model
 *
 * @api
 */
class ServicesId extends Value
{
    /**
     * @var GeneratorInterface
     */
    private $generator;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param ScopeConfigInterface $config
     * @param TypeListInterface $cacheTypeList
     * @param GeneratorInterface $generator
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        GeneratorInterface $generator,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->generator = $generator;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * Capture previous UUID value
     *
     * @return Value
     * @throws InstanceIdGenerationException
     */
    public function beforeSave() : Value
    {
        $previousUuid = $this->_config->getValue(ServicesConfig::CONFIG_PATH_INSTANCE_ID);
        if ($this->getValue() == null) {
            $uuid = $previousUuid ? $previousUuid : $this->generator->generateInstanceId();
            $uuidString = strtolower($uuid);
        } else {
            $uuidString = strtolower($this->getValue());
        }
        $this->setValue($uuidString);
        return parent::beforeSave();
    }
}
