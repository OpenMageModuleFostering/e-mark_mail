<?php

/**
 * @desc rewrites the controller for the delete actions of a customer in the backend
 *
 */

class Emark_Mail_Admin_Newsletter_SubscriberController extends Mage_Adminhtml_Controller_Action
{

	#mass unsubscribe for the newsletter subscribers
	public function massUnsubscribeAction() {
        	$subscribersIds = $this->getRequest()->getParam('subscriber');
	        if (!is_array($subscribersIds)) {
        	     Mage::getSingleton('adminhtml/session')->addError(Mage::helper('newsletter')->__('Please select subscriber(s)'));
	        }
        	else {
	            try {
                	foreach ($subscribersIds as $subscriberId) {
        	            $subscriber = Mage::getModel('newsletter/subscriber')->load($subscriberId);
	                    $subscriber->unsubscribe();

			    #added unsubscribe for the EM
			    Mage::Helper('mail/customer')->checkUnsubscribe($subscriber->getEmail(), $subscriber->getStoreId());
        	        }
	
                	Mage::getSingleton('adminhtml/session')->addSuccess(
        	            Mage::helper('adminhtml')->__(
	                        'Total of %d record(s) were updated', count($subscribersIds)
                	    )
        	        );
	            } catch (Exception $e) {
                	Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        	    }
	        }

        	$this->_redirect('*/*/index');
	}

	#mass delete for the newsletter subscriber + removes module selection of the subscribers
	public function massDeleteAction()
	{
		$subscribersIds = $this->getRequest()->getParam('subscriber');
		if (!is_array($subscribersIds)) {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('newsletter')->__('Please select subscriber(s)'));
		}
		else {
			try {
				foreach ($subscribersIds as $subscriberId) {
					$subscriber = Mage::getModel('newsletter/subscriber')->load($subscriberId);

					#added unsubscribe for the EM
					Mage::Helper('mail/customer')->checkUnsubscribe($subscriber->getEmail(), $subscriber->getShopId());
					$subscriber->delete();
				}
				Mage::getSingleton('adminhtml/session')->addSuccess(
				Mage::helper('adminhtml')->__(
					'Total of %d record(s) were deleted', count($subscribersIds)
				)
			);
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
			}
		}

		$this->_redirect('*/*/index');
	}
}
