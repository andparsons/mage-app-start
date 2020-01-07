<?php
namespace Magento\Support\Model\Report\Group\Cron;

/**
 * All Global Cron Jobs
 */
class AllGlobalCronJobsSection extends AbstractCronJobsSection
{
    /**
     * {@inheritdoc}
     */
    public function generate()
    {
        $data = $this->prepareCronList($this->cronJobs->getAllCronJobs());
        return $this->getReportData(__('All Global Cron Jobs'), $data);
    }
}
