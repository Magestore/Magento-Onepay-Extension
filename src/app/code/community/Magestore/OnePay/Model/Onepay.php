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
 * Onepay Model
 * 
 * @category    Magestore
 * @package     Magestore_OnePay
 * @author      Magestore Developer
 */
class Magestore_OnePay_Model_Onepay extends Mage_Payment_Model_Method_Abstract
{
	protected $_code  = 'onepay';
	protected $_formBlockType = 'onepay/form';
	protected $_infoBlockType = 'onepay/info';
	
	public function getTitle()
    {
        return $this->getConfigData('title');
    }
	
	public function getDescription()
    {
        return $this->getConfigData('description');
    }
	
	public function get_icon()
    {
        return $this->getConfigData('icon');
    }

	public function getOrderPlaceRedirectUrl()
		{			
				return Mage::getUrl('onepay/standard/redirect', array('_secure' => true));
		}
	public function getUrlOnepay($orderid){
		
		$_order = Mage::getModel('sales/order')->loadByIncrementId($orderid);
		$getGrandTotal = $_order->getGrandTotal();
		$getGrandTotalArr = explode(".", $getGrandTotal);
		$getGrandTotalArr0 = $getGrandTotalArr[0];
		$getGrandTotalArr1 = $getGrandTotalArr[1];
		$getGrandTotalArr1 = substr ($getGrandTotalArr1 , 0 ,2 );
		$amount_total = $getGrandTotalArr0.'.'.$getGrandTotalArr1;
		$arrayvalue =array();
		$arrayvalue['Title'] = "VPC 3-Party";
		$arrayvalue['AgainLink'] = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
		$arrayvalue['vpc_Merchant']=$this->getConfigData('vpc_Merchant');
		$arrayvalue['vpc_AccessCode']=$this->getConfigData('vpc_AccessCode');
		$arrayvalue['vpc_MerchTxnRef'] = date( 'YmdHis' ).rand();
		$arrayvalue['vpc_OrderInfo']= $orderid;
		$arrayvalue['vpc_Amount']= ($amount_total*100);
		$arrayvalue['vpc_ReturnURL']= Mage::getUrl('onepay/standard/success');
		$arrayvalue['vpc_Version']=2;//$this->getConfigData('vpc_Version');
		$arrayvalue['vpc_Command']='pay';//$this->getConfigData('vpc_Command');
		$arrayvalue['vpc_Currency']=$this->getConfigData('vpc_Currency');
		$arrayvalue['vpc_Locale']=$this->getConfigData('vpc_Locale');
		$arrayvalue['vpc_TicketNo']=$_SERVER ['REMOTE_ADDR'];
	
		//$arrayvalue['vpc_SHIP_Street01']= $_order->getShippingAddress()->getStreet1(); //
		//$arrayvalue['vpc_SHIP_Provice']=$_order->getShippingAddress()->getCity(); 
		//$arrayvalue['vpc_SHIP_City']=$_order->getShippingAddress()->getRegion(); 
		//$arrayvalue['vpc_SHIP_Country']=$_order->getShippingAddress()->getCountry_id(); 
		//$arrayvalue['vpc_Customer_Phone']=$_order->getShippingAddress()->getTelephone();//
		//$arrayvalue['vpc_Customer_Email']=$_order->getCustomerEmail();
				
		$SECURE_SECRET = $this->getConfigData('hash_code');				
		$vpcURL = $this->getConfigData('virtualPaymentClientURL')."?";						
		$stringHashData = "";				
		ksort ($arrayvalue);				
		$appendAmp = 0;				
		foreach($arrayvalue as $key => $value) {				  
			if (strlen($value) > 0) {				      
				if ($appendAmp == 0) {
					$vpcURL .= urlencode($key) . '=' . urlencode($value);
					$appendAmp = 1;
				} else {
					$vpcURL .= '&' . urlencode($key) . "=" . urlencode($value);
				}
				     
				if ((strlen($value) > 0) && ((substr($key, 0,4)=="vpc_") || (substr($key,0,5) =="user_"))) {
					$stringHashData .= $key . "=" . $value . "&";
				}
			}
		}				
		$stringHashData = rtrim($stringHashData, "&");				
		if (strlen($SECURE_SECRET) > 0) {				
			$vpcURL .= "&vpc_SecureHash=" . strtoupper(hash_hmac('SHA256', $stringHashData, pack('H*',$SECURE_SECRET)));
		}
		return 	$vpcURL;		
	}	
	public function getResponseDescription($responseCode) {
	
		switch ($responseCode) {
			case "0" :
				$result = Mage::helper('onepay')->__("Approved");
				break;
			case "1" :
				$result = Mage::helper('onepay')->__("Bank Declined");
				break;
			case "3" :
				$result = Mage::helper('onepay')->__("Merchant not exist");
				break;
			case "4" :
				$result = Mage::helper('onepay')->__("Invalid access code");
				break;
			case "5" :
				$result = Mage::helper('onepay')->__("Invalid amount");
				break;
			case "6" :
				$result = Mage::helper('onepay')->__("Invalid currency code");
				break;
			case "7" :
				$result = Mage::helper('onepay')->__("Unspecified Failure");
				break;
			case "8" :
				$result = Mage::helper('onepay')->__("Invalid card Number");
				break;
			case "9" :
				$result = Mage::helper('onepay')->__("Invalid card name");
				break;
			case "10" :
				$result = Mage::helper('onepay')->__("Expired Card");
				break;
			case "11" :
				$result = Mage::helper('onepay')->__("Card Not Registed Service(internet banking)");
				break;
			case "12" :
				$result = Mage::helper('onepay')->__("Invalid card date");
				break;
			case "13" :
				$result = Mage::helper('onepay')->__("Exist Amount");
				break;
			case "21" :
				$result = Mage::helper('onepay')->__("Insufficient fund");
				break;
			case "99" :
				$result = Mage::helper('onepay')->__("User cancel");
				break;
			default :
				$result = Mage::helper('onepay')->__("Unable to be determined");
		}
		return $result;
	}
	public function transStatus($hashValidated,$txnResponseCode){
		$transStatus = "";
		if($hashValidated=="CORRECT" && $txnResponseCode=="0"){
			$transStatus = Mage::helper('onepay')->__("Transaction Success");
		}elseif ($txnResponseCode!="0"){
			$transStatus = Mage::helper('onepay')->__("Transaction Fail: %s",$this->getResponseDescription($txnResponseCode));
		}elseif ($hashValidated=="INVALID HASH"){
			$transStatus = Mage::helper('onepay')->__("Transaction Pendding");
		}
		return $transStatus;
	}
}