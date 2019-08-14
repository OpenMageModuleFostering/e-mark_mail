<?php
class Emark_Cartalert_RecoverController extends Mage_Core_Controller_Front_Action
{
    public function cartAction()
    {
        $code = (string) $this->getRequest()->getParam('code');
        $id   = (int) $this->getRequest()->getParam('id');
        
        $history = Mage::getModel('emark_cartalert/history')->load($id);
        if (!$history->getId() || $history->getRecoverCode() != $code){
            $this->_redirect('/');
            return;
        }
        
        $s = Mage::getSingleton('customer/session');
        if ($s->isLoggedIn()){
            if ($history->getCustomerId() == $s->getCustomerId()){
                $this->redirectToCart($history);
                return;
            }
            else 
                $s->logout();
        }
        // customer. login
        if ($history->getCustomerId()){
            $customer = Mage::getModel('customer/customer')->load($history->getCustomerId());
            if ($customer->getId())
                $s->setCustomerAsLoggedIn($customer);
        }
        elseif ($history->getQuoteId()){
            //visitor. restore quote in the session
            $quote = Mage::getModel('sales/quote')->load($history->getQuoteId());
            if ($quote){
                Mage::getSingleton('checkout/session')->replaceQuote($quote); 
            }
            
        }
        
        $this->redirectToCart($history);
    }
    
    private function redirectToCart($history){
        if (!is_null($history)){
            $history->setRecoveredAt(now());
            if (isset($_SERVER['REMOTE_ADDR'])) {
                $history->setRecoveredFrom($_SERVER['REMOTE_ADDR']);
            } 
            
            if (Mage::getStoreConfig('catalog/emark_cartalert/stop_after_visit')){
                $cartalert = Mage::getResourceModel('emark_cartalert/cartalert')
                    ->cancelAlertsFor($history->getCustomerEmail());
            }
            
            $history->save();
        } 

        $this->_redirect('checkout/cart');
    } 
}
