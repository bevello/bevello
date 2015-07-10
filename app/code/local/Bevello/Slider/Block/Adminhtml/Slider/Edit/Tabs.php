<?php
/**
 * Created by PhpStorm.
 * User: mcoffey
 * Date: 5/28/15
 * Time: 12:36 PM
 */
class Bevello_Slider_Block_Adminhtml_Slider_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

    public function __construct()
{
    parent::__construct();
    $this->setId('slider_tabs');
    $this->setDestElementId('edit_form');
    $this->setTitle(Mage::helper('slider')->__('Slider Image'));
}

    protected function _beforeToHtml()
{
    $this->addTab('form_section', array(
        'label'     => Mage::helper('slider')->__('Item Information'),
        'title'     => Mage::helper('slider')->__('Item Information'),
        'content'   => $this->getLayout()->createBlock('slider/adminhtml_slider_edit_tab_form')->toHtml(),
    ));

    return parent::_beforeToHtml();
}
}