<?php
class Bevello_Catalog_Block_Product_List_Toolbar extends Mage_Catalog_Block_Product_List_Toolbar
{
    /**
     * Retrieve available Order fields list
     *
     * @return array
     */
    public function getAvailableOrders()
    {
		$this->_availableOrder = array(
			'entity_id' => $this->__('Newest Arrivals'),
			'name' => $this->__('Name'),
			'price' => $this->__('Price')
		);
		return $this->_availableOrder;
    }
	
	/**
     * Retrieve current direction
     *
     * @return string
     */
	public function getCurrentDirection()
    {
        $dir = $this->_getData('_current_grid_direction');
        if ($dir) {
            return $dir;
        }

        $directions = array('asc', 'desc');
        $dir = strtolower($this->getRequest()->getParam($this->getDirectionVarName()));
        if ($dir && in_array($dir, $directions)) {
            if ($dir == $this->_direction) {
                Mage::getSingleton('catalog/session')->unsSortDirection();
            } else {
                $this->_memorizeParam('sort_direction', $dir);
            }
        } else {
            //$dir = Mage::getSingleton('catalog/session')->getSortDirection();
			$dir = 'desc';
        }
        // validate direction
        if (!$dir || !in_array($dir, $directions)) {
            $dir = $this->_direction;
        }
        $this->setData('_current_grid_direction', $dir);
        return $dir;
    }
}
