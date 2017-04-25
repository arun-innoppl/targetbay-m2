<?php

namespace Targetbay\Tracking\Model\System\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class Page implements ArrayInterface
{
    const ALL_PAGES = 'all';
    const PAGE_VISIT = 'page-visit';
    const PRODUCT_VIEW = 'product-view';
    const CATEGORY_VIEW = 'category-view';
    const DELETE_PRODUCT = "delete-product";
    const UPDATE_PRODUCT = 'update-product';
    const ADD_PRODUCT = 'add-product';
    const CREATE_ACCOUNT = 'create-account';
    const ADMIN_ACTIVATE_ACCOUNT = 'admin-activate-customer-account';
    const LOGIN = 'login';
    const LOGOUT = 'logout';
    const ADD_TO_CART = 'add-to-cart';
    const REMOVE_CART = 'remove-to-cart';
    const UPDATE_CART = 'update-cart';
    const ORDER_ITEMS = 'ordered-items';
    const BILLING = 'billing';
    const SHIPPING = 'shipping';
    const PAGE_REFERRAL = 'referrer';
    const CHECKOUT = 'checkout';
    const CATALOG_SEARCH = 'searched';
    const WISH_LIST = 'wishlist';
    const UPDATE_WISH_LIST = 'update-wishlist';
    const REMOVE_WISH_LIST = 'remove-wishlist';

    /**
     * Page Options configurations
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::ALL_PAGES,
                'label' => __('All Pages')
            ],
            [
                'value' => self::ADD_PRODUCT,
                'label' => __('Add Product')
            ],
            [
                'value' => self::DELETE_PRODUCT,
                'label' => __('Delete Product')
            ],
            [
                'value' => self::UPDATE_PRODUCT,
                'label' => __('Update Product')
            ],
            [
                'value' => self::PAGE_VISIT,
                'label' => __('Page Visit')
            ],
            [
                'value' => self::CATEGORY_VIEW,
                'label' => __('Category View')
            ],
            [
                'value' => self::PRODUCT_VIEW,
                'label' => __('Product View')
            ],
            [
                'value' => self::CATALOG_SEARCH,
                'label' => __('Search Page')
            ],
            [
                'value' => self::CREATE_ACCOUNT,
                'label' => __('Create Account')
            ],
            [
                'value' => self::LOGIN,
                'label' => __('Login')
            ],
            [
                'value' => self::LOGOUT,
                'label' => __('Logout')
            ],
            [
                'value' => self::ADD_TO_CART,
                'label' => __('Add to cart')
            ],
            [
                'value' => self::UPDATE_CART,
                'label' => __('Update cart')
            ],
            [
                'value' => self::REMOVE_CART,
                'label' => __('Remove Cart')
            ],
            [
                'value' => self::CHECKOUT,
                'label' => __('Checkout')
            ],
            [
                'value' => self::BILLING,
                'label' => __('Billing page')
            ],
            [
                'value' => self::SHIPPING,
                'label' => __('Shipping page')
            ],
            [
                'value' => self::ORDER_ITEMS,
                'label' => __('Order page')
            ],
            [
                'value' => self::PAGE_REFERRAL,
                'label' => __('Referrer page')
            ],
            [
                'value' => self::WISH_LIST,
                'label' => __('Wishlist page')
            ],
            [
                'value' => self::UPDATE_WISH_LIST,
                'label' => __('Update Wishlist')
            ],
            [
                'value' => self::REMOVE_WISH_LIST,
                'label' => __('Delete Wishlist')
            ]
        ];
    }
}
