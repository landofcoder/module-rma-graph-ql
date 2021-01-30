<?php

declare(strict_types=1);

namespace Lof\RmaGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Lof\Rma\Helper\Help as RmaHelper;

/**
 * Resolve Store Config information for SendFriend
 */
class RmaConfiguration implements ResolverInterface
{
    /**
     * @var RmaHelper
     */
    private $rmaHelper;

    /**
     * @param RmaHelper $rmaHelper
     */
    public function __construct(RmaHelper $rmaHelper)
    {
        $this->rmaHelper = $rmaHelper;
    }

    /**
     * @inheritDoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $store = $context->getExtensionAttributes()->getStore();
        $storeId = $store->getId();

        return [
            'return_address' => $this->rmaHelper->getConfig($storeId,'rma/general/return_address'),
            'is_active_frontend' => $this->rmaHelper->getConfig($storeId, 'rma/general/is_active_frontend'),
            'default_status' => $this->rmaHelper->getConfig($storeId, 'rma/general/default_status'),
            'default_user' => $this->rmaHelper->getConfig($storeId, 'rma/general/default_user'),
            'enable_guest_rma' => $this->rmaHelper->getConfig($storeId, 'rma/general/enable_guest_rma'),
            'is_gift_active' => $this->rmaHelper->getConfig($storeId, 'rma/general/is_gift_active'),
            'file_allowed_extensions' => $this->rmaHelper->getConfig($storeId, 'rma/general/file_allowed_extensions'),
            'file_size_limit' => $this->rmaHelper->getConfig($storeId, 'rma/general/file_size_limit'),
            'is_require_shipping_confirmation' => $this->rmaHelper->getConfig($storeId, 'rma/general/is_require_shipping_confirmation'),
            'shipping_confirmation_text' => $this->rmaHelper->getConfig($storeId, 'rma/general/shipping_confirmation_text'),
            'enable_bundle_rma_fronend' => $this->rmaHelper->getConfig($storeId, 'rma/general/enable_bundle_rma_fronend'),
            'enable_bundle_rma_backend' => $this->rmaHelper->getConfig($storeId, 'rma/general/enable_bundle_rma_backend'),
            'use_both_rma_type' => $this->rmaHelper->getConfig($storeId, 'rma/general/use_both_rma_type'),
            'return_period' => $this->rmaHelper->getConfig($storeId, 'rma/policy/return_period'),
            'allow_in_statuses' => $this->rmaHelper->getConfig($storeId, 'rma/policy/allow_in_statuses'),
            'return_only_shipped' => $this->rmaHelper->getConfig($storeId, 'rma/policy/return_only_shipped'),
            'is_active' => $this->rmaHelper->getConfig($storeId, 'rma/policy/is_active'),
            'sender_email' => $this->rmaHelper->getConfig($storeId, 'rma/notification/sender_email'),
            'customer_email_template' => $this->rmaHelper->getConfig($storeId, 'rma/notification/customer_email_template'),
            'admin_email_template' => $this->rmaHelper->getConfig($storeId, 'rma/notification/admin_email_template'),
            'rule_template' => $this->rmaHelper->getConfig($storeId, 'rma/notification/rule_template'),
            'send_email_bcc' => $this->rmaHelper->getConfig($storeId, 'rma/notification/send_email_bcc'),
            'enable_reason' => $this->rmaHelper->getConfig($storeId, 'rma/rmafields/enable_reason'),
            'enable_condition' => $this->rmaHelper->getConfig($storeId, 'rma/rmafields/enable_condition'),
            'enable_resolution' => $this->rmaHelper->getConfig($storeId, 'rma/rmafields/enable_resolution'),
        ];
    }
}
