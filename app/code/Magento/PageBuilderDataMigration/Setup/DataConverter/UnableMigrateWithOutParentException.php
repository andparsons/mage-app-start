<?php
declare(strict_types=1);

namespace Magento\PageBuilderDataMigration\Setup\DataConverter;

/**
 * Unable to migrate content type entity without parent content type
 */
class UnableMigrateWithOutParentException extends \Magento\Framework\Exception\LocalizedException
{
}
