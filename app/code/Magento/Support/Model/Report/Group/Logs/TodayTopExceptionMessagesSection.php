<?php
namespace Magento\Support\Model\Report\Group\Logs;

/**
 * Today's Top Exception Messages section of Logs report group
 */
class TodayTopExceptionMessagesSection extends AbstractLogsSection
{
    /**
     * {@inheritdoc}
     */
    public function generate()
    {
        $data = $this->logFilesData->getLogFilesData()[LogFilesData::CURRENT_EXCEPTION_MESSAGES];

        return [
            (string)__('Today\'s Top Exception Messages') => [
                'headers' => [__('Count'), __('Message'), __('Stack Trace'), __('Last Occurrence')],
                'data' => $data
            ]
        ];
    }
}
