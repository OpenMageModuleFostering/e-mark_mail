<?php

/**
 * @desc Class for checking Database Id from the System->configuration
 *
 */

class Emark_Mail_Model_Admin_Apidatabase extends Mage_Core_Model_Config_Data
{
	public function save() {
		#set the error message
		$errorMsg = Mage::Helper('mail')->__('There went something wrong try again later.');

		#get all the databases of this user, for checking if the database is exists
		$dbs = Mage::Helper('mail/api')->getDatabases($this->getScopeId());
		#if cant login, throw message
		if(!$dbs->valid) {
			 Mage::throwException($errorMsg);
		}

		#check if the user has access tot the database id
		$gotDb = false;
		foreach($dbs->data as $db) {
			if($db->id == $this->getValue()) {
				$gotDb = true;
			}
		}

		if(!$gotDb) {
			#if the database does not exists
			Mage::throwException(Mage::Helper('mail')->__('This database doesn\'t exists.'));				
		} else {
			#get the Shop Id + the scope;
			$shopId = $this->getScopeId();
			$shopCode = $this->getScope();

			#get the Shop name. We are using this for naming the selections.
			$shopName = Mage::getModel('core/store')->load($this->getScopeId())->getName();

			#create the selection for the guest subscribers for this specific shop
			$guest =  Mage::Helper('mail/api')->createSelection('Magento Guest - ' . $shopName, $shopId, $this->getValue());

			#if the guest selection is saved
			if($guest->valid) {
				#create the selection for the logged subscribers for this specific shop
				$regUser =  Mage::Helper('mail/api')->createSelection('Magento Registered User - ' . $shopName, $shopId, $this->getValue());

				#if the selection is saved
				if(!$regUser->valid) {
					Mage::throwException($errorMsg);	
				} else {
					#get the selections ID
					$guestId = $guest->data->id;
					$regUserId =  $regUser->data->id;
					
					#Mage::log("SaveDatabse: regId:" . $regUserId . " - Shop Name:" . $shopCode . " - ShopId:" . $shopId . " ");

					#save the id's to the configuration
					Mage::getModel('core/config')->saveConfig('emark_sections/emark_settings/emark_logged_customer', $regUserId, $shopCode, $shopId );
					Mage::getModel('core/config')->saveConfig('emark_sections/emark_settings/emark_guest', $guestId, $shopCode, $shopId );
					Mage::app()->getConfig()->reinit();

					#add a success message
					Mage::getSingleton('core/session')->addSuccess(Mage::Helper('mail')->__("Successfully made 2 new selections name: 'Magento Guest - %s and 'Magento Registered User - %s'", $shopName, $shopName));
				}
			} else {
				Mage::throwException($errorMsg);
			}
		}

		Mage::getSingleton('core/session')->addNotice(Mage::Helper('mail')->__("Don't forget to connect the labels in Configuration->E-Mark Mail->Customer settings"));


		#save the database id
		return parent::save();
	}

	/**
 	  * Get the current scope for saving the configuration
	  */
	public function getScope() {
		$stores = Mage::app()->getRequest()->getParam('store', '');
		$websites = Mage::app()->getRequest()->getParam('websites', '');
		if(!empty($stores)) {
			$scope = "stores";
		} else if(!empty($websites)) {
			$scope = "websites";
		} else {
			$scope = "default";
		}
		
		return $scope;
	}


}
