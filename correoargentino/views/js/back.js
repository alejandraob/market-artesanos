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
 *
 * Don't forget to prefix your containers with your own identifier
 * to avoid any conflicts with others containers.
 */

document.addEventListener("DOMContentLoaded", function () {
  const selectInputList = document.getElementsByName("CORREOARGENTINO_SERVICE_TYPE")
  if (selectInputList.length > 0) {
    const selectInput = selectInputList[0]
    if (selectInput) {
      selectInput.addEventListener("change", function (event) {
        const switchInputList = document.getElementsByName("CORREOARGENTINO_USE_RATES")
        if (switchInputList.length > 0) {
          const switchInput = switchInputList[0]
          const group = switchInput.closest("div .form-group")
          if (this.value === 'MI_CORREO') {
            group.style.display = 'block'
          } else {
            group.style.display = 'none'
          }
        }
      })
    }
  }
})
