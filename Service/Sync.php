<?php
/**
 * @author magefast@gmail.com www.magefast.com
 */

namespace Strekoza\ImportStockSync\Service;

use Exception;

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
     * @param Settings $settings
     * @param UpdateProduct $updateProduct
     * @param PrepareFileToImport $prepareFileToImport
     */
    public function __construct(
        Settings            $settings,
        UpdateProduct      $updateProduct,
        PrepareFileToImport $prepareFileToImport
    )
    {
        $this->settings = $settings;
        $this->updateProduct = $updateProduct;
        $this->prepareFileToImport = $prepareFileToImport;
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
    public function run()
    {
        $this->syncData();
    }

    /**
     * @throws Exception
     */
    private function syncData(): void
    {
        $file = $this->settings->getPathInternalFile();

        if (!file_exists($file)) {
            $this->errors[] = __('File Import not exist');
            return;
        }

        $csvData = $this->prepareFileToImport->execute($file);

        if (count($csvData) == 0) {
            $this->errors[] = __('Not rows to sync');
        }

        $this->updateProduct->update($csvData);

        $this->notices = array_merge($this->updateProduct->getNotices());
        $this->errors = array_merge($this->updateProduct->getErrors());
    }
}
