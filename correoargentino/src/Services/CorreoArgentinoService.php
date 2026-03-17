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

use PrestaShop\PrestaShop\Core\Domain\Order\Exception\OrderException;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;


class CorreoArgentinoService
{

    const API_URL_PROD = "https://api.correoargentino.com.ar/paqar/v1";
    const API_URL_TEST = "https://apitest.correoargentino.com.ar/paqar/v1";

    /**
     * @var \Symfony\Contracts\HttpClient\HttpClientInterface
     */
    private $client;


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
            'verify_peer' => !(bool) Configuration::get('CORREOARGENTINO_SANDBOX_MODE'),
            'verify_host' => !(bool) Configuration::get('CORREOARGENTINO_SANDBOX_MODE')
        ];

        if ($this->apiKey && $this->agreement) {
            $defaultHeaders["headers"]["Authorization"] = "Apikey " . $this->apiKey;
            $defaultHeaders["headers"]["Agreement"] = $this->agreement;
        }
        $this->client = HttpClient::create($defaultHeaders);

    }


    /**
     * @return CorreoArgentinoService
     */
    public static function getInstanceWithLogin()
    {
        $service = new CorreoArgentinoService();
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


    /**
     * @param $cartBranch
     * @return mixed|null
     */
    public function getBranch($cartBranch)
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
    }

    public static function getBranchById($branch_id)
    {
        $agencies = json_decode(Tools::file_get_contents(_PS_MODULE_DIR_ . 'correoargentino/mock/agencies.json'));

        return array_values(array_filter($agencies, function ($value) use ($branch_id) {
            return $value->agency_id === $branch_id;
        }, ARRAY_FILTER_USE_BOTH))[0] ?? null;
    }

    /**
     * @return array|false
     * @throws DecodingExceptionInterface
     */
    public function getBranches()
    {
        try {
            $options = ['query' => ['pickup_availability' => true]];
            $response = $this->client->request('GET', $this->url . '/agencies', $options);
            $statusCode = $response->getStatusCode();
            if ($statusCode === 200) {
                return $response->toArray();
            }
            return false;
        } catch (ClientExceptionInterface | RedirectionExceptionInterface | ServerExceptionInterface | TransportExceptionInterface $e) {
            PrestaShopLogger::addLog($e->getMessage(), 1);
            return false;
        }
    }


    /**
     * @return int|mixed
     * @throws TransportExceptionInterface
     */
    public function getRates()
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
        } catch (ClientExceptionInterface | DecodingExceptionInterface | RedirectionExceptionInterface | ServerExceptionInterface | TransportExceptionInterface $e) {
            PrestaShopLogger::addLog($e->getMessage(), 1);
        }

    }


    /**
     * @param $tracking
     * @return bool
     * @throws TransportExceptionInterface
     */
    public function cancel($tracking): bool
    {
        $res = $this->client->request('PATCH', $this->url . "/orders/$tracking/cancel");
        $code = $res->getStatusCode();
        if ($code == 200) {
            return true;
        }
        return false;
    }


    /**
     * @param Order $order
     * @return bool|Exception|ClientExceptionInterface|DecodingExceptionInterface|RedirectionExceptionInterface|ServerExceptionInterface|TransportExceptionInterface
     * @throws OrderException
     * @throws PrestaShopException
     */
    public function registerOrder(Order $order)
    {
        $products = $order->getCartProducts();
        $delivery = new Address($order->id_address_delivery);
        $customer = $order->getCustomer();
        $isBranch = CorreoArgentinoRatesModel::isBranch($order->id_carrier);
        $state = new State($delivery->id_state);
        $cartBranch = CorreoArgentinoBranchOrderModel::findOrCreate($order->id_cart);
        $parcels = array_map(function ($value) {
            return [
                "declaredValue" => (float) $value['total_wt'],
                "dimensions" => [
                    "depth" => (int) $value['depth'],
                    "height" => (int) $value['height'],
                    "width" => (int) $value['width'],
                ],
                "productCategory" => filter_var($value['product_name'], FILTER_SANITIZE_STRING),
                "productWeight" => (int) ($value['product_weight'] * 1000)
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
            $branch = $this->getBranch($cartBranch);
            $location = $branch['location'];
            $shippingData = [
                "address" => [
                    "cityName" => $location['city_name'] ?? 'CABA',
                    "department" => "",
                    "floor" => "",
                    "state" => "B",
                    // @todo: check this hardcoded value
                    "streetName" => $location['street_name'] ?? 'Dolores',
                    "streetNumber" => $location['street_number'] ?? '27',
                    "zipCode" => CorreoArgentinoUtil::normalizeZipCode($location['zip_code'] ?? '1407')
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

        $cityName = Configuration::get('CORREOARGENTINO_CITYNAME') ?? 'Ciudad Autonoma de Buenos Aires';
        $department = Configuration::get('CORREOARGENTINO_DEPARTAMENT') ?? 's/d';
        $floor = Configuration::get('CORREOARGENTINO_FLOOR') ?? 's/d';
        $state = Configuration::get('CORREOARGENTINO_STATE') ?? 'B';
        $streetName = Configuration::get('CORREOARGENTINO_STREET_NAME') ?? 'Lavalle';
        $streetNumber = Configuration::get('CORREOARGENTINO_STREET_NUMBER') ?? '0';
        $zipCode = Configuration::get('CORREOARGENTINO_ZIP_CODE') ?? 'C1190AAN';
        $businessName = Configuration::get('CORREOARGENTINO_BUSINESS_NAME') ?? 'My company';
        $areaCodeCellphone = Configuration::get('CORREOARGENTINO_AREA_CODE_CELL_PHONE') ?? '11';
        $cellphoneNumber = Configuration::get('CORREOARGENTINO_CELL_PHONE_NUMBER') ?? '12345678';
        $areaCodePhone = Configuration::get('CORREOARGENTINO_AREA_CODE_PHONE') ?? '112345678';
        $phoneNumber = Configuration::get('CORREOARGENTINO_PHONE_NUMBER') ?? '12345678';
        $email = Configuration::get('CORREOARGENTINO_EMAIL') ?? 'default@domain.com';
        $observation = Configuration::get('CORREOARGENTINO_OBSERVATION') ?? 'No comments';

        $body = [
            "sellerId" => "",
            "trackingNumber" => "",
            "order" => [
                "agencyId" => $isBranch ? $cartBranch->branch_code : "",
                "deliveryType" => $isBranch ? "agency" : "homeDelivery",
                "parcels" => $parcels,
                "shipmentClientId" => $order->reference,
                "serviceType" => "CP",
                "saleDate" => date('Y-m-d\TH:i:sO'),
                "senderData" => [
                    "address" => [
                        "cityName" => filter_var($cityName, FILTER_SANITIZE_STRING),
                        "department" => $department,
                        "floor" => $floor,
                        "state" => $state,
                        "streetName" => filter_var($streetName, FILTER_SANITIZE_STRING),
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
            $response = $this->client->request('POST', $this->url . '/orders', ['body' => json_encode($body, 1)]);
            $statusCode = $response->getStatusCode();
            if ($statusCode == 200) {
                $content = $response->toArray();
                $order->setWsShippingNumber($content['trackingNumber']);
                $order->update();
                $cartBranch->shipping_type = $isBranch ? 'agency' : 'homeDelivery';
                $cartBranch->tracking = $content['trackingNumber'];
                $cartBranch->shipping_date = time();
                $cartBranch->update();
                return true;
            } else {
                $output = $response->getContent(false);
                $output = json_decode($output, true);
                throw new OrderException($output["message"]);
            }

        } catch (ClientExceptionInterface | DecodingExceptionInterface | RedirectionExceptionInterface | ServerExceptionInterface | TransportExceptionInterface $e) {

            return $e;
        }

    }
}