<?php

/**
 * @author Targetbay
 * @copyright Copyright (c) 2016 TargetBay
 * @package Targetbay_Tracking
 */

namespace Targetbay\Tracking\Model\Customer;

class Subscriber extends \Magento\Newsletter\Model\Subscriber
{
    /**
     * Sends out confirmation success email
     *
     * @return $this
     */
    public function sendConfirmationSuccessEmail()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $trackingHelper = $objectManager->get('Targetbay\Tracking\Helper\Data');

        $emailStatus = $trackingHelper->getEmailStatus();

        if ($emailStatus == 1) {
            return false;
        }

        if ($this->getImportMode()) {
            return $this;
        }

        if (!$this->_scopeConfig->getValue(
                self::XML_PATH_SUCCESS_EMAIL_TEMPLATE,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            ) || !$this->_scopeConfig->getValue(
                self::XML_PATH_SUCCESS_EMAIL_IDENTITY,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            )
        ) {
            return $this;
        }

        $this->inlineTranslation->suspend();

        $this->_transportBuilder->setTemplateIdentifier(
            $this->_scopeConfig->getValue(
                self::XML_PATH_SUCCESS_EMAIL_TEMPLATE,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            )
        )->setTemplateOptions(
            [
                'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                'store' => $this->_storeManager->getStore()->getId(),
            ]
        )->setTemplateVars(
            ['subscriber' => $this]
        )->setFrom(
            $this->_scopeConfig->getValue(
                self::XML_PATH_SUCCESS_EMAIL_IDENTITY,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            )
        )->addTo(
            $this->getEmail(),
            $this->getName()
        );
        $transport = $this->_transportBuilder->getTransport();
        $transport->sendMessage();

        $this->inlineTranslation->resume();

        return $this;
    }

    /**
     * Sends out un-subscription email
     *
     * @return $this
     */
    public function sendUnsubscriptionEmail()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $trackingHelper = $objectManager->get('Targetbay\Tracking\Helper\Data');

        $trackingHelper->debug('sendUnsubscriptionEmail');
        $emailStatus = $trackingHelper->getEmailStatus();

        if ($emailStatus) {
            return false;
        }

        if ($this->getImportMode()) {
            return $this;
        }
        if (!$this->_scopeConfig->getValue(
                self::XML_PATH_UNSUBSCRIBE_EMAIL_TEMPLATE,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            ) || !$this->_scopeConfig->getValue(
                self::XML_PATH_UNSUBSCRIBE_EMAIL_IDENTITY,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            )
        ) {
            return $this;
        }

        $this->inlineTranslation->suspend();

        $this->_transportBuilder->setTemplateIdentifier(
            $this->_scopeConfig->getValue(
                self::XML_PATH_UNSUBSCRIBE_EMAIL_TEMPLATE,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            )
        )->setTemplateOptions(
            [
                'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                'store' => $this->_storeManager->getStore()->getId(),
            ]
        )->setTemplateVars(
            ['subscriber' => $this]
        )->setFrom(
            $this->_scopeConfig->getValue(
                self::XML_PATH_UNSUBSCRIBE_EMAIL_IDENTITY,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            )
        )->addTo(
            $this->getEmail(),
            $this->getName()
        );
        $transport = $this->_transportBuilder->getTransport();
        $transport->sendMessage();

        $this->inlineTranslation->resume();

        return $this;
    }
}
