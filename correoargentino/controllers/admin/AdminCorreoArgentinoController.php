<?php

/**
 * 2007-2020 PrestaShop
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
 * @copyright 2007-2020 PrestaShop SA
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use CorreoArgentino\Services\CorreoArgentinoServiceFactory;
use CorreoArgentino\Repository\CorreoArgentinoBranchOrderModel;
use CorreoArgentino\Utils\CorreoArgentinoUtil;

class AdminCorreoArgentinoController extends ModuleAdminController
{

    const ADMIN_GLOBAL = 'Admin.Global';
    const ADMIN_SHIPPING_FEATURE = 'Admin.Shipping.Feature';
    const CANCEL = 'cancel';
    const CONFIRMATION_MESSAGE = 'confirmation_message';
    const PDF = 'pdf';
    const VIEW = 'view';
    const VIEW_ORDER = 'vieworder';
    const SYNC = 'sync';
    const SYNC_ORDER = 'syncorder';

    protected $_module = NULL;

    public function __construct()
    {
        $this->bootstrap = true;
        $this->table = 'correoargentino_branch_order';
        $this->lang = false;

        $this->explicitSelect = true;
        $this->identifier = 'id_order';
        $this->list_no_link = true;

        parent::__construct();

        $label = Configuration::get('CORREOARGENTINO_SERVICE_TYPE') === 'PAQ_AR' ? $this->l('SyncPaq.ar') : $this->l('Sync');
        $confirm = Configuration::get('CORREOARGENTINO_SERVICE_TYPE') === 'PAQ_AR' ? $this->l('Are you sure you want to pre-impose selected orders?') : $this->l('Are you sure you want to import selected orders?');
        $this->bulk_actions = array(
            'sync' => array(
                'text' => $label,
                'confirm' => $confirm,
            ),
        );

        $this->_select = '
        b.id_currency,
        reference,
        b.date_add,
        a.status as correoargentino_status,
        b.id_order AS id_pdf,
        a.branch_name,
        oc.tracking_number,
        car.service_type AS service_type,
        car.delivered_type AS delivered_type,
        CONCAT(LEFT(cus.`firstname`, 1), \'. \', cus.`lastname`) AS `customer`, 
        osl.`name` AS `osname`,
        os.`color`,
        country_lang.name as cname,
        IF(b.valid, 1, 0) badge_success';



        $this->_join = '
        LEFT JOIN `' . _DB_PREFIX_ . 'orders` b ON (a.`id_order` = b.`id_order`)
        LEFT JOIN `' . _DB_PREFIX_ . 'order_carrier` oc ON (a.`id_order` = oc.`id_order`)
        LEFT JOIN `' . _DB_PREFIX_ . 'correoargentino_rates` car ON (oc.`id_carrier` = car.`id_carrier`)
        LEFT JOIN `' . _DB_PREFIX_ . 'customer` cus ON (cus.`id_customer` = b.`id_customer`)
        INNER JOIN `' . _DB_PREFIX_ . 'address` address ON address.id_address = b.id_address_delivery
        INNER JOIN `' . _DB_PREFIX_ . 'country` country ON address.id_country = country.id_country
        INNER JOIN `' . _DB_PREFIX_ . 'country_lang` country_lang ON (country.`id_country` = country_lang.`id_country` AND country_lang.`id_lang` = ' . (int)$this->context->language->id . ')
        LEFT JOIN `' . _DB_PREFIX_ . 'order_state` os ON (os.`id_order_state` = b.`current_state`)
        LEFT JOIN `' . _DB_PREFIX_ . 'order_state_lang` osl ON (os.`id_order_state` = osl.`id_order_state` AND osl.`id_lang` = ' . (int)$this->context->language->id . ')';



        $this->_where = 'and a.id_order != 0';

        $this->_orderBy = 'a.id_order';
        $this->_orderWay = 'DESC';
        $this->_use_found_rows = true;

        $this->fields_list = array(
            'id_order' => array(
                'title' => $this->trans('ID', array(), self::ADMIN_GLOBAL),
                'align' => 'text-center',
                'class' => 'fixed-width-xs',
                'filter_key' => 'b!id_order',
            ),
            'reference' => array(
                'title' => $this->trans('Reference', array(), self::ADMIN_GLOBAL),
                'callback' => 'getReference',
                'callback_object' => $this,
            ),
            'tracking_number' => array(
                'title' => (Configuration::get('CORREOARGENTINO_SERVICE_TYPE') === 'PAQ_AR') ? $this->trans('Tracking number', array(), self::ADMIN_SHIPPING_FEATURE) : $this->trans('Número de referencia', array(), self::ADMIN_GLOBAL),
                'callback' => 'getTrackingNumber',
                'callback_object' => $this,
            ),
            'customer' => array(
                'title' => $this->trans('Customer', array(), self::ADMIN_GLOBAL),
                'havingFilter' => true,
            ),
            'osname' => array(
                'title' => $this->trans('Status', array(), self::ADMIN_GLOBAL),
                'color' => 'color',
                'filter_key' => 'os!id_order_state',
                'filter_type' => 'int',
                'order_key' => 'osname',
            ),
            'shipping_type' => array(
                'title' => $this->l('Shipping type'),
                'callback' => 'getShippingType',
            ),
            'branch_name' => array(
                'title' => $this->l('Agency'),
            ),
            'correoargentino_status' => array(
                'title' => $this->l('Correo Argentino Status'),
                'callback' => 'getCorreoArgentinoStatus',
                'callback_object' => $this
            ),
            'date_add' => array(
                'title' => $this->trans('Date', array(), self::ADMIN_GLOBAL),
                'align' => 'text-right',
                'type' => 'datetime',
                'filter_key' => 'b!date_add',
            )
        );

        if (Tools::isSubmit('id_order')) {
            try {
                $order = new Order((int)Tools::getValue('id_order'));
                $viewOrder = Tools::getValue(self::VIEW_ORDER, self::VIEW);
                $tracking = $order->getWsShippingNumber();
                $service = (new CorreoArgentinoServiceFactory())->get();
                $branchOrder = CorreoArgentinoBranchOrderModel::findByOrder($order->id);

                if ($viewOrder == self::CANCEL && empty($tracking)) {
                    $this->context->controller->errors[] = $this->l('You can\'t cancel an order without tracking');
                }

                if ($viewOrder == self::CANCEL) {
                    if ($branchOrder->status == CorreoArgentinoBranchOrderModel::STATUS_CANCELLED) {
                        $this->context->controller->errors[] = $order->id . ': ' . $this->l('Order cancelled');
                        return;
                    }

                    $service = $service::getInstanceWithLogin();
                    try {
                        $message = $service->cancel($tracking);
                        $this->context->controller->confirmations[] = $order->id . ': ' . $message;
                        $order->setCurrentState(Configuration::get('PS_OS_CANCELED'));
                        $order->save();
                        $branchOrder->status = CorreoArgentinoBranchOrderModel::STATUS_CANCELLED;
                        $branchOrder->save();
                    } catch (Exception $e) {
                        $this->context->controller->errors[] = $e->getMessage();
                    }
                }

                if ($viewOrder == self::PDF) {
                    if ($branchOrder->status == CorreoArgentinoBranchOrderModel::STATUS_CANCELLED) {
                        $this->context->controller->errors[] = $order->id . ': ' . $this->l('Order cancelled');
                        return;
                    }
                    $service = $service::getInstanceWithLogin();
                    $label = $service->label($tracking);
                    $this->context->controller->errors[] = $label['result'];
                    CorreoArgentinoUtil::renderPDF($label);
                }
                if ($viewOrder == self::SYNC) {
                    $id_order = Tools::getValue('id_order');
                    $this->registerOrder($id_order);
                }
            } catch (Exception | TransportExceptionInterface $e) {
                $this->context->controller->errors[] = $e->getMessage();
                PrestaShopLogger::addLog($e->getMessage(), 3);
            }
        }
    }

    protected function processBulkSync()
    {
        if (is_array($this->boxes) && !empty($this->boxes)) {
            $service = (new CorreoArgentinoServiceFactory())->get();
            foreach ($this->boxes as $id_order) {
                $this->registerOrder($id_order);
            }
        }
    }

    protected function registerOrder($id_order)
    {
        $service = (new CorreoArgentinoServiceFactory())->get();
        $branchOrder = CorreoArgentinoBranchOrderModel::findByOrder($id_order);

        if ($branchOrder->status == CorreoArgentinoBranchOrderModel::STATUS_IMPORTED) {
            $label = Configuration::get('CORREOARGENTINO_SERVICE_TYPE') === 'PAQ_AR' ? $this->l('The order has already been pre-imposed') : $this->l('The order has already been imported');
            $this->context->controller->confirmations[] = $id_order . ': ' . $label;
            return;
        }

        if ($branchOrder->status == CorreoArgentinoBranchOrderModel::STATUS_CANCELLED) {
            $this->context->controller->errors[] = $id_order . ': ' . $this->l('Order cancelled');
            return;
        }

        try {
            $service->registerOrder($id_order);

            $branchOrder->status = CorreoArgentinoBranchOrderModel::STATUS_IMPORTED;
            $branchOrder->save();
            $label = Configuration::get('CORREOARGENTINO_SERVICE_TYPE') === 'PAQ_AR' ? $this->l('Pre-imposed order') : $this->l('Imported order');
            $this->context->controller->confirmations[] = $id_order . ': ' . $label;
        } catch (Exception | TransportExceptionInterface $e) {
            $branchOrder->status = $e->getMessage();
            $branchOrder->save();
            $this->context->controller->errors[] = $id_order . ': ' . $e->getMessage();
            PrestaShopLogger::addLog($e->getMessage(), 3);
        }
    }

    public function getShippingType($value, $row)
    {
        $service_type = $row['service_type'];
        $delivered_type = $row['delivered_type'];

        $shipping_types = [
            'CPS' => 'Clásico a Sucursal',
            'EPS' => 'Expreso a Sucursal',
            'CPD' => 'Clásico a Domicilio',
            'EPD' => 'Expreso a Domicilio'
        ];

        if (!in_array("{$service_type}{$delivered_type}", ['CPS', 'EPS', 'CPD', 'EPD'])) {
            echo "error";
            return;
        }

        return $shipping_types["{$service_type}{$delivered_type}"];
    }




    public function renderList()
    {
        $this->addRowAction(self::VIEW);
        $this->addRowAction(self::SYNC);
        if (Configuration::get('CORREOARGENTINO_SERVICE_TYPE') === 'PAQ_AR') {
            $this->addRowAction(self::CANCEL);
            $this->addRowAction(self::PDF);
        }
        return parent::renderList();
    }

    /**
     * @return string
     */
    public function getBranchName($branch_id)
    {
        $serviceInstance = (new CorreoArgentinoServiceFactory())->get();
        $branch = $serviceInstance::getBranchById($branch_id);
        if (!$branch) {
            return $branch_id;
        }
        return $branch->agency_name . ' (' . $branch->location->street_name . ' ' . $branch->location->street_number . ')';
    }

    /**
     * @return string
     */
    public function getReference($reference, $data)
    {
        $order_id = $data['id_order'];
        // get admin link to order
        $order_link = $this->context->link->getAdminLink('AdminOrders', true, array('id_order' => $order_id, 'vieworder' => ''));
        return '<a href="' . $order_link . '">' . $reference . '</a>';
    }

    /**
     * @return string
     */
    public function getTrackingNumber($reference, $data)
    {
        $tracking_link = "https://www.correoargentino.com.ar/formularios/e-commerce?id={$reference}";
        $number = Configuration::get('CORREOARGENTINO_SERVICE_TYPE') === 'PAQ_AR' ? '<a href="' . $tracking_link . '">' . $reference . '</a>' : $reference;
        return $number;
    }

    /**
     * @return string
     */
    public function getCorreoArgentinoStatus($status)
    {

        if ($status && $status != '') {
            return $this->l($status);
        }
        return $status;
    }

    public function getShippingTypeTranslation($type)
    {
        return $type == 'agency' ? $this->l('Branch') : $this->l('Home delivery');
    }

    /**
     * @param null $token
     * @param $id
     * @param null $name
     * @return false|string
     * @throws SmartyException
     */
    public function displayCancelLink($token = null, $id, $name = null)
    {
        $branchOrder = CorreoArgentinoBranchOrderModel::findByOrder($id);
        if ($branchOrder->status == CorreoArgentinoBranchOrderModel::STATUS_CANCELLED) {
            return null;
        }
        $tpl = $this->context->smarty->createTemplate(_PS_MODULE_DIR_ . '/correoargentino/views/templates/admin/action_cancel.tpl');

        $order = new Order($id);
        $tracking = $order->getWsShippingNumber();

        if (!$tracking || $this->checkOrderState($order, 'Canceled')) {
            return null;
        }
        self::$cache_lang[self::CANCEL] = $this->l(ucfirst(self::CANCEL));
        $href = $this->context->link->getAdminLink('AdminCorreoArgentino', true, null, array(self::VIEW_ORDER => self::CANCEL, 'id_order' => $id));

        $tpl->assign(
            array(
                'href' => $href,
                'action' => self::$cache_lang[self::CANCEL],
                'confirmation_message' => $this->l('Are you sure to cancel this order?'),
                'id' => $id
            )
        );

        return $tpl->fetch();
    }

    public function checkOrderState($order, $state): bool
    {
        return in_array($state, $order->getCurrentOrderState()->name);
    }

    /**
     * @param null $token
     * @param $id
     * @param null $name
     * @return false|string
     * @throws SmartyException
     */
    public function displayPdfLink($token = null, $id, $name = null)
    {
        $branchOrder = CorreoArgentinoBranchOrderModel::findByOrder($id);
        if ($branchOrder->status == CorreoArgentinoBranchOrderModel::STATUS_CANCELLED) {
            return null;
        }
        $tpl = $this->context->smarty->createTemplate(_PS_MODULE_DIR_ . '/correoargentino/views/templates/admin/action_pdf.tpl');
        $order = new Order($id);
        $tracking = $order->getWsShippingNumber();
        if (!$tracking || $this->checkOrderState($order, 'Canceled')) {
            return null;
        }
        self::$cache_lang[self::PDF] = $this->l('Label');
        $href = $this->context->link->getAdminLink('AdminCorreoArgentino', true, null, array(self::VIEW_ORDER => self::PDF, 'id_order' => $id));

        $tpl->assign(
            array(
                'href' => $href,
                'action' => self::$cache_lang[self::PDF],
                'id' => $id
            )
        );

        return $tpl->fetch();
    }

    public function displayViewLink($token = null, $id, $name = null)
    {
        $tpl = $this->createTemplate('helpers/list/list_action_view.tpl');
        self::$cache_lang[self::VIEW] = $this->l('View');
        $href = $this->context->link->getAdminLink('AdminOrders', true, null, array(self::VIEW_ORDER => '', 'id_order' => $id));

        $tpl->assign(
            array(
                'href' => $href,
                'action' => self::$cache_lang[self::VIEW],
                'id' => $id
            )
        );

        return $tpl->fetch();
    }

    public function displaySyncLink($token = null, $id, $name = null)
    {
        $branchOrder = CorreoArgentinoBranchOrderModel::findByOrder($id);

        $statusesToExclude = [
            CorreoArgentinoBranchOrderModel::STATUS_IMPORTED,
            CorreoArgentinoBranchOrderModel::STATUS_CANCELLED
        ];

        if (in_array($branchOrder->status, $statusesToExclude)) {
            return null;
        }

        $buttonText = $this->getSyncButtonText();

        $syncOrderUrl = $this->context->link->getAdminLink('AdminCorreoArgentino', true, null, [
            self::VIEW_ORDER => self::SYNC,
            'id_order' => $id
        ]);

        $tpl = $this->context->smarty->createTemplate(_PS_MODULE_DIR_ . '/correoargentino/views/templates/admin/action_sync.tpl');

        $tpl->assign([
            'href' => $syncOrderUrl,
            'action' => $buttonText,
            'id' => $id
        ]);

        return $tpl->fetch();
    }

    private function getSyncButtonText()
    {
        return $label = Configuration::get('CORREOARGENTINO_SERVICE_TYPE') === 'PAQ_AR' ? $this->l('SyncPaq.ar') : $this->l('Sync');
    }


    public function viewAccess($disable = false)
    {
        if (version_compare(_PS_VERSION_, '1.7.6.5', '>=')) {
            return true;
        }
        return parent::viewAccess($disable);
    }

    /**
     *
     */
    public function renderView()
    {
        return parent::renderView();
    }

    /**
     *
     */
    public function initContent()
    {
        parent::initContent();
    }

    public function initToolbar()
    {
        parent::initToolbar();

        unset($this->toolbar_btn['new']);
    }
}
