<?php
/**
 * Magestore
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category    Magestore
 * @package     Magestore_OnePay
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/** @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

/**
 * create onepay table
 */
$installer->run("


DROP TABLE IF EXISTS {$this->getTable('onepay')};

CREATE TABLE {$this->getTable('onepay')} (
  `vpc_OrderInfo` int(11) unsigned NOT NULL,
  `vpc_TxnResponseCode` varchar(255) NOT NULL default '',
  `vpc_SecureHash` varchar(255) NOT NULL default '',
  `vpc_TransactionNo` varchar(255) NOT NULL default '',
  `vpc_Message` varchar(255) NOT NULL default '',
  `vpc_Merchant` varchar(255) NOT NULL default '',
  `vpc_MerchTxnRef` varchar(255) NOT NULL default '',
  PRIMARY KEY (`vpc_OrderInfo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

");

$installer->endSetup();

