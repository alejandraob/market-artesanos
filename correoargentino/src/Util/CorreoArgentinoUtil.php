<?php
namespace CorreoArgentino\Utils;

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

class CorreoArgentinoUtil
{

    /**
     * @param $code
     * @return array|string|string[]|null
     */
    public static function normalizeZipCode($code)
    {
        return preg_replace("/[^0-9]/", "", $code);
    }

    /**
     * @param $phone
     * @return string|string[]|null
     */
    public static function cleanPhone($phone)
    {
        return preg_replace("/\-/", "", $phone);
    }

    /**
     * @param $carrier_id
     * @param $lang_id
     * @return bool
     */
    public static function isBranch($carrier_id, $lang_id): bool
    {
        if (!$carrier_id || !$lang_id) {
            return false;
        }
        $carrier = new Carrier($carrier_id);
        return $carrier->delay[$lang_id] == 'Agency';
    }

    public static function renderPDF($label)
    {
        if (!strlen($label["fileBase64"]) > 0) {
            return null;
        }
        $filename = $label['filename'];
        header('Content-type: application/octet-stream');
        header("Content-Type: application/pdf");
        header("Content-Disposition: attachment; filename=$filename");
        echo base64_decode($label['fileBase64']);
    }

    /**
     * Return the service type stored in the Prestashop configuration table
     * 
     * @return string 
     * @throws Exception If no value has been set
     */
    public static function getCurrentServiceType(): string
    {
        if (!Configuration::hasKey("CORREOARGENTINO_SERVICE_TYPE")) {
            throw new \Exception("Correo Argentino service type is not present");
        }
        return Configuration::get("CORREOARGENTINO_SERVICE_TYPE");
    }

    /**
     * Get box size from cart
     * 
     * @param $cart
     * @return array
     */
    public static function getPackageSize($cart)
    {
        $packages = [];
    
        if (count($cart->getProducts()) == 0) {
            return [
                'height' => 0,
                'width' => 0,
                'length' => 0,
                'weight' => 0,
            ];
        }
    
        foreach ($cart->getProducts() as $product) {
            $product_dimensions = [
                (float) $product['width'],
                (float) $product['height'],
                (float) $product['depth'],
            ];
    
            sort($product_dimensions);
    
            for ($i = 0; $i < $product['quantity']; $i++) {
                $packages[] = $product_dimensions;
            }
        }
    
        $height = array_sum(array_column($packages, 0));
    
        $width = max(array_column($packages, 1));
    
        $length = max(array_column($packages, 2));
    
        return [
            'height' => $height,
            'width' => $width,
            'length' => $length,
            'weight' => (int) ($cart->getTotalWeight() * 1000), 
        ];
    }
    
}
