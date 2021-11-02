<?php
/**
 * @author magefast@gmail.com www.magefast.com
 */

namespace Strekoza\ImportStockSync\Service;

class Sync
{
    /**
     * @var array
     */
    private $errors = [];

    /**
     * @var Settings
     */
    private $settings;

    public function __construct(Settings $settings)
    {
        $this->settings = $settings;
    }

    public function run()
    {
        $this->syncData();
        die('ruuuunn');
    }


    private function syncData()
    {
        $file = $this->settings->getPathInternalFile();

        if (!file_exists($file)) {
            $this->errors[] = __('File Import not exist');
            return false;
        }

        $data = $this->prepareFileToImportData($file);

        return true;
    }


    /**
     * @param string $file
     * @return array
     */
    private function prepareFileToImportData(string $file): array
    {
        $data = [];

        return $data;
    }


}
