<?php
declare(strict_types=1);

namespace Lof\RmaGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\CustomerGraphQl\Model\Customer\GetCustomer;
use Magento\Sales\Model\Order;

/**
 * Class CreateRma
 * @package Lof\RmaGraphQl\Model\Resolver
 */
class CreateRma implements ResolverInterface
{

    /**
     * @var DataProvider\Rma
     */
    private $rmaProvider;
    /**
     * @var GetCustomer
     */
    private $getCustomer;
    /**
     * @var Order
     */
    private $order;

    /**
     * CreateRma constructor.
     * @param DataProvider\Rma $rma
     * @param GetCustomer $getCustomer
     * @param Order $order
     */
    public function __construct(
        DataProvider\Rma $rma,
        GetCustomer $getCustomer,
        Order $order
    ) {
        $this->rmaProvider = $rma;
        $this->getCustomer = $getCustomer;
        $this->order = $order;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {

        /** @var ContextInterface $context */
        if (!$context->getExtensionAttributes()->getIsCustomer()) {
            throw new GraphQlAuthorizationException(__('The current customer isn\'t authorized.'));
        }

        $args = $args['input'];

        $customer = $this->getCustomer->execute($context);
        if (!($args['order_id']) || !isset($args['order_id'])) {
            throw new GraphQlInputException(__('"order_id" can\'t be empty.'));
        }

        if (!($args['items']) || !isset($args['items'])) {
            throw new GraphQlInputException(__('"Items" can\'t be empty.'));
        }

        $order = $this->order->load($args['order_id']);

        if ($order->getCustomerId() != $customer->getId()) {
            throw new GraphQlAuthorizationException(__('You don\'t have permission to return this order.'));
        }

        $args['customer_id'] = $order->getCustomerId();
        return $this->rmaProvider->createRma($args);
    }


}
