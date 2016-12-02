<?php

class WaPoNe_StatusProductsManager_Model_StatusProductsManager extends Mage_Core_Model_Abstract
{

    const ACTIVE = 1;
    const DEACTIVE = 0;

    protected function _construct()
    {
        $this->_init('statusproductsmanager/statusproductsmanager');
    }

    /* WaPoNe (02-12-2016): Setting enable products */
    private function enableProducts()
    {
        // Check dates
        if ($this->getActionDate('statusproductsmanager/statusproductsmanager_group/activestartdate', 'statusproductsmanager/statusproductsmanager_group/activestarttime')) {

            Mage::log('Setting enable products', null, 'wapone.log');

            //Products to active
            $products_to_active = $this->getProducts('statusproductsmanager/statusproductsmanager_group/productstoactivate');

            for ($row = 0; $row < count($products_to_active); $row++) {
                $sku = $products_to_active[$row];
                $catalog = Mage::getModel('catalog/product');
                $productId = $catalog->getIdBySku($sku);
                $storeId = 1; //store id
                Mage::getModel('catalog/product_status')->updateProductStatus($productId, $storeId, Mage_Catalog_Model_Product_Status::STATUS_ENABLED);
            }

            Mage::getConfig()->saveConfig('statusproductsmanager/statusproductsmanager_group/module_activation_status', '0', 'default', 0);
        }
    }

    /* WaPoNe (02-12-2016): Setting disable products */
    private function disableProducts()
    {
        // Check dates
        if ($this->getActionDate('statusproductsmanager/statusproductsmanager_group/deactivestartdate', 'statusproductsmanager/statusproductsmanager_group/deactivestarttime')) {

            Mage::log('Setting disable products', null, 'wapone.log');

            //Products to deactive
            $products_to_deactive = $this->getProducts('statusproductsmanager/statusproductsmanager_group/productstodeactivate');

            for ($row = 0; $row < count($products_to_deactive); $row++) {
                $sku = $products_to_deactive[$row];
                $catalog = Mage::getModel('catalog/product');
                $productId = $catalog->getIdBySku($sku);
                $storeId = 1; //store id
                Mage::getModel('catalog/product_status')->updateProductStatus($productId, $storeId, Mage_Catalog_Model_Product_Status::STATUS_DISABLED);
            }

            Mage::getConfig()->saveConfig('statusproductsmanager/statusproductsmanager_group/module_deactivation_status', '0', 'default', 0);
        }
    }

    public function manageProductsStatus()
    {
        if ((int)Mage::getStoreConfig('statusproductsmanager/statusproductsmanager_group/module_activation_status') === 1) :
            $this->enableProducts();
        endif;

        if ((int)Mage::getStoreConfig('statusproductsmanager/statusproductsmanager_group/module_deactivation_status') === 1) :
            $this->disableProducts();
        endif;

    }


    /* WaPoNe (02-12-2016): Checking if it's time to start script */
    private function getActionDate($date_param, $time_param)
    {
        $date = Mage::getStoreConfig($date_param);
        $time = Mage::getStoreConfig($time_param);

        if (!empty($date)) {
            $now = Mage::app()->getLocale()->date()->toString('yy-MM-dd HH.mm');
            $date1 = new DateTime($now);

            $arr_date = explode("-", $date);
            $arr_time = explode(",", $time);

            try {
                $date2 = new DateTime($arr_date[2] . "-" . $arr_date[1] . "-" . $arr_date[0] . " " . $arr_time[0] . ":" . $arr_time[1] . ":" . $arr_time[2]);
            } catch (Exception $e) {
                Mage::log($e->getMessage(), null, 'wapone.log');
            }
            //Mage::log("Date1:".$date1->format('Y-m-d H:i:s')." - Date2:".$date2->format('Y-m-d H:i:s'), null, "wapone.log");

            if ($date1 >= $date2) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }

    }

    /* WaPoNe (02-12-2016): Obtaining products list to enable/disable */
    public function getProducts($param)
    {
        $methods = Mage::getStoreConfig($param);
        $arr_result = array();

        if (!empty($methods)):
            $arr_result = explode(";", $methods);
        endif;

        return $arr_result;
    }

}
