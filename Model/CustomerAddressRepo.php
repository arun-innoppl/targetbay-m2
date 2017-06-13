<?php

/**
 * @author Targetbay Team
 * @copyright Copyright (c) 2016 Targetbay
 * @package Targetbay_Tracking
 */

namespace Targetbay\Tracking\Model;

use Targetbay\Tracking\Api\CustomerAddressRepoInterface;

/**
 * Defines the implementation class of the CustomerAddressRepoInterface.
 */
class CustomerAddressRepo implements CustomerAddressRepoInterface
{
    /**
     * @var \Magento\Customer\Api\Data\AddressSearchResultsInterfaceFactory
     */
    public $addressSearchResultsFactory;

    /**
     * @var \Magento\Customer\Model\AddressRegistry
     */
    public $addressRegistry;

    /**
     * @param \Magento\Customer\Model\AddressRegistry $addressRegistry
     * @param \Magento\Customer\Api\Data\AddressSearchResultsInterfaceFactory $addressSearchResultsFactory
     */
    public function __construct(
        \Magento\Customer\Model\AddressRegistry $addressRegistry,
        \Magento\Customer\Api\Data\AddressSearchResultsInterfaceFactory $addressSearchResultsFactory
    ) {
        $this->addressRegistry = $addressRegistry;
        $this->addressSearchResultsFactory = $addressSearchResultsFactory;
    }

    /**
     * Get the list of customers
     * @return CustomerAddressRepoInterface
     */
    public function getList(\Magento\Framework\Api\SearchCriteria $searchCriteria)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $customerAddressCollection = $objectManager->create('\Magento\Customer\Model\ResourceModel\Address\CollectionFactory');

        $searchResults = $this->addressSearchResultsFactory->create();
        $collection = $customerAddressCollection->create();

        // Add filters from root filter group to the collection.
        foreach ($searchCriteria->getFilterGroups() as $group) {
            $this->addFilterGroupToCollection($group, $collection);
        }
        $searchResults->setTotalCount($collection->getSize());
        /** @var SortOrder $sortOrder */
        foreach ((array) $searchCriteria->getSortOrders() as $sortOrder) {
            $field = $sortOrder->getField();
            $collection->addOrder(
                $field,
                ($sortOrder->getDirection() == SortOrder::SORT_ASC) ? 'ASC' : 'DESC'
            );
        }

        $collection->setCurPage($searchCriteria->getCurrentPage());
        $collection->setPageSize($searchCriteria->getPageSize());

        /** @var \Magento\Customer\Api\Data\AddressInterface[] $addresses */
        $addresses = [];

        /** @var \Magento\Customer\Model\Address $address */
        foreach ($collection->getItems() as $address) {
            $addresses[] = $this->getById($address->getId());
        }
        $searchResults->setItems($addresses);
        $searchResults->setSearchCriteria($searchCriteria);

        return $searchResults;
    }

    /**
     * Retrieve customer address.
     *
     * @param int $addressId
     * @return \Magento\Customer\Api\Data\AddressInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($addressId)
    {
        $address = $this->addressRegistry->retrieve($addressId);
        return $address->getDataModel();
    }
}
