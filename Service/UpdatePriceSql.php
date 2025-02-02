<?php
/**
 * @author magefast@gmail.com www.magefast.com
 */

namespace Strekoza\ImportStockSync\Service;

use Exception;
use Magento\Framework\App\ResourceConnection;
use Strekoza\ImportStockSync\Logger\Logger;

class UpdatePriceSql
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
     * @var int|null
     */
    private $entityTypeId = null;

    /**
     * @var int|null
     */
    private $attributeIdPrice = null;

    /**
     * @var array
     */
    private $allSku = [];

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @param ResourceConnection $connection
     * @param Logger $logger
     */
    public function __construct(ResourceConnection $connection, Logger $logger)
    {
        $this->connection = $connection;
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
     */
    public function updatePrice(array $data): void
    {
        $count = 0;

        foreach ($data as $d) {
            $count++;
            $sku = $d['sku'];
            $price = $d['price'];

            if (!$this->checkIfSkuExists($sku)) {
                $this->addError($count . ' row. FAILURE:: Product with SKU (' . $sku . ') doesn\'t exist.');
                continue;
            }

            try {
                $this->updatePricesSql($sku, $price);
                $this->updatedCount++;

                //$message = $count . '. SUCCESS:: Updated SKU (' . $sku . ') with price (' . $price . ')';
            } catch (Exception $e) {
                $this->addError($count . ' row. ERROR:: While updating  SKU (' . $sku . ') with Price (' . $price . ') => ' . $e->getMessage());
            }
        }

        $this->addNotice(__('Total Price updated count: ' . $this->updatedCount));
    }

    /**
     * @param $sku
     * @return bool
     */
    private function checkIfSkuExists($sku): bool
    {
        if (empty($this->allSku)) {
            $connection = $this->connection->getConnection('core_read');
            $table = $this->connection->getTableName('catalog_product_entity');

            $sql = "SELECT sku FROM " . $table;
            $dataSql = $connection->fetchCol($sql);

            foreach ($dataSql as $d) {
                $this->allSku[$d] = $d;
            }
        }

        if (isset($this->allSku[$sku])) {
            return true;
        }

        return false;
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
     * @param string $sku
     * @param float $price
     * @param int $storeId
     */
    private function updatePricesSql(string $sku, float $price, int $storeId = 0)
    {
        $connection = $this->connection->getConnection('core_write');

        $entityId = $this->getIdFromSku($sku);
        $attributeId = $this->getAttributeIdPrice('price');
        $table = $this->connection->getTableName('catalog_product_entity_decimal');

        $sql = "INSERT INTO " . $table . " (attribute_id, store_id, entity_id, value) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE value=VALUES(value)";

        $connection->query(
            $sql,
            [
                $attributeId,
                $storeId,
                $entityId,
                $price
            ]
        );
    }

    /**
     * @param string $sku
     * @return int
     */
    private function getIdFromSku(string $sku): int
    {
        $connection = $this->connection->getConnection('core_read');
        $table = $this->connection->getTableName('catalog_product_entity');

        $sql = "SELECT entity_id FROM " . $table . " WHERE sku = ?";

        return $connection->fetchOne($sql, [$sku]);
    }

    /**
     * @param string $attributeCode
     * @return int
     */
    private function getAttributeIdPrice(string $attributeCode): int
    {
        if (empty($this->attributeIdPrice)) {
            $this->attributeIdPrice = $this->getAttributeId($attributeCode);
        }

        return $this->attributeIdPrice;
    }

    /**
     * @param string $attributeCode
     * @return int
     */
    private function getAttributeId(string $attributeCode): int
    {
        $connection = $this->connection->getConnection('core_read');
        $table = $this->connection->getTableName('eav_attribute');
        $entityTypeId = $this->getEntityTypeId();

        $sql = "SELECT attribute_id FROM " . $table . " WHERE entity_type_id = ? AND attribute_code = ?";

        return $connection->fetchOne($sql, [$entityTypeId, $attributeCode]);
    }

    /**
     * @return string|null
     */
    private function getEntityTypeId(): ?string
    {
        if (empty($this->entityTypeId)) {
            $connection = $this->connection->getConnection('core_read');
            $table = $this->connection->getTableName('eav_entity_type');

            $sql = "SELECT entity_type_id FROM " . $table . " WHERE entity_type_code = ?";
            $this->entityTypeId = $connection->fetchOne($sql, ['catalog_product']);
        }

        return $this->entityTypeId;
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
