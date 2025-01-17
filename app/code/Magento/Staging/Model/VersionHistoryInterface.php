<?php

namespace Magento\Staging\Model;

/**
 * Class VersionHistoryInterface
 */
interface VersionHistoryInterface
{
    /**
     * @return int
     */
    public function getMaximumInDB();

    /**
     * @param int $maximumVersions
     * @return void
     */
    public function setMaximumInDB($maximumVersions);

    /**
     * @return int
     */
    public function getCurrentId();

    /**
     * @param int $versionId
     * @return void
     */
    public function setCurrentId($versionId);
}
