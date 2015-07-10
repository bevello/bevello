<?php
/**
 * Created by PhpStorm.
 * User: mcoffey
 * Date: 5/28/15
 * Time: 12:43 PM
 */


    class Bevello_Slider_Adminhtml_FooteradsController extends Mage_Adminhtml_Controller_Action
    {

        protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('footerads/items')
            ->_addBreadcrumb(Mage::helper('adminhtml')->__('Items Manager'), Mage::helper('adminhtml')->__('Item Manager'));
        return $this;
    }

        public function indexAction() {
            $rowId = $this->getRequest()->getParam('row_id');
            if($rowId) Mage::register('footerrow_rowid', $rowId);
            $this->_initAction();
            $this->_addContent($this->getLayout()->createBlock('footerads/adminhtml_footerads'));
            $this->renderLayout();
    }

        public function editAction()
    {
            $sliderId     = $this->getRequest()->getParam('id');
            $sliderModel  = Mage::getModel('slider/footerads')->load($sliderId);

            $rowId = $this->getRequest()->getParam('row_id');
            if($rowId) Mage::register('footerrow_rowid', $rowId);

            if ($sliderModel->getId() || $sliderId == 0) {
                $sliderModel->setData('rowId', Mage::registry('footerrow_rowid'));
                Mage::register('footerads_data', $sliderModel);
     
                $this->loadLayout();
                $this->_setActiveMenu('footerads/items');
               
                $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item Manager'), Mage::helper('adminhtml')->__('Item Manager'));
                $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item News'), Mage::helper('adminhtml')->__('Item News'));
               
                $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
               
                $this->_addContent($this->getLayout()->createBlock('footerads/adminhtml_footerads_edit'))
                    ->_addLeft($this->getLayout()->createBlock('footerads/adminhtml_footerads_edit_tabs'));
                   
                $this->renderLayout();
            } else {
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('slider')->__('Item does not exist'));
        $this->_redirect('*/*/');
    }
        }

        public function newAction()
    {

        $this->_forward('edit');
    }

        public function saveAction()
    {
        if ( $this->getRequest()->getPost() ) {
            $postData = $this->getRequest()->getPost();
            try {
                //upload image
                if(isset($_FILES['image']['name']) and (file_exists($_FILES['image']['tmp_name']))) {
                    try {
                        $uploader = new Varien_File_Uploader('image');
                        $uploader->setAllowedExtensions(array('jpg','jpeg','gif','png')); // or pdf or anything


                        $uploader->setAllowRenameFiles(false);

                        // setAllowRenameFiles(true) -> move your file in a folder the magento way
                        // setAllowRenameFiles(true) -> move your file directly in the $path folder
                        $uploader->setFilesDispersion(false);

                        $path = Mage::getBaseDir('media') .'/bevello_slider/' . DS ;

                        //add timestamp to image name
                        $filename = explode('.',$_FILES['image']['name']);
                        $name = $filename[0] . '_' .time(). '.' . $filename[1];

                        $uploader->save($path, $name);

                        $postData['image'] = 'bevello_slider/'. $name;
                    }catch(Exception $e) {

                    }
                } elseif (isset($postData['image']['delete']) && $postData['image']['delete'] == 1) {
                    $path = Mage::getBaseDir('media') . '/bevello_slider/';
                    if(file_exists($path . $postData['image']['value'])) {
                        unlink($path . $postData['image']['value']);
                    }
                    $postData['image'] = '';
                } elseif (isset($_POST['image']['value'])) {
                    $postData['image'] = $_POST['image']['value'];
                } else {
                    $postData['image'] = '';
                }


                $sliderModel = Mage::getModel('slider/footerads');
                    $sliderModel->setId($this->getRequest()->getParam('id'))
                    ->setTitle($postData['title'])
                    ->setData('link',$postData['link'])
                    ->setData('image',$postData['image'])
                    ->setData('alt',$postData['alt'])
                    ->setData('event_category', $postData['event_category'])
                    ->setData('event_label', $postData['event_label'])
                    ->setStatus($postData['status'])
                    ->setData('rowId',$postData['rowId'])
                    ->save();
                   
                    Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Item was successfully saved'));
                    Mage::getSingleton('adminhtml/session')->setSliderData(false);
     
                    $this->_redirect('*/*/',array('row_id' => $postData['rowId']));
                    return;
                } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setSliderData($this->getRequest()->getPost());
                    $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                    return;
                }
        }
        $this->_redirect('*/*/');
    }

        public function deleteAction()
    {
        if( $this->getRequest()->getParam('id') > 0 ) {
            try {
                $sliderModel = Mage::getModel('slider/footerads');
                    $row_id = $sliderModel->getData('rowId');
                   
                    $sliderModel->setId($this->getRequest()->getParam('id'))
                    ->delete();
                       
                    Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Item was successfully deleted'));
                    $this->_redirect('*/*/',array('row_id' => $row_id));
                } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
        }
        $this->_redirect('*/*/');
    }
        /**
         * Product grid for AJAX request.
         * Sort and filter result for example.
         */
        public function gridAction()
    {
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('footerads/adminhtml_footerads_grid')->toHtml()
        );
    }


}