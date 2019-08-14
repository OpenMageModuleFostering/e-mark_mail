<?php
class Emark_Cartalert_Model_Observer
{
    public function createCartalerts()
    {
        $cartalert = Mage::getModel('emark_cartalert/cartalert');
        $cartalert->generate(date('Y-m-d H:i:s'));
        
        $this->sendCartalerts();
        
        return $this;
    }

    public function sendCartalerts()
    {
        if (!Mage::getStoreConfig('catalog/emark_cartalert/sending_enabled'))
            return $this;

        $collection = Mage::getModel('emark_cartalert/cartalert')->getCollection()
            ->addReadyForSendingFilter() 
            ->setPageSize(50)
            ->setCurPage(1)
            ->load();
        foreach ($collection as $cartalert){
            if ($cartalert->send()){
                $cartalert->delete(); 
            } 
        }  
        return $this;
    }
    
    public function processOrderCreated($observer){
        $order = $observer->getEvent()->getOrder(); 
        
        if (Mage::getStoreConfig('catalog/emark_cartalert/stop_after_order')){
            $cartalert = Mage::getResourceModel('emark_cartalert/cartalert')
                ->cancelAlertsFor($order->getCustomerEmail());
        }
        return $this;

    } 
    
    public function updateAlertsStatus($observer)
    {
    	if (!Mage::registry('alerts_status_updated'))
    	{
    		Mage::register('alerts_status_updated', true);
    		
			$quote = Mage::getSingleton('checkout/session')->getQuote();
			
			if ($quote)
			{
				$quote->setAllowAlerts(1);
				
				if (Mage::getStoreConfig('catalog/emark_cartalert/stop_after_order')){
		            $cartalert = Mage::getResourceModel('emark_cartalert/cartalert')
		                ->cancelAlertsFor($quote->getCustomerEmail());
		        }
			}
    	}
		
        return $this;
    }
} 
