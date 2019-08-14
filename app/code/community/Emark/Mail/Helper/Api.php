<?php

/**
 * @desc Wrapper for the E-mark Rest Api V2
 *
 */

class Emark_Mail_Helper_Api extends Mage_Core_Helper_Abstract
{
	protected $token = array();
	public $initErrors = '';
	public $subscriberLabels = array();

	#method for getting the response of the rest api
        public function rest_get ($call, $data = array(), $shopId, $configUrl = null) {
		#if no config vars get the api url of the configuration settings
		if(!is_null($configUrl)) {
			$apiUrl = $configUrl;
		} else {
			$apiUrl =  Mage::getStoreConfig('emark_sections/emark_settings/emark_api', $shopId);
		}


		#get the api url + the call that you want to make
                $url = $apiUrl . $call;

		#if there is data
                if(count($data)) $url .= '?' . http_build_query($data);

		#start a curl request
                $c = curl_init();

		#curl settings
                curl_setopt_array($c, array(
                        CURLOPT_URL => $url,
                        CURLOPT_RETURNTRANSFER => true
                ));

		#execute the curl
                $result = curl_exec($c);

		#json decode the returned answer of the rest api
                $json = json_decode($result);
                curl_close($c);

		#if there went something wrong return a error
                if(!is_object($json)) {
                        $json = new stdClass();
                        $json->valid = false;
                        $json->errors = array('Critical failure, result was not in valid JSON format.');
                }
                $json->url = $url;

		#return the json object
                return $json;
        }


	#get database id from the configuration settings
        public function getDatabase($shopId = null) {
		if(is_null($shopId)) {
			$shopId = Mage::app()->getStore()->getStoreId();
		}

                return  Mage::getStoreConfig('emark_sections/emark_settings/emark_db', $shopId);
        }

	#check if the api, user and password for the rest api are correct
	public function checkConfigSuccessLogin($usr, $passwd, $shopId) {
		if(!isset($passwd)) {
			return false;
		}

		$call = $this->rest_get('user/login', array(
			'username' => $usr, # Mage::getStoreConfig('emark_sections/emark_settings/emark_user', Mage::app()->getStore()->getStoreId()),
			'password' => $passwd
		), $shopId);

		if(!$call->valid) {
			$this->initErrors = $call;
			return false;
			die();
		} else {
			$this->token[$shopId] = $call->data->token;
			return true;
			die();
		}
	}

	#delete a selection of a specific subscriber
	public function deleteSelection($email, $shopId, $database = null, $test = false) {
		#check if there is a database id
		if(is_null($database)) {
			$database = $this->getDatabase($shopId);
		}

		#initialize the rest api
		if($this->_init($shopId)) {
			#get the subscriber selections
			$subscriberSelections = $this->getSubscriberByEmail($email, $shopId)->data->selections;

			#get the E-mark magento module selections
			$delSelections = array(
				Mage::getStoreConfig('emark_sections/emark_settings/emark_guest', $shopId),
				Mage::getStoreConfig('emark_sections/emark_settings/emark_logged_customer', $shopId)
			);

			#remove the module selections of the subscriber selections
			$newSubscriberSelections = array_diff($subscriberSelections, $delSelections);

			#if the subscriber selections is now empty, create a string empty.
			if(empty($newSubscriberSelections)) {
				$newSubscriberSelections = "empty";
			}

			#create the webserver options
			$vars = array(
				'token' => $this->token[$shopId],
				'database' => $database,
				'email' => $email,
				"source" => Mage::getStoreConfig('emark_sections/emark_settings/emark_source', $shopId),
				'options' => array(
					"subscriber" => "update",
					"selection" => "removeOther",
					"resend_optin" => "yes",
					"optin" => "none"
				),
				'pretend' => $test,
				'languages' => array(1),
				'selections' => $newSubscriberSelections
			);

			#invoke the rest api call
			$call = $this->rest_get('subscriber/store', $vars, $shopId);
			return $call;
		} else {
			#return error if there whent something wrong
			return $this->initErrors;
		}
	}

	#initialize the webserver
	public function _init($shopId) {
		#if there is already a token use that one
		if(!isset($this->token[$shopId])) {

			#login into the rest api
			$call = $this->rest_get('user/login', array(
   			     'username' => Mage::getStoreConfig('emark_sections/emark_settings/emark_user', $shopId),
			     'password' => Mage::getStoreConfig('emark_sections/emark_settings/emark_password', $shopId)
			), $shopId);

			#if the call is valid set the token else return the error
			if(!$call->valid) {
				$this->initErrors = $call;
				return false;
				die();
			} else {
				$this->token[$shopId] = $call->data->token;
				return true;
				die();
			}
		}

		return true;
	}

	#get all the databases of a specific rest api user
	public function getDatabases($shopId) {
		if($this->_init($shopId)) {
			$call  = $this->rest_get('user/databases', array(
				'token' => $this->token[$shopId],
			), $shopId);

			return $call;
		} else {
			return $this->initErrors;
		}
	}


	#create a selection in a specific database
	public function createSelection($name, $shopId, $database = null, $test = "off") {
		 if(is_null($database)) {
                         $database = $this->getDatabase($shopId);
                 }

		 if($this->_init($shopId)) {
		  	$call = $this->rest_get('selections/create', array(
				'token' => $this->token[$shopId],
				'database' => $database,
				'name' => $name
			), $shopId);

			return $call;
		 } else {
		  	return $this->initErrors;
		 }
	}


	#create a subscriber in E-mark Mail
	public function createSubscriber($email, $fields = array(), $guest = false, $shopId, $database = null, $test = false) {
		if(is_null($database)) {
			$database = $this->getDatabase($shopId);
		}
		if($this->_init($shopId)) {
			$labels = $this->getSubscriberLabel($shopId, $database);
			$subscriberLabel = array();
			if($labels->valid) {
				foreach($labels->data as $data) {
					if(array_key_exists($data->name, $fields)) {
						$subscriberLabel[$data->name] = $fields[$data->name];
					}
				}
			}
			$vars = array(
			 	'token' => $this->token[$shopId],
				'database' => $database,
				'email' => $email,
				"source" => Mage::getStoreConfig('emark_sections/emark_settings/emark_source', $shopId),
				'options' => array(
					"subscriber" => "update",
					"selection" => "add",
					"resend_optin" => "yes",
					"optin" => "none"
				),
				'pretend' => $test,
				'subscriber' => $subscriberLabel,
				'languages' => array(1)
			);

			#check if the subscriber is a guest, if he is a guest put the subscriber in a special selections
			if($guest) {
				$vars['selections'] = array(Mage::getStoreConfig('emark_sections/emark_settings/emark_guest', $shopId));
			} else {
				$vars['selections'] = array(Mage::getStoreConfig('emark_sections/emark_settings/emark_logged_customer', $shopId));
			}

			$call = $this->rest_get('subscriber/store', $vars, $shopId);

			return $call;
		} else {
			return $this->initErrors;
		}
	}

	#get all the labels of a database for a subscriber
	public function getSubscriberLabel($shopId, $database = null) {
		if(!isset($this->subscriberLabels[$shopId]) || !is_null($database)) {
			if(is_null($database)) {
				$database = $this->getDatabase($shopId);
			}
			if($this->_init($shopId)) {
				$call = $this->rest_get('subscriber/getFormFields', array(
					'token' => $this->token[$shopId],
					'database' => $database
				), $shopId);

				$this->subscriberLabels[$shopId] = $call;
			} else {
				return $this->initErrors;
			}
		}

		return $this->subscriberLabels[$shopId];
	}

	#get a subscriber
	public function getSubscriberByEmail($email, $shopId, $database = null) {
		if(is_null($database)) {
			$database = $this->getDatabase($shopId);
		}
		if($this->_init($shopId)) {
			$call = $this->rest_get('subscriber/findByEmail', array(
				'token' => $this->token[$shopId],
				'database' => $database,
				'email' => $email
			), $shopId);

			return $call;
		} else {
			return $this->initErrors;
		}
	}
}
