<?php
/**
 * @author magefast@gmail.com www.magefast.com
 */

namespace Strekoza\ImportStockSync\Service;

use Exception;
use Magento\Framework\File\Csv;

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
     * @var Csv
     */
    private $csv;

    /**
     * @var array
     */
    private $csvData = [];

    /**
     * @var UpdatePrice
     */
    private $updatePrice;

    public function __construct(Settings $settings, Csv $csv, UpdatePrice $updatePrice)
    {
        $this->settings = $settings;
        $this->csv = $csv;
        $this->updatePrice = $updatePrice;
    }

    /**
     * @return array|null
     */
    public function getErrors(): ?array
    {
        $this->errors = array_merge($this->updatePrice->getErrors());

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
        $this->notices = array_merge($this->updatePrice->getNotices());

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

        $this->prepareFileToImportData($file);

        if (count($this->csvData) == 0) {
            $this->errors[] = __('Not rows to sync');
        }

        $this->updatePrice->updatePrice($this->csvData);


    }

    /**
     * @param string $file
     * @throws Exception
     */
    private function prepareFileToImportData(string $file)
    {
        $csvData = $this->csv->getData($file);

        $i = 0;
        foreach ($csvData as $c) {
            $i++;
            if ($i == 1) {
                continue;
            }

            $this->csvData[trim($c[Settings::CSV_COLUMN_NUM_SKU])] = [
                'sku' => trim($c[Settings::CSV_COLUMN_NUM_SKU]),
                'qty' => intval($c[Settings::CSV_COLUMN_NUM_QTY]),
                'in_stock' => $this->_prepareColumnDataInStock(trim($c[Settings::CSV_COLUMN_NUM_SKU])),
                'price' => floatval($c[Settings::CSV_COLUMN_NUM_PRICE]),
                'special_price' => floatval($c[Settings::CSV_COLUMN_NUM_SPECIAL_PRICE])
            ];
        }
    }

    /**
     * @param string $string
     * @return int
     */
    private function _prepareColumnDataInStock(string $string = ''): int
    {
        return intval($string);
    }
}
