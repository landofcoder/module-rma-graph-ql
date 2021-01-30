<?php
/**
 * Copyright © Landofcoder All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Lof\RmaGraphQl\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * Interface OrderRepositoryInterface
 * @package Lof\RmaGraphQl\Api
 */
interface OrderRepositoryInterface
{


    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return mixed
     */
    public function getListOrders(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria,
        $customerId
    );

}
