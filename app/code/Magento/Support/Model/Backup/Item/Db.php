<?php
namespace Magento\Support\Model\Backup\Item;

use Magento\Support\Model\Backup\AbstractItem;

/**
 * Backup DB
 */
class Db extends AbstractItem
{
    /**
     * {@inheritdoc}
     */
    protected function setCmdScriptName()
    {
        $this->cmdObject->setScriptName('bin/magento support:backup:db -v');
    }
}
