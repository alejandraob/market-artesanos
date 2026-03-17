<?php

namespace CorreoArgentino\Services;

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

use Configuration;
use Order;
use Address;
use State;
use PrestaShopLogger;
use CorreoArgentino\Utils\CorreoArgentinoUtil;
use CorreoArgentino\Repository\CorreoArgentinoBranchOrderModel;
use CorreoArgentino\Repository\CorreoArgentinoRatesModel;
use CorreoArgentino\Exceptions\CorreoArgentinoException;
use CorreoArgentino\Interfaces\CorreoArgentinoServicePaqArInterface;
use Exception;
use PrestaShop\PrestaShop\Core\Domain\Order\Exception\OrderException;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Tools;


class CorreoArgentinoPaqArService implements CorreoArgentinoServicePaqArInterface
{

    const API_URL_PROD = "https://api.correoargentino.com.ar/paqar/v1";
    const API_URL_TEST = "https://apitest.correoargentino.com.ar/paqar/v1";
    /**
     * @var string
     */
    public $apiKey;
    /**
     * @var string
     */
    public $url;
    /**
     * @var string
     */
    public $agreement;
    /**
     * Get default environment variables
     * @var null[]|string[]
     */
    public $defaults;
    /**
     * @var HttpClientInterface
     */
    private $client;

    /**
     * CorreoArgentinoService constructor.
     */
    public function __construct()
    {
        $this->url = Configuration::get('CORREOARGENTINO_SANDBOX_MODE') ? self::API_URL_TEST : self::API_URL_PROD;
        $this->apiKey = Configuration::get('CORREOARGENTINO_API_KEY', '');
        $this->agreement = Configuration::get('CORREOARGENTINO_AGREEMENT', '');

        $defaultHeaders = [
            "headers" => [
                "Content-Type" => "application/json",
                "Connection" => "keep-alive"
            ],
            'verify_peer' => !Configuration::get('CORREOARGENTINO_SANDBOX_MODE'),
            'verify_host' => !Configuration::get('CORREOARGENTINO_SANDBOX_MODE')
        ];

        if ($this->apiKey && $this->agreement) {
            $defaultHeaders["headers"]["Authorization"] = "Apikey " . $this->apiKey;
            $defaultHeaders["headers"]["Agreement"] = $this->agreement;
        }
        $this->client = HttpClient::create($defaultHeaders);
    }


    public static function getInstanceWithLogin()
    {
        $service = new CorreoArgentinoPaqArService();
        $agreement = Configuration::get('CORREOARGENTINO_AGREEMENT');
        $apiKey = Configuration::get('CORREOARGENTINO_API_KEY');
        $service->login($agreement, $apiKey);
        return $service;
    }

    /**
     * @param $agreement
     * @param $apiKey
     * @return bool
     */
    public function login($agreement, $apiKey, $sandbox = false): bool
    {
        try {
            if ($sandbox) {
                $this->url = self::API_URL_TEST;
            }
            $response = $this->client->request('GET', $this->url . '/auth', [
                'headers' => [
                    "Authorization" => "Apikey " . $apiKey,
                    "Agreement" => $agreement
                ]
            ]);
            $statusCode = $response->getStatusCode();

            if ($statusCode === 204) {
                Configuration::updateValue("CORREOARGENTINO_API_KEY_SUCCESS", true);
                Configuration::updateValue("CORREOARGENTINO_API_KEY", $apiKey);
                Configuration::updateValue("CORREOARGENTINO_AGREEMENT", $agreement);
                return true;
            }
            return false;
        } catch (ClientExceptionInterface | RedirectionExceptionInterface | ServerExceptionInterface | TransportExceptionInterface $e) {
            PrestaShopLogger::addLog($e->getMessage(), 1);
            return false;
        }
    }

    public static function getBranchById($branch_id)
    {
        $agencies = json_decode(Tools::file_get_contents(_PS_MODULE_DIR_ . 'correoargentino/mock/agencies.json'));

        return array_values(array_filter($agencies, function ($value) use ($branch_id) {
            return $value->agency_id === $branch_id;
        }, ARRAY_FILTER_USE_BOTH))[0] ?? null;
    }

    public function setHeaders($headers): bool
    {
        return true;
    }

    /**
     * @return array|false
     * @throws DecodingExceptionInterface
     */
    public function getBranches(string $iso_state): array
    {
        $stateId = [];
        if (isset($iso_state)) {
            $stateId = ['stateId' => $iso_state];
        }
        try {
            $options = [
                'query' => array_merge(
                    ['pickup_availability' => true],
                    $stateId
                )
            ];
            $response = $this->client->request('GET', $this->url . '/agencies', $options);
            $statusCode = $response->getStatusCode();
            if ($statusCode === 200) {
                return $response->toArray();
            }
        } catch (
            ClientExceptionInterface
            | RedirectionExceptionInterface
            | ServerExceptionInterface
            | TransportExceptionInterface $e
        ) {
            PrestaShopLogger::addLog($e->getMessage(), 1);
        }
        return false;
    }

    public function getBranchData(?string $branch_id, string $iso_state): array
    {
        if ($branch_id) {
            $branches = $this->getBranches($iso_state);
            if ($branches) {
                $branch = array_filter(
                    $branches,
                    function ($item) use ($branch_id) {
                        return $item['agency_id'] === $branch_id;
                    }
                );
                return $branch && !empty($branch) ? reset($branch) : [];
            }
        }
        return [];
    }

    public function getBranchName(array $branch): string
    {
        return isset($branch['agency_name']) ? $branch['agency_name'] : '';
    }

    public function branchesAdapter(array $agencies): array
    {
        $results = [];
        foreach ($agencies as $value) {
            $text = join(
                ", ",
                [
                    trim($value['location']['state_name']),
                    trim($value['location']['city_name']),
                    trim($value['agency_name']),
                    trim($value['location']['street_name']) . " " . trim($value['location']['street_number']),
                    "(" . trim($value['location']['zip_code']) . ")"
                ]
            );

            $results[] = [
                "id" => $value['agency_id'],
                "text" => $text,
                "postcode" => isset($value['location']['zip_code']) ? $value['location']['zip_code'] : "",
            ];
        }
        return $results;
    }

    /**
     * @return int|mixed
     * @throws TransportExceptionInterface
     */
    public function getRates($postalCode, $deliveryType, $dimensions)
    {
        $body = [
            "agreement" => "string",
            "deliveryType" => "string",
            "parcels" => [
                [
                    "declaredValue" => "string",
                    "dimensions" => [
                        "depth" => "string",
                        "height" => "string",
                        "width" => "string"
                    ],
                    "weight" => "string"
                ]
            ],
            "senderData" => [
                "zipCode" => "string"
            ],
            "serviceType" => "string",
            "shippingData" => [
                "zipCode" => "string"
            ]
        ];

        $response = $this->client->request('POST', $this->url . '/rates', ['headers' => ["Content-Type" => "application/json", "Accept" => "application/json"], 'body' => json_encode($body)]);
        $statusCode = $response->getStatusCode();
        if ($statusCode == 200) {
            return $response->json();
        }
        return $statusCode;
    }

    /**
     * @param $tracking
     * @return array
     */
    public function label($tracking)
    {
        $body = json_encode([["trackingNumber" => $tracking]]);
        try {
            $response = $this->client->request('POST', $this->url . '/labels', ["body" => $body]);
            $statusCode = $response->getStatusCode();
            if ($statusCode == 200) {
                return $response->toArray()[0];
            }
        } catch (
            ClientExceptionInterface |
            DecodingExceptionInterface |
            RedirectionExceptionInterface |
            ServerExceptionInterface |
            TransportExceptionInterface $e
        ) {
            PrestaShopLogger::addLog($e->getMessage(), 1);
        }
    }

    /**
     * @param $tracking
     * @return array|string
     *
     * @throws CorreoArgentinoException
     */
    public function cancel($tracking)
    {
        try {
            $response = $this->client->request('PATCH', $this->url . "/orders/$tracking/cancel");
            return $response->getContent(false);
        } catch (
            \Symfony\Component\HttpClient\Exception\TransportException |
            \Symfony\Component\HttpClient\Exception\ClientException $e
        ) {
            $content = json_decode($e->getResponse()->getContent(false));
            throw new CorreoArgentinoException($content->message, 1);
        }
    }

    /**
     * @param Order $order
     * @return bool|Exception|ClientExceptionInterface|DecodingExceptionInterface|RedirectionExceptionInterface|ServerExceptionInterface|TransportExceptionInterface
     * @throws OrderException
     * @throws PrestaShopException
     */
    public function registerOrder(string $id_order)
    {
        $branchOrder = CorreoArgentinoBranchOrderModel::findByOrder($id_order);
        if ($branchOrder->status === CorreoArgentinoBranchOrderModel::STATUS_IMPORTED) {
            throw new CorreoArgentinoException("La orden fue importada con anterioridad.", 1);
        }

        $order = new Order($id_order);
        $products = $order->getCartProducts();
        $delivery = new Address($order->id_address_delivery);
        $customer = $order->getCustomer();
        $isBranch = CorreoArgentinoRatesModel::isBranch($order->id_carrier);
        $carrierSettings = CorreoArgentinoRatesModel::getSettingsByCarrierId($order->id_carrier);
        $state = new State($delivery->id_state);
        // $cartBranch = CorreoArgentinoBranchOrderModel::findOrCreate($order->id_cart);
        $parcels = array_map(function ($value) {
            return [
                "declaredValue" => (float)$value['total_wt'],
                "dimensions" => [
                    "depth" => (int)$value['depth'],
                    "height" => (int)$value['height'],
                    "width" => (int)$value['width'],
                ],
                "productCategory" => filter_var($value['product_name'], FILTER_SANITIZE_STRING),
                "productWeight" => (int)($value['product_weight'] * 1000)
            ];
        }, $products);

        $shippingData = [
            "address" => [
                "cityName" => $delivery->city,
                "department" => $delivery->address1,
                "floor" => $delivery->address2,
                "state" => $state->iso_code,
                "streetName" => $delivery->address1,
                "streetNumber" => $delivery->address1,
                "zipCode" => CorreoArgentinoUtil::normalizeZipCode($delivery->postcode)
            ],
            "areaCodeCellphone" => "54",
            "areaCodePhone" => "54",
            "phoneNumber" => $delivery->phone,
            "cellphoneNumber" => $delivery->phone_mobile,
            "email" => $customer->email,
            "name" => $customer->firstname . ' ' . $customer->lastname,
            "observation" => "",
        ];

        if ($isBranch) {
            $branch = $this->getBranchData($branchOrder->branch_code, $branchOrder->branch_state);
            if (empty($branch)) {
                throw new Exception('Sucursal no encontrada');
            }
            $location = $branch['location'];
            $shippingData = [
                "address" => [
                    "cityName" => $location['city_name'],
                    "department" => "",
                    "floor" => "",
                    "state" => $branchOrder->branch_state,
                    "streetName" => $location['street_name'],
                    "streetNumber" => $location['street_number'],
                    "zipCode" => CorreoArgentinoUtil::normalizeZipCode($location['zip_code'])
                ],
                "areaCodeCellphone" => "54",
                "areaCodePhone" => "54",
                "phoneNumber" => $delivery->phone_mobile,
                "cellphoneNumber" => $delivery->phone,
                "email" => $customer->email,
                "name" => $customer->firstname . ' ' . $customer->lastname,
                "observation" => "agency",
            ];
        }

        $cityName = Configuration::get('CORREOARGENTINO_CITYNAME');
        $department = Configuration::get('CORREOARGENTINO_DEPARTAMENT');
        $floor = Configuration::get('CORREOARGENTINO_FLOOR');
        $state = Configuration::get('CORREOARGENTINO_STATE');
        $streetName = Configuration::get('CORREOARGENTINO_STREET_NAME');
        $streetNumber = Configuration::get('CORREOARGENTINO_STREET_NUMBER');
        $zipCode = Configuration::get('CORREOARGENTINO_ZIP_CODE');
        $businessName = Configuration::get('CORREOARGENTINO_BUSINESS_NAME');
        $areaCodeCellphone = Configuration::get('CORREOARGENTINO_AREA_CODE_CELL_PHONE');
        $cellphoneNumber = Configuration::get('CORREOARGENTINO_CELL_PHONE_NUMBER');
        $areaCodePhone = Configuration::get('CORREOARGENTINO_AREA_CODE_PHONE');
        $phoneNumber = Configuration::get('CORREOARGENTINO_PHONE_NUMBER');
        $email = Configuration::get('CORREOARGENTINO_EMAIL');
        $observation = Configuration::get('CORREOARGENTINO_OBSERVATION');

        if (empty($cityName)) {
            throw new Exception('Los datos comerciales no han sido completados, complete el formulario de configuracion del módulo de Correo Argentino');
        }

        $body = [
            "sellerId" => "",
            "trackingNumber" => "",
            "order" => [
                "agencyId" => $isBranch ? $branchOrder->branch_code : "",
                "deliveryType" => $isBranch ? "agency" : "homeDelivery",
                "parcels" => $parcels,
                "shipmentClientId" => $order->reference,
                "serviceType" => $carrierSettings['service_type'],
                "saleDate" => date('Y-m-d\TH:i:sO'),
                "senderData" => [
                    "address" => [
                        "cityName" => $cityName,
                        "department" => $department,
                        "floor" => $floor,
                        "state" => $state,
                        "streetName" => $streetName,
                        "streetNumber" => $streetNumber,
                        "zipCode" => CorreoArgentinoUtil::normalizeZipCode($zipCode)
                    ],
                    "businessName" => $businessName,
                    "areaCodeCellphone" => $areaCodeCellphone,
                    "cellphoneNumber" => $cellphoneNumber,
                    "areaCodePhone" => $areaCodePhone,
                    "phoneNumber" => $phoneNumber,
                    "email" => $email,
                    "observation" => $observation,
                ],
                "shippingData" => $shippingData,
            ]
        ];

        try {
            $jsonBody = json_encode($body, 1);
            PrestaShopLogger::addLog("Paq.ar Shipping/Import: ({$jsonBody})", PrestaShopLogger::LOG_SEVERITY_LEVEL_INFORMATIVE);
            $response = $this->client->request('POST', $this->url . '/orders', ['body' => $jsonBody]);
            $statusCode = $response->getStatusCode();
            if ($statusCode == 200) {
                $content = $response->toArray();
                $order->setWsShippingNumber($content['trackingNumber']);
                $order->update();
                $branchOrder->shipping_type = $isBranch ? 'agency' : 'homeDelivery';
                $branchOrder->tracking = $content['trackingNumber'];
                $branchOrder->shipping_date = time();
                $branchOrder->status = CorreoArgentinoBranchOrderModel::STATUS_IMPORTED;
                $branchOrder->update();
                return true;
            } else {
                $output = $response->getContent(false);
                PrestaShopLogger::addLog("Paq.ar Shipping/Import: CA API Response ({$output})", PrestaShopLogger::LOG_SEVERITY_LEVEL_ERROR);
                $output = json_decode($output, true);
                throw new OrderException($output["message"]);
            }
        } catch (
            ClientExceptionInterface |
            DecodingExceptionInterface |
            RedirectionExceptionInterface |
            ServerExceptionInterface |
            TransportExceptionInterface $e
        ) {
            PrestaShopLogger::addLog("Paq.ar Shipping/Import: {$e->getMessage()}", PrestaShopLogger::LOG_SEVERITY_LEVEL_ERROR);
            return $e;
        }
    }

    /*public function getBranch($cartBranch)
    {
        $agencies = json_decode(Tools::file_get_contents(_PS_MODULE_DIR_ . 'correoargentino/mock/agencies.json'), 'true');
        $find = null;
        for ($i = 0; $i < count($agencies); $i++) {
            $data = $agencies[$i];
            if ($cartBranch->branch_code == $data['agency_id']) {
                $find = $data;
                break;
            }
        }

        return $find;
    }*/

    /**
     * Get branch details based on the provided branch code.
     *
     * @param object $cartBranch - Cart branch information.
     * @return array|null - Branch details if found, otherwise null.
     */
    // public function getBranch($cartBranch)
    // {
    //     $agencies = json_decode(file_get_contents(_PS_MODULE_DIR_ . 'correoargentino/mock/agencies.json'), true);

    //     foreach ($agencies as $data) {
    //         if ($cartBranch->branch_code == $data['agency_id']) {
    //             return $data;
    //         }
    //     }

    //     return null;
    // }
}
