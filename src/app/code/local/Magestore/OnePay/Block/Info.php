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
 * Info Block
 * 
 * @category    Magestore
 * @package     Magestore_Info
 * @author      Magestore Developer
 */
class Magestore_OnePay_Block_Info extends Mage_Payment_Block_Info
{


    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('onepay/info.phtml');
    }

    public function toPdf()
    {
        $this->setTemplate('onepay/pdf/info.phtml');
        return $this->toHtml();
    }



}
