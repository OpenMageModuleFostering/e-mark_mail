<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_CatalogInventory
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Emark_Mail_Helper_Connectfields
{

    /**
     * Generate a storable representation of a value
     *
     * @param mixed $value
     * @return string
     */
    protected function _serializeValue($value)
    {
        if (is_array($value)) {
            $data = array();
            foreach ($value as $labelids => $label) {
                if (!array_key_exists($labelids, $data) && $labelids != "__empty") {
                    $data[$labelids] = $label;
                }
            }

            return serialize($data);
        } else {
            return '';
        }
    }


    public function getArrayConnectFields($value) {
	$fields = array();
	$value = $this->_unserializeValue($value);
	if(is_array($value)) {	
		foreach($value as $v) {
			if(is_array($v)) {
				if($v['customer_label'] !== "-- Magento fields --" && $v['subscriber_label'] !== "-- E-mark Mail fields --") {
					$fields[$v["customer_label"]] = $v['subscriber_label'];
				}
			}
		}
		return $fields;
	} else {
		return array();
	}
    }

    /**
     * Create a value from a storable representation
     *
     * @param mixed $value
     * @return array
     */
    protected function _unserializeValue($value)
    {
        if (is_string($value) && !empty($value)) {
            return unserialize($value);
        } else {
            return array();
        }
    }

    /**
     * Check whether value is in form retrieved by _encodeArrayFieldValue()
     *
     * @param mixed
     * @return bool
     */
    protected function _isEncodedArrayFieldValue($value)
    {
        if (!is_array($value)) {
            return false;
        }
        foreach ($value as $_id => $row) {
            if (!is_array($row) || !array_key_exists('customer_label', $row) || !array_key_exists('subscriber_label', $row)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Encode value to be used in Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
     *
     * @param array
     * @return array
     */
    protected function _encodeArrayFieldValue(array $value)
    {
        $result = array();
        foreach ($value as $groupId => $qty) {
            $_id = Mage::helper('core')->uniqHash('_');
            $result[$_id] = array(
                'customer_group_id' => $groupId,
                'min_sale_qty' => $qty,
            );
        }
        return $result;
    }

    /**
     * Decode value from used in Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
     *
     * @param array
     * @return array
     */
    protected function _decodeArrayFieldValue(array $value)
    {
        $result = array();
        foreach ($value as $_id => $row) {
            if (!is_array($row) || !array_key_exists('customer_label', $row) || !array_key_exists('subscriber_label', $row)) {
                continue;
            }
            $subMag = $row['customer_label'];
            $subEm = $row['subscriber_label'];
            $result[$subMag] = $subEm;
        }
        return $result;
    }

    /**
     * Make value readable by Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
     *
     * @param mixed $value
     * @return array
     */
    public function makeArrayFieldValue($value)
    {
        $value = $this->_unserializeValue($value);
        if (!$this->_isEncodedArrayFieldValue($value)) {
            $value = $this->_encodeArrayFieldValue($value);
        }
        return $value;
    }

    /**
     * Make value ready for store
     *
     * @param mixed $value
     * @return string
     */
    public function makeStorableArrayFieldValue($value)
    {
        if ($this->_isEncodedArrayFieldValue($value)) {
            $value = $this->_decodeArrayFieldValue($value);
        }
        $value = $this->_serializeValue($value);
        return $value;
    }
}
