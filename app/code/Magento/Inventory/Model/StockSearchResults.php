<?php
declare(strict_types=1);

namespace Magento\Inventory\Model;

use Magento\Framework\Api\SearchResults;
use Magento\InventoryApi\Api\Data\StockSearchResultsInterface;

class StockSearchResults extends SearchResults implements StockSearchResultsInterface
{
}
