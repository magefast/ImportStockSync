<?php
/**
 * @author magefast@gmail.com www.magefast.com
 */

namespace Strekoza\ImportStockSync\Service;

class PrepareFileToImport
{
    /**
     * @param string $file
     * @param int $id
     * @return array
     */
    public function execute(string $file, int $id): array
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
                'status' => $this->_prepareColumnDataStatus(trim($c[Settings::CSV_COLUMN_NUM_STATUS])),
                'price' => $this->_prepareColumnDataPrice(floatval($c[Settings::CSV_COLUMN_NUM_PRICE]), floatval($c[Settings::CSV_COLUMN_NUM_OLD_PRICE])),
                'special_price' => $this->_prepareColumnDataSpecialPrice(floatval($c[Settings::CSV_COLUMN_NUM_PRICE]), floatval($c[Settings::CSV_COLUMN_NUM_OLD_PRICE])),
                'qty' => intval($c[Settings::CSV_COLUMN_NUM_QTY]),
                'is_in_stock' => $this->_prepareColumnDataInStock(trim($c[Settings::CSV_COLUMN_NUM_STOCK_STATUS])),
                'time_delivery' => '',
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

    /**
     * @param string $string
     * @return int
     */
    private function _prepareColumnDataStatus(string $string = ''): int
    {
        $string = intval($string);
        if ($string === Settings::ATTRIBUTE_STATUS_VALUES_ENABLED) {
            return Settings::ATTRIBUTE_STATUS_VALUES_ENABLED;
        }

        return Settings::ATTRIBUTE_STATUS_VALUES_DISABLED;
    }

    /**
     * @param float $price
     * @param float $oldPrice
     * @return float
     */
    private function _prepareColumnDataPrice(float $price, float $oldPrice)
    {
        if ($oldPrice > 0 && $oldPrice > $price) {
            return $oldPrice;
        }

        return $price;
    }

    /**
     * @param float $price
     * @param float $oldPrice
     * @return float|string
     */
    private function _prepareColumnDataSpecialPrice(float $price, float $oldPrice)
    {
        if ($oldPrice > 0 && $oldPrice > $price) {
            return $price;
        }

        return '';
    }
}
