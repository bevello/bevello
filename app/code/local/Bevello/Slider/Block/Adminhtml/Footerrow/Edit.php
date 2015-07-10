<?php
/**
 * Created by PhpStorm.
 * User: mcoffey
 * Date: 5/28/15
 * Time: 12:22 PM
 */

class Bevello_Slider_Block_Adminhtml_Footerrow_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
{
    parent::__construct();

    $this->_objectId = 'id';
    $this->_blockGroup = 'footerrow';
    $this->_controller = 'adminhtml_footerrow';

    $this->_updateButton('save', 'label', Mage::helper('slider')->__('Save Item'));
    $this->_updateButton('delete', 'label', Mage::helper('slider')->__('Delete Item'));
}

    public function getHeaderText()
{
    if( Mage::registry('footerrow_data') && Mage::registry('footerrow_data')->getId() ) {
        return Mage::helper('slider')->__("Edit Item '%s'", $this->htmlEscape(Mage::registry('footerrow_data')->getTitle()));
    } else {
        return Mage::helper('slider')->__('Add Item');
    }
}
}