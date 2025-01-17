<?php
namespace Magento\Support\Test\Unit\Model\Backup\Item;

use \Magento\Support\Test\Unit\Model\Backup\AbstractItemTest;

class DbTest extends AbstractItemTest
{
    /**
     * @return void
     */
    protected function createTestedItem()
    {
        $this->item = $this->objectManagerHelper->getObject(
            \Magento\Support\Model\Backup\Item\Db::class,
            [
                'backupFactory' => $this->backupFactoryMock,
                'shellHelper' => $this->shellHelperMock,
                'cmdPhpFactory' => $this->cmdPhpFactoryMock,
                'filesystem' => $this->filesystemMock,
                'resource' => $this->resourceMock
            ]
        );
    }

    /**
     * @return void
     */
    protected function setCmdScriptName()
    {
        $this->cmdPhpMock->expects($this->once())
            ->method('setScriptName')
            ->with('bin/magento support:backup:db -v');
    }
}
