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

/**
 * OnePay Status Model
 * 
 * @category    Magestore
 * @package     Magestore_OnePay
 * @author      Magestore Developer
 */
class Magestore_OnePay_Model_Config extends Mage_Core_Model_Abstract
{
	protected $_options;
    
    public function toOptionArray()
    {
        if (!$this->_options) {
            $this->_options = array(
								'vn' => Mage::helper('onepay')->__('Vietnamese'),
								'en' => Mage::helper('onepay')->__('English'));
        }
        $options = $this->_options;
        return $options;
    }
}