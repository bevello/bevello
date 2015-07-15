<?php
/**
 * Created by PhpStorm.
 * User: mcoffey
 * Date: 5/28/15
 * Time: 12:20 PM
 */
class Bevello_Slider_Block_Adminhtml_Footerads extends Mage_Adminhtml_Block_Widget_Grid_Container
{

    public function __construct()
{
    $this->_controller = 'adminhtml_footerads';
    $this->_blockGroup = 'footerads';
    $this->_headerText = Mage::helper('slider')->__('Item Manager');
    $this->_addButtonLabel = Mage::helper('slider')->__('Add Item');

    $this->_addButton('btnAdd', array(
        'label'     => Mage::helper('adminhtml')->__('Add Item'),
        'onclick'   => "setLocation('" . $this->getUrl('*/*/new', array('row_id' => Mage::registry('footerrow_rowid'))) . "')",
        'class'   => 'add'
    ));

    parent::__construct();

    //Remove original add button
    $this->_removeButton('add');
}

}