<?php
/**
 * @author magefast@gmail.com www.magefast.com
 */

namespace Strekoza\ImportStockSync\Service;

class PrepareFileToImport
{
    /**
     * @param string $file
     * @return array
     */
    public function execute(string $file): array
    {
        $csvData = [];

        if (($handle = fopen($file, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
                $csvData[] = $data;
            }
            fclose($handle);
        }
        unset($handle);
        unset($data);

        $csvDataArray = [];

        $i = 0;
        foreach ($csvData as $c) {
            $i++;
            if ($i == 1) {
                continue;
            }

            $csvDataArray[trim($c[Settings::CSV_COLUMN_NUM_SKU])] = [
                'sku' => trim($c[Settings::CSV_COLUMN_NUM_SKU]),
                'status' => trim($c[Settings::CSV_COLUMN_NUM_STATUS]),
                'price' => floatval($c[Settings::CSV_COLUMN_NUM_PRICE]),
                'special_price' => floatval($c[Settings::CSV_COLUMN_NUM_SPECIAL_PRICE]),
                'stock_data' => [
                    'qty' => intval($c[Settings::CSV_COLUMN_NUM_QTY]),
                    'is_in_stock' => $this->_prepareColumnDataInStock(trim($c[Settings::CSV_COLUMN_NUM_STOCK_STATUS])),
                ]
            ];
        }
        unset($csvData);

        return $csvDataArray;
    }

    /**
     * @param string $string
     * @return int
     */
    private function _prepareColumnDataInStock(string $string = ''): int
    {
        if (in_array($string, Settings::CSV_STOCK_STATUS_VALUES_IN_STOCK)) {
            return 1;
        }

        return 0;
    }
}
