<?php
/**
 * @author magefast@gmail.com www.magefast.com
 */

namespace Strekoza\ImportStockSync\Service;

use Magento\CatalogInventory\Model\ResourceModel\Stock\Item;
use Magento\CatalogInventory\Model\ResourceModel\Stock\Status;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;

class UpdateStock
{
    public const TABLE_STOCK_ITEM = 'cataloginventory_stock_item';
    /**
     * @var Status
     */
    protected $stockStatusResource;
    /**
     * @var array
     */
    private $stockValuesToUpdate = [];
    /**
     * @var int
     */
    private $dbCountLoop = 0;
    /**
     * @var ResourceConnection
     */
    private $connection;


    /**
     * @var Item
     */
    private $resourceStockItem;

    /**
     * @param ResourceConnection $connection
     * @param Item $resourceStockItem
     */
    public function __construct(ResourceConnection $connection, Item $resourceStockItem)
    {
        $this->connection = $connection;
        $this->resourceStockItem = $resourceStockItem;
    }

    /**
     * @param $productId
     * @param $qty
     * @param $status
     */
    public function updateQty($productId, $qty, $status): void
    {
        $this->dbCountLoop++;

        $where = ['product_id = ?' => $productId];
        $bind = ['qty' => $qty, 'is_in_stock' => $status];

        $this->stockValuesToUpdate[] = ['where' => $where, 'bind' => $bind];

        $this->getConnection()->beginTransaction();

        foreach ($this->stockValuesToUpdate as $value) {
            $this->getConnection()->update(self::TABLE_STOCK_ITEM, $value['bind'], $value['where']);
        }

        $this->getConnection()->commit();

        $this->stockValuesToUpdate = [];
    }

    /**
     * Get connection
     *
     * @return AdapterInterface
     * @codeCoverageIgnore
     */
    private function getConnection(): AdapterInterface
    {
        return $this->connection->getConnection();
    }

    /**
     * @param $productId
     * @param $status
     * @param $qty
     * @param $websiteId
     */
    public function updateStatus($productId, $status, $qty, $websiteId)
    {
        $this->getConnection()->beginTransaction();
        $this->getStockStatusResource()->saveProductStatus($productId, $status, $qty, $websiteId);
        $this->getConnection()->commit();
    }

    /**
     *
     */
    private function getStockStatusResource()
    {
        if (empty($this->stockStatusResource)) {
            $this->stockStatusResource = ObjectManager::getInstance()->get(
                Status::class
            );
        }
        return $this->stockStatusResource;
    }

    public function finalizeUpdateStock($websiteId)
    {
        $this->getConnection()->beginTransaction();
        $this->resourceStockItem->updateSetOutOfStock($websiteId);
        $this->resourceStockItem->updateSetInStock($websiteId);
        $this->resourceStockItem->updateLowStockDate($websiteId);
        $this->getConnection()->commit();
    }
}
