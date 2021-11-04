<?php
/**
 * @author magefast@gmail.com www.magefast.com
 */

namespace Strekoza\ImportStockSync\Service;

use Exception;
use Magento\Framework\File\Csv;

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

    private $csv;

    private $csvData;

    public function __construct(Settings $settings, Csv $csv)
    {
        $this->settings = $settings;
        $this->csv = $csv;
    }

    public function run()
    {
        $this->syncData();
        die('ruuuunn');
    }


    /**
     * @throws Exception
     */
    private function syncData()
    {
        $file = $this->settings->getPathInternalFile();

        if (!file_exists($file)) {
            $this->errors[] = __('File Import not exist');
            return false;
        }

        $this->prepareFileToImportData($file);

        if (count($this->csvData) == 0) {
            $this->errors[] = __('Not rows to sync');
        }

        var_dump($this->csvData);
        die('---');
        return true;
    }

    /**
     * @param string $file
     * @throws Exception
     */
    private function prepareFileToImportData(string $file)
    {
        $csvData = $this->csv->getData($file);

        foreach ($csvData as $c) {
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
