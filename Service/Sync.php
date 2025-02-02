<?php
/**
 * @author magefast@gmail.com www.magefast.com
 */

namespace Strekoza\ImportStockSync\Service;

use Exception;
use Magento\Catalog\Model\Indexer\Product\Eav\Processor as EavProcessor;
use Magento\Catalog\Model\Indexer\Product\Price\Processor as PriceProcessor;
use Magento\CatalogInventory\Model\Indexer\Stock\Processor;
use Magento\CatalogRule\Model\Indexer\Product\ProductRuleProcessor;
use Magento\CatalogRule\Model\Indexer\Rule\RuleProductProcessor;
use Magento\InventoryIndexer\Indexer\InventoryIndexer;
use Magento\Store\Model\StoreManagerInterface;
use Strekoza\ImportStockSync\Logger\Logger;

class Sync
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
     * @var UpdateProduct
     */
    private $updateProduct;

    /**
     * @var PrepareFileToImport
     */
    private $prepareFileToImport;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Processor
     */
    private $stockIndexerProcessor;

    /**
     * @var PriceProcessor
     */
    private $priceIndexerProcessor;

    /**
     * @var InventoryIndexer
     */
    private $inventoryIndexerProcessor;

    /**
     * @var EavProcessor
     */
    private $eavIndexerProcessor;

    /**
     * @var RuleProductProcessor
     */
    private $ruleProductIndexerProcessor;

    /**
     * @var ProductRuleProcessor
     */
    private $productRuleIndexerProcessor;

    /**
     * @var FlagSync
     */
    private $flagSync;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @param Settings $settings
     * @param UpdateProduct $updateProduct
     * @param PrepareFileToImport $prepareFileToImport
     * @param StoreManagerInterface $storeManager
     * @param Processor $stockIndexerProcessor
     * @param PriceProcessor $priceIndexerProcessor
     * @param ProductRuleProcessor $productRuleIndexerProcessor
     * @param RuleProductProcessor $ruleProductIndexerProcessor
     * @param EavProcessor $eavIndexerProcessor
     * @param FlagSync $flagSync
     * @param Logger $logger
     */
    public function __construct(
        Settings              $settings,
        UpdateProduct         $updateProduct,
        PrepareFileToImport   $prepareFileToImport,
        StoreManagerInterface $storeManager,
        Processor             $stockIndexerProcessor,
        PriceProcessor        $priceIndexerProcessor,
        //InventoryIndexer      $inventoryIndexerProcessor,
        ProductRuleProcessor  $productRuleIndexerProcessor,
        RuleProductProcessor  $ruleProductIndexerProcessor,
        EavProcessor          $eavIndexerProcessor,
        FlagSync              $flagSync,
        Logger                $logger
    )
    {
        $this->settings = $settings;
        $this->updateProduct = $updateProduct;
        $this->prepareFileToImport = $prepareFileToImport;
        $this->storeManager = $storeManager;
        $this->stockIndexerProcessor = $stockIndexerProcessor;
        $this->priceIndexerProcessor = $priceIndexerProcessor;
        //$this->inventoryIndexerProcessor = $inventoryIndexerProcessor;
        $this->productRuleIndexerProcessor = $productRuleIndexerProcessor;
        $this->ruleProductIndexerProcessor = $ruleProductIndexerProcessor;
        $this->eavIndexerProcessor = $eavIndexerProcessor;
        $this->flagSync = $flagSync;
        $this->logger = $logger;
    }

    /**
     * @return array|null
     */
    public function getErrors(): ?array
    {
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
        if (count($this->notices) == 0) {
            return null;
        }

        return $this->notices;
    }

    /**
     * @throws Exception
     */
    public function run(bool $skipFlag = false)
    {
        if ($skipFlag !== true) {
            if ($this->flagSync->get() == true) {
                $this->addError(__('Sync is Running. Please try in 10 minutes.'));
                return;
            }
        }

        $this->flagSync->setFlag(1);

        $start = microtime(true);

        $this->addNotice(__('Sync Start at: ') . date('m/d/Y h:i:s a'));

        if ($skipFlag === true) {
            $this->addNotice(__('started with cli command'));
        }

        $this->syncData();

        if ($this->updateProduct->getUpdatedCount() > 0) {
            $this->ruleProductIndexerProcessor->reindexAll();
            $this->eavIndexerProcessor->reindexAll();
            //$this->inventoryIndexerProcessor->executeFull();
            $this->productRuleIndexerProcessor->reindexAll();
            $this->stockIndexerProcessor->reindexAll();
            $this->priceIndexerProcessor->reindexAll();
        }

        $this->flagSync->setFlag(0);

        $times = microtime(true) - $start;
        $this->addNotice(__('Sync take time(sec.): ') . $times);
    }

    /**
     * @param $value
     */
    public function addError($value)
    {
        $this->errors[] = $value;
        $this->logger->error($value);
    }

    /**
     * @param $value
     */
    public function addNotice($value)
    {
        $this->notices[] = $value;
        $this->logger->notice($value);
    }

    /**
     * @throws Exception
     */
    private function syncData(): void
    {
        $websiteIds = $this->getWebsiteIds();

        foreach ($websiteIds as $websiteId) {
            $websiteId = intval($websiteId);

            if ($this->settings->isEnabled($websiteId)) {
                $file = $this->settings->getPathInternalFile($websiteId);

                if (!file_exists($file)) {
                    $this->addError(__('File Import not exist'));
                    return;
                }

                $csvData = $this->prepareFileToImport->execute($file, $websiteId);

                if (count($csvData) == 0) {
                    $this->addError(__('Not rows to sync'));
                }

                $this->updateProduct->update($csvData, $websiteId);

                unlink($file);
            }
        }

        $this->notices = array_merge($this->updateProduct->getNotices());
        $this->errors = array_merge($this->updateProduct->getErrors());
    }

    /**
     * @return array
     */
    private function getWebsiteIds(): array
    {
        return array_keys($this->storeManager->getWebsites());
    }
}
