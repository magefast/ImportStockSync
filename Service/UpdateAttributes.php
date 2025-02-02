<?php
/**
 * @author magefast@gmail.com www.magefast.com
 */

namespace Strekoza\ImportStockSync\Service;

use Exception;
use Magento\Catalog\Model\ResourceModel\Product\Action;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

class UpdateAttributes extends Action
{
    /**
     * @var array
     */
    private $attribute = [];

    /**
     * @var array
     */
    private $attributeBackendType = [];

    /**
     * @var array
     */
    private $attributeBackendTable = [];

    /**
     * @var array
     */
    private $tableBackend = [];

    /**
     * @var int
     */
    private $dbCountLoop = 0;

    /**
     * @var int
     */
    private $dbCountQueryProcessing = 100;

    /**
     * @param $entityId
     * @param $attrData
     * @param $storeId
     * @param $countRowToUpdate
     * @return $this
     * @throws Exception
     */
    public function updateAttributesCustom($entityId, $attrData, $storeId, $countRowToUpdate)
    {
        try {
            foreach ($attrData as $attrCode => $value) {
                $this->dbCountLoop++;

                $attribute = $this->getAttributeByCode($attrCode);
                if (!$attribute || !$attribute->getAttributeId()) {
                    continue;
                }

                // collect data for save
                $this->_saveAttributeValueCustom($storeId, $entityId, $attribute, $value);
            }

            if ($this->dbCountLoop % $this->dbCountQueryProcessing == 0) {
                if ($this->dbCountQueryProcessing <= $countRowToUpdate) {
                    $this->getConnection()->beginTransaction();
                    $this->_processAttributeValues();
                    $this->getConnection()->commit();
                }
            }

            if ($this->dbCountQueryProcessing > $countRowToUpdate) {
                $this->getConnection()->beginTransaction();
                $this->_processAttributeValues();
                $this->getConnection()->commit();
            }

        } catch (Exception $e) {
            $this->getConnection()->rollBack();
            throw $e;
        }

        return $this;
    }

    /**
     * @param $attrCode
     * @return false|AbstractAttribute|mixed
     * @throws LocalizedException
     */
    private function getAttributeByCode($attrCode)
    {
        if (!isset($this->attribute[$attrCode])) {
            $this->attribute[$attrCode] = $this->getAttribute($attrCode);
        }

        return $this->attribute[$attrCode];
    }

    /**
     * Insert or Update attribute data
     *
     * @param int $storeId
     * @param int $id
     * @param $attribute
     * @param $value
     * @return $this
     * @throws NoSuchEntityException
     */
    protected function _saveAttributeValueCustom(int $storeId, int $id, $attribute, $value)
    {
        $connection = $this->getConnection();

        $attributeId = $attribute->getAttributeId();

        if (!isset($this->tableBackend[$attributeId])) {
            $table = $attribute->getBackend()->getTable();
            $this->tableBackend[$attributeId] = $table;
        } else {
            $table = $this->tableBackend[$attributeId];
        }

        $entityId = $this->resolveEntityId($id, $table);

        /**
         * If we work in single store mode all values should be saved just
         * for default store id
         * In this case we clear all not default values
         */
        if ($this->_storeManager->hasSingleStore()) {
            $storeId = $this->getDefaultStoreId();
            $connection->delete(
                $table,
                [
                    'attribute_id = ?' => $attributeId,
                    $this->getLinkField() . ' = ?' => $entityId,
                    'store_id <> ?' => $storeId
                ]
            );
        }

        $data = new DataObject(
            [
                'attribute_id' => $attributeId,
                'store_id' => $storeId,
                $this->getLinkField() => $entityId,
                'value' => $this->_prepareValueForSaveUpdateAttributes($value, $attribute, $attributeId),
            ]
        );
        $bind = $this->_prepareDataForTable($data, $table);

        if ($attribute->isScopeStore()) {
            /**
             * Update attribute value for store
             */
            $this->_attributeValuesToSave[$table][] = $bind;
        } elseif ($attribute->isScopeWebsite() && $storeId != $this->getDefaultStoreId()) {
            /**
             * Update attribute value for website
             */
            $storeIds = $this->_storeManager->getStore($storeId)->getWebsite()->getStoreIds(true);
            foreach ($storeIds as $storeId) {
                $bind['store_id'] = (int)$storeId;
                $this->_attributeValuesToSave[$table][] = $bind;
            }
        } else {
            /**
             * Update global attribute value
             */
            $bind['store_id'] = $this->getDefaultStoreId();
            $this->_attributeValuesToSave[$table][] = $bind;
        }

        return $this;
    }

    /**
     * Prepare value for save
     *
     * @param mixed $value
     * @param AbstractAttribute $attribute
     * @param $attributeId
     * @return mixed
     */
    protected function _prepareValueForSaveUpdateAttributes($value, AbstractAttribute $attribute, $attributeId)
    {
        if (!isset($this->attributeBackendType[$attributeId])) {
            $type = $attribute->getBackendType();
            $this->attributeBackendType[$attributeId] = $type;
        } else {
            $type = $this->attributeBackendType[$attributeId];
        }

        if (($type == 'int' || $type == 'decimal' || $type == 'datetime') && $value === '') {
            $value = null;
        } elseif ($type == 'decimal') {
            $value = $this->_localeFormat->getNumber($value);
        }

        if (!isset($this->attributeBackendTable[$attributeId])) {
            $backendTable = $attribute->getBackendTable();
            $this->attributeBackendTable[$attributeId] = $backendTable;
        } else {
            $backendTable = $this->attributeBackendTable[$attributeId];
        }

        if (!isset(self::$_attributeBackendTables[$backendTable])) {
            self::$_attributeBackendTables[$backendTable] = $this->getConnection()->describeTable($backendTable);
        }
        $describe = self::$_attributeBackendTables[$backendTable];
        return $this->getConnection()->prepareColumnValue($describe['value'], $value);
    }
}