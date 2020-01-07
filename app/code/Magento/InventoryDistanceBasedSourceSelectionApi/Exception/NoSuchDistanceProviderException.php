<?php
declare(strict_types=1);

namespace Magento\InventoryDistanceBasedSourceSelectionApi\Exception;

use Magento\Framework\Exception\LocalizedException;

/**
 * Exception to be thrown when a non existing distance provider is requested
 *
 * @api
 */
class NoSuchDistanceProviderException extends LocalizedException
{

}
