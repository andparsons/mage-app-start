<?php

namespace Magento\GiftCardSampleData\Setup\Patch\Data;

use Magento\Framework\Setup;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;

/**
 * Class InstallGiftCardSampleData
 * @package Magento\GiftCardSampleData\Setup\Patch\Data
 */
class InstallGiftCardSampleData implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var Setup\SampleData\Executor
     */
    protected $executor;

    /**
     * @var \Magento\GiftCardSampleData\Setup\Installer
     */
    protected $installer;

    /**
     * InstallGiftCardSampleData constructor.
     * @param Setup\SampleData\Executor $executor
     * @param \Magento\GiftCardSampleData\Setup\Installer $installer
     */
    public function __construct(
        Setup\SampleData\Executor $executor,
        \Magento\GiftCardSampleData\Setup\Installer $installer
    ) {
        $this->executor = $executor;
        $this->installer = $installer;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $this->executor->exec($this->installer);
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
        return '2.0.0';
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}
