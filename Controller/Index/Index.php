<?php
/**
 * @author magefast@gmail.com www.magefast.com
 */

namespace Strekoza\ImportStockSync\Controller\Index;

ini_set("memory_limit", '-1');
ini_set("set_time_limit", 600);
ini_set("max_execution_time", 600);
ini_set("max_input_time", 600);

use Exception;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Strekoza\ImportStockSync\Service\Sync as ServiceSync;

class Index extends Action
{
    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var ServiceSync
     */
    private $serviceSync;

    /**
     * @param JsonFactory $resultJsonFactory
     * @param Context $context
     * @param ServiceSync $serviceSync
     */
    public function __construct(JsonFactory $resultJsonFactory, Context $context, ServiceSync $serviceSync)
    {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->serviceSync = $serviceSync;
    }

    /**
     * @throws Exception
     */
    public function execute()
    {
        $this->serviceSync->run();

        $result = [];
        $result['result'] = $this->serviceSync->getNotices();
        $result['errors'] = $this->serviceSync->getErrors();

        $resultJson = $this->resultJsonFactory->create();

        return $resultJson->setData($result);
    }
}