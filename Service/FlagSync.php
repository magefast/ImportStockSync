<?php
/**
 * @author magefast@gmail.com www.magefast.com
 */

namespace Strekoza\ImportStockSync\Service;

use Exception;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Flag;
use Magento\Framework\Flag\FlagResource;
use Magento\Framework\FlagFactory;

class FlagSync
{
    const FLAG = 'sync_stock_status_flag';

    /**
     * @var FlagResource
     */
    private $flagResource;

    /**
     * @var FlagFactory
     */
    private $flagFactory;

    /**
     * @param FlagResource $flagResource
     * @param FlagFactory $flagFactory
     */
    public function __construct(
        FlagResource $flagResource,
        FlagFactory  $flagFactory
    )
    {
        $this->flagResource = $flagResource;
        $this->flagFactory = $flagFactory;
    }

    /**
     * @param int $value
     * @throws LocalizedException
     */
    public function setFlag(int $value)
    {
        try {
            $flag = $this->getFlagObject();
            $flag->setFlagData($value);
            $this->flagResource->save($flag);
        } catch (Exception $exception) {
            throw new LocalizedException(__("The hash isn't saved."), $exception);
        }
    }

    /**
     * @return bool
     */
    public function get(): bool
    {
        $flag = $this->getFlagObject();
        return (bool)($flag->getFlagData() ?: 0);
    }

    /**
     * @return Flag
     */
    private function getFlagObject(): Flag
    {
        $flag = $this->flagFactory
            ->create(['data' => ['flag_code' => self::FLAG]]);
        $this->flagResource->load($flag, self::FLAG, 'flag_code');
        return $flag;
    }
}