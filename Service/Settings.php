<?php
/**
 * @author magefast@gmail.com www.magefast.com
 */

namespace Strekoza\ImportStockSync\Service;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Settings
{
    public const DEFAULT_SYNC_WEBSITE = 1;

    public const CSV_COLUMN_NUM_SKU = 4;
    public const CSV_COLUMN_NUM_PRICE = 6;
    public const CSV_COLUMN_NUM_QTY = 7;
    public const CSV_COLUMN_NUM_STOCK_STATUS = 8;
    public const CSV_COLUMN_NUM_STATUS = 9;
    public const CSV_COLUMN_NUM_OLD_PRICE = 10;
    public const CSV_STOCK_STATUS_VALUES_IN_STOCK = ['7', '5'];
    public const ATTRIBUTE_STATUS_VALUES_ENABLED = 1;
    public const ATTRIBUTE_STATUS_VALUES_DISABLED = 2;

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
     * @param $id
     * @return bool
     */
    public function isEnabled($id): bool
    {
        return (bool)$this->scopeConfig->getValue('importStockSync/settings/status', ScopeInterface::SCOPE_WEBSITE, $id);
    }

    /**
     * Get settings Full Server Path to Import Stock file
     *
     * @param $id
     * @return mixed|string
     */
    public function getPathInternalFile($id)
    {
        $settings = $this->scopeConfig->getValue('importStockSync/settings/path_internal_file', ScopeInterface::SCOPE_WEBSITE, $id);

        return $settings !== null ? $settings : '';
    }
}
