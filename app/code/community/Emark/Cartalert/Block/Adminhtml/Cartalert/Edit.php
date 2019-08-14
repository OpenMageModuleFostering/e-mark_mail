<?php

class Emark_Cartalert_Block_Adminhtml_Cartalert_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id'; // ?
        $this->_blockGroup = 'emark_cartalert';
        $this->_controller = 'adminhtml_cartalert';

        $this->_removeButton('reset');
		
        $this->_addButton('send', array(
            'label'     => Mage::helper('emark_cartalert')->__('Save and Send Out'),
            'onclick'   => 'sendAndDelete()',
            'class'     => 'save',
        ), -100);

        $this->_formScripts[] = "
            function sendAndDelete(){
                $('edit_form').action += 'send/edit';
                editForm.submit();
            }
        ";
    }

    public function getHeaderText()
    {
            return Mage::helper('emark_cartalert')->__('Abandoned Cart Alert');
    }
} 