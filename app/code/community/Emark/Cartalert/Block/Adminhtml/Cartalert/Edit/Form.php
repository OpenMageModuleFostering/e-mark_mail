<?php

class Emark_Cartalert_Block_Adminhtml_Cartalert_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form(array(
          'id' => 'edit_form',
          'action' => $this->getUrl('*/*/save', array('id' => $this->getRequest()->getParam('id'))),
          'method' => 'post'));

      $form->setUseContainer(true);
      $this->setForm($form);
      $hlp = Mage::helper('emark_cartalert');

      $fldInfo = $form->addFieldset('emark_cartalert_info', array('legend'=> $hlp->__('Alert Variables')));
      
      $fldInfo->addField('store_id', 'select', array(
          'label'     => $hlp->__('Store View'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'store_id',
          'values'   => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm()
      ));
      
      $fldInfo->addField('follow_up', 'select', array(
          'label'        => $hlp->__('Follow Up'),
          'name'         => 'follow_up',
          'options'      => array(
            'first'     => $hlp->__('First'),
            'second'    => $hlp->__('Second'),
            'third'     => $hlp->__('Third'),
          ),
      ));

      $fldInfo->addField('sheduled_at', 'date', array(
          'label'        => $hlp->__('Alert Will Be Sent On'),
          'image'        => $this->getSkinUrl('images/grid-cal.gif'),
          'format'       => Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT),
          'name'         => 'sheduled_at',
      ));
     
      $fldInfo->addField('customer_email', 'text', array(
          'label'     => $hlp->__('Customer E-mail'),
          'class'     => 'required-entry validate-email',
          'required'  => true,
          'name'      => 'customer_email',
      ));
      $fldInfo->addField('customer_fname', 'text', array(
          'label'     => $hlp->__('Customer First Name'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'customer_fname',
      ));
      $fldInfo->addField('customer_lname', 'text', array(
          'label'     => $hlp->__('Customer Last Name'),
          'name'      => 'customer_lname',
      ));
      
      $fldInfo->addField('products', 'textarea', array(
          'label'     => $hlp->__('Products'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'products',
          'style'     => 'width:35em;height:15em;',
      ));
      $fldInfo->addField('is_preprocessed', 'hidden', array(
          'name'      => 'is_preprocessed',
          'value'     => 1,
      ));

      if ( Mage::registry('cartalert_data') ) {
          $form->setValues(Mage::registry('cartalert_data')->getData());
      }
      
      return parent::_prepareForm();
  }
}