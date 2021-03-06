<?php
/**
 * Copyright © LandOfCoder All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Lof\RmaGraphQl\Model\Resolver;

use Magento\CustomerGraphQl\Model\Customer\GetCustomer;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\GraphQl\Model\Query\ContextInterface;

/**
 * Class Rma
 * @package Lof\RmaGraphQl\Model\Resolver
 */
class Rma implements ResolverInterface
{

    /**
     * @var DataProvider\Rma
     */
    private $rmaProvider;
    /**
     * @var \Lof\Rma\Model\Rma
     */
    private $rmaModel;
    /**
     * @var GetCustomer
     */
    private $getCustomer;

    /**
     * @param DataProvider\Rma $RmaRepository
     * @param \Lof\Rma\Model\Rma $rma
     * @param GetCustomer $getCustomer
     */
    public function __construct(
        DataProvider\Rma $RmaRepository,
        \Lof\Rma\Model\Rma $rma,
        GetCustomer $getCustomer

    ) {
        $this->rmaProvider = $RmaRepository;
        $this->rmaModel = $rma;
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
        if (!($args['rma_id']) || !isset($args['rma_id'])) {
            throw new GraphQlInputException(__('"rma_id" value can\'t be empty'));
        }
        $customer = $this->getCustomer->execute($context);
        if ($this->rmaModel->load($args['rma_id'])->getCustomerId() != $customer->getId()) {
            throw new GraphQlAuthorizationException(__('You have no permission to view this Rma.'));
        }
        return $this->rmaProvider->getRmaById($args['rma_id']);
    }
}

