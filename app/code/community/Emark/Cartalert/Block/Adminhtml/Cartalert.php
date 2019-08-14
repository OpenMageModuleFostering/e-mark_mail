<?php
class Emark_Cartalert_Block_Adminhtml_Cartalert extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
      
    $this->_addButton('generate', array(
        'label'     => Mage::helper('emark_cartalert')->__('Update Queue Now'),
        'onclick'   => "location.href='".$this->getUrl('*/*/generate')."';return false;",
        'class'     => '',
    ));       
      
    $this->_controller = 'adminhtml_cartalert';
    $this->_blockGroup = 'emark_cartalert';
    $this->_headerText = Mage::helper('emark_cartalert')->__('Alerts Queue');
    $this->_addButtonLabel = Mage::helper('emark_cartalert')->__('Add Alert');
    parent::__construct();
  }
}