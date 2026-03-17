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

(function ($) {
    const elm = '.correoargentino_branch_select'
    const stateElm = '.correoargentino_state_select'
    const branchPostCode = '.correoargentino_branch_postcode'
    $.fn.getAgencies = function ( state = null) {
        var reload = false
        if (state) {
            reload = true
        }
        const basePath = prestashop.urls.base_url
        const targetUrl = basePath + 'index.php?fc=module&module=correoargentino&controller=branch&id_lang=1' + (state ? '&state=' + state : '')
        $.ajax({
            url: targetUrl,
            dataType: 'json',
            type: 'GET',
            success: function (response) {
                if (response) {
                    let postcode = ''
                    for (const element of response) {
                        const entry = new Option(element.text, element.id, false, false)
                        entry.setAttribute('data-postcode', element.postcode)
                        $(elm).append(entry).trigger('change.select2');
                        postcode = element.postcode
                    }
                    $(branchPostCode).val(postcode)
                    if (reload) {
                        $(elm).first().trigger('change')
                    }
                }
            },
            error: function (xhr, ajaxOptions, thrownError) {
                alert("Ha ocurrido un error al cargar las sucursales")
            }
        })
    }
    $.fn.updateBranch = function (postcode, branch_selected, state_selected) {
        // disable the button
        $('button[name="confirmDeliveryOption"]').prop('disabled', true)
        const basePath = prestashop.urls.base_url
        const targetUrl = basePath + 'index.php?fc=module&module=correoargentino&controller=branch&id_lang=1&action=selectBranch' + (postcode ? '&postcode=' + postcode : '') + (branch_selected ? '&branch=' + branch_selected : '') + (state_selected ? '&state=' + state_selected : '')
        $.ajax({
            url: targetUrl,
            dataType: 'json',
            type: 'GET',
            success: function (response) {
                if (response) {
                    // reload the page
                    location.reload()
                }
            }
        })
        
    }
    $.fn.correoArgentino = function () {
        //$.fn.getAgencies()
        $(elm).select2({
            placeholder: "Cargando..."
        })
        $(elm).on('change', function () {
            $(branchPostCode).val($(this).find(':selected').data('postcode'))
            $.fn.updateBranch($(this).find(':selected').data('postcode'), $(this).val(), $(stateElm).val())
        })
        $(stateElm).select2()
        $(stateElm).on('change', function () {
            $(elm).empty()
            $(stateElm).val($(this).val()).trigger('change.select2');
            //$.fn.getAgencies($(this).val())
            $.fn.updateBranch($(this).find(':selected').data('postcode'), $(this).val(), $(stateElm).val())
        })
    }
}(jQuery))