<?php
/**
 * @author magefast@gmail.com www.magefast.com
 */

namespace Strekoza\ImportStockSync\Service;

use Exception;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Strekoza\ImportStockSync\Logger\Logger;

class UpdateProduct
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
     * @var int
     */
    private $updatedCount = 0;

    /**
     * @var array
     */
    private $allSku = [];

    /**
     * @var array
     */
    private $storeIds = [];

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ProductCollectionFactory
     */
    private $productCollectionFactory;

    /**
     * @var UpdateAttributes
     */
    private $updateAttributes;

    /**
     * @var UpdateStock
     */
    private $updateStock;

    /**
     * @var int
     */
    private $totalRowToUpdate = 0;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @param StoreManagerInterface $storeManager
     * @param ProductCollectionFactory $productCollectionFactory
     * @param UpdateAttributes $updateAttributes
     * @param UpdateStock $updateStock
     * @param Logger $logger
     */
    public function __construct(
        StoreManagerInterface    $storeManager,
        ProductCollectionFactory $productCollectionFactory,
        UpdateAttributes         $updateAttributes,
        UpdateStock              $updateStock,
        Logger $logger
    )
    {
        $this->storeManager = $storeManager;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->updateAttributes = $updateAttributes;
        $this->updateStock = $updateStock;
        $this->logger = $logger;
    }

    /**
     * @return array
     */
    public function getErrors(): ?array
    {
        return $this->errors;
    }

    /**
     * @return array
     */
    public function getNotices(): array
    {
        return $this->notices;
    }

    /**
     * @param array $data
     * @param int $websiteId
     */
    public function update(array $data, int $websiteId): void
    {
        $this->allSku = [];
        $this->storeIds = [];
        $this->totalRowToUpdate = count($data);

        $this->prepareProductsData($websiteId);

        $count = 0;

        foreach ($data as $d) {
            $count++;
            $sku = $d['sku'];

            if (!$this->checkIfSkuExists($sku, $websiteId)) {
                $value = $count . ' row. FAILURE:: Product with SKU (' . $sku . ') doesn\'t exist.';
                $this->addError($value);
                continue;
            }

            $id = $this->allSku[$sku];

            try {
                $this->updateProductData($id, $d, $websiteId);
                $this->updateProductStockData($id, $d, $websiteId);

                $this->updatedCount++;
            } catch (Exception $e) {
                $value = $count . ' row. ERROR:: While updating  SKU (' . $sku . ') => ' . $e->getMessage();
                $this->addError($value);
            }

            $this->totalRowToUpdate--;
        }

        $this->updateStock->finalizeUpdateStock($websiteId);

        $value = __('Updated for Website ID: ' . $websiteId . '; Total updated count: ' . $this->updatedCount);
        $this->addNotice($value);
    }

    /**
     * @throws Exception
     */
    private function updateProductData(int $id, $data, int $websiteId)
    {
        unset($data['stock_data']);
        unset($data['sku']);

        $storeIds = $this->getStoreIds($websiteId);

        //$times = [];

        foreach ($storeIds as $storeId) {
            //$start = microtime(true);
            $this->updateAttributes->updateAttributesCustom($id, $data, $storeId, $this->totalRowToUpdate);
            //$times[] = microtime(true) - $start;
        }
        //var_dump((array_sum($times) / count($times)));
    }

    /**
     *
     */
    private function updateProductStockData(int $id, array $data, int $websiteId): void
    {
        //$times = [];
        //$start = microtime(true);

        $data = $data['stock_data'];

        if (isset($data['qty'])) {
            $this->updateStock->updateQty($id, $data['qty'], $data['is_in_stock']);
            $this->updateStock->updateStatus($id, $data['is_in_stock'], $data['qty'], $websiteId);
        }

        //$times[] = microtime(true) - $start;
        //var_dump((array_sum($times) / count($times)));
    }

    /**
     * @param $sku
     * @param int $websiteId
     * @return bool
     */
    private function checkIfSkuExists($sku, int $websiteId): bool
    {
        if (empty($this->allSku)) {
            $this->prepareProductsData($websiteId);
        }

        if (isset($this->allSku[$sku])) {
            return true;
        }

        return false;
    }

    /**
     *
     */
    private function prepareProductsData(int $websiteId): void
    {
        if (empty($this->allSku)) {

            $productCollection = $this->productCollectionFactory->create();
            $productCollection->addAttributeToSelect(['sku']);
            $productCollection->addWebsiteFilter($websiteId);

            $rows = (array)$productCollection->toArray();

            foreach ($rows as $v) {
                if (!empty($v['sku'])) {
                    $this->allSku[$v['sku']] = $v['entity_id'];
                }
            }
        }
    }

    /**
     * @param int $websiteId
     * @return array
     */
    private function getStoreIds(int $websiteId): array
    {
        if (empty($this->storeIds)) {
            $stores = $this->storeManager->getStoreByWebsiteId($websiteId);

            foreach ($stores as $storeId) {
                $this->storeIds[intval($storeId)] = intval($storeId);
            }

            if ($websiteId === Settings::DEFAULT_SYNC_WEBSITE) {
                $this->storeIds[Store::DEFAULT_STORE_ID] = Store::DEFAULT_STORE_ID;
            }
        }

        return $this->storeIds;
    }

    /**
     * @return int
     */
    public function getUpdatedCount(): int
    {
        return $this->updatedCount;
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
}
