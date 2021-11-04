<?php
/**
 * @author magefast@gmail.com www.magefast.com
 * @command php bin/magento sync:stock
 */

namespace Strekoza\ImportStockSync\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Strekoza\ImportStockSync\Service\Sync as ServiceSync;

class Sync extends Command
{
    private $serviceSync;

    public function __construct(ServiceSync $serviceSync)
    {
        $this->serviceSync = $serviceSync;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('sync:stock');
        $this->setDescription('Update Stock data from Import file');

        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $this->serviceSync->run();
        die('-w-w-');
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $generatorModel = $objectManager->create('\Strekoza\Feed\Model\Generator');

        $output->writeln("Export Feed... start");

        $generatorModel->runExport();

        $output->writeln("Export Feed... finish");

        unset($feed);
        unset($generator);
    }
}
