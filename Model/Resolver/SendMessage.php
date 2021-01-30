<?php
declare(strict_types=1);

namespace Lof\RmaGraphQl\Model\Resolver;

use Lof\Rma\Model\RmaFactory;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\CustomerGraphQl\Model\Customer\GetCustomer;
use Magento\Sales\Model\Order;

/**
 * Class SendMessage
 * @package Lof\RmaGraphQl\Model\Resolver
 */
class SendMessage implements ResolverInterface
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
     * @var RmaFactory
     */
    private $rmaFactory;

    /**
     * CreateRma constructor.
     * @param DataProvider\Rma $rma
     * @param GetCustomer $getCustomer
     * @param RmaFactory $rmaFactory
     */
    public function __construct(
        DataProvider\Rma $rma,
        GetCustomer $getCustomer,
        RmaFactory $rmaFactory
    ) {
        $this->rmaProvider = $rma;
        $this->getCustomer = $getCustomer;
        $this->rmaFactory = $rmaFactory;
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

        $rma = $this->rmaFactory->create()->load($args['rma_id']);

        if ($rma->getCustomerId() != $customer->getId()) {
            throw new GraphQlAuthorizationException(__('You don\'t have permission to send message for this rma.'));
        }
        $args['customer_name'] = $customer->getFirstname().' '.$customer->getLastname();
        $args['customer_id'] = $customer->getId();

        return $this->rmaProvider->ConfirmShipping($args);
    }


}
