<?php

class Emark_Cartalert_Adminhtml_HistoryController extends Mage_Adminhtml_Controller_action
{
	public function indexAction() {
		$this->loadLayout(); 
		$this->_setActiveMenu('newsletter/emark_cartalert/history');
		$this->_addBreadcrumb($this->__('Carts Alerts'), $this->__('History')); 
		$this->_addContent($this->getLayout()->createBlock('emark_cartalert/adminhtml_history')); 	    
		$this->renderLayout();
	}

	public function editAction() {
		$id     = $this->getRequest()->getParam('id');
		$model  = Mage::getModel('emark_cartalert/history')->load($id);

		if (!$model->getId()) {
		    $this->_redirect('*/*/');
		}
	    	Mage::register('history_data', $model);

		$this->loadLayout();
		$this->_setActiveMenu('newsletter/emark_cartalert/history');
        	$this->_addContent($this->getLayout()->createBlock('emark_cartalert/adminhtml_history_edit'));
		$this->renderLayout();
	}
 
    public function massDeleteAction()
    {
        $ids = $this->getRequest()->getParam('cartalert');
        if (!is_array($ids)) {
             Mage::getSingleton('adminhtml/session')->addError(Mage::helper('emark_cartalert')->__('Please select cartalert(s)'));
        } else {
            try {
                foreach ($ids as $id) {
                    $model = Mage::getModel('emark_cartalert/history')->load($id);
                    $model->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d record(s) were successfully deleted', count($ids)
                    )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/');
    }
    
    public function deleteAction() {
	if ($this->getRequest()->getParam('id') > 0 ) {
		try {
			$model = Mage::getModel('emark_cartalert/history');
			$model->setId($this->getRequest()->getParam('id'))
				->delete();
					 
			Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('emark_cartalert')->__('Alert has been deleted'));
			$this->_redirect('*/*/');
		} catch (Exception $e) {
			Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
			$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
		}
	}
	$this->_redirect('*/*/');
    }
	
} 
