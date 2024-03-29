<?php

class Emark_Cartalert_Adminhtml_CartalertController extends Mage_Adminhtml_Controller_action
{
	public function indexAction() {
	    	$this->loadLayout(); 
        	$this->_setActiveMenu('newsletter/emark_cartalert/alerts');
	        $this->_addBreadcrumb($this->__('Carts Alerts'), $this->__('Carts Alerts')); 
        	$this->_addContent($this->getLayout()->createBlock('emark_cartalert/adminhtml_cartalert')); 	    
		$this->renderLayout();
	}

	public function editAction() {
		$id     = $this->getRequest()->getParam('id');
		$model  = Mage::getModel('emark_cartalert/cartalert')->load($id);
		$model->preprocess();

		if ($model->getId() || $id == 0) {
			$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
			if (!empty($data)) {
				$model->setData($data);
			}

			Mage::register('cartalert_data', $model);

			$this->loadLayout();
			$this->_setActiveMenu('newsletter/emark_cartalert/alerts');

			$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

	        $this->_addContent($this->getLayout()->createBlock('emark_cartalert/adminhtml_cartalert_edit'));
            
			$this->renderLayout();
		} else {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('emark_cartalert')->__('Item does not exist'));
			$this->_redirect('*/*/');
		}
	}
 
	public function newAction() {
		$this->editAction();
	}
 
	public function saveAction() {
	    $id     = $this->getRequest()->getParam('id');
	    $model  = Mage::getModel('emark_cartalert/cartalert');
		if ($data = $this->getRequest()->getPost()) {
			$model->setData($data)->setId($id);
	    try {
                $format = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
                if ($date = $model->getData('sheduled_at')) {
                    $model->setData('sheduled_at', Mage::app()->getLocale()->date($date, $format, null, false)
                        ->toString(Varien_Date::DATETIME_INTERNAL_FORMAT));
                }
                else {
                    $model->setData('sheduled_at', now());
                } 			    
			    
		$model->save();
		if($this->getRequest()->getParam('send')) {
                    if($model->load($id)->send()){
                        $model->delete();
    				    Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('emark_cartalert')->__('Alert has been successfully sent and deleted'));    
                    }
                    else{
    				    Mage::getSingleton('adminhtml/session')->addError(Mage::helper('emark_cartalert')->__('Can not send the ID %d. Please check the email address and your server configuration', $id));    
                    }
		}
		else {
		    Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('emark_cartalert')->__('Alert has been successfully saved'));    
		}

		Mage::getSingleton('adminhtml/session')->setFormData(false);

		$this->_redirect('*/*/');
		return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('emark_cartalert')->__('Unable to find item to save'));
        $this->_redirect('*/*/');
	}
 
    public function massDeleteAction()
    {
        $ids = $this->getRequest()->getParam('cartalert');
        if(!is_array($ids)) {
             Mage::getSingleton('adminhtml/session')->addError(Mage::helper('emark_cartalert')->__('Please select cartalert(s)'));
        } else {
            try {
                foreach ($ids as $id) {
                    $model = Mage::getModel('emark_cartalert/cartalert')->load($id);
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
    
    public function massSendAction()
    {
        $ids = $this->getRequest()->getParam('cartalert');
        if(!is_array($ids)) {
             Mage::getSingleton('adminhtml/session')->addError(Mage::helper('emark_cartalert')->__('Please select cartalert(s)'));
        } else {
            try {
                $total = 0;
                $totalIgnored = 0;
                foreach ($ids as $id) {
                    $model = Mage::getModel('emark_cartalert/cartalert')->load($id);
                    
                    $res = $model->send();
			
                    if($res === true){
                        $model->delete();
                        $total++;
                    }
                    elseif($res === 1)
                    {
                        $model->delete();
                        $totalIgnored++;
                    }
                    else {
                        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('emark_cartalert')->__('Can not send the alert ID %d. Please check the email address and your server configuration', $id));    
                    }
                }
                if ($total){
                    Mage::getSingleton('adminhtml/session')->addSuccess(
                        Mage::helper('adminhtml')->__(
                            'Total of %d email(s) have been successfully sent', $total
                        )
                    );
                }
                if($totalIgnored)
                {
                    Mage::getSingleton('adminhtml/session')->addWarning(
                        Mage::helper('adminhtml')->__(
                            'Total of %d email(s) haven\'t been successfully sent: no visible products', $totalIgnored
                        )
                    );
                }
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/');
    }    
	
    public function deleteAction() {
		if( $this->getRequest()->getParam('id') > 0 ) {
			try {
				$model = Mage::getModel('emark_cartalert/cartalert');
				 
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
	
	public function generateAction() {
	   try {
            $model = Mage::getModel('emark_cartalert/cartalert');
            list($from, $to) = $model->generate(now());

            $format = Mage_Core_Model_Locale::FORMAT_TYPE_SHORT;
            $from   = Mage::helper('core')->formatDate($from, $format, true);
            $to     = Mage::helper('core')->formatDate($to, $format, true);
             
            Mage::getSingleton('adminhtml/session')->addSuccess(
                Mage::helper('emark_cartalert')->__('Alerts for carts abandoned from %s to %s have been successfully added to the queue', 
                    $from, $to)
            );
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('emark_cartalert')->__('Unable to update queue')
            );
            throw $e;
        } 
        $this->_redirect('*/*/');
	}
}
