<?php

/**
 * @desc overwrites a core block so we can add a action to the grid
 *
 */

class Emark_Mail_Block_Admin_Newsletter_Subscriber_Grid extends Mage_Adminhtml_Block_Newsletter_Subscriber_Grid
{
	protected function _prepareMassaction()
	{
		$this->setMassactionIdField('subscriber_id');
		$this->getMassactionBlock()->setFormFieldName('subscriber');

		$this->getMassactionBlock()->addItem('unsubscribe', array(
			'label'        => Mage::helper('newsletter')->__('Unsubscribe'),
			'url'          => $this->getUrl('*/*/massUnsubscribe')
		));

		if(!is_null(Mage::getStoreConfig('emark_sections/emark_settings/emark_guest'))) {
			#add action E-mark Mail Sync
			$this->getMassactionBlock()->addItem('E-mark Mail Sync', array(
				'label'    => Mage::helper('mail')->__('E-mark Mail Sync'),
				'url'      => $this->getUrl('mail/sync/mass')
			));
		}
	
		$this->getMassactionBlock()->addItem('delete', array(
			'label'        => Mage::helper('newsletter')->__('Delete'),
			'url'          => $this->getUrl('*/*/massDelete')
		));

		return $this;
	}

}
