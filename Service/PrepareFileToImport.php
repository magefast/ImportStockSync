<?php
/**
 * @author magefast@gmail.com www.magefast.com
 */

namespace Strekoza\ImportStockSync\Service;

use Exception;
use Magento\Framework\File\Csv;

class PrepareFileToImport
{
    /**
     * @var Csv
     */
    private $csv;

    /**
     * @param Csv $csv
     */
    public function __construct(Csv $csv)
    {
        $this->csv = $csv;
    }

    /**
     * @param string $file
     * @return array
     * @throws Exception
     */
    public function execute(string $file): array
    {
        $csvData = $this->csv->getData($file);
        $csvDataArray = [];

        $i = 0;
        foreach ($csvData as $c) {
            $i++;
            if ($i == 1) {
                continue;
            }

            $csvDataArray[trim($c[Settings::CSV_COLUMN_NUM_SKU])] = [
                'sku' => trim($c[Settings::CSV_COLUMN_NUM_SKU]),
                'qty' => intval($c[Settings::CSV_COLUMN_NUM_QTY]),
                'in_stock' => $this->_prepareColumnDataInStock(trim($c[Settings::CSV_COLUMN_NUM_SKU])),
                'price' => floatval($c[Settings::CSV_COLUMN_NUM_PRICE]),
                'special_price' => floatval($c[Settings::CSV_COLUMN_NUM_SPECIAL_PRICE])
            ];
        }

        return $csvDataArray;
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
