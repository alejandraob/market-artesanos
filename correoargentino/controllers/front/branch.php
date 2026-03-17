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
 * @author PrestaShop SA <contact@prestashop.com>
 * @copyright  2007-2022 PrestaShop SA
 * @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */
include_once _PS_MODULE_DIR_ . 'correoargentino/vendor/autoload.php';

use CorreoArgentino\Services\CorreoArgentinoServiceFactory;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;

class CorreoArgentinoBranchModuleFrontController extends ModuleFrontController
{
    public $ssl = true;
    public $display_column_left = false;

    /**
     * @return void
     */
    public function initContent()
    {
        try {

            if (Tools::getValue('action') && Tools::getValue('action') == 'selectBranch'){
                $cookie = $this->context->cookie;
                $cookie->branch_postcode = Tools::getValue('postcode');
                $cookie->branch_selected = Tools::getValue('branch');
                $cookie->state_selected = Tools::getValue('state');
                unset($cookie->correoargentino_rates);


                $return = array(
                    'postcode' => Tools::getValue('postcode'),
                    'branch' => Tools::getValue('branch'),
                    'state' => Tools::getValue('state')
                );
                $this->sendResponse($return);
            }
            else{
                $cookie = $this->context->cookie;
            
                $cart = Context::getContext()->cart;
                $address = new Address((int)($cart->id_address_delivery));
                $state = new State((int) $address->id_state);
                $iso_code = $cookie->state_selected ? $cookie->state_selected : $state->iso_code;
                $iso_code = Tools::getValue('state', $iso_code);
    
                if (!isset($iso_code)) {
                    $this->sendResponse([]);
                }
    
                parent::initContent();
    
                $service = (new CorreoArgentinoServiceFactory())->get();
                $agencies = $service->getBranches($iso_code);
                $results = $service->branchesAdapter($agencies);
    
                $cookieId = 'correoargentino_rates';
                unset($cookie->$cookieId);
                unset($cookie->branch_postcode);
                unset($cookie->branch_selected);
                unset($cookie->state_selected);
    
                $this->sendResponse($results);
            }
        } catch (\Throwable $th) {
            $this->sendResponse(['error' => $th->getMessage()]);
        }
    }

    /**
     * Send JSON response with appropriate headers
     * @param array $data
     * @return void
     */
    private function sendResponse(array $data): void
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);
        exit;
    }
}
