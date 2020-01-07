<?php
namespace Magento\Support\Model\Report\Group\Logs;

/**
 * Top System Messages section of Logs report group
 */
class TopSystemMessagesSection extends AbstractLogsSection
{
    /**
     * {@inheritdoc}
     */
    public function generate()
    {
        $data = $this->logFilesData->getLogFilesData()[LogFilesData::SYSTEM_MESSAGES];

        return [
            (string)__('Top System Messages') => [
                'headers' => [__('Count'), __('Message'), __('Last Occurrence')],
                'data' => $data
            ]
        ];
    }
}
