<?php
/**
 * @author magefast@gmail.com www.magefast.com
 */

namespace Strekoza\ImportStockSync\Cron;

use Exception;
use Psr\Log\LoggerInterface;
use Strekoza\ImportStockSync\Service\Sync as ServiceSync;

class Sync
{
    private $logger;
    private $serviceSync;

    public function __construct(
        LoggerInterface $logger,
        ServiceSync     $serviceSync
    )
    {
        $this->logger = $logger;
        $this->serviceSync = $serviceSync;
    }

    /**
     * @return $this
     * @throws Exception
     */
    public function execute()
    {
        $this->logger->info('Import Stock Sync - START');

        $this->serviceSync->run();

        $notices = $this->serviceSync->getNotices();
        $errors = $this->serviceSync->getErrors();

        foreach ($notices as $notice) {
            $this->logger->notice($notice);
        }

        foreach ($errors as $error) {
            $this->logger->error($error);
        }

        $this->logger->info('Import Stock Sync - FINISH');

        return $this;
    }
}