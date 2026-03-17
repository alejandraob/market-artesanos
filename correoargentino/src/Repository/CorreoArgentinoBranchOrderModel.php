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

class CorreoArgentinoBranchOrderModel extends ObjectModel
{

    public static $definition = [
        'table' => 'correoargentino_branch_order',
        'primary' => 'id_branch',
        'multilang' => false,
        'fields' => [
            'id_cart' => [
                'type' => self::TYPE_INT
            ],
            'branch_select' => [
                'type' => self::TYPE_STRING
            ],
            'branch_state' => [
                'type' => self::TYPE_STRING
            ],
            'branch_code' => [
                'type' => self::TYPE_STRING
            ],
            'branch_name' => [
                'type' => self::TYPE_STRING
            ],
            'id_order' => [
                'type' => self::TYPE_INT,
                'validate' => 'isNullOrUnsignedId',
                'allow_null' => true
            ],
            'tracking' => [
                'type' => self::TYPE_STRING
            ],
            'shipping_date' => [
                'type' => self::TYPE_DATE
            ],
            'total' => [
                'type' => self::TYPE_FLOAT
            ],
            'status' => [
                'type' => self::TYPE_STRING
            ],
            'shipping_type' => [
                'type' => self::TYPE_STRING
            ],
        ]
    ];
    public $id;
    public $id_cart;
    public $branch_select;
    public $branch_state;
    public $branch_code;
    public $branch_name;
    public $shipping_date;
    public $shipping_type;
    public $id_order;
    public $tracking;
    public $total;
    public $status;

    const STATUS_IMPORTED = 'imported';
    const STATUS_CANCELLED = 'cancelled';


    /**
     *
     */
    public function __construct($id = null, $lang = null)
    {
        parent::__construct($id, $lang);
    }


    /**
     *
     * @throws PrestaShopException
     * @throws \PrestaShopException
     */
    public static function findOrCreate($cart_id): CorreoArgentinoBranchOrderModel
    {
        $sql = "SELECT `id_branch` FROM `" . _DB_PREFIX_ . "correoargentino_branch_order` WHERE `id_cart` = '" . pSql($cart_id) . "'";
        $id_branch = Db::getInstance()->getValue($sql);

        if (!empty($id_branch)) {
            $item = new CorreoArgentinoBranchOrderModel($id_branch);
        } else {
            $item = new CorreoArgentinoBranchOrderModel();
            $item->id_cart = $cart_id;
            // set to null to avoid error
            $item->id_order = null;
        }
        $item->save();
        return $item;
    }

    /**
     *
     * @throws PrestaShopException
     * @throws \PrestaShopException
     */
    public static function findByOrder($order_id): CorreoArgentinoBranchOrderModel
    {
        $sql = "SELECT `id_branch` FROM `" . _DB_PREFIX_ . "correoargentino_branch_order` WHERE `id_order` = '" . pSql($order_id) . "'";
        $id_branch = Db::getInstance()->getValue($sql);

        return new CorreoArgentinoBranchOrderModel($id_branch);
    }
}
