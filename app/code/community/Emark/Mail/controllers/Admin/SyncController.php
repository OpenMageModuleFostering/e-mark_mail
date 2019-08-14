<?php

/**
 * @desc Mas sync function for the extra "E-mark Mail sync" action on the page "newsletter->newsletter subscribers" in the admin panel of magento
 *
 */

class Emark_Mail_Admin_SyncController extends Mage_Adminhtml_Controller_Action
{

	#Mass sync method between magento and E-mark Mail.
	public function massAction() {

		#get the subscriber Id's from the mass selection
		$subscribersIds = $this->getRequest()->getParam('subscriber');

		#if the there is no array throw a error
		if (!is_array($subscribersIds)) {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('newsletter')->__('Please select subscriber(s)'));
		} else {
			try {
				$error = 0;
				$countSelected = 0;
				$countSynct = 0;

				#loop trough the selected subscribers
				foreach ($subscribersIds as $subscriberId) {

					#get the subscriber model
					$subscriber = Mage::getModel('newsletter/subscriber')->load($subscriberId);
					$email = $subscriber->getEmail();
					$shopId = $subscriber->getStoreId();
					$countSelected++;

					/**
					  * 1: Subscribed 2: Status Not Active 3: Unsubscribed
					  * These are the known statussen of the subscriber
					  */
					if($subscriber->getStatus() == 1) {
						Mage::Helper('mail/customer')->sync($email, $shopId);		
						$countSynct++;
					} else {
						Mage::Helper('mail/customer')->checkUnsubscribe($email, $shopId);
					}
				}
				
				#counted synct, not sync or unsubscribers
				$diffStatus = $countSelected - $countSynct;

			} catch(Exception $ex) {
				$error = 1;
				Mage::getSingleton('adminhtml/session')->addError($ex->getMessage());
			}

			#if there are no errors
			if(!$error) {
				 Mage::getSingleton('adminhtml/session')->addSuccess(Mage::Helper('mail')->__('From the %s selected subscribers there are %s synct to E-mark Mail. %s Subscribers where not active or Unsubscribed.', $countSelected, $countSynct, $diffStatus));
			}
		}


		#redirect back to the overview of the subscribers
		$this->_redirect('adminhtml/newsletter_subscriber/index');
	}
}
