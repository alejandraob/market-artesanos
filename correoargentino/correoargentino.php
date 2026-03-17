<?php

/**
 * 2007-2022 PrestaShop
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
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2022 PrestaShop SA
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */
if (!defined('_PS_VERSION_')) {
    exit;
}
include_once(__DIR__ . '/vendor/autoload.php');
include_once(__DIR__ . '/autoload.php');

use CorreoArgentino\Utils\CorreoArgentinoUtil;
use CorreoArgentino\Repository\CorreoArgentinoBranchOrderModel;
use CorreoArgentino\Repository\CorreoArgentinoRatesModel;
use CorreoArgentino\Services\CorreoArgentinoServiceFactory;
use CorreoArgentino\Utils\CorreoArgentinoConstants;
use PrestaShop\PrestaShop\Core\Domain\Order\Exception\OrderException;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use GuzzleHttp\Exception\ClientException;
use PrestaShop\PrestaShop\Adapter\Entity\Address;
use PrestaShop\PrestaShop\Adapter\Entity\State;

class CorreoArgentino extends CarrierModule
{
    const CA_USERNAME_MI_CORREO = 'PRESTASHOP';
    const CA_PASSWORD_MI_CORREO = 'Espada-38';
    const RATE_HOME_DELIVERY = 'Envío a domicilio';
    const ENABLE_EXTERNAL_RATES = true;
    const RATE_HOME_DELIVERY_NAME = 'Correo Argentino - Domicilio';
    const RATE_AGENCY_DELIVERY_NAME = 'Correo Argentino - Sucursal';
    const CREATE_ACCOUNT_URL_MI_CORREO = 'create_account_mi_correo';
    public $id_carrier;
    protected $config_form = false;

    public function __construct()
    {
        $this->name = 'correoargentino';
        $this->tab = 'shipping_logistics';
        $this->version = '1.3.8';
        $this->author = 'Correo Argentino.';
        $this->need_instance = 0;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Correo Argentino');
        $this->description = $this->l('Shipping method module of Correo Argentino');
        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
    }


    /**
     * @return bool
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function install()
    {

        if (extension_loaded('curl') == false) {
            $this->_errors[] = $this->l('You have to enable the cURL extension on your server to install this module');
            return false;
        }

        include(dirname(__FILE__) . "/sql/install.php");

        Configuration::updateValue('CORREOARGENTINO_SANDBOX_MODE', 0);
        Configuration::updateValue('CORREOARGENTINO_AUTH_HASH', base64_encode(self::CA_USERNAME_MI_CORREO . ':' . self::CA_PASSWORD_MI_CORREO));
        Configuration::updateValue('CORREOARGENTINO_ACCESS_TOKEN', null);

        $this->registerCarriers();

        return parent::install() &&
            $this->installTab() &&
            $this->registerHook('header') &&
            $this->registerHook('displayBackOfficeHeader') &&
            $this->registerHook('displayCarrierExtraContent') &&
            $this->registerHook('actionOrderStatusUpdate') &&
            $this->registerHook('actionValidateStepComplete') &&
            $this->registerHook('actionGetAdminOrderButtons') &&
            $this->registerHook('actionValidateOrder') &&
            $this->registerHook('actionPresentOrder') &&
            $this->registerHook('displayContentWrapperTop') &&
            $this->registerHook('actionCartSave');
    }

    /**
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    protected function registerCarriers()
    {

        $rates = [
            "rates" => [
                [
                    "serviceName" => "Correo Argentino Clásico - Domicilio",
                    "description" => "Envío a domicilio",
                    "is_rate" => false,
                    "serviceType" => "CP",
                    "deliveredType" => "D",
                ],
                [
                    "serviceName" => "Correo Argentino Clásico - Sucursal",
                    "description" => "Envio a sucursal de CA",
                    "is_rate" => true,
                    "serviceType" => "CP",
                    "deliveredType" => "S",
                ],
                [
                    "serviceName" => "Correo Argentino Expreso - Domicilio",
                    "description" => "Envío a domicilio",
                    "is_rate" => false,
                    "serviceType" => "EP",
                    "deliveredType" => "D",
                ],
                [
                    "serviceName" => "Correo Argentino Expreso - Sucursal",
                    "description" => "Envio a sucursal de CA",
                    "is_rate" => true,
                    "serviceType" => "EP",
                    "deliveredType" => "S",
                ]
            ]
        ];


        foreach ($rates['rates'] as $i => $rate) {
            $newCarrier = $this->addCarrier($rate['serviceName'], $rate['description']);
            $this->addZones($newCarrier);
            $this->addGroups($newCarrier);
            $this->addRanges($newCarrier);
            $this->addRates($newCarrier, $rate['is_rate'], $rate['serviceType'], $rate['deliveredType']);
        }
    }

    /**
     * @param $type
     * @return Carrier|false
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    protected function addCarrier($name, $description)
    {
        $lang_id = Configuration::get('PS_LANG_DEFAULT');
        $carrier = new Carrier();
        $carrier->name = $this->l($name);
        $carrier->is_module = true;
        $carrier->active = 1;
        $carrier->need_range = 1;
        $carrier->is_free = !self::ENABLE_EXTERNAL_RATES; // If is set this prevents to add default values;
        $carrier->shipping_external = self::ENABLE_EXTERNAL_RATES;
        $carrier->range_behavior = 0;
        $carrier->external_module_name = $this->name;
        $carrier->shipping_method = 2;
        $carrier->url = 'https://www.correoargentino.com.ar/formularios/e-commerce?id=@';

        $delay = $description == self::RATE_HOME_DELIVERY ? $this->l('Home delivery') : $this->l('Agency');
        $carrier->delay[$lang_id] = $delay;

        if ($carrier->add()) {
            @copy(dirname(__FILE__) . '/views/img/ca_logo_240x240.jpeg', _PS_SHIP_IMG_DIR_ . '/' . (int)$carrier->id . '.jpg');
            return $carrier;
        }

        return false;
    }

    /**
     * @param $carrier
     */
    protected function addZones($carrier)
    {
        // Set South America as delivery region
        $country_id = Country::getByIso('AR');
        $zone_id = Country::getIdZone($country_id);
        $carrier->addZone($zone_id);
    }

    /**
     * @param $carrier
     */
    protected function addGroups($carrier)
    {
        $groups_ids = array();
        $groups = Group::getGroups(Context::getContext()->language->id);
        foreach ($groups as $group)
            $groups_ids[] = $group['id_group'];

        $carrier->setGroups($groups_ids);
    }

    /**
     * @param $carrier
     */
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

    /**
     * @param $carrier
     * @throws PrestaShopException
     */
    protected function addRates($carrier, $isBranch, $serviceType, $deliveredType)
    {
        CorreoArgentinoRatesModel::create($carrier, $isBranch, $serviceType, $deliveredType);
    }

    /**
     * @return bool
     */
    private function installTab(): bool
    {
        $tab = new Tab();
        $tab->active = 1;
        $tab->class_name = 'AdminCorreoArgentino';
        $tab->position = 3;
        $tab->name = array();
        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = 'Correo Argentino';
        }
        $tab->id_parent = (int)Tab::getIdFromClassName('AdminParentOrders');
        $tab->module = $this->name;
        $tab->save();
        return true;
    }

    /**
     * @return bool
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function uninstall()
    {
        include(dirname(__FILE__) . "/sql/uninstall.php");
        Configuration::deleteByName('CORREOARGENTINO_SANDBOX_MODE');
        Configuration::deleteByName('CORREOARGENTINO_AGREEMENT');
        Configuration::deleteByName('CORREOARGENTINO_SERVICE_TYPE');
        Configuration::deleteByName('CORREOARGENTINO_USE_RATES');
        Configuration::deleteByName('CORREOARGENTINO_API_KEY');
        Configuration::deleteByName('CORREOARGENTINO_CITYNAME');
        Configuration::deleteByName('CORREOARGENTINO_DEPARTAMENT');
        Configuration::deleteByName('CORREOARGENTINO_FLOOR');
        Configuration::deleteByName('CORREOARGENTINO_STATE');
        Configuration::deleteByName('CORREOARGENTINO_STREET_NAME');
        Configuration::deleteByName('CORREOARGENTINO_STREET_NUMBER');
        Configuration::deleteByName('CORREOARGENTINO_ZIP_CODE');
        Configuration::deleteByName('CORREOARGENTINO_AREA_CODE_CELL_PHONE');
        Configuration::deleteByName('CORREOARGENTINO_CELL_PHONE_NUMBER');
        Configuration::deleteByName('CORREOARGENTINO_AREA_CODE_PHONE');
        Configuration::deleteByName('CORREOARGENTINO_PHONE_NUMBER');
        Configuration::deleteByName('CORREOARGENTINO_BUSINESS_NAME');
        Configuration::deleteByName('CORREOARGENTINO_EMAIL');
        Configuration::deleteByName('CORREOARGENTINO_OBSERVATION');
        Configuration::deleteByName('CORREOARGENTINO_STATE_ORDER');
        Configuration::deleteByName('CORREOARGENTINO_CARRIER_HOME_ID');
        Configuration::deleteByName('CORREOARGENTINO_CARRIER_BRANCH_ID');
        Configuration::deleteByName('CORREOARGENTINO_API_KEY');
        Configuration::deleteByName('CORREOARGENTINO_API_KEY_SUCCESS');
        Configuration::deleteByName('CORREOARGENTINO_STATE_ORDER');
        Configuration::deleteByName('CORREOARGENTINO_FIRST_NAME');
        Configuration::deleteByName('CORREOARGENTINO_LAST_NAME');
        Configuration::deleteByName('CORREOARGENTINO_DOCUMENT_ID');
        Configuration::deleteByName('CORREOARGENTINO_DOCUMENT_TYPE');
        Configuration::deleteByName('CORREOARGENTINO_USERNAME_MICORREO');
        Configuration::deleteByName('CORREOARGENTINO_PASSWORD_MICORREO');
        Configuration::deleteByName('CORREOARGENTINO_CUSTOMER_ID');
        Configuration::deleteByName('CORREOARGENTINO_FORM_FILLED');

        $this->unregisterCarriers();

        return parent::uninstall() && $this->uninstallTab();
    }

    /**
     * @throws PrestaShopDatabaseException
     */
    protected function unregisterCarriers()
    {
        $carriers = $this->getRegisteredCarriersId();
        foreach ($carriers as $c) {
            $carrier = new Carrier($c['id_carrier']);
            $carrier->delete();
        }
    }

    /**
     * @throws PrestaShopDatabaseException
     */
    protected function getRegisteredCarriersId()
    {
        $sql = new DbQuery();
        $sql->select('id_carrier');
        $sql->from('carrier', 'c');
        $sql->where('c.external_module_name = "correoargentino"');
        $sql->where('c.deleted = 0');
        return Db::getInstance()->executeS($sql);
    }

    protected function getAgencyCarriersId()
    {
        $sql = new DbQuery();
        $sql->select('id_carrier');
        $sql->from('correoargentino_rates', 'c');
        $sql->where('c.delivered_type = "S"');
        return Db::getInstance()->executeS($sql);
    }

    /**
     * @return bool
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    private function uninstallTab(): bool
    {
        $tabId = (int)Tab::getIdFromClassName('AdminCorreoArgentino');

        if (!$tabId) {
            return true;
        }

        $tab = new Tab($tabId);

        return $tab->delete();
    }

    public function disable($force_all = false)
    {
        $carriers = Carrier::getCarriers($this->context->language->id, true, false, false, null, Carrier::CARRIERS_MODULE);
        foreach ($carriers as $carrier) {
            if ($carrier['external_module_name'] == 'correoargentino') {
                $carrierObj = new Carrier($carrier['id_carrier']);
                $carrierObj->active = '0';
                $carrierObj->save();
            }
        }

        return parent::disable($force_all);
    }

    public function enable($force_all = false)
    {
        $carriers = Carrier::getCarriers($this->context->language->id, false, false, false, null, Carrier::CARRIERS_MODULE);
        foreach ($carriers as $carrier) {
            if ($carrier['external_module_name'] == 'correoargentino') {
                $carrierObj = new Carrier($carrier['id_carrier']);
                $carrierObj->active = '1';
                $carrierObj->save();
            }
        }

        return parent::enable($force_all);
    }

    /**
     * @param $params
     * @param $shipping_cost
     * @return float
     * @throws PrestaShopDatabaseException
     */
    public function getOrderShippingCost($params, $shipping_cost)
    {
        return $this->getOrderShippingCostExternal($params);
    }

    /**
     * @param $params
     * @return float
     * @throws PrestaShopDatabaseException
     */
    public function getOrderShippingCostExternal($params)
    {
        $dimensions = CorreoArgentinoUtil::getPackageSize($params);

        if (isset($dimensions['weight'])) {
            if (Configuration::get('CORREOARGENTINO_SERVICE_TYPE') === 'PAQ_AR') {
                if ($dimensions['weight'] > CorreoArgentinoConstants::CA_MAX_WEIGHT_PAQ_AR) {
                    return false;
                }
            } else {
                if ($dimensions['weight'] > CorreoArgentinoConstants::CA_MAX_WEIGHT_MI_CORREO) {
                    return false;
                }
            }
        }


        if (!Configuration::get('CORREOARGENTINO_USE_RATES') || Configuration::get('CORREOARGENTINO_SERVICE_TYPE') === 'PAQ_AR') {
            return 0;
        }

        // Check if the carrier is enabled
        if (!Configuration::get('CORREOARGENTINO_ZIP_CODE') || '' == Configuration::get('CORREOARGENTINO_ZIP_CODE')) {
            return false;
        }

        /*
         * @todo: we have to consume the /rates endpoint in order to get the actual values
         * It's necessary to get a unique key for each record in order to associate with its carrier method
         */

        $carrierSettings = CorreoArgentinoRatesModel::getSettingsByCarrierId($this->id_carrier);

        $cookieId = 'correoargentino_rates';

        $cookie = $this->context->cookie;


        if (!$cookie->branch_postcode) {

            $address = new  Address((int)$params->id_address_delivery);
            $state = new State($address->id_state);

            $stateSelected = $cookie->state_selected;

            $iso_code = $stateSelected ? $stateSelected : $state->iso_code;

            if ($iso_code) {
                $service = (new CorreoArgentinoServiceFactory())->get();
                $agencies = $service->getBranches($iso_code);
                $results = $service->branchesAdapter($agencies);

                $cookie->branch_postcode = $results[0]['postcode'];
                $cookie->branch_selected = $results[0]['id'];
                unset($cookie->correoargentino_rates);
            }
        }


        if (!isset($cookie->$cookieId)) {

            $dimensions = CorreoArgentinoUtil::getPackageSize($params);

            // get zip code
            $address = new Address($params->id_address_delivery);
            $zipCode = CorreoArgentinoUtil::normalizeZipCode($address->postcode);

            $rates = [];
            if ('' == $zipCode) {
                $response = null;
            } else {
                $service = (new CorreoArgentinoServiceFactory())->get();

                $response = $service->getRates($zipCode, $dimensions);

                if (isset($response['rates']) && count($response['rates']) > 0) {
                    foreach ($response['rates'] as $rate) {
                        $rates[$rate['productType'] . '_' . $rate['deliveredType']] = $rate['price'];
                    }
                }
            }

            $cookie->$cookieId = json_encode($rates);
        } else {
            $rates = json_decode($cookie->$cookieId, true);
        }


        if (isset($rates[$carrierSettings['service_type'] . '_' . $carrierSettings['delivered_type']])) {

            return (float) $rates[$carrierSettings['service_type'] . '_' . $carrierSettings['delivered_type']];
        }

        return false;
    }

    /**
     * Add the CSS & JavaScript files you want to be loaded in the BO.
     */
    public function hookDisplayBackOfficeHeader()
    {
        if ((Tools::getValue('module_name') == $this->name) || (Tools::getValue('configure') == $this->name)) {
            $this->context->controller->addJS($this->_path . 'views/js/back.js');
            $this->context->controller->addCSS($this->_path . 'views/css/back.css');
        }

        // Check if is order page
        if (Tools::getValue('controller') == 'AdminOrders') {
            if (Configuration::get('CORREOARGENTINO_SERVICE_TYPE') === CorreoArgentinoConstants::MI_CORREO) {
                // Get order id
                $id_order = Tools::getValue('id_order');
                $order = new Order($id_order);
                $carrier = new Carrier($order->id_carrier);
                if ($carrier->external_module_name == $this->name) {
                    $this->context->controller->addJS($this->_path . 'views/js/order.js');
                    echo $this->context->smarty->fetch($this->local_path . 'views/templates/hook/display-back-office-header-order.tpl');
                }
            }
        }
    }

    /**
     * @param $params
     * @throws PrestaShopException
     * @throws OrderException
     */
    public function hookActionOrderStatusUpdate($params)
    {
        try {

            $newOrderStatus = $params['newOrderStatus'];

            $id_order = $params['id_order'];

            if ((int)Configuration::get('CORREOARGENTINO_STATE_ORDER') !== $newOrderStatus->id) {
                return;
            }

            $order = new Order((int)$id_order);
            $carrier = new Carrier($order->id_carrier);

            /**
             * Solo podemos procesar órdenes del módulo
             */
            if ($carrier->external_module_name != "correoargentino") {
                return;
            }

            /**
             * Validacion para no registrar duplicado
             */
            if (!empty($order->getWsShippingNumber())) {
                if ($this->context->controller) {
                    $this->context->controller->confirmations[] = $this->l('successful registration in Correo Argentino');
                }
                return;
            }

            $service = (new CorreoArgentinoServiceFactory())->get();

            $response = $service->registerOrder($id_order);

            if ($response instanceof Exception) {
                throw new OrderException($this->l($response->getResponse()->getContent()));
            } else {
                $this->context->controller->confirmations[] = $this->l('successful registration in Correo Argentino');
            }
        } catch (ClientException $e) {
            $exception = json_decode((string)$e->getResponse()->getBody(), true);
            $this->context->controller->errors[] = $exception["message"];
        } catch (PrestashopException $e) {
            $this->context->controller->errors[] = $e->getMessage();
        }
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        /**
         * If values have been submitted in the form, process.
         */
        $this->getConfigFormValues();

        $submitted = true;
        $errors = [];
        $success = false;

        // PaqAr Login submit 
        if ((Tools::isSubmit('submitCorreoargentinoModule') == true && Tools::isSubmit('submitCorreoargentinoModuleLogin')) == true) {
            try {
                $mode = Tools::getValue('CORREOARGENTINO_SANDBOX_MODE');
                Configuration::updateValue("CORREOARGENTINO_SANDBOX_MODE", $mode);
                if (empty(Tools::getValue("CORREOARGENTINO_AGREEMENT")) || empty(Tools::getValue("CORREOARGENTINO_API_KEY"))) {
                    throw new Exception($this->l('Agreement and API Key are required'));
                }
                $response = $this->postProcessPaqArLogin($mode);
                if (!$response) {
                    throw new Exception($this->l('Invalid credentials'));
                }
                $success = true;
            } catch (Exception $e) {
                $errors[] = $e->getMessage();
                Configuration::updateValue("CORREOARGENTINO_SANDBOX_MODE", null);
                Configuration::updateValue("CORREOARGENTINO_AGREEMENT", null);
                Configuration::updateValue("CORREOARGENTINO_API_KEY", null);
            }
        } else
            // MiCorreo Login submit
            if ((Tools::isSubmit('submitCorreoargentinoModule') == true && Tools::isSubmit('submitCorreoArgentinoMiCorreoModuleLogin')) == true) {
                try {
                    $mode = Tools::getValue('CORREOARGENTINO_SANDBOX_MODE');
                    Configuration::updateValue("CORREOARGENTINO_SANDBOX_MODE", $mode);
                    if (empty(Tools::getValue("CORREOARGENTINO_USERNAME_MICORREO")) || empty(Tools::getValue("CORREOARGENTINO_PASSWORD_MICORREO"))) {
                        throw new Exception($this->l('Username and Password are required'));
                    }
                    $response = $this->postProcessMiCorreoLogin($mode);
                    if (!$response) {
                        throw new Exception($this->l('Invalid credentials'));
                    }
                    $success = true;
                } catch (Exception $e) {
                    $errors[] = $e->getMessage();
                    Configuration::updateValue("CORREOARGENTINO_SANDBOX_MODE", null);
                    Configuration::updateValue("CORREOARGENTINO_USERNAME_MICORREO", null);
                    Configuration::updateValue("CORREOARGENTINO_PASSWORD_MICORREO", null);
                }
            }

        if ((Tools::isSubmit('submitCorreoargentinoModule') == true && Tools::isSubmit('submitCorreoargentinoModuleServiceTypeSelector')) == true) {
            try {
                $this->postProcessServiceTypeSelector();
                $success = true;
            } catch (Exception $e) {
                $success = false;
            }
        }

        if ((Tools::isSubmit('submitCorreoargentinoModule') == true && Tools::isSubmit('submitCorreoargentinoModuleStatus')) == true) {
            try {
                $this->postProcessStatus();
                $success = true;
            } catch (Exception $e) {
                $success = false;
            }
        }

        if ((Tools::isSubmit('submitCorreoargentinoModule') == true && Tools::isSubmit('submitCorreoargentinoModuleAddress')) == true) {


            try {
                $success = true;

                $documentType = Tools::getValue('CORREOARGENTINO_DOCUMENT_TYPE');

                $documentDNINumberRegex = "/^[0-9]{7,8}$/"; // 7 or 8 digits
                $documentCUITNumberRegex = "/^[0-9]{10,11}$/"; // 10 or 11 digits
                $documentNumber = Tools::getValue('CORREOARGENTINO_DOCUMENT_ID');

                $passwordRegex = "/^[^\s]{6,20}$/";
                $password = Tools::getValue('CORREOARGENTINO_PASSWORD_MICORREO');
                

                $zipCodeRegex = "/^([A-HJ-TP-Z]{1}\d{4}[A-Z]{3}|[a-z]{1}\d{4}[a-hj-tp-z]{3}|[0-9][0-9][0-9][0-9])$/";
                $zipCode = Tools::getValue('CORREOARGENTINO_ZIP_CODE');

                $streetNumberRegex = "/^[0-9]{1,4}$/";
                $streetNumber = Tools::getValue('CORREOARGENTINO_STREET_NUMBER');

                $streetNameRegex = "/^[0-9a-zA-ZÀ-ÿ\s]{1,32}$/";
                $streetName = Tools::getValue('CORREOARGENTINO_STREET_NAME');

                $cellphoneCodeRegex = "/^[0-9]{2,4}$/";
                $cellphoneCode = Tools::getValue('CORREOARGENTINO_AREA_CODE_CELL_PHONE');

                $cellphoneNumberRegex = "/^[0-9]{6,16}$/";
                $cellphoneNumber = Tools::getValue('CORREOARGENTINO_CELL_PHONE_NUMBER');

                $telephoneCodeRegex = "/^[0-9]{2,4}$/";
                $telephoneCode = Tools::getValue('CORREOARGENTINO_AREA_CODE_PHONE');

                $telephoneNumberRegex = "/^[0-9]{6,16}$/";
                $telephoneNumber = Tools::getValue('CORREOARGENTINO_PHONE_NUMBER');

                $citynameRegex = "/^[a-zA-ZÀ-ÿ\s]{1,32}$/";
                $cityname = Tools::getValue('CORREOARGENTINO_CITYNAME');

                $email = Tools::getValue('CORREOARGENTINO_EMAIL');

                $formFields = $this->getFormaAddressFields();

                foreach ($formFields as $field) {
                    if ((isset($field['required']) && $field['required'] == true) ||
                        ($documentType == 'DNI' && isset($field['desc']) && $field['desc'] == $this->l('Requerido para DNI'))
                    ) {
                        $value = Tools::getValue($field['name']);
                        if (empty($value)) {
                            $label = $field['label'];
                            $errors[] = $this->l("El campo $label es requerido.");
                            $success = false;
                        }
                    }
                }

                // check if field document id is present and needs to be validated
                if ($documentType) {
                    if (($documentType === 'DNI')) {
                        if (!preg_match($documentDNINumberRegex, $documentNumber)) {
                            $errors[] = $this->l('Debe especificar un número de documento válido (Sólo números sin puntos ni guiones)');
                            $success = false;
                        }
                    } else                     
                    if (($documentType === 'CUIT')) {
                        if (!preg_match($documentCUITNumberRegex, $documentNumber)) {
                            $errors[] = $this->l('Debe especificar un CUIT válido  (Sólo números sin puntos ni guiones)');
                            $success = false;
                        }
                    }
                }

                // check if field password is present and needs to be validated
                if (Tools::getValue('CORREOARGENTINO_PASSWORD_MICORREO')) {
                    $password = Tools::getValue('CORREOARGENTINO_PASSWORD_MICORREO');
                    if (strlen($password) < 6 || strlen($password) > 20) {
                        $errors[] = $this->l('La contraseña debe tener entre 6 y 20 caracteres.');
                        $success = false;
                    }
                    if (preg_match('/\s/', $password)) {
                        $errors[] = $this->l('La contraseña no debe contener espacios.');
                        $success = false;
                    }
                }
                // Zip Code
                if (($documentType === 'DNI') && !isset($zipCode)) {
                    $errors[] = $this->l('El campo Código postal es requerido');
                    $success = false;
                }

                if (!empty($zipCode)) {
                    if (!preg_match($zipCodeRegex, $zipCode)) {
                        $errors[] = $this->l('The Postal Code field is invalid');
                        $success = false;
                    }
                }

                // Street number
                if (($documentType === 'DNI') && !isset($streetNumber)) {
                    $errors[] = $this->l('El campo Altura de calle es requerido');
                    $success = false;
                }

                if (!empty($streetNumber) && !preg_match($streetNumberRegex, $streetNumber)) {
                    $errors[] = $this->l('The Street Number field must be numeric');
                    $success = false;
                }

                // Street name
                if (($documentType === 'DNI') && !isset($streetName)) {
                    $errors[] = $this->l('El campo Calle es requerido');
                    $success = false;
                }

                if (!empty($streetName) && !preg_match($streetNameRegex, $streetName)) {
                    $errors[] = $this->l('The Street Name field does not accept special characters');
                    $success = false;
                }

                // State
                if (($documentType === 'DNI') && !Tools::getValue('CORREOARGENTINO_STATE')) {
                    $errors[] = $this->l('El campo Provincia es requerido');
                    $success = false;
                }


                if (!empty($cellphoneCode) && !preg_match($cellphoneCodeRegex, $cellphoneCode)) {

                    $errors[] = $this->l('The Cellphone Code Area field must be numerica');
                    $success = false;
                }

                if (!empty($cellphoneNumber) && !preg_match($cellphoneNumberRegex, $cellphoneNumber)) {

                    $errors[] = $this->l('The Cellphone Number field must be numeric');
                    $success = false;
                }

                if (!empty($telephoneCode) && !preg_match($telephoneCodeRegex, $telephoneCode)) {
                    $errors[] = $this->l('The Phone Code Area field must be numeric');
                    $success = false;
                }

                if (!empty($telephoneNumber) && !preg_match($telephoneNumberRegex, $telephoneNumber)) {
                    $errors[] = $this->l('The Phone Number field must be numeric');
                    $success = false;
                }

                // City Name
                if (($documentType === 'DNI') && !isset($cityname)) {
                    $errors[] = $this->l('El campo Ciudad es requerido');
                    $success = false;
                }

                if (!empty($cityname) && !preg_match($citynameRegex, $cityname)) {
                    $errors[] = $this->l('The City field does not accept special characters');
                    $success = false;
                }

                if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $errors[] = $this->l('The Email field is not valid');
                    $success = false;
                }

                $this->updateAddressFields();

                if (count($errors) < 1) {
                    $this->postProcessAddress();
                }
            } catch (Exception $e) {
                $errors[] = $e->getMessage();
                $success = false;
            }
        }

        $this->context->smarty->assign('module_dir', $this->_path);
        $this->context->smarty->assign('success', $success);

        $output = $this->context->smarty->fetch($this->local_path . 'views/templates/admin/configure.tpl', compact('success', 'submitted', 'errors'));

        return $output . $this->renderForm();
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        return array(
            'CORREOARGENTINO_SANDBOX_MODE' => Configuration::get('CORREOARGENTINO_SANDBOX_MODE'),
            'CORREOARGENTINO_AGREEMENT' => Configuration::get('CORREOARGENTINO_AGREEMENT'),
            'CORREOARGENTINO_SERVICE_TYPE' => Configuration::get('CORREOARGENTINO_SERVICE_TYPE'),
            'CORREOARGENTINO_USE_RATES' => Configuration::get('CORREOARGENTINO_USE_RATES'),
            'CORREOARGENTINO_API_KEY' => Configuration::get('CORREOARGENTINO_API_KEY'),
            'CORREOARGENTINO_CITYNAME' => Configuration::get('CORREOARGENTINO_CITYNAME'),
            'CORREOARGENTINO_DEPARTAMENT' => Configuration::get('CORREOARGENTINO_DEPARTAMENT'),
            'CORREOARGENTINO_FLOOR' => Configuration::get('CORREOARGENTINO_FLOOR'),
            'CORREOARGENTINO_STATE' => Configuration::get('CORREOARGENTINO_STATE'),
            'CORREOARGENTINO_STREET_NAME' => Configuration::get('CORREOARGENTINO_STREET_NAME'),
            'CORREOARGENTINO_STREET_NUMBER' => Configuration::get('CORREOARGENTINO_STREET_NUMBER'),
            'CORREOARGENTINO_ZIP_CODE' => Configuration::get('CORREOARGENTINO_ZIP_CODE'),
            'CORREOARGENTINO_AREA_CODE_CELL_PHONE' => Configuration::get('CORREOARGENTINO_AREA_CODE_CELL_PHONE'),
            'CORREOARGENTINO_CELL_PHONE_NUMBER' => Configuration::get('CORREOARGENTINO_CELL_PHONE_NUMBER'),
            'CORREOARGENTINO_AREA_CODE_PHONE' => Configuration::get('CORREOARGENTINO_AREA_CODE_PHONE'),
            'CORREOARGENTINO_PHONE_NUMBER' => Configuration::get('CORREOARGENTINO_PHONE_NUMBER'),
            'CORREOARGENTINO_BUSINESS_NAME' => Configuration::get('CORREOARGENTINO_BUSINESS_NAME'),
            'CORREOARGENTINO_EMAIL' => Configuration::get('CORREOARGENTINO_EMAIL'),
            'CORREOARGENTINO_OBSERVATION' => Configuration::get('CORREOARGENTINO_OBSERVATION'),
            'CORREOARGENTINO_USERNAME_MICORREO' => Configuration::get('CORREOARGENTINO_USERNAME_MICORREO'),
            'CORREOARGENTINO_STATE_ORDER' => Configuration::get('CORREOARGENTINO_STATE_ORDER'),
            'CORREOARGENTINO_FIRST_NAME' => Configuration::get('CORREOARGENTINO_FIRST_NAME'),
            'CORREOARGENTINO_LAST_NAME' => Configuration::get('CORREOARGENTINO_LAST_NAME'),
            'CORREOARGENTINO_DOCUMENT_ID' => Configuration::get('CORREOARGENTINO_DOCUMENT_ID'),
            'CORREOARGENTINO_DOCUMENT_TYPE' => Configuration::get('CORREOARGENTINO_DOCUMENT_TYPE'),
            'CORREOARGENTINO_USERNAME_MICORREO' => Configuration::get('CORREOARGENTINO_USERNAME_MICORREO'),
        );
    }

    /**
     * Save form data.
     */
    protected function postProcessPaqArLogin($mode)
    {
        try {
            $agreement = str_replace(' ', '', Tools::getValue('CORREOARGENTINO_AGREEMENT'));
            $apiKey = str_replace(' ', '', Tools::getValue('CORREOARGENTINO_API_KEY'));
            $service = (new CorreoArgentinoServiceFactory())->get();
            $response = $service->login($agreement, $apiKey, $mode);
            if ($response) {
                Configuration::updateValue("CORREOARGENTINO_AGREEMENT", $agreement);
                Configuration::updateValue("CORREOARGENTINO_API_KEY", $apiKey);
            }
            return $response;
        } catch (Exception | TransportExceptionInterface $e) {
            Configuration::updateValue("CORREOARGENTINO_AGREEMENT", null);
            Configuration::updateValue("CORREOARGENTINO_API_KEY", null);
            Configuration::updateValue("CORREOARGENTINO_API_KEY_SUCCESS", false);
            PrestaShopLogger::addLog($e->getMessage());
            return false;
        }
    }

    /**
     * MiCorreo login form process
     * 
     * @return bool MiCorreo account is validates or not
     */
    protected function postProcessMiCorreoLogin()
    {
        try {
            $username = str_replace(' ', '', Tools::getValue('CORREOARGENTINO_USERNAME_MICORREO'));
            $password = str_replace(' ', '', Tools::getValue('CORREOARGENTINO_PASSWORD_MICORREO'));
            $service = (new CorreoArgentinoServiceFactory())->get();
            $response = $service->userValidate($username, $password);
            if ($response) {
                Configuration::updateValue("CORREOARGENTINO_USERNAME_MICORREO", $username);
                Configuration::updateValue("CORREOARGENTINO_PASSWORD_MICORREO", $password);
            }
            return $response;
        } catch (Exception | TransportExceptionInterface $e) {
            Configuration::updateValue("CORREOARGENTINO_USERNAME_MICORREO", null);
            Configuration::updateValue("CORREOARGENTINO_PASSWORD_MICORREO", null);
            Configuration::updateValue("CORREOARGENTINO_CUSTOMER_ID", null);
            Configuration::deleteByName("CORREOARGENTINO_ACCESS_TOKEN_EXPIRE", null);
            Configuration::deleteByName("CORREOARGENTINO_ACCESS_TOKEN", null);
            Configuration::deleteByName("CORREOARGENTINO_MICORREO_SUCCESS", false);
            PrestaShopLogger::addLog("PostProcessMiCorreoLogin - {$e->getMessage()}", PrestaShopLogger::LOG_SEVERITY_LEVEL_ERROR);
        }
        return false;
    }

    protected function postProcessServiceTypeSelector()
    {
        if (Tools::getValue('CORREOARGENTINO_SERVICE_TYPE') === CorreoArgentinoConstants::MI_CORREO) {
            // MI_CORREO can use rates or not
            Configuration::updateValue("CORREOARGENTINO_SERVICE_TYPE", Tools::getValue('CORREOARGENTINO_SERVICE_TYPE'));
            Configuration::updateValue("CORREOARGENTINO_USE_RATES", Tools::getValue('CORREOARGENTINO_USE_RATES'));
        } else if (Tools::getValue('CORREOARGENTINO_SERVICE_TYPE') === CorreoArgentinoConstants::PAQ_AR) {
            // PAQ.AR not use rates
            Configuration::updateValue("CORREOARGENTINO_SERVICE_TYPE", Tools::getValue('CORREOARGENTINO_SERVICE_TYPE'));
            Configuration::updateValue("CORREOARGENTINO_USE_RATES", 0);
        }
    }

    /**
     *
     */
    protected function postProcessStatus()
    {
        Configuration::updateValue('CORREOARGENTINO_STATE_ORDER', Tools::getValue('CORREOARGENTINO_STATE_ORDER'));
    }

    /**
     * Update Address fields
     */
    protected function updateAddressFields()
    {
        Configuration::updateValue('CORREOARGENTINO_CITYNAME', Tools::getValue('CORREOARGENTINO_CITYNAME'));
        Configuration::updateValue('CORREOARGENTINO_DEPARTAMENT', Tools::getValue('CORREOARGENTINO_DEPARTAMENT'));
        Configuration::updateValue('CORREOARGENTINO_FLOOR', Tools::getValue('CORREOARGENTINO_FLOOR'));
        Configuration::updateValue('CORREOARGENTINO_STATE', Tools::getValue('CORREOARGENTINO_STATE'));
        Configuration::updateValue('CORREOARGENTINO_STREET_NAME', Tools::getValue('CORREOARGENTINO_STREET_NAME'));
        Configuration::updateValue('CORREOARGENTINO_STREET_NUMBER', Tools::getValue('CORREOARGENTINO_STREET_NUMBER'));
        Configuration::updateValue('CORREOARGENTINO_ZIP_CODE', Tools::getValue('CORREOARGENTINO_ZIP_CODE'));
        Configuration::updateValue('CORREOARGENTINO_AREA_CODE_CELL_PHONE', Tools::getValue('CORREOARGENTINO_AREA_CODE_CELL_PHONE'));
        Configuration::updateValue('CORREOARGENTINO_CELL_PHONE_NUMBER', Tools::getValue('CORREOARGENTINO_CELL_PHONE_NUMBER'));
        Configuration::updateValue('CORREOARGENTINO_AREA_CODE_PHONE', Tools::getValue('CORREOARGENTINO_AREA_CODE_PHONE'));
        Configuration::updateValue('CORREOARGENTINO_PHONE_NUMBER', Tools::getValue('CORREOARGENTINO_PHONE_NUMBER'));
        Configuration::updateValue('CORREOARGENTINO_BUSINESS_NAME', Tools::getValue('CORREOARGENTINO_BUSINESS_NAME'));
        Configuration::updateValue('CORREOARGENTINO_EMAIL', Tools::getValue('CORREOARGENTINO_EMAIL'));
        Configuration::updateValue('CORREOARGENTINO_OBSERVATION', Tools::getValue('CORREOARGENTINO_OBSERVATION'));
        Configuration::updateValue('CORREOARGENTINO_FIRST_NAME', Tools::getValue('CORREOARGENTINO_FIRST_NAME'));
        Configuration::updateValue('CORREOARGENTINO_LAST_NAME', Tools::getValue('CORREOARGENTINO_LAST_NAME'));
        Configuration::updateValue('CORREOARGENTINO_DOCUMENT_ID', Tools::getValue('CORREOARGENTINO_DOCUMENT_ID'));
        Configuration::updateValue('CORREOARGENTINO_PASSWORD_MICORREO', Tools::getValue('CORREOARGENTINO_PASSWORD_MICORREO'));
        Configuration::updateValue('CORREOARGENTINO_DOCUMENT_TYPE', Tools::getValue('CORREOARGENTINO_DOCUMENT_TYPE'));
    }

    /**
     *
     */
    protected function postProcessAddress()
    {
        if (Tools::isSubmit('submitCorreoargentinoModuleAddress') && Tools::isSubmit(self::CREATE_ACCOUNT_URL_MI_CORREO) == true) {
            $service = new CorreoArgentinoServiceFactory();
            $service = $service->get();

            $address = [
                "streetName" => Configuration::get('CORREOARGENTINO_STREET_NAME'),
                "streetNumber" => Configuration::get('CORREOARGENTINO_STREET_NUMBER'),
                "floor" => Configuration::get('CORREOARGENTINO_FLOOR'),
                "apartment" => Configuration::get('CORREOARGENTINO_DEPARTAMENT'),
                "city" => Configuration::get('CORREOARGENTINO_CITYNAME'),
                "provinceCode" => Configuration::get('CORREOARGENTINO_STATE'),
                "postalCode" => Configuration::get('CORREOARGENTINO_ZIP_CODE')
            ];

            $body = [
                "firstName" => Configuration::get('CORREOARGENTINO_FIRST_NAME'),
                "lastName" => Configuration::get('CORREOARGENTINO_LAST_NAME'),
                "email" => Configuration::get('CORREOARGENTINO_EMAIL'),
                "password" => Configuration::get('CORREOARGENTINO_PASSWORD_MICORREO'),
                "documentType" => Configuration::get('CORREOARGENTINO_DOCUMENT_TYPE'),
                "documentId" => Configuration::get('CORREOARGENTINO_DOCUMENT_ID'),
                "phone" => CorreoArgentinoUtil::cleanPhone(Configuration::get('CORREOARGENTINO_AREA_CODE_PHONE') . Configuration::get('CORREOARGENTINO_PHONE_NUMBER')),
                "cellPhone" => CorreoArgentinoUtil::cleanPhone(Configuration::get('CORREOARGENTINO_AREA_CODE_CELL_PHONE') . Configuration::get('CORREOARGENTINO_CELL_PHONE_NUMBER')),
                "address" => $address,
            ];

            $response = $service->createAccount($body);

            if ($response) {
                //redirect to configure correoargentino module
                Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules', true) . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name . '&step=1');
            }
        } else {
            Configuration::updateValue('CORREOARGENTINO_FORM_FILLED', true);
        }
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
        $helper->submit_action = 'submitCorreoargentinoModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false) . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name . '&step=1';
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        $forms = [];
        if (empty(Configuration::get('CORREOARGENTINO_SERVICE_TYPE'))) {
            $forms = [$this->getServiceSelectorForm()];
        }
        if (Configuration::get('CORREOARGENTINO_API_KEY_SUCCESS') && !empty(Configuration::get('CORREOARGENTINO_API_KEY'))) {
            $forms = [
                $this->getLoginFormPaqAr(),
                $this->getConfigFormAddress(),
                $this->getStatusesForm()
            ];
        }
        if (!empty(Configuration::get('CORREOARGENTINO_SERVICE_TYPE'))) {
            if (Tools::isSubmit(self::CREATE_ACCOUNT_URL_MI_CORREO) == true) {
                $forms = [$this->getConfigFormAddress()];
                $helper->currentIndex .= '&' . self::CREATE_ACCOUNT_URL_MI_CORREO;
            } else {
                $loginForm = Configuration::get('CORREOARGENTINO_SERVICE_TYPE') === CorreoArgentinoConstants::MI_CORREO
                    ? $this->getLoginFormMiCorreo()
                    : $this->getLoginFormPaqAr();
                $forms = [
                    $loginForm,
                ];


                if ((Configuration::get('CORREOARGENTINO_SERVICE_TYPE') === 'PAQ_AR' &&
                        !(empty(Configuration::get("CORREOARGENTINO_AGREEMENT")) || empty(Configuration::get("CORREOARGENTINO_API_KEY"))))
                    || (int)Configuration::get('CORREOARGENTINO_CUSTOMER_ID') > 0
                ) {
                    $forms[] = $this->getConfigFormAddress();
                }

                $forms[] = $this->getStatusesForm();
            }
        }

        return $helper->generateForm($forms);
    }

    public function getServiceSelectorForm()
    {
        return array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Conexión API '),
                    'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'select',
                        'label' => $this->l('Tipo de Servicio'),
                        'name' => 'CORREOARGENTINO_SERVICE_TYPE',
                        'options' => array(
                            'query' => array(
                                array('id_option' => CorreoArgentinoConstants::MI_CORREO, 'name' => CorreoArgentinoConstants::MI_CORREO_SELECT_LABEL),
                                array('id_option' => CorreoArgentinoConstants::PAQ_AR, 'name' => CorreoArgentinoConstants::PAQ_AR_SELECT_LABEL),
                            ),
                            'id' => 'id_option',
                            'name' => 'name',
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('¿Desea incluir el cotizador?'),
                        'name' => 'CORREOARGENTINO_USE_RATES',
                        'is_bool' => true,
                        'values' => array(
                            array('id' => 'activo', 'value' => 1, 'label' => $this->l('Sí')),
                            array('id' => 'inactivo', 'value' => 0, 'label' => $this->l('No')),
                        ),
                    ),
                ),
                'submit' => array(
                    'name' => 'submitCorreoargentinoModuleServiceTypeSelector',
                    'title' => $this->l('Guardar'),
                ),
            ),
        );
    }

    /**
     * Create the structure of your form.
     */
    protected function getLoginFormPaqAr(): array
    {

        if ((int)Configuration::get('CORREOARGENTINO_API_KEY_SUCCESS') > 0) {
            return array(
                'form' => array(
                    'collapsible' => true,
                    'legend' => array(
                        'title' => $this->l('API Credentials'),
                        'icon' => 'icon-cogs',
                    ),
                    'input' => array(
                        array(
                            'col' => 4,
                            'type' => 'text',
                            'name' => 'CORREOARGENTINO_AGREEMENT',
                            'label' => $this->l('Agreement'),
                            'readonly' => true
                        ),
                        array(
                            'col' => 6,
                            'type' => 'textarea',
                            'name' => 'CORREOARGENTINO_API_KEY',
                            'desc' => $this->l('Don\'t have an account yet?') . " " . "<a href='https://www.correoargentino.com.ar/'>" . $this->l('Create a new account') . "</a>",
                            'label' => $this->l('API Key'),
                            'readonly' => true
                        ),
                        array(
                            'type' => 'switch',
                            'label' => $this->l('Sandbox'),
                            'name' => 'CORREOARGENTINO_SANDBOX_MODE',
                            'is_bool' => true,
                            'desc' => $this->l('Enable this if you want to work in test mode'),
                            'disabled' => true,
                            'values' => array(
                                array(
                                    'id' => 'label2_on',
                                    'value' => 1,
                                    'label' => $this->l('Enabled')
                                ),
                                array(
                                    'id' => 'label2_off',
                                    'value' => 0,
                                    'label' => $this->l('Disabled')
                                )
                            )
                        )
                    )
                )
            );
        }

        return array(
            'form' => array(
                'collapsible' => true,
                'legend' => array(
                    'title' => $this->l('API Credentials'),
                    'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'col' => 4,
                        'type' => 'text',
                        'name' => 'CORREOARGENTINO_AGREEMENT',
                        'label' => $this->l('Agreement'),

                    ),
                    array(
                        'col' => 6,
                        'type' => 'textarea',
                        'name' => 'CORREOARGENTINO_API_KEY',
                        'desc' => $this->l('Don\'t have an account yet?') . " " . "<a href='https://www.correoargentino.com.ar/'>" . $this->l('Create a new account') . "</a>",
                        'label' => $this->l('API Key'),

                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Sandbox'),
                        'name' => 'CORREOARGENTINO_SANDBOX_MODE',
                        'is_bool' => true,
                        'desc' => $this->l('Enable this if you want to work in test mode'),

                        'values' => array(
                            array(
                                'id' => 'label2_on',
                                'value' => 1,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'label2_off',
                                'value' => 0,
                                'label' => $this->l('Disabled')
                            )
                        )
                    )
                ),
                'submit' => array(
                    'name' => 'submitCorreoargentinoModuleLogin',
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    /**
     * Create the structure of your form.
     */
    protected function getConfigFormAddress()
    {
        $formFields = $this->getFormaAddressFields();

        $formTitle = (Configuration::get('CORREOARGENTINO_SERVICE_TYPE') === CorreoArgentinoConstants::PAQ_AR || (int) Configuration::get('CORREOARGENTINO_CUSTOMER_ID') > 0)
            ? $this->l('Shop Settings')
            : $this->l('Create new account');
        $return = [
            'form' => [
                'legend' => [
                    'title' => $formTitle,
                    'icon' => 'icon-cogs',
                ],
                'input' => $formFields,
            ]
        ];

        if (!Configuration::get('CORREOARGENTINO_FORM_FILLED')) {
            $return['form']['submit'] = [
                'name' => 'submitCorreoargentinoModuleAddress',
                'title' => $this->l('Save'),
            ];
        }

        return $return;
    }

    /**
     * Get config form address fields
     * 
     * @return array
     */
    protected function getFormaAddressFields()
    {
        $country = Configuration::get('PS_COUNTRY_DEFAULT');
        $states = array_map(function ($c) {
            return ['id' => $c['iso_code'], 'name' => $c['name']];
        }, State::getStatesByIdCountry($country));

        $documentTypes = [
            ["id" => "DNI", "name" => "DNI"],
            ["id" => "CUIT", "name" => "CUIT"],
        ];

        $createAccountFormFields = [
            [
                'col' => 4,
                'type' => 'select',
                'maxlength' => 100,
                'name' => 'CORREOARGENTINO_DOCUMENT_TYPE',
                'label' => $this->l('Tipo de Documento'),
                'options' => [
                    'query' => $documentTypes,
                    'id' => 'id',
                    'name' => 'name'
                ],
                'required' => true,
            ],
            [
                'col' => 4,
                'type' => 'text',
                'maxlength' => 100,
                'name' => 'CORREOARGENTINO_DOCUMENT_ID',
                'label' => $this->l('Número de Documento'),
                'required' => true,
                'desc' => $this->l('Si elegís CUIT solo agregá el mismo sin guiones')
            ],
            [
                'col' => 4,
                'type' => 'text',
                'maxlength' => 100,
                'name' => 'CORREOARGENTINO_FIRST_NAME',
                'label' => $this->l('Nombre'),
                'required' => true,
            ],
            [
                'col' => 4,
                'type' => 'text',
                'maxlength' => 100,
                'name' => 'CORREOARGENTINO_LAST_NAME',
                'label' => $this->l('Apellido'),
                'desc' => $this->l('Requerido para DNI'),
            ],
            [
                'col' => 4,
                'type' => 'text',
                'constraint' => "isEmail",
                'maxlength' => 120,
                'hint' => $this->l('Enter the following format:') . ' correoargentino@correo.com',
                'name' => 'CORREOARGENTINO_EMAIL',
                'label' => $this->l('E-mail'),
                'required' => true,
            ],
            [
                'col' => 4,
                'type' => 'password',
                'label' => $this->l('Contraseña'),
                'name' => 'CORREOARGENTINO_PASSWORD_MICORREO',
                'required' => true,
            ],
            [

                'col' => 6,
                'type' => 'text',
                'maxlength' => 64,
                'name' => 'CORREOARGENTINO_STREET_NAME',
                'label' => $this->l('Street'),
                'desc' => $this->l('Requerido para DNI'),
            ],
            [
                'col' => 2,
                'type' => 'text',
                'maxlength' => 6,
                'name' => 'CORREOARGENTINO_STREET_NUMBER',
                'label' => $this->l('Street number'),
                'desc' => $this->l('Requerido para DNI'),
            ],
            [
                'col' => 2,
                'type' => 'text',
                'maxlength' => 2,
                'name' => 'CORREOARGENTINO_FLOOR',
                'label' => $this->l('Floor'),
                'required' => false
            ],
            [
                'col' => 2,
                'type' => 'text',
                'maxlength' => 2,
                'name' => 'CORREOARGENTINO_DEPARTAMENT',
                'label' => $this->l('Department'),
                'required' => false
            ],
            [
                'col' => 6,
                'type' => 'text',
                'maxlength' => 64,
                'name' => 'CORREOARGENTINO_CITYNAME',
                'label' => $this->l('City'),
                'desc' => $this->l('Requerido para DNI'),
            ],
            [
                'col' => 4,
                'type' => 'select',
                'name' => 'CORREOARGENTINO_STATE',
                'label' => $this->l('State'),
                'desc' => $this->l('Requerido para DNI'),
                'options' => [
                    'query' => $states,
                    'id' => 'id',
                    'name' => 'name'
                ]
            ],
            [
                'col' => 4,
                'type' => 'text',
                'maxlength' => 8,
                'name' => 'CORREOARGENTINO_ZIP_CODE',
                'label' => $this->l('Postal code'),
                'desc' => $this->l('Requerido para DNI'),
                'desc' => $this->l('Please enter your postal code. You can find it in ') . "<a href='https://www.correoargentino.com.ar/formularios/cpa'>" . $this->l('here') . "</a>",
                'hint' => $this->l('You can enter only the numeric part'),
                'required' => true
            ],
            [
                'col' => 2,
                'type' => 'text',
                'maxlength' => 4,
                'name' => 'CORREOARGENTINO_AREA_CODE_CELL_PHONE',
                'label' => $this->l('Cellphone area code'),
                'required' => true
            ],
            [
                'col' => 6,
                'type' => 'text',
                'maxlength' => 16,
                'name' => 'CORREOARGENTINO_CELL_PHONE_NUMBER',
                'label' => $this->l('Cellphone'),
                'required' => true
            ],
            [
                'col' => 2,
                'type' => 'text',
                'maxlength' => 4,
                'name' => 'CORREOARGENTINO_AREA_CODE_PHONE',
                'label' => $this->l('Telephone area code'),
                'required' => false
            ],
            [
                'col' => 6,
                'type' => 'text',
                'maxlength' => 16,
                'name' => 'CORREOARGENTINO_PHONE_NUMBER',
                'label' => $this->l('Telephone'),
                'required' => false
            ],
        ];

        $businessFormFields = [
            [
                'col' => 6,
                'type' => 'text',
                'maxlength' => 120,
                'name' => 'CORREOARGENTINO_BUSINESS_NAME',
                'label' => $this->l('Business name'),
                'required' => true,
                'desc' => $this->l('You can use a personal or business name.')
            ],
            [
                'col' => 4,
                'type' => 'text',
                'constraint' => "isEmail",
                'maxlength' => 120,
                'hint' => $this->l('Enter the following format:') . ' correoargentino@correo.com',
                'name' => 'CORREOARGENTINO_EMAIL',
                'label' => $this->l('E-mail'),
                'required' => true
            ],
            [
                'col' => 4,
                'type' => 'select',
                'name' => 'CORREOARGENTINO_STATE',
                'label' => $this->l('State'),
                'required' => false,
                'options' => [
                    'query' => $states,
                    'id' => 'id',
                    'name' => 'name'
                ]
            ],
            [
                'col' => 6,
                'type' => 'text',
                'maxlength' => 64,
                'name' => 'CORREOARGENTINO_CITYNAME',
                'label' => $this->l('City'),
                'required' => true
            ],
            [
                'col' => 2,
                'type' => 'text',
                'maxlength' => 2,
                'name' => 'CORREOARGENTINO_DEPARTAMENT',
                'label' => $this->l('Department'),
                'required' => false
            ],
            [
                'col' => 2,
                'type' => 'text',
                'maxlength' => 2,
                'name' => 'CORREOARGENTINO_FLOOR',
                'label' => $this->l('Floor'),
                'required' => false
            ],
            [

                'col' => 6,
                'type' => 'text',
                'maxlength' => 64,
                'name' => 'CORREOARGENTINO_STREET_NAME',
                'label' => $this->l('Street'),
                'required' => true
            ],
            [
                'col' => 2,
                'type' => 'text',
                'maxlength' => 6,
                'name' => 'CORREOARGENTINO_STREET_NUMBER',
                'label' => $this->l('Street number'),
                'required' => true
            ],
            [
                'col' => 4,
                'type' => 'text',
                'maxlength' => 8,
                'name' => 'CORREOARGENTINO_ZIP_CODE',
                'label' => $this->l('Postal code'),
                'required' => true,
                'desc' => $this->l('Please enter your postal code. You can find it in ') . "<a href='https://www.correoargentino.com.ar/formularios/cpa'>" . $this->l('here') . "</a>",
                'hint' => $this->l('You can enter only the numeric part')
            ],
            [
                'col' => 2,
                'type' => 'text',
                'maxlength' => 2,
                'name' => 'CORREOARGENTINO_AREA_CODE_CELL_PHONE',
                'label' => $this->l('Cellphone area code'),
                'required' => false
            ],
            [
                'col' => 6,
                'type' => 'text',
                'maxlength' => 16,
                'name' => 'CORREOARGENTINO_CELL_PHONE_NUMBER',
                'label' => $this->l('Cellphone'),
                'required' => false
            ],
            [
                'col' => 2,
                'type' => 'text',
                'maxlength' => 2,
                'name' => 'CORREOARGENTINO_AREA_CODE_PHONE',
                'label' => $this->l('Telephone area code'),
                'required' => false
            ],
            [
                'col' => 6,
                'type' => 'text',
                'maxlength' => 16,
                'name' => 'CORREOARGENTINO_PHONE_NUMBER',
                'label' => $this->l('Telephone'),
                'required' => false
            ],
            [
                'col' => 6,
                'type' => 'textarea',
                'name' => 'CORREOARGENTINO_OBSERVATION',
                'maxlength' => 500,
                'label' => $this->l('Observation'),
                'required' => false,
                'desc' => $this->l('Enter the details to help us to find your address')
            ]
        ];
        $formFields = (Configuration::get('CORREOARGENTINO_SERVICE_TYPE') === CorreoArgentinoConstants::PAQ_AR || (int) Configuration::get('CORREOARGENTINO_CUSTOMER_ID') > 0)
            ? $businessFormFields
            : $createAccountFormFields;

        return $formFields;
    }

    /**
     * @return array[]
     */
    protected function getStatusesForm(): array
    {
        $states = new PrestaShop\PrestaShop\Adapter\OrderState\OrderStateDataProvider();
        $statesList = $states->getOrderStates(Configuration::get('PS_LANG_DEFAULT'));

        $optionsQuery = array_map(
            function ($v) {
                return [
                    'id' => $v['id_order_state'],
                    'name' => $v['name']
                ];
            },
            $statesList
        );

        $optionsQuery[] = [
            'id' => '',
            'name' => 'Sin asignar'
        ];

        return [
            'form' => [
                'name' => 'anotherXForm',
                'legend' => [
                    'title' => $this->l('Shipment Trigger'),
                    'icon' => 'icon-cogs',
                ],
                'input' => [
                    [
                        'col' => 6,
                        'type' => 'select',
                        'name' => 'CORREOARGENTINO_STATE_ORDER',
                        'label' => $this->l('Order state'),
                        'desc' => $this->l('This will be the state which will create a new order into Correo Argentino API'),
                        'required' => true,
                        'options' => [
                            'query' => $optionsQuery,
                            'id' => 'id',
                            'name' => 'name'
                        ]
                    ]
                ],
                'submit' => [
                    'name' => 'submitCorreoargentinoModuleStatus',
                    'title' => $this->l('Save'),
                ]
            ]
        ];
    }

    /**
     * @return array[]
     */
    public function getLoginFormMiCorreo(): array
    {
        if ((int)Configuration::get('CORREOARGENTINO_CUSTOMER_ID') > 0) {
            return array(
                'form' => array(
                    'legend' => array(
                        'title' => $this->l('Acceder a MiCorreo'),
                        'icon' => 'icon-cogs',
                    ),
                    'input' => array(
                        array(
                            'col' => 3,
                            'type' => 'text',
                            'constraint' => "isEmail",
                            'maxlength' => 120,
                            'hint' => $this->l('Enter the following format:') . ' correoargentino@correo.com',
                            'label' => $this->l('Email'),
                            'name' => 'CORREOARGENTINO_USERNAME_MICORREO',
                            'disabled' => true,
                        ),
                        array(
                            'type' => 'switch',
                            'label' => $this->l('Sandbox'),
                            'name' => 'CORREOARGENTINO_SANDBOX_MODE',
                            'is_bool' => true,
                            'values' => array(
                                array(
                                    'id' => 'label2_on',
                                    'value' => 1,
                                    'label' => $this->l('Enabled')
                                ),
                                array(
                                    'id' => 'label2_off',
                                    'value' => 0,
                                    'label' => $this->l('Disabled')
                                )
                            ),
                            'disabled' => true,

                        )
                    ),
                ),
            );
        }
        return array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Acceder a MiCorreo'),
                    'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'constraint' => "isEmail",
                        'maxlength' => 120,
                        'hint' => $this->l('Enter the following format:') . ' correoargentino@correo.com',
                        'label' => $this->l('Email'),
                        'name' => 'CORREOARGENTINO_USERNAME_MICORREO',
                        'required' => true
                    ),
                    array(
                        'type' => 'password',
                        'label' => $this->l('Contraseña'),
                        'name' => 'CORREOARGENTINO_PASSWORD_MICORREO',
                        'desc' => $this->l('Don\'t have an account yet?') . " " . "<a href=" . $this->context->link->getAdminLink('AdminModules', true) . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name . '&step=1&' . self::CREATE_ACCOUNT_URL_MI_CORREO . ">" . $this->l('Create a new account') . "</a>",
                        'required' => true,
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Sandbox'),
                        'name' => 'CORREOARGENTINO_SANDBOX_MODE',
                        'is_bool' => true,
                        'desc' => $this->l('Enable this if you want to work in test mode'),
                        'values' => array(
                            array(
                                'id' => 'label2_on',
                                'value' => 1,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'label2_off',
                                'value' => 0,
                                'label' => $this->l('Disabled')
                            )
                        )
                    )
                ),
                'submit' => array(
                    'name' => 'submitCorreoArgentinoMiCorreoModuleLogin',
                    'title' => $this->l('Guardar'),
                ),
            ),
        );
    }

    /**
     * Add the CSS & JavaScript files you want to be added on the FO.
     */
    public function hookHeader()
    {
        $this->context->controller->addJS($this->_path . '/views/js/front.js');
        $this->context->controller->addJS($this->_path . '/views/js/select2.min.js');
        $this->context->controller->addCSS($this->_path . '/views/css/front.css');
        $this->context->controller->addCSS($this->_path . '/views/css/select2.min.css');
    }


    /**
     * @throws PrestaShopDatabaseException
     */
    public function hookDisplayCarrierExtraContent($params)
    {
        $carrier_id = $params['carrier']['id'];
        $agency_ids = $carrier_ids = array_map(function ($item) {
            return (int)$item['id_carrier'];
        }, $this->getAgencyCarriersId());
        $carrier_ids = array_map(function ($item) {
            return (int)$item['id_carrier'];
        }, $this->getRegisteredCarriersId());
        if (
            !in_array($carrier_id, $carrier_ids) ||
            !in_array($carrier_id, $agency_ids)
        ) {
            return;
        }

        $cookie = $this->context->cookie;
        $cart = Context::getContext()->cart;
        $address = new Address((int)($cart->id_address_delivery));
        $state = new State((int) $address->id_state);
        $iso_code = $state->iso_code;

        $country = new Country($address->id_country);
        $states = $country->id ? State::getStatesByIdCountry($country->id) : [];

        $stateSelected = $cookie->state_selected;

        $iso_code = $stateSelected ? $stateSelected : $iso_code;

        if ($iso_code) {
            $service = (new CorreoArgentinoServiceFactory())->get();
            $agencies = $service->getBranches($iso_code);
            $results = $service->branchesAdapter($agencies);
        } else {
            $results = [];
        }
        $branchPostCode = $cookie->branch_postcode;
        $branchSelected = $cookie->branch_selected;

        $is_enabled = Module::isEnabled($this->name);
        $is_enabled_mobile = Module::isEnabledForMobileDevices($this->name);
        $this->smarty->assign(compact('carrier_id', 'is_enabled', 'is_enabled_mobile', 'iso_code', 'states', 'results', 'branchSelected'));
        return $this->display(__FILE__, 'display-carrier-extra-content.tpl');
    }

    /** 
     * Hook action cart save
     * 
     * @param cart $params
     */
    public function hookActionCartSave($params)
    {
        $cookie = $this->context->cookie;
        $cookieId = 'correoargentino_rates';
        unset($cookie->$cookieId);
        unset($cookie->branch_postcode);
        unset($cookie->branch_selected);
        unset($cookie->state_selected);
    }

    public function hookActionPresentOrder($params)
    {
        if (Module::isEnabled($this->name) == false) {
            return false;
        }
    }

    /**
     * @param $params
     * @return false
     * @throws PrestaShopException
     */
    public function hookActionValidateStepComplete($params)
    {
        $step_name = $params['step_name'];

        if (Module::isEnabled($this->name) == false) {
            return;
        }

        if ($step_name == 'delivery') {
            // Load cart
            $cart = $params['cart'];
            $id_carrier = $cart->id_carrier;

            // Get carrier info
            $isBranch = CorreoArgentinoRatesModel::isBranch($id_carrier);
            $request_params = $params['request_params'];
            $branch_id = $isBranch ? $request_params["correoargentino_branch_id_" . $id_carrier] : null;
            $branch_state = $isBranch ? $request_params["correoargentino_state_id_" . $id_carrier] : null;

            // Get branch data
            $address = new  Address((int)$cart->id_address_delivery);
            $state = new State($address->id_state);
            $service = (new CorreoArgentinoServiceFactory())->get();

            $cart = new \Cart(\Context::getContext()->cart->id);
            $carrier_id = $cart->id_carrier;


            $branchPostCode = Tools::getValue('correoargentino_state_id_' . $carrier_id);

            $iso_code = $branchPostCode ? $branchPostCode : $state->iso_code;


            $branchData = $service->getBranchData($branch_id, $iso_code);

            // Save BranchOrder
            $branch = CorreoArgentinoBranchOrderModel::findOrCreate($cart->id);
            $branch->branch_state = $isBranch ? $branch_state : null;
            $branch->branch_code = $isBranch ? $branch_id : null;
            $branch->branch_select = $isBranch ? $id_carrier : null;
            $branch->branch_name = $service->getBranchName($branchData);
            $branch->id_order = null;
            $branch->tracking = null;
            $branch->total = 0; //TODO: get total
            $branch->save();
        }
    }

    /**
     *
     * @throws PrestaShopException
     */
    public function hookActionValidateOrder($params)
    {
        $order = $params['order'];
        $carrier = new Carrier($order->id_carrier);

        if ($carrier->external_module_name != "correoargentino" || Module::isEnabled($this->name) == false) {
            return;
        }

        $isBranch = CorreoArgentinoRatesModel::isBranch($order->id_carrier);
        $branch = CorreoArgentinoBranchOrderModel::findOrCreate($order->id_cart);
        $branch->id_order = $order->id;
        $branch->shipping_type = $isBranch ? 'agency' : 'homeDelivery';
        $branch->save();

        $service = (new CorreoArgentinoServiceFactory())->get();
        $branchData = $service->getBranchData($branch->branch_code, $branch->branch_state);

        
        if ($branch->shipping_type === "agency") {
            $context = Context::getContext();
            $customer_thread = new CustomerThread();
            $customer_thread->id_order = $order->id;
            $customer_thread->id_customer = $order->id_customer;
            $customer_thread->id_lang = $context->language->id;
            $customer_thread->id_shop = $context->shop->id;
            $customer_thread->id_contact = 0;
            $customer_thread->token = Tools::passwdGen(12);
            $customer_thread->save();
            $message = new CustomerMessage();
            $message->id_customer_thread = $customer_thread->id;

            if (Configuration::get('CORREOARGENTINO_SERVICE_TYPE') === CorreoArgentinoConstants::MI_CORREO) {
                $message->message = 'Sucursal: ' . $branchData['name'] . PHP_EOL .
                    'Dirección: ' . $branchData['location']['address']['streetName'] . ' ' . $branchData['location']['address']['streetNumber'] . PHP_EOL .
                    'Provincia: ' . $branchData['location']['address']['province'];
            } else {
                $message->message = 'Sucursal: ' . $branchData['agency_name'] . PHP_EOL .
                    'Dirección: ' . $branchData['location']['street_name'] . ' ' . $branchData['location']['street_number'] . PHP_EOL .
                    'Provincia: ' . $branchData['location']['state_name'];
            }
            $message->private = 1;
            $message->save();
        }

    }

    /**
     * Add buttons to main buttons bar
     */
    public function hookActionGetAdminOrderButtons(array $params)
    {
        $order = new Order($params['id_order']);

        if (!CorreoArgentinoRatesModel::isCaOrder($order->id_carrier) || $this->checkOrderState($order, 'Canceled')) {
            return false;
        }

        $branchOrder = CorreoArgentinoBranchOrderModel::findByOrder($order->id);

        if (!$branchOrder) {
            return false;
        }

        if ($branchOrder->status == CorreoArgentinoBranchOrderModel::STATUS_CANCELLED) {
            return false;
        }

        $bar = $params['actions_bar_buttons_collection'];

        if ($branchOrder->status !== CorreoArgentinoBranchOrderModel::STATUS_IMPORTED) {
            $correoArgentinoServiceType = Configuration::get('CORREOARGENTINO_SERVICE_TYPE');

            $label = Configuration::get('CORREOARGENTINO_SERVICE_TYPE') === 'PAQ_AR' ? 'Preimponer' : 'Importar';

            $syncOrderUrl = Context::getContext()->link->getAdminLink('AdminCorreoArgentino', true, null, ['vieworder' => 'sync', 'id_order' => $order->id]);

            $bar->add(
                new \PrestaShopBundle\Controller\Admin\Sell\Order\ActionsBarButton(
                    'btn-action',
                    ['href' => $syncOrderUrl],
                    '<i class="material-icons" aria-hidden="true"></i> ' . $this->l($label)
                )
            );
        } else {
            if (Configuration::get('CORREOARGENTINO_SERVICE_TYPE') === 'PAQ_AR') {
                $confirmation_message = $this->l('Are you sure to cancel this order?');
                $cancelAnOrderUrl = Context::getContext()->link->getAdminLink('AdminCorreoArgentino', true, null, ['vieworder' => 'cancel', 'id_order' => $order->id]);
                $bar->add(
                    new \PrestaShopBundle\Controller\Admin\Sell\Order\ActionsBarButton(
                        'btn-action',
                        ['href' => $cancelAnOrderUrl, 'onClick' => "return confirm('{$confirmation_message}')"],
                        '<i class="material-icons" aria-hidden="true">block</i> ' . $this->l('Cancel')
                    )
                );

                $labelAnOrderUrl = Context::getContext()->link->getAdminLink('AdminCorreoArgentino', true, null, ['vieworder' => 'pdf', 'id_order' => $order->id]);
                $bar->add(
                    new \PrestaShopBundle\Controller\Admin\Sell\Order\ActionsBarButton(
                        'btn-action',
                        ['href' => $labelAnOrderUrl],
                        '<i class="material-icons" aria-hidden="true">picture_as_pdf</i>' . $this->l('Label')
                    )
                );
            }
        }
    }

    public function checkOrderState($order, $state): bool
    {
        return in_array($state, $order->getCurrentOrderState()->name);
    }

    public function hookDisplayContentWrapperTop($params)
    {
        // if is checkout page
        if (Tools::getValue('controller') === 'order') {
            $cart = Context::getContext()->cart;
            $dimensions = CorreoArgentinoUtil::getPackageSize($cart);

            $oversized = false;

            if (isset($dimensions['weight'])) {
                if (Configuration::get('CORREOARGENTINO_SERVICE_TYPE') === 'PAQ_AR') {
                    if ($dimensions['weight'] > CorreoArgentinoConstants::CA_MAX_WEIGHT_PAQ_AR) {
                        $oversized = true;
                    }
                } else {
                    if ($dimensions['weight'] > CorreoArgentinoConstants::CA_MAX_WEIGHT_MI_CORREO) {
                        $oversized = true;
                    }
                }
            }
            if ($oversized) {
                echo $this->display(__FILE__, 'views/templates/hook/display-content-wrapper-top.tpl');
            }
        }
    }
}
