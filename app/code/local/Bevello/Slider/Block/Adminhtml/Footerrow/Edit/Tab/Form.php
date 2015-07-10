<?php
/**
 * Created by PhpStorm.
 * User: mcoffey
 * Date: 5/28/15
 * Time: 12:37 PM
 */

class Bevello_Slider_Block_Adminhtml_Footerrow_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
{
    $form = new Varien_Data_Form();
    $this->setForm($form);
    $fieldset = $form->addFieldset('footerrow_form', array('legend'=>Mage::helper('slider')->__('Item information')));

    $fieldset->addField('title', 'text', array(
        'label'     => Mage::helper('slider')->__('Title'),
        'class'     => 'required-entry',
        'required'  => true,
        'name'      => 'title',
    ));

    $fieldset->addField('type', 'select', array(
        'label'     => Mage::helper('slider')->__('Ad Type'),
        'name'      => 'type',
        'values'    => array(
            array(
                'value'     => 1,
                'label'     => Mage::helper('slider')->__('Slider'),
            ),

            array(
                'value'     => 0,
                'label'     => Mage::helper('slider')->__('Image'),
            ),
        ),
    ));


    $fieldset->addField('status', 'select', array(
        'label'     => Mage::helper('slider')->__('Status'),
        'name'      => 'status',
        'values'    => array(
            array(
                'value'     => 1,
                'label'     => Mage::helper('slider')->__('Active'),
            ),

            array(
                'value'     => 0,
                'label'     => Mage::helper('slider')->__('Inactive'),
            ),
        ),
    ));

    if ( Mage::getSingleton('adminhtml/session')->getSliderData() )
        {
            $form->setValues(Mage::getSingleton('adminhtml/session')->getSliderData());
            Mage::getSingleton('adminhtml/session')->setSliderData(null);
        } elseif ( Mage::registry('footerrow_data') ) {
    $form->setValues(Mage::registry('footerrow_data')->getData());
}
        return parent::_prepareForm();
    }
}