<?php

class AdminCarrierTesterController extends AdminController
{
    protected $position_identifier = 'id_carrier';

    public function __construct()
    {
    	$this->context = Context::getContext();
        $this->bootstrap = true;
        $this->display = 'view';
        $this->meta_title = $this->l('Prestashop carrier tester');
        $this->module = 'prestashopcarriertester';
        /*if (!$this->module->active)
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminHome'));*/
        
        parent::__construct();
    }

    public function initToolbar()
    {
        parent::initToolbar();
    }

    public function initPageHeaderToolbar()
    {
        parent::initPageHeaderToolbar();
    }

    public function renderView()
    {
    	if(Tools::getValue("ajax") != null){
    		switch (Tools::getValue("action")) {
    			case 'getAddresses' :
    				$this->getAddresses(Tools::getValue("customerId"));
    				break;

                case 'calculateCarriers' :
                    $this->calculateCarriers(Tools::getValue("customerId"), Tools::getValue('products'), Tools::getValue('addressId'));
                    break;
    		}
    	} else {
    		// TODO Arreglar la ruta...
    		$this->addJS("http://localhost/store1611/js/jquery/plugins/jquery.typewatch.js");
    		$this->addJS("http://localhost/store1611/js/jquery/plugins/jquery.chosen.js");
            $this->addCSS("http://localhost/store1611/modules/prestashopcarriertester/views/css/back.css");

            $customers = Customer::getCustomers();
            $products = Product::getProducts($this->context->language->id, 0, 100000, 'name', 'ASC');
            /*var_dump($products);*/

            $this->context->smarty->assign(
                array(
                    'customers' => $customers,
                    'products' => $products,
                ));

	    	$tplPath = _PS_MODULE_DIR_ . $this->module . '/views/templates/admin/view.tpl';
	    	$data = $this->context->smarty->createTemplate($tplPath, $this->context->smarty);
	    	return $data->fetch();
	        // return parent::renderView();
    	}
    }

    public function renderList()
    {
        return parent::renderList();
    }

    public function renderForm()
    {
        return parent::renderForm();
    }

    public function postProcess()
    {
        parent::postProcess();
    }

    public function getAddresses($customerId){
    	$idLang = $this->context->language->id;
        $customerObj = new Customer((int) $customerId);
        $addresses = $customerObj->getAddresses($this->context->language->id);

        $resposta['addresses'] = $addresses;
    	die(json_encode($resposta));
    }

    public function calculateCarriers($customerId, $products, $address) {

        $carriersArray = array();

        $cart = new Cart();
        $cart->id_customer = (int)($customerId);
        $cart->id_address_delivery = (int)  ($address);
        $cart->id_address_invoice = $cart->id_address_delivery;
        $cart->id_lang = (int)($this->context->cookie->id_lang);
        $cart->id_currency = (int)($this->context->cookie->id_currency);
        $cart->id_carrier = 1;
        $cart->recyclable = 0;
        $cart->gift = 0;
        $cart->add();
        $this->context->cookie->id_cart = (int)($cart->id);    
        $cart->update();
        
        foreach($products as $key => $product){
            $cart->updateQty(1, (int)$product);
        }

        $zone = Address::getZoneById($address);

        $carriers = Carrier::getCarriersForOrder($zone, null, $cart);

        // Save the results on logs

        $cart->delete();

        die(json_encode($carriers));
    }
}
