<?php

namespace Magento\Setup\Console\Command;

use Magento\Framework\App\MaintenanceMode;
use Magento\Framework\Module\ModuleList;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command for checking maintenance mode status
 */
class MaintenanceStatusCommand extends AbstractSetupCommand
{
    /**
     * @var MaintenanceMode $maintenanceMode
     */
    private $maintenanceMode;

    /**
     * Constructor
     *
     * @param MaintenanceMode $maintenanceMode
     */
    public function __construct(MaintenanceMode $maintenanceMode)
    {
        $this->maintenanceMode = $maintenanceMode;
        parent::__construct();
    }

    /**
     * Initialization of the command
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('maintenance:status')
            ->setDescription('Displays maintenance mode status');
        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln(
            '<info>Status: maintenance mode is ' .
            ($this->maintenanceMode->isOn() ? 'active' : 'not active') . '</info>'
        );
        $addressInfo = $this->maintenanceMode->getAddressInfo();
        $addresses = implode(' ', $addressInfo);
        $output->writeln('<info>List of exempt IP-addresses: ' . ($addresses ? $addresses : 'none') . '</info>');
        return \Magento\Framework\Console\Cli::RETURN_SUCCESS;
    }
}
