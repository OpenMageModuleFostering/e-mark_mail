<?php
class Emark_Cartalert_Model_Source_Discount extends Varien_Object
{
    public function toOptionArray()
    {
        $vals = array(
                'by_percent' => Mage::helper('salesrule')->__('Percent of product price discount'),
                'by_fixed' => Mage::helper('salesrule')->__('Fixed amount discount'),
                'cart_fixed' => Mage::helper('salesrule')->__('Fixed amount discount for whole cart'),
        );

        $options = array();
        foreach ($vals as $k => $v)
            $options[] = array(
                    'value' => $k,
                    'label' => $v
            );
        
        return $options;
    }
}