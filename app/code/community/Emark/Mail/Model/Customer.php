<?php

/**
 * @desc Model for the cron 
 *
 * don't forget to create a cron which leads to: /var/www/path/to/magento-install/cron.php 
 */
class Emark_Mail_Model_Customer extends Mage_Customer_Model_Customer {

	/**
	 * @dec update subscribers in magento
	 *
	 * - Customer get's updated when exists
	 * - Unsubscribed when unsubscribed in magento
	 * 
	 */
	public function updateMagentoCron() {
		#check webservice connection
		if(!Mage::Helper('mail/api')->_init()) {
                        return;
                }

		#get all subscribers from magento
		$customers = Mage::getModel('newsletter/subscriber')->getCollection();

		# loop trough them
		foreach($customers as $customer) {

			#get email adres of subscriber
			$email = $customer->getEmail();
			$storeId = $customer->getStoreId();

			#logger
			#Mage::log($email);
			#echo "CRON: " . $email . " "  . $storeId . "\n";

			#check if subscriber exists in EM
			$em = Mage::Helper('mail/api')->getSubscriberByEmail($email, $storeId);
			
			if($em->valid && (in_array(Mage::getStoreConfig('emark_sections/emark_settings/emark_guest', $storeId), $em->data->selections) || in_array(Mage::getStoreConfig('emark_sections/emark_settings/emark_logged_customer', $storeId), $em->data->selections))) {

				#logger
				#Mage::log('Sync me: ' . $email);
				#echo "Sync me:" . $email . "\n";

				# sync if the user exists
				Mage::Helper('mail/customer')->sync($email, $storeId);
			} else {
				#unsubscribe when not exists
				#Mage::log('Delete me: ' . $email);
				$customer->setStatus(3);
				$customer->save();
			}
		}
	}

}
