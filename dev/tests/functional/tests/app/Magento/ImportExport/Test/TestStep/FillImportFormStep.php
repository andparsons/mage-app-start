<?php
namespace Magento\ImportExport\Test\TestStep;

use Magento\ImportExport\Test\Fixture\Import\File;
use Magento\ImportExport\Test\Fixture\ImportData;
use Magento\ImportExport\Test\Page\Adminhtml\AdminImportIndex;
use Magento\Mtf\TestStep\TestStepInterface;

/**
 * Fill import form.
 */
class FillImportFormStep implements TestStepInterface
{
    /**
     * Import index page.
     *
     * @var AdminImportIndex
     */
    private $adminImportIndex;

    /**
     * Import fixture.
     *
     * @var ImportData
     */
    private $import;

    /**
     * @param AdminImportIndex $adminImportIndex
     * @param ImportData $import
     */
    public function __construct(
        AdminImportIndex $adminImportIndex,
        ImportData $import
    ) {
        $this->adminImportIndex = $adminImportIndex;
        $this->import = $import;
    }

    /**
     * Fill import form.
     *
     * @return array
     */
    public function run()
    {
        $this->adminImportIndex->getImportForm()->fill($this->import);

        /** @var File $file */
        $file = $this->import->getDataFieldConfig('import_file')['source'];

        return [
            'entities' => $file->getEntities(),
            'import' => $this->import
        ];
    }
}
