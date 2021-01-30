<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Lof\RmaGraphQl\Model\Resolver;

use Magento\CustomerGraphQl\Model\Customer\GetCustomer;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\Sales\Model\OrderFactory;

/**
 * Orders data reslover
 */
class Order implements ResolverInterface
{


    /**
     * @var OrderFactory
     */
    private $orderFactory;
    /**
     * @var GetCustomer
     */
    private $getCustomer;

    /**
     * Order constructor.
     * @param OrderFactory $orderFactory
     * @param GetCustomer $getCustomer
     */
    public function __construct(
        OrderFactory $orderFactory,
        GetCustomer $getCustomer

    ) {
        $this->orderFactory = $orderFactory;
        $this->getCustomer = $getCustomer;
    }

    /**
     * @inheritDoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        /** @var ContextInterface $context */
        if (false === $context->getExtensionAttributes()->getIsCustomer()) {
            throw new GraphQlAuthorizationException(__('The current customer isn\'t authorized.'));
        }
        $customer = $this->getCustomer->execute($context);
        $order = $this->orderFactory->create()->load($args['order_id']);

        if ($customer->getId() != $order->getCustomerId()) {
            throw new GraphQlAuthorizationException(__('You have no permission to view this Order.'));
        }
        $order->setData('order_number',$order->getIncrementId());
        $order->setData('id',$order->getId());
        $result = $order->getData();
        $result['model'] = $order;
        return $result;
    }
}
