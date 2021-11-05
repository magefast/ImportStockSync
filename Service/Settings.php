<?php
/**
 * @author magefast@gmail.com www.magefast.com
 */

namespace Strekoza\ImportStockSync\Service;

use Magento\Framework\App\Config\ScopeConfigInterface;

class Settings
{
    public const CSV_COLUMN_NUM_SKU = 4;
    public const CSV_COLUMN_NUM_PRICE = 6;
    public const CSV_COLUMN_NUM_QTY = 7;
    public const CSV_COLUMN_NUM_STOCK_STATUS = 8;
    public const CSV_COLUMN_NUM_STATUS = 9;
    public const CSV_COLUMN_NUM_SPECIAL_PRICE = 10;
    public const CSV_STOCK_STATUS_VALUES_IN_STOCK = ['7'];

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * Api constructor.
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Get settings Full Server Path to Import Stock file
     * @return mixed|string
     */
    public function getPathInternalFile()
    {
        $settings = $this->scopeConfig->getValue('importStockSync/settings/path_internal_file');

        return $settings !== null ? $settings : '';
    }
}
