<?php
/**
 * @author magefast@gmail.com www.magefast.com
 * @command php bin/magento sync:stock
 */

namespace Strekoza\ImportStockSync\Console;

use Exception;
use Strekoza\ImportStockSync\Service\Sync as ServiceSync;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Sync extends Command
{
    private $serviceSync;

    /**
     * @param ServiceSync $serviceSync
     */
    public function __construct(ServiceSync $serviceSync)
    {
        $this->serviceSync = $serviceSync;
        parent::__construct();
    }

    /**
     *
     */
    protected function configure()
    {
        $this->setName('sync:stock');
        $this->setDescription('Update Price data from Import file');

        parent::configure();
    }

    /**
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->serviceSync->run(true);

        $errors = $this->serviceSync->getErrors();
        $notices = $this->serviceSync->getNotices();

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
