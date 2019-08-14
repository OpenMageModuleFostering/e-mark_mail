<?php
class Emark_Cartalert_Block_Adminhtml_History_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form(array(
          'id' => 'edit_form',
          'action' => '',
          'method' => 'post'));

      $form->setUseContainer(true);
      $this->setForm($form);
      $hlp = Mage::helper('emark_cartalert');

      $fldInfo = $form->addFieldset('emark_cartalert_info', array('legend'=> $hlp->__('Alert Variables')));
      
      $fldInfo->addField('customer_email', 'text', array(
          'label'     => $hlp->__('Customer E-mail'),
          'name'      => 'customer_email',
      ));
      $fldInfo->addField('customer_name', 'text', array(
          'label'     => $hlp->__('Customer Name'),
          'name'      => 'customer_name',
      ));
      
      $fldInfo->addField('txt', 'textarea', array(
          'label'     => $hlp->__('Message'),
          'name'      => 'txt',
          'style'     => 'width:35em;height:15em;',
      ));

      if ( Mage::registry('history_data') ) {
          $form->setValues(Mage::registry('history_data')->getData());
      }
      
      return parent::_prepareForm();
  }
}