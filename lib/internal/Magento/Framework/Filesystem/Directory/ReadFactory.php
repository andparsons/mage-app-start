<?php
namespace Magento\Framework\Filesystem\Directory;

use Magento\Framework\Filesystem\DriverPool;

class ReadFactory
{
    /**
     * Pool of filesystem drivers
     *
     * @var DriverPool
     */
    private $driverPool;

    /**
     * Constructor
     *
     * @param DriverPool $driverPool
     */
    public function __construct(DriverPool $driverPool)
    {
        $this->driverPool = $driverPool;
    }

    /**
     * Create a readable directory
     *
     * @param string $path
     * @param string $driverCode
     * @return ReadInterface
     */
    public function create($path, $driverCode = DriverPool::FILE)
    {
        $driver = $this->driverPool->getDriver($driverCode);
        $factory = new \Magento\Framework\Filesystem\File\ReadFactory(
            $this->driverPool
        );

        return new Read(
            $factory,
            $driver,
            $path,
            new PathValidator($driver)
        );
    }
}
