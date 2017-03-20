<?php
/**
* 2007-2015 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2015 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

class Prestashopcarriertester extends CarrierModule
{
    protected $config_form = false;

    public function __construct()
    {
        $this->name = 'prestashopcarriertester';
        $this->tab = 'shipping_logistics';
        $this->version = '0.1.0';
        $this->author = 'Nicolás Giacconi';
        $this->need_instance = 0;

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        $this->moduleTab = 14;
        $this->idTab = 0;

        parent::__construct();

        $this->displayName = $this->l('Prestashop Carrier Tester');
        $this->description = $this->l('A easy-fast carrier tester for Prestashop');
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {
        if (extension_loaded('curl') == false)
        {
            $this->_errors[] = $this->l('You have to enable the cURL extension on your server to install this module');
            return false;
        }

        $position = Tab::getNewLastPosition($this->moduleTab);

        $sql = 'INSERT INTO `'._DB_PREFIX_.'tab`
            (`id_parent`,`class_name`,`module`,`position`,`active`,`hide_host_mode`)
            VALUES ('.$this->moduleTab.', "AdminCarrierTester", "prestashopcarriertester", '.$position.', 1, 0)';

        if(Db::getInstance()->execute($sql) == false){
            $this->_errors[] = $this->l('Error on the MySQL query');
            return false;
        }

        $languages = array();
        $languages[] = Language::getIdByIso('es');
        $languages[] = Language::getIdByIso('ca');
        $languages[] = Language::getIdByIso('en');

        $tabId = $this->getTabId('AdminCarrierTester');

        foreach ($languages as $key => $lang) {
            if($lang != 0){
                $sql = 'INSERT INTO `'._DB_PREFIX_.'tab_lang`
                (`id_tab`,`id_lang`,`name`)
                VALUES ('.$tabId.', ' . $lang . ', "Prestashop Carrier Tester")';

                if(Db::getInstance()->execute($sql) == false){
                    $this->_errors[] = $this->l('Error on the MySQL query');
                    return false;
                }
            }
        }

        return parent::install() &&
            $this->registerHook('header') &&
            $this->registerHook('backOfficeHeader') &&
            $this->registerHook('updateCarrier');
    }

    public function uninstall()
    {
        Configuration::deleteByName('PRESTASHOPCARRIERTESTER_LIVE_MODE');

        if(Tab::getIdFromClassName('AdminCarrierTester') != false)
            $tabId = Tab::getIdFromClassName('AdminCarrierTester');
        else
            $tabId = 0;

        $sql = 'DELETE FROM `'._DB_PREFIX_.'tab`
        WHERE id_tab = ' . $tabId;

        if(Db::getInstance()->execute($sql) == false){
            $this->_errors[] = $this->l('Error deleting the PrestashopCarrierTester Tab');
            return false;
        }

        $sql = 'DELETE FROM `'._DB_PREFIX_.'tab_lang`
        WHERE id_tab = ' . $tabId;

        if(Db::getInstance()->execute($sql) == false){
            $this->_errors[] = $this->l('Error deleting the PrestashopCarrierTester lang Tabs');
            return false;
        }

        return parent::uninstall();
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        /**
         * If values have been submitted in the form, process.
         */
        if (((bool)Tools::isSubmit('submitPrestashopcarriertesterModule')) == true) {
            $this->postProcess();
        }

        $this->context->smarty->assign('module_dir', $this->_path);

        $output = $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');

        return $output.$this->renderForm();
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitPrestashopcarriertesterModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigForm()));
    }

    /**
     * Create the structure of your form.
     */
    protected function getConfigForm()
    {
        return array(
            'form' => array(
                'legend' => array(
                'title' => $this->l('Settings'),
                'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Live mode'),
                        'name' => 'PRESTASHOPCARRIERTESTER_LIVE_MODE',
                        'is_bool' => true,
                        'desc' => $this->l('Use this module in live mode'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-envelope"></i>',
                        'desc' => $this->l('Enter a valid email address'),
                        'name' => 'PRESTASHOPCARRIERTESTER_ACCOUNT_EMAIL',
                        'label' => $this->l('Email'),
                    ),
                    array(
                        'type' => 'password',
                        'name' => 'PRESTASHOPCARRIERTESTER_ACCOUNT_PASSWORD',
                        'label' => $this->l('Password'),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        return array(
            'PRESTASHOPCARRIERTESTER_LIVE_MODE' => Configuration::get('PRESTASHOPCARRIERTESTER_LIVE_MODE', true),
            'PRESTASHOPCARRIERTESTER_ACCOUNT_EMAIL' => Configuration::get('PRESTASHOPCARRIERTESTER_ACCOUNT_EMAIL', 'contact@prestashop.com'),
            'PRESTASHOPCARRIERTESTER_ACCOUNT_PASSWORD' => Configuration::get('PRESTASHOPCARRIERTESTER_ACCOUNT_PASSWORD', null),
        );
    }

    /**
     * Save form data.
     */
    protected function postProcess()
    {
        $form_values = $this->getConfigFormValues();

        foreach (array_keys($form_values) as $key) {
            Configuration::updateValue($key, Tools::getValue($key));
        }
    }

    public function getOrderShippingCost($params, $shipping_cost)
    {
        if (Context::getContext()->customer->logged == true)
        {
            $id_address_delivery = Context::getContext()->cart->id_address_delivery;
            $address = new Address($id_address_delivery);

            /**
             * Send the details through the API
             * Return the price sent by the API
             */
            return 10;
        }

        return $shipping_cost;
    }

    public function getOrderShippingCostExternal($params)
    {
        return true;
    }

    protected function addCarrier()
    {
        $carrier = new Carrier();

        $carrier->name = $this->l('My super carrier');
        $carrier->is_module = true;
        $carrier->active = 1;
        $carrier->range_behavior = 1;
        $carrier->need_range = 1;
        $carrier->shipping_external = true;
        $carrier->range_behavior = 0;
        $carrier->external_module_name = $this->name;
        $carrier->shipping_method = 2;

        foreach (Language::getLanguages() as $lang)
            $carrier->delay[$lang['id_lang']] = $this->l('Super fast delivery');

        if ($carrier->add() == true)
        {
            @copy(dirname(__FILE__).'/views/img/carrier_image.jpg', _PS_SHIP_IMG_DIR_.'/'.(int)$carrier->id.'.jpg');
            Configuration::updateValue('MYSHIPPINGMODULE_CARRIER_ID', (int)$carrier->id);
            return $carrier;
        }

        return false;
    }

    protected function addGroups($carrier)
    {
        $groups_ids = array();
        $groups = Group::getGroups(Context::getContext()->language->id);
        foreach ($groups as $group)
            $groups_ids[] = $group['id_group'];

        $carrier->setGroups($groups_ids);
    }

    protected function addRanges($carrier)
    {
        $range_price = new RangePrice();
        $range_price->id_carrier = $carrier->id;
        $range_price->delimiter1 = '0';
        $range_price->delimiter2 = '10000';
        $range_price->add();

        $range_weight = new RangeWeight();
        $range_weight->id_carrier = $carrier->id;
        $range_weight->delimiter1 = '0';
        $range_weight->delimiter2 = '10000';
        $range_weight->add();
    }

    protected function addZones($carrier)
    {
        $zones = Zone::getZones();

        foreach ($zones as $zone)
            $carrier->addZone($zone['id_zone']);
    }

    /**
    * Add the CSS & JavaScript files you want to be loaded in the BO.
    */
    public function hookBackOfficeHeader()
    {
        if (Tools::getValue('module_name') == $this->name) {
            $this->context->controller->addJS($this->_path.'views/js/back.js');
            $this->context->controller->addCSS($this->_path.'views/css/back.css');
        }
    }

    /**
     * Add the CSS & JavaScript files you want to be added on the FO.
     */
    public function hookHeader()
    {
        $this->context->controller->addJS($this->_path.'/views/js/front.js');
        $this->context->controller->addCSS($this->_path.'/views/css/front.css');
    }

    public function hookUpdateCarrier($params)
    {
        /**
         * Not needed since 1.5
         * You can identify the carrier by the id_reference
        */
    }

    public function getTabId($className){
        $sql = 'SELECT id_tab FROM `'._DB_PREFIX_.'tab`
        WHERE class_name = "' . $className . '"';

        $tabId = Db::getInstance()->getRow($sql);

        if($tabId == false){
            $this->_errors[] = $this->l('Error deleting the PrestashopCarrierTester lang Tabs');
            return false;
        } else {
            return $tabId['id_tab'];
        }
    }
}
