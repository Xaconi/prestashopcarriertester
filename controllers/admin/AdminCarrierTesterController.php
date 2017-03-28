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
    	$this->addJS("http://localhost/store1611/js/jquery/plugins/jquery.typewatch.js");

    	$tplPath = _PS_MODULE_DIR_ . $this->module . '/views/templates/admin/view.tpl';
    	$data = $this->context->smarty->createTemplate($tplPath, $this->context->smarty);
    	return $data->fetch();
        // return parent::renderView();
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
}
