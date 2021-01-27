<?php

declare(strict_types=1);

namespace Lof\RmaGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Lof\Rma\Helper\Data as RmaHelper;

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
            'return_address' => $this->rmaHelper->getConfig('general/return_address', $storeId),
            'is_active_frontend' => $this->rmaHelper->getConfig('general/is_active_frontend', $storeId),
            'default_status' => $this->rmaHelper->getConfig('general/default_status', $storeId),
            'default_user' => $this->rmaHelper->getConfig('general/default_user', $storeId),
            'enable_guest_rma' => $this->rmaHelper->getConfig('general/enable_guest_rma', $storeId),
            'is_gift_active' => $this->rmaHelper->getConfig('general/is_gift_active', $storeId),
            'file_allowed_extensions' => $this->rmaHelper->getConfig('general/file_allowed_extensions', $storeId),
            'file_size_limit' => $this->rmaHelper->getConfig('general/file_size_limit', $storeId),
            'is_require_shipping_confirmation' => $this->rmaHelper->getConfig('general/is_require_shipping_confirmation', $storeId),
            'shipping_confirmation_text' => $this->rmaHelper->getConfig('general/shipping_confirmation_text', $storeId),
            'enable_bundle_rma_fronend' => $this->rmaHelper->getConfig('general/enable_bundle_rma_fronend', $storeId),
            'enable_bundle_rma_backend' => $this->rmaHelper->getConfig('general/enable_bundle_rma_backend', $storeId),
            'use_both_rma_type' => $this->rmaHelper->getConfig('general/use_both_rma_type', $storeId),
            'return_period' => $this->rmaHelper->getConfig('policy/return_period', $storeId),
            'allow_in_statuses' => $this->rmaHelper->getConfig('policy/allow_in_statuses', $storeId),
            'return_only_shipped' => $this->rmaHelper->getConfig('policy/return_only_shipped', $storeId),
            'is_active' => $this->rmaHelper->getConfig('policy/is_active', $storeId),
            'sender_email' => $this->rmaHelper->getConfig('notification/sender_email', $storeId),
            'customer_email_template' => $this->rmaHelper->getConfig('notification/customer_email_template', $storeId),
            'admin_email_template' => $this->rmaHelper->getConfig('notification/admin_email_template', $storeId),
            'rule_template' => $this->rmaHelper->getConfig('notification/rule_template', $storeId),
            'send_email_bcc' => $this->rmaHelper->getConfig('notification/send_email_bcc', $storeId),
            'enable_reason' => $this->rmaHelper->getConfig('rmafields/enable_reason', $storeId),
            'enable_condition' => $this->rmaHelper->getConfig('rmafields/enable_condition', $storeId),
            'enable_resolution' => $this->rmaHelper->getConfig('rmafields/enable_resolution', $storeId),
        ];
    }
}
