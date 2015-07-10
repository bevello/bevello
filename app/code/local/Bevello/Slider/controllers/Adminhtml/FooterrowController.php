<?php
/**
 * Created by PhpStorm.
 * User: mcoffey
 * Date: 5/28/15
 * Time: 12:43 PM
 */


    class Bevello_Slider_Adminhtml_FooterrowController extends Mage_Adminhtml_Controller_Action
    {

        protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('footerrow/items')
            ->_addBreadcrumb(Mage::helper('adminhtml')->__('Items Manager'), Mage::helper('adminhtml')->__('Item Manager'));
        return $this;
    }

        public function indexAction() {
        $this->_initAction();
        $this->_addContent($this->getLayout()->createBlock('footerrow/adminhtml_footerrow'));
        $this->renderLayout();
    }

        public function editAction()
    {
        $sliderId     = $this->getRequest()->getParam('id');
            $sliderModel  = Mage::getModel('slider/footerrow')->load($sliderId);

            if ($sliderModel->getId() || $sliderId == 0) {

        Mage::register('footerrow_data', $sliderModel);

                $this->loadLayout();
                $this->_setActiveMenu('footerrow/items');

                $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item Manager'), Mage::helper('adminhtml')->__('Item Manager'));
                $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item News'), Mage::helper('adminhtml')->__('Item News'));

                $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

                $this->_addContent($this->getLayout()->createBlock('footerrow/adminhtml_footerrow_edit'))
                    ->_addLeft($this->getLayout()->createBlock('footerrow/adminhtml_footerrow_edit_tabs'));

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

                $sliderModel = Mage::getModel('slider/footerrow');

                    $sliderModel->setId($this->getRequest()->getParam('id'))
                    ->setTitle($postData['title'])
                        ->setType($postData['type'])
                    ->setStatus($postData['status'])
                    ->save();

                    Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Item was successfully saved'));
                    Mage::getSingleton('adminhtml/session')->setSliderData(false);

                    $this->_redirect('*/*/');
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
                $sliderModel = Mage::getModel('slider/footerrow');
                   
                    $sliderModel->setId($this->getRequest()->getParam('id'))
                    ->delete();
                       
                    Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Item was successfully deleted'));
                    $this->_redirect('*/*/');
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
            $this->getLayout()->createBlock('footerrow/adminhtml_footerrow_grid')->toHtml()
        );
    }


}