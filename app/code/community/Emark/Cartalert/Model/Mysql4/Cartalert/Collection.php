<?php
class Emark_Cartalert_Model_Mysql4_Cartalert_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('emark_cartalert/cartalert');
    }
    
    public function addReadyForSendingFilter()
    {
        $this->getSelect()->where('sheduled_at < ?',now());
            //->where('status = ?', 'pending');
        return $this;
    } 
}
