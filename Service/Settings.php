<?php
/**
 * @author magefast@gmail.com www.magefast.com
 */

namespace Strekoza\ImportStockSync\Service;

use Magento\Framework\App\Config\ScopeConfigInterface;

class Settings
{
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
