<?php
/**
 * @author magefast@gmail.com www.magefast.com
 */

namespace Strekoza\ImportStockSync\Service;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Ui\DataProvider\Product\Form\Modifier\AdvancedInventory;
use Strekoza\ImportStockSync\Api\SettingsInterface;
use Strekoza\ImportStockSync\Logger\Logger;

class PrepareFileToImport
{
    /**
     * @var Logger
     */
    private $logger;

    /**
     * @param Logger $logger
     */
    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }

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

            $price = floatval(str_replace(",", ".", $c[Settings::CSV_COLUMN_NUM_PRICE]));
            $priceSpecial = floatval(str_replace(",", ".", $c[Settings::CSV_COLUMN_NUM_SPECIAL_PRICE]));

            $specialPriceData = '';
            $specialFromData = '';
            $specialToData = '';

            if ($price == 0) {
                $this->logger->info('Price 0. SKU:' . $c[Settings::CSV_COLUMN_NUM_SKU]);
                continue;
            }

            if (!empty($price2) && $price2 > 0) {
                $price2 = floatval(str_replace(",", ".", $price2));
                $specialPriceData = $this->_prepareColumnDataSpecialPrice($price, $priceSpecial);
                //@todo
                $specialFromData = '';
                $specialToData = '';
            }

            $csvDataArray[trim($c[Settings::CSV_COLUMN_NUM_SKU])] = [
                ProductInterface::SKU => trim($c[Settings::CSV_COLUMN_NUM_SKU]),
                ProductInterface::PRICE => $price,
                SettingsInterface::ATTRIBUTE_SPECIAL_PRICE => $specialPriceData,
                SettingsInterface::ATTRIBUTE_SPECIAL_FROM => $specialFromData,
                SettingsInterface::ATTRIBUTE_SPECIAL_TO => $specialToData,
                StockItemInterface::QTY => intval($c[Settings::CSV_COLUMN_NUM_QTY]),
                StockItemInterface::IS_IN_STOCK => intval($c[Settings::CSV_COLUMN_NUM_QTY]) > 0 ? 1 : 0,
                SettingsInterface::ATTRIBUTE_CENA_ZAKUPKI => trim($c[Settings::CSV_COLUMN_NUM_CENA_ZAKUPKI]),
                SettingsInterface::ATTRIBUTE_PROFIT => trim($c[Settings::CSV_COLUMN_NUM_PROFIT]),
                SettingsInterface::ATTRIBUTE_ROZETKA_PRICE => trim($c[Settings::CSV_COLUMN_NUM_ROZETKA_PRICE]),
                AdvancedInventory::STOCK_DATA_FIELDS => [
                    StockItemInterface::QTY => intval($c[Settings::CSV_COLUMN_NUM_QTY]),
                    StockItemInterface::IS_IN_STOCK => intval($c[Settings::CSV_COLUMN_NUM_QTY]) > 0 ? 1 : 0,
                ]
            ];
        }
        unset($csvData);

        return $csvDataArray;
    }

    /**
     * @param float $price
     * @param float $specialPrice
     * @return float|string
     */
    private function _prepareColumnDataSpecialPrice(float $price, float $specialPrice)
    {
        if ($specialPrice > 0 && $price > $specialPrice) {
            return $specialPrice;
        }

        return '';
    }
}
