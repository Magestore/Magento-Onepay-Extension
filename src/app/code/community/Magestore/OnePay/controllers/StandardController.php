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
 * OnePay Standard Controller
 * 
 * @category    Magestore
 * @package     Magestore_OnePay
 * @author      Magestore Developer
 */
class Magestore_OnePay_StandardController extends Mage_Core_Controller_Front_Action
{

    public function redirectAction()
    {
    	
    	$session = Mage::getSingleton('checkout/session');
    	if( $this->getRequest()->getParam('onepay')!=1){
        $url = Mage::getModel('onepay/onepay')->getUrlOnepay($session->getLastRealOrderId());
    	}else{
    	 $url = Mage::getModel('onepay/onepayquocte')->getUrlOnepay($session->getLastRealOrderId());	
    	}
		
        $this->_redirectUrl($url);
    }

    /**
     * When a customer cancel payment from paypal.
     */
 
    public function  successAction()
    {	
		try {	
			$SECURE_SECRET = Mage::getStoreConfig('payment/onepay/hash_code',Mage::app()->getStore());
			$vpc_Txn_Secure_Hash = '';
			if(isset($_GET ["vpc_SecureHash"]))
			$vpc_Txn_Secure_Hash = $_GET ["vpc_SecureHash"];
			unset ( $_GET ["vpc_SecureHash"] );
			$errorExists = false;
			if (strlen ( $SECURE_SECRET ) > 0 && $_GET ["vpc_TxnResponseCode"] != "7" && $_GET ["vpc_TxnResponseCode"] != "No Value Returned") {
				$stringHashData = "";
				foreach ( $_GET as $key => $value ) {
					if ($key != "vpc_SecureHash" && (strlen($value) > 0) && ((substr($key, 0,4)=="vpc_") || (substr($key,0,5) =="user_"))){
						$stringHashData .= $key . "=" . $value . "&";
					}
				}
				$stringHashData = rtrim($stringHashData, "&");	
				if (strtoupper ( $vpc_Txn_Secure_Hash ) == strtoupper(hash_hmac('SHA256', $stringHashData, pack('H*',$SECURE_SECRET)))) {
					$hashValidated = "CORRECT";
				} else {
					$hashValidated = "INVALID HASH";
				}
			} else {
				$hashValidated = "INVALID HASH";
			}
			$data['vpc_OrderInfo']= $this->null2unknown ( $_GET ["vpc_OrderInfo"] );
			$data['vpc_SecureHash'] = $hashValidated;
			$data['vpc_TxnResponseCode'] = $this->null2unknown($_GET ["vpc_TxnResponseCode"]);
			$data['vpc_TransactionNo'] = '';
			if(isset($_GET ["vpc_TransactionNo"]))
			$data['vpc_TransactionNo'] = $this->null2unknown($_GET ["vpc_TransactionNo"]);
			$data['vpc_Merchant'] = $this->null2unknown($_GET ["vpc_Merchant"]);
			$data['vpc_Message'] = '';
			if(isset($_GET ["vpc_Message"]))
			$data['vpc_Message'] = $this->null2unknown($_GET ["vpc_Message"]);
			$data['vpc_MerchTxnRef'] = '';
			if(isset($_GET ["vpc_MerchTxnRef"]))
			$data['vpc_MerchTxnRef'] = $this->null2unknown($_GET ["vpc_MerchTxnRef"]);
			$model = Mage::getModel('onepay/success')	;
			$model->setData($data);
			$model->save();
		} catch (Exception $e) {}
		
		////////////////////// UPDATE ORDER STATUS ////////////////////////////
		$order = Mage::getModel('sales/order')->loadByIncrementId($data['vpc_OrderInfo']);
		
		if ($hashValidated == "CORRECT" && $data['vpc_TxnResponseCode'] == "0") {
			//$transStatus = "Giao dịch thành công";	
			// fetch write database connection that is used in Mage_Core module
			$write = Mage::getSingleton('core/resource')->getConnection('core_write');	
			/* Update order status */
			$order->setState(Mage::getStoreConfig('payment/onepayquocte/order_status',Mage::app()->getStore()), true)->save();
			
			// notify customer
			$invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice();
            $invoice->register();
			
			$payment = $order->getPayment();
			$payment->setTransactionId($data['vpc_TransactionNo'])
					->setPreparedMessage($data['vpc_Message']);					
			
            Mage::getModel('core/resource_transaction')
                    ->addObject($invoice)
                    ->addObject($order)
					->save();
					
			if ($invoice && !$order->getEmailSent()) {
				$order->sendNewOrderEmail()->addStatusHistoryComment(
					Mage::helper('onepay')->__('Notified customer about invoice #%s.', $invoice->getIncrementId())
				)
				->setIsCustomerNotified(true)
				->save()
				;
			}			
			/* End of update order status */		
							
			Mage::getSingleton('checkout/session')->addSuccess( Mage::getModel('onepay/onepay')->transStatus($hashValidated,$data['vpc_TxnResponseCode']));
			
		}elseif ($hashValidated == "CORRECT" && $data['vpc_TxnResponseCode'] != "0") {
			//$transStatus = "Giao dịch thất bại";
			$order->setState(Mage_Sales_Model_Order::STATE_CANCELED, true)->save();
			// fetch write database connection that is used in Mage_Core module
			$write = Mage::getSingleton('core/resource')->getConnection('core_write');	
			Mage::getSingleton('checkout/session')->addError( Mage::getModel('onepay/onepay')->transStatus($hashValidated,$data['vpc_TxnResponseCode']));
		} elseif ($hashValidated == "INVALID HASH") {
			//$transStatus = "Giao dịch Pendding";
			$order->setState(Mage_Sales_Model_Order::STATE_PENDING_PAYMENT, true)->save();
			// fetch write database connection that is used in Mage_Core module
			$write = Mage::getSingleton('core/resource')->getConnection('core_write');	
			Mage::getSingleton('checkout/session')->addError( Mage::getModel('onepay/onepay')->transStatus($hashValidated,$data['vpc_TxnResponseCode']));			
		}
		/////////////////////////////////////////////////
        $this->_redirect('checkout/onepage/success', array('_secure'=>true));
    }
	public function  successquocteAction()
    {	
		try {
			$SECURE_SECRET = Mage::getStoreConfig('payment/onepayquocte/hash_code',Mage::app()->getStore());
			$vpc_Txn_Secure_Hash = $_GET["vpc_SecureHash"];
			$vpc_MerchTxnRef = $_GET["vpc_MerchTxnRef"];
			unset($_GET["vpc_SecureHash"]);
			$errorExists = false;

			if (strlen($SECURE_SECRET) > 0 && $_GET["vpc_TxnResponseCode"] != "7" && $_GET["vpc_TxnResponseCode"] != "No Value Returned") {
				ksort($_GET);	  
				$md5HashData = "";	  
				foreach ($_GET as $key => $value) {
					if ($key != "vpc_SecureHash" && (strlen($value) > 0) && ((substr($key, 0,4)=="vpc_") || (substr($key,0,5) =="user_"))) {
						$md5HashData .= $key . "=" . $value . "&";
					}
				}
				$md5HashData = rtrim($md5HashData, "&");
				if (strtoupper ( $vpc_Txn_Secure_Hash ) == strtoupper(hash_hmac('SHA256', $md5HashData, pack('H*',$SECURE_SECRET)))) {
					$hashValidated = "CORRECT";
				} else {
					$hashValidated = "INVALID HASH";
				}
			} else {			
				$hashValidated = "INVALID HASH";
			}

			$data['vpc_OrderInfo']= $this->null2unknown ( $_GET ["vpc_OrderInfo"] );
			$data['vpc_SecureHash'] = $hashValidated;
			$data['vpc_TxnResponseCode'] = $this->null2unknown($_GET ["vpc_TxnResponseCode"]);
			$data['vpc_TransactionNo'] = $this->null2unknown($_GET ["vpc_TransactionNo"]);
			$data['vpc_Merchant'] = $this->null2unknown($_GET ["vpc_Merchant"]);
			$data['vpc_Message'] = $this->null2unknown($_GET ["vpc_Message"]);
			$data['vpc_MerchTxnRef'] = $this->null2unknown($_GET ["vpc_MerchTxnRef"]);
			$model = Mage::getModel('onepay/success')	;
			$model->setData($data);
			$model->save();
		} catch (Exception $e) {}
       
		////////////////////// UPDATE ORDER STATUS ////////////////////////////
		$order = Mage::getModel('sales/order')->loadByIncrementId($data['vpc_OrderInfo']);
		if ($hashValidated == "CORRECT" && $data['vpc_TxnResponseCode'] == "0") {
			//$transStatus = "Giao dịch thành công";	
			// fetch write database connection that is used in Mage_Core module
			$write = Mage::getSingleton('core/resource')->getConnection('core_write');		
			// now $write is an instance of Zend_Db_Adapter_Abstract
			/* Update order status */
			$order->setState(Mage::getStoreConfig('payment/onepay/order_status',Mage::app()->getStore()), true)->save();
			
			// notify customer
			$invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice();
            $invoice->register();			
			
            Mage::getModel('core/resource_transaction')
                    ->addObject($invoice)
                    ->addObject($order)
					->save();
					
			if ($invoice && !$order->getEmailSent()) {
				$order->sendNewOrderEmail()->addStatusHistoryComment(
					Mage::helper('onepay')->__('Notified customer about invoice #%s.', $invoice->getIncrementId())
				)
				->setIsCustomerNotified(true)
				->save();
			}					
			Mage::getSingleton('checkout/session')->addSuccess( Mage::getModel('onepay/onepay')->transStatus($hashValidated,$data['vpc_TxnResponseCode']));
		} 
		elseif ($hashValidated == "CORRECT" && $data['vpc_TxnResponseCode'] != "0") {
			//$transStatus = "Giao dịch thất bại";
			// fetch write database connection that is used in Mage_Core module
			$order->setState(Mage_Sales_Model_Order::STATE_CANCELED, true)->save();
			$write = Mage::getSingleton('core/resource')->getConnection('core_write');		
			Mage::getSingleton('checkout/session')->addError( Mage::getModel('onepay/onepay')->transStatus($hashValidated,$data['vpc_TxnResponseCode']));
		} elseif ($hashValidated == "INVALID HASH") {
			//$transStatus = "Giao dịch Pendding";
			$order->setState(Mage_Sales_Model_Order::STATE_PENDING_PAYMENT, true)->save();
			// fetch write database connection that is used in Mage_Core module
			$write = Mage::getSingleton('core/resource')->getConnection('core_write');	
			Mage::getSingleton('checkout/session')->addError( Mage::getModel('onepay/onepay')->transStatus($hashValidated,$data['vpc_TxnResponseCode']));
		}

        $this->_redirect('checkout/onepage/success', array('_secure'=>true));
    }
	public function null2unknown($data) {
		if ($data == "") {
			return "N/A";
		} else {
			return $data;
		}
	}
}