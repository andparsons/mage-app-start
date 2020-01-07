<?php

/**
 * Interface for work with archives
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */

namespace Magento\Framework\Backup;

interface SourceFileInterface
{

    /**
     * Check if keep files of backup
     *
     * @return bool
     */
    public function keepSourceFile();

    /**
     * Set if keep files of backup
     *
     * @param bool $keepSourceFile
     * @return $this
     */
    public function setKeepSourceFile(bool $keepSourceFile);
}
