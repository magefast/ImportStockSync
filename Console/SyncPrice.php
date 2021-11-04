<?php
/**
 * @author magefast@gmail.com www.magefast.com
 * @command php bin/magento sync:price
 */

namespace Strekoza\ImportStockSync\Console;

use Exception;
use Strekoza\ImportStockSync\Service\SyncPrice as ServiceSyncPrice;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SyncPrice extends Command
{
    private $serviceServiceSyncPrice;

    public function __construct(ServiceSyncPrice $serviceServiceSyncPrice)
    {
        $this->serviceServiceSyncPrice = $serviceServiceSyncPrice;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('sync:price');
        $this->setDescription('Update Price data from Import file');

        parent::configure();
    }

    /**
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->serviceServiceSyncPrice->run();

        $errors = $this->serviceServiceSyncPrice->getErrors();
        $notices = $this->serviceServiceSyncPrice->getNotices();

        if (!empty($errors)) {
            foreach ($errors as $error) {
                $output->writeln('<error>' . $error . '</error>');
            }
        }

        if (!empty($notices)) {
            foreach ($notices as $notice) {
                $output->writeln(PHP_EOL . '<info>' . $notice . '</info>');
            }
        }
    }
}
