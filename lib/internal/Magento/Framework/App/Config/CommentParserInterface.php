<?php
namespace Magento\Framework\App\Config;

use Magento\Framework\Exception\FileSystemException;

/**
 * Interface for parsing comments in the configuration file.
 */
interface CommentParserInterface
{
    /**
     * Retrieve config list from file comments.
     *
     * @param string $fileName
     * @return array
     * @throws FileSystemException
     */
    public function execute($fileName);
}
