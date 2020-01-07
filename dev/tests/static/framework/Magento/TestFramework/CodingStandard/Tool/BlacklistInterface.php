<?php

namespace Magento\TestFramework\CodingStandard\Tool;

interface BlacklistInterface
{
    /**
     * Set list of paths to be excluded from tool run
     *
     * @param array $blackList
     * @return void
     */
    public function setBlackList(array $blackList);
}
