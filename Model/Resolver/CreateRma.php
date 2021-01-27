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
     * CreateRma constructor.
     * @param DataProvider\Rma $rma
     * @param GetCustomer $getCustomer
     */
    public function __construct(
        DataProvider\Rma $rma,
        GetCustomer $getCustomer
    ) {
        $this->rmaProvider = $rma;
        $this->getCustomer = $getCustomer;
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
        if (!($args['rma_id']) || !isset($args['rma_id'])) {
            throw new GraphQlInputException(__('"rma_id" can\'t be empty.'));
        }

        $customer = $this->getCustomer->execute($context);
        $args['customer_id'] = $customer->getId();
        $args['customer_name'] = $customer->getFirstname().' '.$customer->getLastname();
        $args['customer_email'] = $customer->getEmail();
        return $this->rmaProvider->createRma($args);
    }


}
