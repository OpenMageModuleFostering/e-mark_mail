<?php

/**
 * @desc rewrites the controller for the delete actions of a customer in the backend
 *
 */
#little hack for extending the current controller
include( Mage::getBaseDir() . "/app/code/core/Mage/Adminhtml/controllers/CustomerController.php");

class Emark_Mail_Admin_CustomerController extends Mage_Adminhtml_CustomerController
{

	#deletes the EM subscriber selection of the customer + the customer in magento
	public function deleteAction()
	{
		$this->_initCustomer();
		$customer = Mage::registry('current_customer');
		if ($customer->getId()) {
			try {
				$customer->load($customer->getId());
				$storeId = $customer->getStoreId();
				Mage::Helper('mail/customer')->checkUnsubscribe($customer->getEmail(), $storeId);
				$customer->delete();
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('The customer has been deleted.'));
			}
			catch (Exception $e){
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
			}
		}
		$this->_redirect('*/customer');
	}

	#Bulk: deletes the EM subscriber selection of the customer + the customer in magento
	public function massDeleteAction()
	{
		$customersIds = $this->getRequest()->getParam('customer');
		if(!is_array($customersIds)) {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select customer(s).'));
		} else {
			try {
				$customer = Mage::getModel('customer/customer');
				foreach ($customersIds as $customerId) {
					$cus = $customer->reset()->load($customerId);
					$storeId = $cus->getStoreId();
					Mage::Helper('mail/customer')->checkUnsubscribe($cus->getEmail(), $storeId);
					$cus->delete();
				}
				Mage::getSingleton('adminhtml/session')->addSuccess(
					Mage::helper('adminhtml')->__(
						'Total of %d record(s) were deleted.', count($customersIds)
					)
				);
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
			}
		}

		$this->_redirect('*/*/index');
	}
}
