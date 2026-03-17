<?php

namespace CorreoArgentino\Services;

use CorreoArgentino\Utils\CorreoArgentinoUtil;
use CorreoArgentino\Interfaces\CorreoArgentinoServiceMiCorreoInterface;
use CorreoArgentino\Exceptions\CorreoArgentinoException;
use DateTime;
use \Configuration;
use Symfony\Component\HttpClient\HttpClient;
use CorreoArgentino\Repository\CorreoArgentinoBranchOrderModel;
use CorreoArgentino\Repository\CorreoArgentinoRatesModel;
use Tools;
use PrestaShop\PrestaShop\Core\Domain\Order\Exception\OrderException;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use PrestaShopLogger;



class CorreoArgentinoMiCorreoService implements CorreoArgentinoServiceMiCorreoInterface
{
    const CA_API_SANDBOX_URL_MI_CORREO = 'https://apitest.correoargentino.com.ar/micorreo/v1/';
    const CA_API_URL_MI_CORREO = 'https://api.correoargentino.com.ar/micorreo/v1/';
    const CA_USERNAME_MI_CORREO = 'PRESTASHOP';
    const CA_PASSWORD_MI_CORREO = 'Espada-38';

    /**
     * @var Client
     */
    public $client;

    /**
     * @var string
     */
    public $url;

    /**
     * @var string
     */
    private $authHash;

    /**
     * @var string
     */
    private $accessToken;

    public function __construct()
    {
        $this->url = (int)Configuration::get('CORREOARGENTINO_SANDBOX_MODE') === 1 ? self::CA_API_SANDBOX_URL_MI_CORREO : self::CA_API_URL_MI_CORREO;
        $this->authHash = Configuration::get('CORREOARGENTINO_AUTH_HASH');

        $this->client = HttpClient::create([
            'base_uri' => $this->url,
            'timeout' => 30
        ]);

        if (Configuration::get('CORREOARGENTINO_ACCESS_TOKEN') !== null && !$this->isValidToken()) {
            $this->renewToken();
        }
        $this->accessToken = Configuration::get('CORREOARGENTINO_ACCESS_TOKEN');
    }

    /**
     * Validates expires date token
     * @return bool
     * @throws CorreoArgentinoException
     */
    public static function isValidToken(): bool
    {
        if (!Configuration::hasKey('CORREOARGENTINO_ACCESS_TOKEN') || !Configuration::hasKey('CORREOARGENTINO_ACCESS_TOKEN_EXPIRE')) {
            return false;
        }
        $expire = Configuration::get('CORREOARGENTINO_ACCESS_TOKEN_EXPIRE');
        $now = new DateTime('now');
        $expireDate = new DateTime($expire);
        return $expireDate > $now;
    }

    /**
     * Renew token access from Correo Argentino API
     * @return bool
     */
    public function renewToken(): bool
    {
        Configuration::deleteByName('CORREOARGENTINO_ACCESS_TOKEN');
        Configuration::deleteByName('CORREOARGENTINO_ACCESS_TOKEN_EXPIRE');
        return $this->login();
    }
    /**
     * Login to the Correo Argentino API and get an access token.
     * The generic user and password are used
     * 
     * @throws CorreoArgentinoException For invalid service response or response format
     */
    public function login(): bool
    {
        try {
            $response = $this->client->request(
                'POST',
                'token',
                [
                    'headers' => $this->setHeaders([]),
                    'auth_basic' => [self::CA_USERNAME_MI_CORREO, self::CA_PASSWORD_MI_CORREO],
                ]
            );
    
            if ($response->getStatusCode() !== 200) {
                throw new CorreoArgentinoException("Correo argentino API login error", 1);
            }
            $responseObject = $response->toArray();
            if (!isset($responseObject['token']) || !isset($responseObject['expire'])) {
                throw new CorreoArgentinoException("Invalid Correo Argentino response");
            }
            Configuration::set('CORREOARGENTINO_ACCESS_TOKEN', $responseObject['token']);
            Configuration::set('CORREOARGENTINO_ACCESS_TOKEN_EXPIRE', $responseObject['expire']);
            return true;
        }catch (
            \Symfony\Component\HttpClient\Exception\TransportException |
            \Symfony\Component\HttpClient\Exception\ClientException $e
        ) {
            $content = json_decode($e->getResponse()->getContent(false));
            PrestaShopLogger::addLog($content);
        }
        return false;
    }

    /**
     * Facade method for getting a valid JWT token and store it in Prestashop configuration table
     */
    public function getToken()
    {
        if (!$this->isValidToken()) {
            $this->renewToken();
        }
        return Configuration::get('CORREOARGENTINO_ACCESS_TOKEN');
    }

    /**
     * Add general header parameters
     */
    public function setHeaders($headers = []): array
    {
        $defaultHeaders = [
            "Content-Type" => "application/json",
            "Accept" => "application/json",
            "Connection" => "keep-alive",
        ];
        return array_merge($defaultHeaders, $headers);
    }

    public function registerOrder($id_order)
    {
        $cartBranch = CorreoArgentinoBranchOrderModel::findByOrder($id_order);

        if ($cartBranch->status === CorreoArgentinoBranchOrderModel::STATUS_IMPORTED) {
            throw new CorreoArgentinoException("La orden fue importada con anterioridad.", 1);
        }

        try {
            $order = new \Order($id_order);
            $shipping_address = new \Address($order->id_address_delivery);
            $customer = new \Customer($order->id_customer);
            $cart = new \Cart($order->id_cart);
            $state = new \State($shipping_address->id_state);
            $isBranch = CorreoArgentinoRatesModel::isBranch($order->id_carrier);

            $carrierSettings = CorreoArgentinoRatesModel::getSettingsByCarrierId($order->id_carrier);
            $recipient = [
                "name" => $shipping_address->firstname . ' ' . $shipping_address->lastname,
                "email" => $customer->email,
                "phone" => $shipping_address->phone,
                "cellPhone" => $shipping_address->phone,
            ];

            $dimensions = CorreoArgentinoUtil::getPackageSize($cart);

            $shipping = [
                "deliveryType" => $carrierSettings['delivered_type'],
                "productType" => $carrierSettings['service_type'],
                "declaredValue" => $order->total_paid,
                "weight" => $dimensions['weight'],
                "height" => $dimensions['height'],
                "length" => $dimensions['length'],
                "width" => $dimensions['width'],
                "agency" => isset($cartBranch->branch_code) ? $cartBranch->branch_code : null,
                "address" => [
                    "streetName" => $shipping_address->address1 . ' ' . $shipping_address->address2,
                    "streetNumber" => 0,
                    "floor" => null,
                    "apartment" => null,
                    "city" => $shipping_address->city,
                    "provinceCode" => $state->iso_code,
                    "postalCode" => CorreoArgentinoUtil::normalizeZipCode($shipping_address->postcode)
                ],
            ];

            $sender = [
                "name" => Configuration::get('CORREOARGENTINO_BUSINESS_NAME'),
                "phone" => Configuration::get('CORREOARGENTINO_AREA_CODE_PHONE') . Configuration::get('CORREOARGENTINO_PHONE_NUMBER'),
                "cellPhone" => Configuration::get('CORREOARGENTINO_AREA_CODE_CELL_PHONE') . Configuration::get('CORREOARGENTINO_CELL_PHONE_NUMBER'),
                "email" => Configuration::get('CORREOARGENTINO_EMAIL'),
                "originAddress" => [
                    "streetName" => Configuration::get('CORREOARGENTINO_STREET_NAME'),
                    "streetNumber" => Configuration::get('CORREOARGENTINO_STREET_NUMBER'),
                    "floor" => Configuration::get('CORREOARGENTINO_FLOOR'),
                    "apartment" => Configuration::get('CORREOARGENTINO_DEPARTMENT'),
                    "city" => Configuration::get('CORREOARGENTINO_CITY_NAME'),
                    "provinceCode" => Configuration::get('CORREOARGENTINO_STATE'),
                    "postalCode" => CorreoArgentinoUtil::normalizeZipCode(Configuration::get('CORREOARGENTINO_ZIP_CODE'))
                ]
            ];

            $body = [
                "customerId" => Configuration::get('CORREOARGENTINO_CUSTOMER_ID'),
                "extOrderId" => $id_order,
                "orderNumber" => $id_order,
                "sender" => $sender,
                "recipient" => $recipient,
                "shipping" => $shipping
            ];

            try {
                $jsonBody = json_encode($body);
                PrestaShopLogger::addLog("MiCorreo Shipping/Import: ({$jsonBody})", PrestaShopLogger::LOG_SEVERITY_LEVEL_INFORMATIVE);
                $response = $this->client->request(
                    'POST',
                    'shipping/import',
                    [
                        'headers' => $this->setHeaders([
                            "Authorization" => "Bearer " . $this->getToken()
                        ]),
                        'body' => json_encode($body),
                    ]
                );
                $statusCode = $response->getStatusCode();
                if ($statusCode == 200) {
                    $content = $response->toArray();
                    $order->setWsShippingNumber($id_order);
                    $order->update();
                    $cartBranch->shipping_type = $isBranch ? 'agency' : 'homeDelivery';
                    $cartBranch->tracking = $id_order;
                    $cartBranch->shipping_date = time();
                    $cartBranch->status = CorreoArgentinoBranchOrderModel::STATUS_IMPORTED;
                    $cartBranch->update();
                    return $content;
                } else {
                    $output = $response->getContent(false);
                    PrestaShopLogger::addLog("MiCorreo Shipping/Import: CA API Response ({$output})", PrestaShopLogger::LOG_SEVERITY_LEVEL_ERROR);
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
                PrestaShopLogger::addLog("MiCorreo Shipping/Import: {$e->getMessage()}", PrestaShopLogger::LOG_SEVERITY_LEVEL_ERROR);
                return $e;
            }
        } catch (
            \Symfony\Component\HttpClient\Exception\TransportException |
            \Symfony\Component\HttpClient\Exception\ClientException $e
        ) {
            PrestaShopLogger::addLog("MiCorreo Shipping/Import: {$e->getResponse()->getContent(false)}", PrestaShopLogger::LOG_SEVERITY_LEVEL_ERROR);
            $content = json_decode($e->getResponse()->getContent(false));
            throw new CorreoArgentinoException($content->message, 1);
        }
    }

    /**
     * @throws CorreoArgentinoException
     */
    public function getRates($postalCode, $dimensions)
    {
        try {

            $cart = new \Cart(\Context::getContext()->cart->id);
            $carrier_id = $cart->id_carrier;

            $body = [
                "customerId" => Configuration::get('CORREOARGENTINO_CUSTOMER_ID'),
                "postalCodeOrigin" => CorreoArgentinoUtil::normalizeZipCode(Configuration::get('CORREOARGENTINO_ZIP_CODE')),
                "postalCodeDestination" => CorreoArgentinoUtil::normalizeZipCode($postalCode),
                "deliveredType" => "D",
                "dimensions" => $dimensions
            ];

            $response = $this->client->request(
                'POST',
                'rates',
                [
                    'headers' => $this->setHeaders([
                        "Authorization" => "Bearer " . $this->getToken()
                    ]),
                    'body' => json_encode($body),
                ]
            );

            $responseObjectD = $response->toArray();

            $cookie = \Context::getContext()->cookie;

            $branchPostCode = $cookie->branch_postcode;

            $postalCode = $branchPostCode ? $branchPostCode : $postalCode;

            $body = [
                "customerId" => Configuration::get('CORREOARGENTINO_CUSTOMER_ID'),
                "postalCodeOrigin" => CorreoArgentinoUtil::normalizeZipCode(Configuration::get('CORREOARGENTINO_ZIP_CODE')),
                "postalCodeDestination" => CorreoArgentinoUtil::normalizeZipCode($postalCode),
                "deliveredType" => "S",
                "dimensions" => $dimensions
            ];

            $response = $this->client->request(
                'POST',
                'rates',
                [
                    'headers' => $this->setHeaders([
                        "Authorization" => "Bearer " . $this->getToken()
                    ]),
                    'body' => json_encode($body),
                ]
            );

            $responseObjectS = $response->toArray();

            $rates = array_merge($responseObjectD['rates'], $responseObjectS['rates']);

            $responseObject = [];

            $responseObject['rates'] = $rates;

            return $responseObject;
        } catch (
            \Symfony\Component\HttpClient\Exception\TransportException |
            \Symfony\Component\HttpClient\Exception\ClientException $e
        ) {
            $content = json_decode($e->getResponse()->getContent(false));
            throw new CorreoArgentinoException($content->message, 1);
        }
    }

    /**
     * @deprecated
     */
    public static function getBranchById($branch_id)
    {
        $agencies = json_decode(Tools::file_get_contents(_PS_MODULE_DIR_ . 'correoargentino/mock/agencies.json'));

        return array_values(array_filter($agencies, function ($value) use ($branch_id) {
            return $value->agency_id === $branch_id;
        }, ARRAY_FILTER_USE_BOTH))[0] ?? null;
    }

    public function getBranches(string $iso_state): array
    {
        try {
            $response = $this->client->request(
                'GET',
                'agencies',
                [
                    'headers' => $this->setHeaders([
                        "Authorization" => "Bearer " . $this->getToken()
                    ]),
                    'query' => [
                        'services' => 'pickup_availability',
                        'customerId' =>  Configuration::get('CORREOARGENTINO_CUSTOMER_ID'),
                        'provinceCode' => $iso_state
                    ],
                ]
            );
            $responseObject = $response->toArray();
            return $responseObject;
        } catch (
            \Symfony\Component\HttpClient\Exception\TransportException |
            \Symfony\Component\HttpClient\Exception\ClientException $e
        ) {
            $content = json_decode($e->getResponse()->getContent(false));
            throw new CorreoArgentinoException($content->message, 1);
        }
    }

    public function getBranchData(?string $branch_id, string $iso_state): array
    {
        if ($branch_id) {
            $branches = $this->getBranches($iso_state);
            if ($branches) {
                $branch = array_filter(
                    $branches,
                    function ($item) use ($branch_id) {
                        return $item['code'] === $branch_id;
                    }
                );
                return $branch && !empty($branch) ? reset($branch) : [];
            }
        }
        return [];
    }

    public function getBranchName(array $branch): string
    {
        return isset($branch['name']) ? $branch['name'] : '';
    }

    public function branchesAdapter(array $agencies): array
    {
        $results = [];
        foreach ($agencies as $value) {
            $text = join(
                ", ",
                [
                    trim($value['location']['address']['province']),
                    trim($value['location']['address']['city']),
                    trim($value['name']),
                    trim($value['location']['address']['streetName']),
                    trim($value['location']['address']['streetNumber']),
                    "(" . trim($value['location']['address']['postalCode']) . ")"
                ]
            );

            $results[] = [
                "id" => $value['code'],
                "text" => $text,
                "postcode" => $value['location']['address']['postalCode'],
            ];
        }
        return $results;
    }

    /**
     * @throws CorreoArgentinoException
     */
    public function createAccount($body)
    {
        try {
            $response = $this->client->request(
                'POST',
                'register?t=' . time(),
                [
                    'headers' => $this->setHeaders([
                        "Authorization" => "Bearer " . $this->getToken()
                    ]),
                    'body' => json_encode($body),
                ]
            );

            $responseObject = $response->toArray();
            Configuration::updateValue("CORREOARGENTINO_USERNAME_MICORREO", $body['email']);
            Configuration::updateValue("CORREOARGENTINO_PASSWORD_MICORREO", $body['password']);
            Configuration::updateValue("CORREOARGENTINO_CUSTOMER_ID", $responseObject["customerId"]);
            return $responseObject;
        } catch (
            \Symfony\Component\HttpClient\Exception\TransportException |
            \Symfony\Component\HttpClient\Exception\ClientException $e
        ) {
            $content = json_decode($e->getResponse()->getContent(false));
            throw new CorreoArgentinoException($content->message, 1);
        }
    }

    /**
     * Validates MiCorreo accounts with Correo Argentino service, it's returns a customer id if valid user
     * Store the customerId and CreateAt fields in the Prestashop configurations table
     * 
     * @param string $email MiCorreo user email
     * @param string $password MiCorreo user password
     * @return bool User validate true
     * @throws CorreoArgentinoException For invalid service response or response format
     */
    public function userValidate(string $email, string $password): bool
    {
        try {
            $response = $this->client->request(
                'POST',
                'users/validate',
                [
                    'headers' => $this->setHeaders([
                        "Authorization" => "Bearer " . $this->getToken()
                    ]),
                    'body' => json_encode([
                        "email" => $email,
                        "password" => $password
                    ])
                ]
            );
            $responseObject = $response->toArray();
            if (!isset($responseObject['customerId']) || !isset($responseObject['createdAt'])) {
                throw new CorreoArgentinoException("Invalid Correo Argentino response in user validate service");
            }
            Configuration::updateValue('CORREOARGENTINO_CUSTOMER_ID', $responseObject['customerId']);
            Configuration::updateValue('CORREOARGENTINO_CUSTOMER_CREATE_AT', $responseObject['createdAt']);
            return true;
        } catch (
            \Symfony\Component\HttpClient\Exception\TransportException |
            \Symfony\Component\HttpClient\Exception\ClientException $e
        ) {
            if (406 === $e->getResponse()->getStatusCode()) {
                throw new CorreoArgentinoException("Usuario no válido o inexistente", 1);
            }
            throw new CorreoArgentinoException("Correo argentino API user validate error", 1);
        }
    }
}
