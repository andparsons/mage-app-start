<?php
namespace Magento\Mtf\Util\Generate\File;

/**
 * Interface for file template.
 */
interface TemplateInterface
{
    /**
     * Create and return file content.
     *
     * @return string
     */
    public function render();

    /**
     * Get filename. Without directory.
     *
     * @return string
     */
    public function getName();
}
