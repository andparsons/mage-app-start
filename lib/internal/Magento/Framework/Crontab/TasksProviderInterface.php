<?php
namespace Magento\Framework\Crontab;

/**
 * Interface \Magento\Framework\Crontab\TasksProviderInterface
 *
 */
interface TasksProviderInterface
{
    /**
     * Get list of tasks
     *
     * @return array
     */
    public function getTasks();
}
