<?php
/**
 * Copyright © LandOfCoder All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Lof\RmaGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

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
     * @param DataProvider\Rma $RmaRepository
     */
    public function __construct(
        DataProvider\Rma $RmaRepository
    ) {
        $this->rmaProvider = $RmaRepository;
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
        return $this->rmaProvider->getRmaById($args['rma_id']);
    }
}

