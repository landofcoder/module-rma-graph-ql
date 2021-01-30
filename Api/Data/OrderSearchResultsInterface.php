<?php


namespace Lof\RmaGraphQl\Api\Data;

/**
 * Interface OrderSearchResultsInterface
 * @package Lof\RmaGraphQl\Api\Data
 */
interface OrderSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{

    /**
     * @return \Magento\Framework\Api\ExtensibleDataInterface[]
     */
    public function getItems();


    /**
     * @param array $items
     * @return OrderSearchResultsInterface
     */
    public function setItems(array $items);
}
