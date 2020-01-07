<?php

namespace Magento\NegotiableQuote\Setup\Patch\Data;

use Magento\Framework\DB\DataConverter\SerializedToJson;
use Magento\Framework\DB\FieldDataConverterFactory;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * Class ConvertValuesFromSerializeToJson
 * @package Magento\NegotiableQuote\Setup\Patch\Data
 */
class ConvertValuesFromSerializeToJson implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var string
     */
    private $quoteConnectionName = 'checkout';

    /**
     * @var FieldDataConverterFactory
     */
    private $fieldDataConverterFactory;

    /**
     * ConvertValuesFromSerializeToJson constructor.
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param FieldDataConverterFactory $fieldDataConverterFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        FieldDataConverterFactory $fieldDataConverterFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->fieldDataConverterFactory = $fieldDataConverterFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $this->convertValuesFromSerializeToJson();
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getVersion()
    {
        return '2.0.1';
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * Upgrade to version 2.0.1,
     * Convert data for `quote_id` field in `negotiable_quote` table from php-serialized to JSON format.
     * Convert data for `log_data` and `snapshot_data` fields in `negotiable_quote_history` table
     * from php-serialized to JSON format
     *
     * @return void
     */
    private function convertValuesFromSerializeToJson()
    {
        $fieldDataConverter = $this->fieldDataConverterFactory->create(SerializedToJson::class);
        $fieldDataConverter->convert(
            $this->moduleDataSetup->getConnection($this->quoteConnectionName),
            $this->moduleDataSetup->getTable('negotiable_quote'),
            'quote_id',
            'snapshot'
        );
        $fieldDataConverter->convert(
            $this->moduleDataSetup->getConnection($this->quoteConnectionName),
            $this->moduleDataSetup->getTable('negotiable_quote_history'),
            'history_id',
            'log_data'
        );
        $fieldDataConverter->convert(
            $this->moduleDataSetup->getConnection($this->quoteConnectionName),
            $this->moduleDataSetup->getTable('negotiable_quote_history'),
            'history_id',
            'snapshot_data'
        );
    }
}
