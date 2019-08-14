<?php
class Emark_Cartalert_Block_Adminhtml_History extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    parent::__construct();
    $this->_controller = 'adminhtml_history';
    $this->_blockGroup = 'emark_cartalert';
    $this->_headerText = Mage::helper('emark_cartalert')->__('Sent Alerts');
    $this->_removeButton('add'); 
  }
}
