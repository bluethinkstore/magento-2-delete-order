<?php

/**
 * Copyright © Bluethinkinc All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Bluethinkinc\OrderDelete\Controller\Adminhtml\Index;

use Magento\Framework\Controller\ResultFactory;

class Delete extends \Magento\Framework\App\Action\Action
{
    /**
     * @var $request
     */
    private $request;

    /**
     * @var $context
     */
    private $context;

    /**
     * @var $order
     */
    protected $order;

    /**
     * @var $orderRepository
     */
    protected $orderRepository;

    /**
     * @var $messageManager
     */
    protected $messageManager;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Sales\Model\OrderFactory $order
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Sales\Model\OrderFactory $order,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        parent::__construct($context);
        $this->request = $request;
        $this->order = $order->create();
        $this->orderRepository = $orderRepository;
        $this->messageManager = $messageManager;
    }

    /**
     * Delete all details
     *
     * @return resultRedirectFactory
     */
    public function execute()
    {
        try {
            $orderId = $this->request->getParam('order_id');
            if (isset($orderId) && $orderId!='') {
                $order = $this->orderRepository->get($orderId);
                $orderIncrementId = $order->getIncrementId();
                $order = $this->order->loadByIncrementId($orderIncrementId);

                // delete all invoices related to order
                $invoices = $order->getInvoiceCollection();
                if (is_array($invoices)) {
                    foreach ($invoices as $invoice) {
                        $invoice->delete();
                    }
                }

                // delete all shipments related to order
                $shipments = $order->getShipmentsCollection();
                if (is_array($shipments)) {
                    foreach ($shipments as $shipment) {
                        $shipment->delete();
                    }
                }

                // delete all creditmemos related to order
                $creditmemos = $order->getCreditmemosCollection();
                if (is_array($creditmemos)) {
                    foreach ($creditmemos as $creditmemo) {
                        $creditmemo->delete();
                    }
                }

                // finally delete order
                $order->delete();

                // return with success message
                $this->messageManager->addSuccessMessage(__('Order %1 deleted successfully!', $orderIncrementId));
                return $this->resultRedirectFactory->create()->setPath('sales/order/index');
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Something went wrong!'));
            return $this->resultRedirectFactory->create()->setPath('sales/order/index');
        }
    }
}
