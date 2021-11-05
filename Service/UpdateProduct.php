<?php
/**
 * @author magefast@gmail.com www.magefast.com
 */

namespace Strekoza\ImportStockSync\Service;

use Exception;
use Magento\Catalog\Model\ResourceModel\Product\Action;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;

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
     * @var ResourceConnection
     */
    private $connection;

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
     * @var Action
     */
    private $productAction;

    /**
     * @var StockRegistryInterface
     */
    private $stockRegistry;

    public function __construct(
        ResourceConnection     $connection,
        StoreManagerInterface  $storeManager,
        Action                 $productAction,
        StockRegistryInterface $stockRegistry

    )
    {
        $this->connection = $connection;
        $this->storeManager = $storeManager;
        $this->productAction = $productAction;
        $this->stockRegistry = $stockRegistry;
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
     */
    public function update(array $data): void
    {
        $this->prepareProductsData();

        $count = 0;

        foreach ($data as $d) {
            $count++;
            $sku = $d['sku'];

            if (!$this->checkIfSkuExists($sku)) {
                $this->errors[] = $count . ' row. FAILURE:: Product with SKU (' . $sku . ') doesn\'t exist.';
                continue;
            }

            $id = $this->allSku[$sku];

            try {
                $this->updateProductData($id, $d);
                $this->updateProductStockData($id, $sku, $d);

                $this->updatedCount++;
            } catch (Exception $e) {
                $this->errors[] = $count . ' row. ERROR:: While updating  SKU (' . $sku . ') => ' . $e->getMessage();
            }
        }

        $this->notices[] = __('Total Price updated count: ' . $this->updatedCount);
    }

    /**
     * @throws Exception
     */
    private function updateProductData(int $id, $data)
    {
        unset($data['stock_data']);

        $storeIds = $this->getStoreIds();
        foreach ($storeIds as $storeId) {
            $this->productAction->updateAttributes([$id], $data, $storeId);
        }
    }

    /**
     * @throws NoSuchEntityException
     */
    private function updateProductStockData(int $id, string $sku, $data): void
    {
        $data = $data['stock_data'];

        if (isset($data['qty'])) {
            $stockItem = $this->stockRegistry->getStockItemBySku($sku);
            $stockItem->setQty($data['qty']);
            $stockItem->setIsInStock($data['is_in_stock']);
            $stockItem->save();
        }
    }


    /**
     * @param $sku
     * @return bool
     */
    private function checkIfSkuExists($sku): bool
    {
        if (empty($this->allSku)) {
            $this->prepareProductsData();
        }

        if (isset($this->allSku[$sku])) {
            return true;
        }

        return false;
    }

    /**
     *
     */
    private function prepareProductsData(): void
    {
        if (empty($this->allSku)) {
            $connection = $this->connection->getConnection('core_read');
            $table = $this->connection->getTableName('catalog_product_entity');

            $sql = "SELECT entity_id, sku FROM " . $table;
            $dataSql = $connection->fetchAll($sql);

            foreach ($dataSql as $k => $v) {
                if (!empty($v['sku'])) {
                    $this->allSku[$v['sku']] = $v['entity_id'];
                }
            }
        }
    }

    /**
     * @return array
     */
    private function getStoreIds(): array
    {
        if (empty($this->storeIds)) {
            $this->storeIds = array_keys($this->storeManager->getStores());
        }

        return $this->storeIds;
    }
}
