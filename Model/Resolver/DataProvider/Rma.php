<?php
/**
 * Copyright © LandOfCoder All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Lof\RmaGraphQl\Model\Resolver\DataProvider;

use Lof\Rma\Api\Data\RmaInterface;
use Lof\Rma\Api\Data\RmaSearchResultsInterfaceFactory;
use Lof\Rma\Api\Repository\RmaRepositoryInterface;
use Lof\Rma\Helper\Data;
use Lof\Rma\Helper\Help;
use Lof\Rma\Helper\Mail;
use Lof\Rma\Model\AttachmentFactory;
use Lof\Rma\Model\Condition;
use Lof\Rma\Model\Item;
use Lof\Rma\Model\ItemFactory;
use Lof\Rma\Model\MessageFactory;
use Lof\Rma\Model\Reason;
use Lof\Rma\Model\Resolution;
use Lof\Rma\Model\ResourceModel\Rma\CollectionFactory;
use Lof\Rma\Model\RmaFactory;
use Lof\Rma\Model\Status;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Model\Order\Address\Renderer;
use Magento\Sales\Model\OrderFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\User\Model\UserFactory;

/**
 * Class Rma
 * @package Lof\RmaGraphQl\Model\Resolver\DataProvider
 */
class Rma
{


    /**
     * @var RmaRepositoryInterface
     */
    private $rmaRepository;
    /**
     * @var CollectionFactory
     */
    private $rmaCollection;
    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;
    /**
     * @var RmaSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;
    /**
     * @var RmaFactory
     */
    private $rmaFactory;
    /**
     * @var UserFactory
     */
    private $userFactory;
    /**
     * @var StoreManagerInterface
     */
    private $storeManagement;
    /**
     * @var Help
     */
    private $helper;
    /**
     * @var MessageFactory
     */
    private $messageFactory;
    /**
     * @var AttachmentFactory
     */
    private $attachment;
    /**
     * @var OrderFactory
     */
    private $orderFactory;
    /**
     * @var Status
     */
    private $rmaStatus;
    /**
     * @var Renderer
     */
    private $addressRenderer;
    /**
     * @var Item
     */
    private $rmaItem;
    /**
     * @var Reason
     */
    private $reason;
    /**
     * @var Condition
     */
    private $condition;
    /**
     * @var Resolution
     */
    private $resolution;
    /**
     * @var \Magento\Sales\Model\Order\Item
     */
    private $orderItem;
    /**
     * @var ProductRepository
     */
    private $productRepository;
    /**
     * @var ItemFactory
     */
    private $itemFactory;
    /**
     * @var Data
     */
    private $helperData;
    /**
     * @var Mail
     */
    private $rmaMail;

    /**
     * Rma constructor.
     * @param RmaRepositoryInterface $rmaRepository
     * @param CollectionFactory $rmaCollection
     * @param CollectionProcessorInterface $collectionProcessor
     * @param RmaSearchResultsInterfaceFactory $searchResultsFactory
     * @param RmaFactory $rmaFactory
     * @param UserFactory $userFactory
     * @param StoreManagerInterface $storeManagement
     * @param Help $helper
     * @param MessageFactory $messageFactory
     * @param AttachmentFactory $attachment
     * @param OrderFactory $orderFactory
     * @param Status $status
     * @param Renderer $addressRenderer
     * @param Item $rmaItem
     * @param Reason $reason
     * @param Condition $condition
     * @param Resolution $resolution
     * @param \Magento\Sales\Model\Order\Item $orderItem
     * @param ProductRepository $productRepository
     * @param ItemFactory $itemFactory
     * @param Data $helperData
     * @param Mail $helperMail
     */
    public function __construct(
        RmaRepositoryInterface $rmaRepository,
        CollectionFactory $rmaCollection,
        CollectionProcessorInterface $collectionProcessor,
        RmaSearchResultsInterfaceFactory $searchResultsFactory,
        RmaFactory $rmaFactory,
        UserFactory $userFactory,
        StoreManagerInterface $storeManagement,
        Help $helper,
        MessageFactory $messageFactory,
        AttachmentFactory $attachment,
        OrderFactory $orderFactory,
        Status $status,
        Renderer $addressRenderer,
        Item $rmaItem,
        Reason $reason,
        Condition $condition,
        Resolution $resolution,
        \Magento\Sales\Model\Order\Item $orderItem,
        ProductRepository $productRepository,
        ItemFactory $itemFactory,
        Data $helperData,
        Mail $helperMail
    )
    {
        $this->rmaRepository = $rmaRepository;
        $this->rmaCollection = $rmaCollection;
        $this->collectionProcessor = $collectionProcessor;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->rmaFactory = $rmaFactory;
        $this->userFactory = $userFactory;
        $this->storeManagement = $storeManagement;
        $this->helper = $helper;
        $this->messageFactory = $messageFactory;
        $this->attachment = $attachment;
        $this->orderFactory = $orderFactory;
        $this->rmaStatus = $status;
        $this->addressRenderer = $addressRenderer;
        $this->rmaItem = $rmaItem;
        $this->reason = $reason;
        $this->condition = $condition;
        $this->resolution = $resolution;
        $this->orderItem = $orderItem;
        $this->productRepository = $productRepository;
        $this->itemFactory = $itemFactory;
        $this->helperData = $helperData;
        $this->rmaMail = $helperMail;

    }

    /**
     * @param $rma_id
     * @return RmaInterface
     * @throws LocalizedException
     */
    public function getRmaById($rma_id)
    {
        $data = $this->rmaRepository->getById($rma_id);
        return $this->getRmaData($data);
    }

    /**
     * @param $criteria
     * @return \Lof\Rma\Api\Data\RmaSearchResultsInterface
     */
    public function getListRmas($criteria, $customerId)
    {
        $collection = $this->rmaCollection->create()->addFieldToFilter('main_table.customer_id', $customerId);

        $this->collectionProcessor->process($criteria, $collection);

        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);

        $items = [];
        foreach ($collection as $key => $model) {
            $data = $model->getData();
            $items[$key] = $this->getRmaData($data);
        }

        $searchResults->setItems($items);
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * @param $rma
     */
    protected function getRmaData($data)
    {
        $orderId = $data['order_id'];
        $rmaId = $data['rma_id'];
        if ($orderId) {
            $order = $this->orderFactory->create()->load($orderId);
            $data['order_date'] = $order->getCreatedAt();
            $data['order_status'] = $order->getStatus();
            $data['shipping_address'] = $this->getAddress($order);
        }
        $rmaStatus = $this->rmaStatus->load($data['status_id']);
        $data['status'] = $rmaStatus->getName();
        $data['items'] = $this->getRmaItems($rmaId);
        $data['messages'] = $this->getRmaMessage($rmaId);
        return $data;
    }

    /**
     * @param $rmaId
     * @return array
     */
    protected function getRmaItems($rmaId)
    {
        $rmaItems = $this->rmaItem->getCollection()->addFieldToFilter('rma_id', $rmaId);
        $data = [];
        foreach ($rmaItems as $key => $item) {
            $dataItem = $item->getData();
            $dataItem['reason'] = $this->reason->load($item->getReasonId())->getName();
            $dataItem['condition'] = $this->condition->load($item->getConditionId())->getName();
            $dataItem['reason'] = $this->reason->load($item->getReasonId())->getName();
            $orderItem = $this->orderItem->load($item->getOrderItemId());
            $dataItem['product_name'] = $orderItem->getName();
            $dataItem['product_id'] = $orderItem->getProductId();
            $dataItem['SKU'] = $orderItem->getSku();
            if (isset($orderItem->getData('product_options')['attributes_info'])) {
                foreach ($orderItem->getData('product_options')['attributes_info'] as $key2 => $attributes_info) {
                    $dataItem['product_options'][$key2]['label'] = $attributes_info['label'];
                    $dataItem['product_options'][$key2]['value'] = $attributes_info['value'];
                }
            }
            $data[$key] = $dataItem;
        }
        return $data;
    }

    /**
     * @param $rmaId
     * @return array
     */
    protected function getRmaMessage($rmaId)
    {
        $messages = $this->messageFactory->create()->getCollection()->addFieldToFilter('rma_id', $rmaId);
        $data = [];
        foreach ($messages as $key => $message) {
            $data[$key] = $message->getData();
        }
        return $data;
    }

    /**
     * @param $order
     * @return string|null
     */
    protected function getAddress($order)
    {
        if ($order->getShippingAddress()) {
            $address = $order->getShippingAddress();
        } else {
            $address = $order->getBillingAddress();
        }
        return $this->addressRenderer->format($address, 'html');
    }


    /**
     * @param $data
     * @return bool
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function createRma($data)
    {
        if (!$this->helperData->validate($data)) {
            return false;
        }
        $rmaData = $data;
        unset($rmaData['items']);
        $rma = $this->rmaFactory->create();
        if (isset($rmaData['street2']) && $rmaData['street2'] != '') {
            $rmaData['street'] .= "\n" . $rmaData['street2'];
            unset($rmaData['street2']);
        }
        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->orderFactory->create()->load((int)$rmaData['order_id']);
        $rma->setCustomerId($order->getCustomerId());
        $rma->setStoreId($order->getStoreId());
        $rma->setStatusId($this->helper->getConfig($store = null, 'rma/general/default_status'));
        $rma->addData($rmaData);
        $rma->save();

        $itemdatas = $data['items'];
        $this->createItems($itemdatas, $order->getItemsCollection(), $rma);
        if ((isset($data['reply']) && $data['reply'] != '') || (!empty($data['attachment']) && !empty($data['attachment_name']))) {

            $message = $this->messageFactory->create();
            $message->setRmaId($rma->getRmaId())
                ->setText($data['reply'], false);

            if (!isset($data['isNotified'])) {
                $data['isNotified'] = 1;
            }
            if (!isset($data['isVisible'])) {
                $data['isVisible'] = 1;
            }
            $message->setIsCustomerNotified($data['isNotified']);
            $message->setIsVisibleInFrontend($data['isVisible']);
            $message->setCustomerId($order->getId())
                ->setCustomerName($order->getCustomerName());

            $message->save();
            $rma->setLastReplyName($order->getCustomerName())
                ->setIsAdminRead($order->getCustomer() instanceof \Magento\User\Model\User);

            $this->rmaRepository->save($rma);
            $this->attachFile($data, $message->getMessageId());
            if ($message->getIsCustomerNotified()) {
                $this->rmaMail->sendNotificationCustomer($rma, $message);
            }
            return $this->getRmaData($rma->getData());
        }
    }

    /**
     * @param $data
     * @param $messageId
     * @throws \Exception
     */
    public function attachFile($data, $messageId)
    {
        if (isset($data['attachment'])) {
            $attachment = $data['attachment'];
            $attachmentModel = $this->attachment->create();
            $content = @file_get_contents(addslashes($attachment['body']));
            $attachmentModel
                ->setItemType('message')
                ->setItemId($messageId)
                ->setName($attachment['name'])
                ->setSize($attachment['size'])
                ->setBody($content)
                ->setType($attachment['type'])
                ->save();
        }
    }

    /**
     * @param $data
     * @return mixed
     * @throws LocalizedException
     */
    public function ConfirmShipping($data)
    {
        $id = (isset($data['rma_id']) && $data['rma_id'] !== null) ? (int)$data['rma_id'] : 0;
        if ($data && $id) {
            $rma = $this->rmaRepository->getById($id);
            $customerName = $data['customer_name'];
            $customerId = $data['customer_id'];

            if (isset($data['shipping_confirmation'])) {
                $messagetext = __('I confirm that I have sent the package to the RMA department.');
                $rma->setStatusId(4); //4: package sent;

            } else {
                $messagetext = isset($data['reply']) ? $data['reply'] : '';
            }

            if ($messagetext) {
                $dataMessage = [
                    'isNotifyAdmin' => 1,
                    'isNotified' => 0,
                ];
                $message = $this->messageFactory->create();
                $message->setRmaId($id)
                    ->setText($messagetext, false);

                if (!isset($dataMessage['isNotified'])) {
                    $dataMessage['isNotified'] = 1;
                }
                if (!isset($dataMessage['isVisible'])) {
                    $dataMessage['isVisible'] = 1;
                }
                $message->setIsCustomerNotified($dataMessage['isNotified']);
                $message->setIsVisibleInFrontend($dataMessage['isVisible']);
                $message->setCustomerId($customerId)
                    ->setCustomerName($customerName);
                $message->save();

                $rma->setLastReplyName($customerName)
                    ->setIsAdminRead(0);
                $this->rmaRepository->save($rma);

                $this->attachFile($data, $message->getMessageId());

                if ($message->getIsCustomerNotified()) {
                    $this->rmaMail->sendNotificationCustomer($rma, $message);
                }

            }
            return $this->getRmaData($rma->getData());
        }
    }

    public function createItems($itemdatas, $itemCollection, $rma)
    {
        foreach ($itemdatas as $item) {
            if (isset($item['reason_id']) && !(int)$item['reason_id']) {
                unset($item['reason_id']);
                $item['qty_requested'] = 0;
            }
            if (isset($item['resolution_id']) && !(int)$item['resolution_id']) {
                unset($item['resolution_id']);
            }
            if (isset($item['condition_id']) && !(int)$item['condition_id']) {
                unset($item['condition_id']);
            }
            $item['order_id'] = $rma->getOrderId();

            $orderItem = $itemCollection->getItemById($item['order_item_id']);
            if ($orderItem) {
                $productId = $orderItem->getProductId();
                if (!$productId) {
                    $product = $this->productRepository->get($orderItem->getSku());
                    $productId = $product->getId();
                }
                $item['product_id'] = $productId;
            }
            $itemdatas[$item['order_item_id']] = $item;
        }
        foreach ($itemdatas as $item) {
            $items = $this->itemFactory->create();
            if (isset($item['item_id']) && $item['item_id']) {
                $items->load((int)$item['item_id']);
            }
            unset($item['item_id']);
            $items->addData($item)
                ->setRmaId($rma->getId());
            $items->save();
        }
    }
}

