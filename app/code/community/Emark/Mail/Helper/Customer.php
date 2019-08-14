<?php

/**
 * @desc Helper for checking and syncing between magento and E-mark Mail
 *
 * TODO: - create one method of the createGetFieldMag and createSetFieldMag
 */

class Emark_Mail_Helper_Customer extends Mage_Core_Helper_Abstract
{

	#method for syncing to or from E-mark Mail
	public function sync($email,$shopId, $isRegForm = false, $thisCustomer = null) {
		if(!Mage::Helper('mail/api')->_init($shopId)) {
			return;
		}

		#If the class has its own customer init, then use that one
		if(!$isRegForm) {
	                $customer = Mage::getModel('customer/customer')->getCollection()->addFilter('email', $email)->getFirstItem();
	        } else {
	       		$customer = $thisCustomer;
		}

		#check if the user exists in the EM
		#$checkEm = Mage::Helper('mail/api')->getSubscriberByEmail($email);
			
		#if our customer doesn't exist or the user doesn't exist in E-mark, create the user
		#Mage::log($isRegForm . " " . $email . " " . $customer->getId());

			
		if(!is_object($customer) || is_null($customer->getId())) {
			$customerType = "guest";
			$guest = Mage::Helper('mail/api')->createSubscriber($email, array(), true, $shopId);
			$this->trowError($guest);
		} else {
			#magento uses UTC so lets convert this to CET. Because EM is using CET.
			$d = new DateTime(date("Y-m-d H:i:s", strtotime($customer->getUpdatedAt())));
                        $d->setTimezone(new DateTimeZone("CET"));
			$dateMag = $d->format("Y-m-d H:i:s");

			#check wich of the 2 systems has the latest update time, to determine who has to update
			$update = $this->determineUpdate($email, $dateMag, $shopId);

			#funky logger
			#Mage::log("Update is:" . $update);
	
			if($update == "Magento -> EM") {
				
				$var = array();

				#get the customer Fields
				$fields = $this->getCustomerFieldsArray($shopId);
				foreach($fields as $key=>$value) {
					if($value !== false) {
						$func = $this->createGetFieldMag($key);
						if(!is_null($customer->$func())) {
							$var[$value] = $customer->$func();
						} else {
							unset($var[$value]);
							unset($fields[$value]);
						}
					}
				}

				#create the subscriber
				$call = Mage::Helper('mail/api')->createSubscriber($email, $var, false, $shopId);

				#trow a error if there is one
				$this->trowError( $call, $email);
			} else {

				#get the customer fields
				$fields = $this->getCustomerFieldsArray($shopId);

				#get the subscriber labels
				$labels = Mage::Helper('mail/api')->getSubscriberLabel($shopId);

				$emSubscriber = Mage::Helper('mail/api')->getSubscriberByEmail($email, $shopId);
				#check if the labels aren't empty
				if(!empty($labels->data)) {	

					#loop to the labels and create the value for the model customer
					foreach($labels->data as $data) {
						if(in_array($data->name, $fields)) {
							foreach($fields as $key=>$value) {
								if($value == $data->name) {
									$func = $this->createSetFieldMag($key);
									$emLabel = $data->name;	
									#Mage::log($emLabel);
									$customer->$func($emSubscriber->data->$emLabel); 
								}
							}
						}
					}

					#save the customer, if it is not a success throw a error
					try {
						$customer->setIsSubscribed(1);
						$customer->save();
					} catch(Exception $ex) {
						Mage::throwException($ex->getMessage());
					}
				}
			}
		}
		
	}

	#method to trow a error
	protected function trowError($call, $email = "") {
		if(!$call->valid) {
			foreach($call->errors as $error) {
				Mage::throwException($email . " " . $this->__($error));
			}
		}
	}

	#check if the user is unsubscribed
	public function checkUnsubscribe($email, $shopId) {
		#Mage::log('unscubsribed');
		$emSubscriber = Mage::Helper('mail/api')->getSubscriberByEmail($email, $shopId);

		if($emSubscriber->valid) {
			$emSubscriber = Mage::Helper('mail/api')->deleteSelection($email, $shopId);	
		} 
	}

	#method to create the naming convention of the get field for the method save
	protected function createGetFieldMag($key) {
		$key = explode('_', $key);
		$fnc = "get";
		foreach($key as $k) {
			$fnc .= ucfirst($k);
		}

		return $fnc;
	}

	#method to create the naming convention of the set field for the method save
	protected function createSetFieldMag($key) {
		$key = explode('_', $key);
		$fnc = "set";
		foreach($key as $k) {
			$fnc .= ucfirst($k);
		}

		return $fnc;
	}

	#check if magento must update the EM or the Em must update Magento
	protected function determineUpdate($email, $date, $shopId) {
		$subscriber = Mage::Helper('mail/api')->getSubscriberByEmail($email, $shopId);
		if($subscriber->valid) {
			$subsciberEmDate = $subscriber->data->lastupdate;
			$selections = array(
	        	        Mage::getStoreConfig('emark_sections/emark_settings/emark_guest', $shopId),
		                Mage::getStoreConfig('emark_sections/emark_settings/emark_logged_customer', $shopId)
		        );
	
			#some logging
			#Mage::log("EM time: " .  $subsciberEmDate);
			#Mage::log("Mag time:" . $date);
			#Mage::log((in_array($selections[1], $subscriber->data->selections) || in_array($selections[0], $subscriber->data->selections)));

			
			if($subsciberEmDate > $date && (in_array($selections[1], $subscriber->data->selections) || in_array($selections[0], $subscriber->data->selections))) {
				return "EM -> Magento";
			} else {
				return "Magento -> EM";
			}
			die();
		} else {
			return "Magento -> EM";
		}
	}

	#connect the magento customer fields to the subscriber fields of the EM
	public function getCustomerFieldsArray($shopId) {
		return Mage::Helper("mail/connectfields")->getArrayConnectFields(Mage::getStoreConfig('emark_customer_settings/emark_labels/emark_label', $shopId));

		return $labels;
	}
}
