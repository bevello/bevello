<?php
/**
 * Created by PhpStorm.
 * User: mcoffey
 * Date: 5/28/15
 * Time: 12:22 PM
 */

class Bevello_Slider_Block_Adminhtml_Footerads_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
{


    $this->_objectId = 'id';
    $this->_blockGroup = 'footerads';
    $this->_controller = 'adminhtml_footerads';

    $this->_updateButton('save', 'label', Mage::helper('slider')->__('Save Item'));
    $this->_updateButton('delete', 'label', Mage::helper('slider')->__('Delete Item'));

    $this->_addButton('btnBack', array(
        'label'     => Mage::helper('adminhtml')->__('Back'),
        'onclick'   => "setLocation('" . $this->getUrl('*/*/index', array('row_id' => Mage::registry('footerrow_rowid'))) . "')",
        'class'   => 'back'
    ));
    $this->_addButton('btnReset', array(
        'label'     => Mage::helper('adminhtml')->__('Reset'),
        'onclick'   => "setLocation('setLocation(window.location.href)')",
        'class'   => 'reset'
    ));

    parent::__construct();
    $this->_removeButton('back');
    $this->_removeButton('reset');
}

    public function getHeaderText()
{
    if( Mage::registry('footerads_data') && Mage::registry('footerads_data')->getId() ) {
        return Mage::helper('slider')->__("Edit Item '%s'", $this->htmlEscape(Mage::registry('footerads_data')->getTitle()));
    } else {
        return Mage::helper('slider')->__('Add Item');
    }
}
}