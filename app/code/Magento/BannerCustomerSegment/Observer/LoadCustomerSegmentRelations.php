<?php
namespace Magento\BannerCustomerSegment\Observer;

use Magento\Banner\Model\Banner;
use Magento\Framework\Event\ObserverInterface;

class LoadCustomerSegmentRelations implements ObserverInterface
{
    /**
     * @var \Magento\CustomerSegment\Helper\Data
     */
    private $segmentHelper;

    /**
     * @var \Magento\BannerCustomerSegment\Model\ResourceModel\BannerSegmentLink
     */
    private $bannerSegmentLink;

    /**
     * @param \Magento\CustomerSegment\Helper\Data $segmentHelper
     * @param \Magento\BannerCustomerSegment\Model\ResourceModel\BannerSegmentLink $bannerSegmentLink
     */
    public function __construct(
        \Magento\CustomerSegment\Helper\Data $segmentHelper,
        \Magento\BannerCustomerSegment\Model\ResourceModel\BannerSegmentLink $bannerSegmentLink
    ) {
        $this->segmentHelper = $segmentHelper;
        $this->bannerSegmentLink = $bannerSegmentLink;
    }

    /**
     * Assign the list of customer segment ids associated with a banner entity, passed as an event argument
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->segmentHelper->isEnabled()) {
            return;
        }
        /** @var Banner $banner */
        $banner = $observer->getEvent()->getBanner();
        $segmentIds = $this->bannerSegmentLink->loadBannerSegments($banner->getId());
        $banner->setData('customer_segment_ids', $segmentIds);
    }
}
