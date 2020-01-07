<?php
namespace Magento\WishlistSampleData\Setup;

use Magento\Framework\Setup;

class Installer implements Setup\SampleData\InstallerInterface
{
    /**
     * @var \Magento\WishlistSampleData\Model\Wishlist
     */
    protected $wishlist;

    /**
     * @param \Magento\WishlistSampleData\Model\Wishlist $wishlist
     */
    public function __construct(\Magento\WishlistSampleData\Model\Wishlist $wishlist) {
        $this->wishlist = $wishlist;
    }

    /**
     * {@inheritdoc}
     */
    public function install()
    {
        $this->wishlist->install(['Magento_WishlistSampleData::fixtures\wishlist.csv']);
    }
}
