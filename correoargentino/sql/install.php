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
 *  @author    PrestaShop SA <contact@prestashop.com>
 *  @copyright 2007-2022 PrestaShop SA
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

$sql = array();

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'correoargentino_branch_order` (
    `id_branch` int(11) NOT NULL AUTO_INCREMENT,
    `id_cart` int(10) UNSIGNED NULL,
    `branch_select` varchar(50) NULL,
    `branch_state` varchar(1) NULL,
    `branch_code` varchar(6) NULL,
    `branch_name` varchar(1024) NULL,
    `id_order` int(10) UNSIGNED NULL,
    `tracking` varchar(35) NULL,
    `shipping_date` datetime NULL, 
    `total` decimal(20,6) NULL,
    `status` varchar(35) NULL,
    `shipping_type`  varchar(35) NULL,
    PRIMARY KEY  (`id_branch`),
    FOREIGN KEY (`id_cart`) REFERENCES ' . _DB_PREFIX_ . 'cart(`id_cart`),
    FOREIGN KEY (`id_order`) REFERENCES ' . _DB_PREFIX_ . 'orders(`id_order`)
)';

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'correoargentino_rates` (
    `id_rate` int(11) NOT NULL AUTO_INCREMENT,
    `id_carrier` int(10) UNSIGNED NULL,
    `is_branch` BOOLEAN NULL,
    `service_type` varchar(2) NULL,
    `delivered_type` varchar(1) NULL,
    PRIMARY KEY  (`id_rate`),
    FOREIGN KEY (`id_carrier`) REFERENCES ' . _DB_PREFIX_ . 'carrier(`id_carrier`)
)';

foreach ($sql as $query) {
    Db::getInstance()->execute($query);
}
