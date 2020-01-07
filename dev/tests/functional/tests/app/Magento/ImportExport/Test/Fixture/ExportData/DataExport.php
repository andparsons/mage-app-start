<?php

namespace Magento\ImportExport\Test\Fixture\ExportData;

use Magento\Mtf\Fixture\DataSource;
use Magento\Mtf\Fixture\InjectableFixture;

/**
 * Source for attribute field.
 */
class DataExport extends DataSource
{
    /**
     * @param InjectableFixture $data
     */
    public function __construct(InjectableFixture $data)
    {
        $this->data = $data->getData();
    }

    /**
     * Get export data.
     *
     * @return array
     */
    public function getDataExport()
    {
        return $this->data;
    }
}
