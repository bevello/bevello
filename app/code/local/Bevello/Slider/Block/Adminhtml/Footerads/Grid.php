<?php
/**
 * Created by PhpStorm.
 * User: mcoffey
 * Date: 5/28/15
 * Time: 12:24 PM
 */
class Bevello_Slider_Block_Adminhtml_Footerads_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
{
    parent::__construct();

    // This is the primary key of the database
    $this->setDefaultSort('slider_id');
    $this->setDefaultDir('ASC');
    $this->setSaveParametersInSession(true);
    $this->setUseAjax(true);

}

    public function getMainButtonsHtml()
    {

        $html = parent::getMainButtonsHtml();//get the parent class buttons
        $addButton = $this->getLayout()->createBlock('adminhtml/widget_button') //create the add button
            ->setData(array(
                'label'     => Mage::helper('adminhtml')->__('Back'),
                'onclick'   => "setLocation('" . $this->getUrl('*/adminhtml_footerrow/index') . "')",
                'class'   => 'back'
            ))->toHtml();
        return $addButton.$html;
    }

    protected function _prepareCollection()
{
    $collection = Mage::getModel('footerads/footerads')->getCollection()->addFilter('rowId',Mage::registry('footerrow_rowid'));
    $this->setCollection($collection);
    return parent::_prepareCollection();
}

    protected function _prepareColumns()
{
    $this->addColumn("image", array(
        "header" => Mage::helper("slider")->__("Image"),
        "index" => "image",
        "renderer" =>"Bevello_Slider_Block_Adminhtml_Renderer_Image",
    ));

    $this->addColumn('title', array(
        'header'    => Mage::helper('slider')->__('Title'),
        'align'     =>'left',
        'index'     => 'title',
    ));

    $this->addColumn('title', array(
        'header'    => Mage::helper('slider')->__('Title'),
        'align'     =>'left',
        'index'     => 'title',
    ));

    $this->addColumn('link', array(
        'header'    => Mage::helper('slider')->__('Link'),
        'align'     =>'left',
        'index'     => 'link',
    ));

    $this->addColumn('status', array(

        'header'    => Mage::helper('slider')->__('Status'),
        'align'     => 'left',
        'width'     => '80px',
        'index'     => 'status',
        'type'      => 'options',
        'options'   => array(
            1 => 'Active',
            0 => 'Inactive',
        ),
    ));

    return parent::_prepareColumns();
}

    public function getRowUrl($row)
{
    return $this->getUrl('*/*/edit', array('id' => $row->getId(), 'row_id' =>Mage::registry('footerrow_rowid')));
}

    public function getGridUrl()
{
    return $this->getUrl('*/*/grid', array('_current'=>true,'row_id' =>Mage::registry('footerrow_rowid')));
}


}