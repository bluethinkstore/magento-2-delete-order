<?php
/**
 * Copyright © BluethinkInc All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Bluethinkinc\OrderDelete\Controller\Adminhtml\Index;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Framework\Message\ManagerInterface;

class MassDelete extends \Magento\Backend\App\Action implements HttpPostActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Magento_Sales::sales';

    /**
     * @var Context
     */
    protected $context;

    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var messageManager
     */
    protected $messageManager;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param ManagerInterface $messageManager
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        Context $context,
        Filter $filter,
        ManagerInterface $messageManager,
        CollectionFactory $collectionFactory
    ) {
        $this->filter = $filter;
        $this->messageManager = $messageManager;
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context);
    }

    /**
     * Execute action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException|\Exception
     */
    public function execute()
    {
        try {
            $collection = $this->filter->getCollection($this->collectionFactory->create());
            $collectionSize = $collection->getSize();

            if ($collectionSize >= 1) {
                foreach ($collection as $data) {

                    // delete all invoices related to order
                    $invoices = $data->getInvoiceCollection();
                    if ($invoices->getSize() >= 1) {
                        foreach ($invoices as $invoice) {
                            $invoice->delete();
                        }
                    }

                    // delete all shipments related to data
                    $shipments = $data->getShipmentsCollection();
                    if ($shipments->getSize() >= 1) {
                        foreach ($shipments as $shipment) {
                            $shipment->delete();
                        }
                    }

                    // delete all creditmemos related to data
                    $creditmemos = $data->getCreditmemosCollection();
                    if ($creditmemos->getSize() >= 1) {
                        foreach ($creditmemos as $creditmemo) {
                            $creditmemo->delete();
                        }
                    }

                    // finally delete order data
                    $data->delete();
                }
            }

            // return with success message
            $this->messageManager->addSuccessMessage(
                __('A total of %1 record(s) have been deleted successfully!', $collectionSize)
            );
            return $this->resultRedirectFactory->create()->setPath('sales/order/index');

        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Something went wrong!'));
            return $this->resultRedirectFactory->create()->setPath('sales/order/index');
        }
    }
}
