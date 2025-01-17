<?php
namespace Magento\Framework\Process;

class PhpExecutableFinderFactory
{
    /**
     * Create PhpExecutableFinder instance
     *
     * @return \Symfony\Component\Process\PhpExecutableFinder
     */
    public function create()
    {
        return new \Symfony\Component\Process\PhpExecutableFinder();
    }
}
