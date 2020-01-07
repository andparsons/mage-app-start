<?php
namespace Magento\Support\Model\Report\Group\Logs;

/**
 * Top Debug Messages section of Logs report group
 */
class TopDebugMessagesSection extends AbstractLogsSection
{
    /**
     * {@inheritdoc}
     */
    public function generate()
    {
        $data = $this->logFilesData->getLogFilesData()[LogFilesData::DEBUG_MESSAGES];

        return [
            (string)__('Top Debug Messages') => [
                'headers' => [__('Count'), __('Message'), __('Last Occurrence')],
                'data' => $data
            ]
        ];
    }
}
