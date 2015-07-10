<?php
/**
 * Created by PhpStorm.
 * User: mcoffey
 * Date: 5/28/15
 * Time: 12:24 PM
 */
class Bevello_Slider_Block_Adminhtml_Footerrow_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
{
    parent::__construct();
    $this->setId('footerrowGrid');
    // This is the primary key of the database
    $this->setDefaultSort('row_id');
    $this->setDefaultDir('ASC');
    $this->setSaveParametersInSession(true);
    $this->setUseAjax(true);
}

    protected function _prepareCollection()
{
    $collection = Mage::getModel('footerrow/footerrow')->getCollection();
    $collection->getSelect()
        ->columns('GROUP_CONCAT(e.image SEPARATOR \',\') AS preview')
        ->joinleft(array('e' => 'bve_bevello_footerads'), 'e.rowId = main_table.rowId and e.status = 1',array('slider_id'=>'slider_id'))
        ->group('e.rowId');

    $this->setCollection($collection);

    return parent::_prepareCollection();
}

    protected function _prepareColumns()
{
    $this->addColumn('preview', array(
        'header'    => Mage::helper('slider')->__('Preview'),
        'align'     =>'left',
        'index'     => 'preview',
        "renderer" =>"Bevello_Slider_Block_Adminhtml_Renderer_Miniview",
    ));

    $this->addColumn('title', array(
        'header'    => Mage::helper('slider')->__('Title'),
        'align'     =>'left',
        'index'     => 'title',
    ));

    $this->addColumn('type', array(

        'header'    => Mage::helper('slider')->__('Type'),
        'align'     => 'left',
        'width'     => '80px',
        'index'     => 'type',
        'type'      => 'options',
        'options'   => array(
            1 => 'Slider',
            0 => 'Image',
        ),
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

    $this->addColumn('action',
        array(
            'header' => Mage::helper('slider')->__('Action'),
            'width' => '100',
            'type' => 'action',
            'getter' => 'getId',
            'actions' => array(
                array(
                    'caption' => Mage::helper('slider')->__('Edit'),
                    'url' => array('base'=> '*/*/edit'),
                    'field' => 'id'
                )),
            'filter' => false,
            'sortable' => false,
            'index' => 'stores',
            'is_system' => true,
        ));


    return parent::_prepareColumns();
}

    public function getRowUrl($row)
{
    return $this->getUrl('*/adminhtml_footerads/index', array('row_id' => $row->getId()));
}

    public function getGridUrl()
{
    return $this->getUrl('*/*/grid', array('_current'=>true));
}


}