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
    			case 'getProducts':
    				$this->getProducts(Tools::getValue("customerId"));
    				break;
    		}
    	} else {
    		// TODO Arreglar la ruta...
    		$this->addJS("http://localhost/store1611/js/jquery/plugins/jquery.typewatch.js");
    		$this->addJS("http://localhost/store1611/js/jquery/plugins/jquery.chosen.js");

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

    public function getProducts($customerId){
    	$idLang = $this->context->language->id;
    	$products = Product::getProducts($idLang, 0, 10000000, 'p.id_product', 'asc');
    	die(json_encode($products));
    }
}
