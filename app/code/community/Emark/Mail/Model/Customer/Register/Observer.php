<?php
/**
 * @desc Observer after customer is saved.
 *
 * This method sends the customer to the sync or the unsubscribe for E-mark Mail
 * 
 */
class Emark_Mail_Model_Customer_Register_Observer
{
	public function updateEm($observer) {

		# line for logging so you can see how manu times is being called see var/www/magento/var/log/system.log
		#Mage::log('Emark Mail: Observer');

		# get the customer out of the observer vars
		$customer = $observer->getCustomer();

		$customerId = $customer->getId();

		# get the Email adress of the customer
		$email = $customer->getEmail();

		if(isset($customerId) && is_null($customer->getIsSubscribed())) {
			$subs = Mage::getModel('newsletter/subscriber')->loadByEmail($email);
			$storeId = $subs->getStoreId();

			if($subs->getStatus() == 1) {
				$subscribed = true;
			} else {
				$subscribed = false;
			}

		} else {
			$subscribed = $customer->getIsSubscribed();
			$storeId = $customer->getStoreId();
			if(!$storeId) {
				$storeId = $customer->getSendemailStoreId();
			}
		}

		if($subscribed) {
			# sync the customer with E-mark Mail, and use the observer customer
			Mage::Helper('mail/customer')->sync($email, $storeId, true, $customer);	
		} else {
			# unsunscribe the customer
			Mage::Helper('mail/customer')->checkUnsubscribe($email, $storeId);
		}
	}
}
