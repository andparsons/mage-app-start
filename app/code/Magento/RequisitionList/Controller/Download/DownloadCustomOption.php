<?php
declare(strict_types=1);

namespace Magento\RequisitionList\Controller\Download;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\RequisitionList\Model\Checker\RequisitionListItemOptionAvailability as OptionChecker;
use Magento\RequisitionList\Model\RequisitionList\Items;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Class DownloadCustomOption from requisition list.
 */
class DownloadCustomOption extends Action implements HttpGetActionInterface
{
    /**
     * @var Json
     */
    private $serializer;

    /**
     * @var OptionChecker
     */
    private $optionChecker;

    /**
     * @var FileFactory
     */
    private $fileFactory;

    /**
     * @var Items
     */
    private $requisitionListItems;

    /**
     * @param Context $context
     * @param Json $serializer
     * @param OptionChecker $optionChecker
     * @param FileFactory $fileFactory
     * @param Items $requisitionListItems
     */
    public function __construct(
        Context $context,
        Json $serializer,
        OptionChecker $optionChecker,
        FileFactory $fileFactory,
        Items $requisitionListItems
    ) {
        $this->serializer = $serializer;
        $this->optionChecker = $optionChecker;
        $this->fileFactory = $fileFactory;
        $this->requisitionListItems = $requisitionListItems;
        parent::__construct($context);
    }

    /**
     * Custom options download action for requisition list.
     *
     * @return ResultInterface
     */
    public function execute()
    {
        $requisitionListItemId = $this->getRequest()->getParam('id');

        try {
            $requisitionListItem = $this->requisitionListItems->get($requisitionListItemId);
        } catch (NoSuchEntityException $e) {
            return $this->redirectNoRoute();
        }

        $code = $this->getRequest()->getParam('code');

        if (!$this->optionChecker->isAvailableForCurrentUser($requisitionListItem)
            || !$this->optionChecker->isCustomOptionForDownload($code)) {
            return $this->redirectNoRoute();
        }

        try {
            $option = $requisitionListItem->getOptions()[$code];
            $info = $this->serializer->unserialize($option);
            if ($this->getRequest()->getParam('key') != $info['secret_key']) {
                return $this->redirectNoRoute();
            }

            /** @var ResultInterface $result */
            $result = $this->resultFactory->create(ResultFactory::TYPE_RAW);
            return $result->setContents(
                $this->fileFactory->create(
                    $info['title'],
                    ['value' => $info['quote_path'], 'type' => 'filename'],
                    DirectoryList::MEDIA,
                    $info['type']
                )
            );
        } catch (\Exception $e) {
            return $this->redirectNoRoute();
        }
    }

    /**
     * Redirect to noroute page.
     *
     * @return ResultInterface
     */
    private function redirectNoRoute()
    {
        $result = $this->resultFactory->create(ResultFactory::TYPE_FORWARD);
        return $result->forward('noroute');
    }
}
