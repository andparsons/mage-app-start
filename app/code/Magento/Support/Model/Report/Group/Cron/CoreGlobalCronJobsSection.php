<?php
namespace Magento\Support\Model\Report\Group\Cron;

/**
 * Core Global Cron Jobs
 */
class CoreGlobalCronJobsSection extends AbstractCronJobsSection
{
    /**
     * {@inheritdoc}
     */
    public function generate()
    {
        $cronJobs = $this->cronJobs->getCronJobsByType(
            $this->cronJobs->getAllCronJobs(),
            CronJobs::TYPE_CORE
        );
        $data = $this->prepareCronList($cronJobs);

        return $this->getReportData(__('Core Global Cron Jobs'), $data);
    }
}
