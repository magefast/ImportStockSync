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

    public const CSV_COLUMN_NUM_SKU = 0;
    public const CSV_COLUMN_NUM_PRICE = 6;
    public const CSV_COLUMN_NUM_CENA_ZAKUPKI = 7;
    public const CSV_COLUMN_NUM_PROFIT = 8;
    public const CSV_COLUMN_NUM_QTY = 9;
    public const CSV_COLUMN_NUM_OLD_PRICE = 10;
    public const CSV_COLUMN_NUM_SPECIAL_PRICE = 16;
    public const CSV_COLUMN_NUM_ROZETKA_PRICE = 17;

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
