<?php
/**
 * Created by PhpStorm.
 * User: mcoffey
 * Date: 5/28/15
 * Time: 12:37 PM
 */

class Bevello_Slider_Block_Adminhtml_Footerads_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
{
    $form = new Varien_Data_Form();
    $this->setForm($form);
    $fieldset = $form->addFieldset('footerads_form', array('legend'=>Mage::helper('slider')->__('Item information')));

    $fieldset->addField('title', 'text', array(
        'label'     => Mage::helper('slider')->__('Title'),
        'class'     => 'required-entry',
        'required'  => true,
        'name'      => 'title',
    ));

    $fieldset->addField('link', 'text', array(
        'label'     => Mage::helper('slider')->__('Link'),
        'class'     => 'required-entry',
        'required'  => true,
        'name'      => 'link',
    ));

    $fieldset->addField('image', 'image', array(
        'label'     => Mage::helper('slider')->__('Image'),
        'required'  => false,
        'name'      => 'image',
    ));

    $fieldset->addField('alt', 'text', array(
        'label'     => Mage::helper('slider')->__('Alt'),
        'required'  => false,
        'name'      => 'alt',
    ));

    $fieldset->addField('event_category', 'text', array(
        'label'     => Mage::helper('slider')->__('Event Category'),
        'required'  => false,
        'name'      => 'event_category',
    ));

    $fieldset->addField('event_label', 'text', array(
        'label'     => Mage::helper('slider')->__('Event Label'),
        'required'  => false,
        'name'      => 'event_label',
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

    $fieldset->addField('rowId', 'hidden', array(
        'label' => Mage::helper('slider')->__('Row'),
        'class' => 'required-entry',
        'required' => true,
        'name' => 'rowId'
    ));


    if ( Mage::getSingleton('adminhtml/session')->getFooteradsData() )
    {
        $data = Mage::getSingleton('adminhtml/session')->getFooteradsData();
        $data->setData('rowID', Mage::registry('footerrow_rowid'));
        $data["rowId"] = Mage::registry('footerrow_rowid');
        $form->setValues($data);
        Mage::getSingleton('adminhtml/session')->setFooteradsData(null);
    } elseif ( Mage::registry('footerads_data') ) {
        $form->setValues(Mage::registry('footerads_data')->getData());
    } else {
        $data["rowId"] = Mage::registry('footerrow_rowid');
        $form->setValues($data);
    }
    
        return parent::_prepareForm();
    }
}