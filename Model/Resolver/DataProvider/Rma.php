<?php
/**
 * Copyright © LandOfCoder All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Lof\RmaGraphQl\Model\Resolver\DataProvider;

use Exception;
use Lof\Rma\Api\Data\RmaInterface;
use Lof\Rma\Api\Data\RmaSearchResultsInterfaceFactory;
use Lof\Rma\Api\Repository\RmaRepositoryInterface;
use Lof\Rma\Helper\Data;
use Lof\Rma\Model\Attachment;
use Lof\Rma\Model\MessageFactory;
use Lof\Rma\Model\ResourceModel\Rma\CollectionFactory;
use Lof\Rma\Model\RmaFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Store\Model\StoreManagerInterface;
use Magento\User\Model\UserFactory;

/**
 * Class Rma
 * @package Lof\RmaGraphQl\Model\Resolver\DataProvider
 */
class Rma
{


    public function __construct(
        RmaRepositoryInterface $rmaRepository,
        CollectionFactory $rmaCollection,
        CollectionProcessorInterface $collectionProcessor,
        RmaSearchResultsInterfaceFactory $searchResultsFactory,
        RmaFactory $rmaFactory,
        UserFactory $userFactory,
        StoreManagerInterface $storeManagement,
        Data $helper,
        MessageFactory $messageFactory,
        Attachment $attachment
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
    }

    /**
     * @param $rma_id
     * @return RmaInterface
     */
    public function getRmaById($rma_id)
    {
        try {
            return $this->rmaRepository->getById($rma_id);
        } catch (LocalizedException $e) {
        }
    }

    /**
     * @param $criteria
     * @return \Lof\Rma\Api\Data\RmaSearchResultsInterface
     */
    public function getListRmas($criteria)
    {
        $collection = $this->rmaCollection->create();

        $this->collectionProcessor->process($criteria, $collection);

        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);

        $items = [];
        foreach ($collection as $key => $model) {
            $model->load($model->getRmaId());
            $items[$key] = $model->getData();
        }

        $searchResults->setItems($items);
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * @param $data
     * @return false|\Lof\Rma\Model\Rma
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @throws Exception
     */
    public function createRma($data)
    {

        if ($data) {
            $rmaModel = $this->rmaFactory->create();
            $category = $this->categoryRepository->get($data['category_id']);
            if (!$category) {
                throw new GraphQlInputException(__('Category Id does not exists'));
            }
            $user = $this->userFactory->create();
            $store = $this->storeManagement;
            $data['store_id'] = $store->getStore()->getId();
            $data['status_id'] = 1;
            $data['last_reply_name'] = $data['customer_name'];
            $data['reply_cnt'] = 0;
            $data['category'] = $category['title'];
            $data['namestore'] = $this->helper->getStoreName();
            $data['urllogin'] = $this->helper->getCustomerLoginUrl();
            $data['department_id'] = $this->helper->getDepartmentByCategory($data['category_id']);
            $data['status'] = $this->getStatus($data['status_id']);
            $department = $this->departmentRepository->get($data['department_id']);
            $data['department'] = $department->getTitle();
            $data['email_to'] = [];
            if (count($department) > 0) {
                foreach ($department['users'] as $key => $_user) {
                    $user->load($_user, 'user_id');
                    $data['email_to'][] = $user->getEmail();
                }
            }

            if ($this->isSpam($data)) return false;

            $rmaModel->setData($data)->save();
            if (count($data['email_to'])) {
                $this->sender->newRma($data);
            }
            return $rmaModel;
        }
    }

    /**
     * @param $status_id
     * @return \Magento\Framework\Phrase|string
     */
    protected function getStatus($status_id)
    {
        $data = '';
        if ($status_id == 0) {
            $data = __('Close');
        } elseif ($status_id == 1) {
            $data = __('Open');
        } elseif ($status_id == 2) {
            $data = __('Processing');
        } elseif ($status_id == 3) {
            $data = __('Done');
        }
        return $data;
    }

    /**
     * @param $data
     * @return bool|\Lof\Rma\Model\Message
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function SendMessageRma($data)
    {
        if ($data) {
            $messageModel = $this->messageFactory->create();
            if ($this->isSpam($data)) return false;
            $messageModel->setData($data)->save();
            $data = $this->updateData($data);
            if ($this->helper->getConfig('email_settings/enable_testmode')) {
                $this->sender->newMessage($data);
            }

            $attachmentData = [];
            $attachmentData['message_id'] = $messageModel->getId();
            $attachmentData['body'] = $data['attachment'];
            $attachmentData['name'] = $data['attachment_name'];
            $this->attachment->setData($attachmentData)->save();
            return $messageModel;
        }
    }

    /**
     * @param $data
     * @return Like
     * @throws Exception
     */
    public function LikeRma($data)
    {
        if ($data) {
            $like = $this->_like->load($data['message_id'], 'message_id');
            $like->setData('customer_id', $data['customer_id'])->setData('message_id', $data['message_id'])->save();
            return $like;
        }
    }

    /**
     * @param $data
     * @return \Lof\Rma\Model\Rma
     */
    public function RateRma($data)
    {
        if ($data) {
            $rma = $this->rmaFactory->create()->load($data['rma_id']);
            $rma->setRating($data['rating'])->save();
            return $rma;
        }
    }

    /**
     * @param $data
     * @return bool
     */
    public function isSpam($data)
    {
        foreach ($this->spamCollection->addFieldToFilter('is_active', 1) as $key => $spam) {
            if ($this->helper->checkSpam($spam, $data)) {
                return true;
            }
        }
    }

    /**
     * @param $data
     * @return mixed
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function updateData($data)
    {
        $store = $this->storeManagement;
        $rma = $this->rmaRepository->get($data['rma_id']);
        $category = $this->categoryRepository->get($rma['category_id']);
        $data['namerma'] = $rma['subject'];
        $data['category'] = $category['title'];
        $data['store_id'] = $store->getStore()->getId();
        $data['namestore'] = $this->helper->getStoreName();
        $data['urllogin'] = $this->helper->getCustomerLoginUrl();
        $user = $this->userFactory->create();
        $department = $this->departmentFactory->create();
        foreach ($department->getCollection() as $key => $_department) {
            $dataDepartment = $department->load($_department->getDepartmentId())->getData();
            if (in_array($rma['category_id'], $dataDepartment['category_id']) && $dataDepartment['is_active'] == 1 && (in_array($data['store_id'], $dataDepartment['store_id']) || in_array(0, $dataDepartment['store_id']))) {
                $data['email_to'] = [];
                foreach ($dataDepartment['users'] as $key => $_user) {
                    $user->load($_user, 'user_id');
                    $data['email_to'][] = $user->getEmail();
                }
            }
        }
        return $data;
    }
}

