<?php
/**
 * @author magefast@gmail.com www.magefast.com
 */

namespace Strekoza\ImportStockSync\Service;

use Exception;

class SyncPrice
{
    /**
     * @var array
     */
    private $errors = [];

    /**
     * @var array
     */
    private $notices = [];

    /**
     * @var Settings
     */
    private $settings;

    /**
     * @var UpdatePrice
     */
    private $updatePrice;

    /**
     * @var PrepareFileToImport
     */
    private $prepareFileToImport;

    /**
     * @param Settings $settings
     * @param UpdatePrice $updatePrice
     * @param PrepareFileToImport $prepareFileToImport
     */
    public function __construct(
        Settings            $settings,
        UpdatePrice         $updatePrice,
        PrepareFileToImport $prepareFileToImport
    )
    {
        $this->settings = $settings;
        $this->updatePrice = $updatePrice;
        $this->prepareFileToImport = $prepareFileToImport;
    }

    /**
     * @return array|null
     */
    public function getErrors(): ?array
    {
        if (count($this->errors) == 0) {
            return null;
        }

        return $this->errors;
    }

    /**
     * @return array|null
     */
    public function getNotices(): ?array
    {
        if (count($this->notices) == 0) {
            return null;
        }

        return $this->notices;
    }

    /**
     * @throws Exception
     */
    public function run()
    {
        $this->syncData();
    }

    /**
     * @throws Exception
     */
    private function syncData(): void
    {
        $file = $this->settings->getPathInternalFile();

        if (!file_exists($file)) {
            $this->errors[] = __('File Import not exist');
            return;
        }

        $csvData = $this->prepareFileToImport->execute($file);

        if (count($csvData) == 0) {
            $this->errors[] = __('Not rows to sync');
        }

        $this->updatePrice->updatePrice($csvData);

        $this->notices = array_merge($this->updatePrice->getNotices());
        $this->errors = array_merge($this->updatePrice->getErrors());
    }
}
