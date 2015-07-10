<?php
/**
 * Created by PhpStorm.
 * User: mcoffey
 * Date: 5/28/15
 * Time: 12:22 PM
 */

class Bevello_Slider_Block_Adminhtml_Slider_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
{
    parent::__construct();

    $this->_objectId = 'id';
    $this->_blockGroup = 'slider';
    $this->_controller = 'adminhtml_slider';

    $this->_updateButton('save', 'label', Mage::helper('slider')->__('Save Item'));
    $this->_updateButton('delete', 'label', Mage::helper('slider')->__('Delete Item'));
}

    public function getHeaderText()
{
    if( Mage::registry('slider_data') && Mage::registry('slider_data')->getId() ) {
        return Mage::helper('slider')->__("Edit Item '%s'", $this->htmlEscape(Mage::registry('slider_data')->getTitle()));
    } else {
        return Mage::helper('slider')->__('Add Item');
    }
}
}