<?php

namespace CorreoArgentino\Repository;

use Db;
use ObjectModel;

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

class CorreoArgentinoRatesModel extends ObjectModel
{

    /**
     * @var
     */
    public $id;
    public $id_rate;
    public $id_carrier;
    public $is_branch;
    public $service_type;
    public $delivered_type;

    public static $definition = [
        'table' => 'correoargentino_rates',
        'primary' => 'id_rate',
        'multilang' => false,
        'fields' => [
            'id_rate' => [
                'type' => self::TYPE_INT
            ],
            'id_carrier' => [
                'type' => self::TYPE_INT
            ],
            'is_branch' => [
                'type' => self::TYPE_INT
            ],
            'service_type' => [
                'type' => self::TYPE_STRING
            ],
            'delivered_type' => [
                'type' => self::TYPE_STRING
            ],
        ]
    ];

    /**
     *
     */
    public function __construct($id = null, $lang = null)
    {
        parent::__construct($id, $lang);
    }


    /**
     * @param $carrier
     * @param $isBranch
     * @param $serviceType
     * @param $deliveredType
     * @throws PrestaShopException
     */
    public static function create($carrier, $isBranch, $serviceType, $deliveredType)
    {

        $rate = new CorreoArgentinoRatesModel();
        $rate->id_carrier = $carrier->id;
        $rate->is_branch = $isBranch;
        $rate->service_type = $serviceType;
        $rate->delivered_type = $deliveredType;
        $rate->save();
    }

    /**
     * @param $carrier_id
     * @return CorreoArgentinoRatesModel|string
     */
    public static function getSettingsByCarrierId($carrier_id)
    {
        $sql = "SELECT * FROM `" . _DB_PREFIX_ . "correoargentino_rates` WHERE `id_carrier` = '" . pSql($carrier_id) . "'";
        $result = Db::getInstance()->getRow($sql);

        if (!$result) {
            return false;
        }
        return $result;
    }

    /**
     * @param $carrier_id
     * @return bool
     */
    public static function isBranch($carrier_id): bool
    {
        $sql = "SELECT 1 FROM `" . _DB_PREFIX_ . "correoargentino_rates` WHERE `delivered_type`='S' AND `id_carrier` = '" . pSql($carrier_id) . "'";
        $result = Db::getInstance()->getValue($sql);

        if (!$result) {
            return false;
        }
        return (bool)$result;
    }

    public static function isCaOrder($carrier_id)
    {
        $sql = "SELECT * FROM `" . _DB_PREFIX_ . "correoargentino_rates` WHERE `id_carrier` = '" . pSql($carrier_id) . "'";
        $result = Db::getInstance()->getValue($sql);

        if (!$result) {
            return false;
        }
        return (bool)$result;;
    }
}
