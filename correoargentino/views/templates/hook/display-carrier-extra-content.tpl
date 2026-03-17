{**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/OSL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://devdocs.prestashop.com/ for more information.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 *}
{if $carrier_id}
    {if $is_enabled}
        <div class="correoargentino_container delivery-option">
            <div class="col-sm-12">{l s='Please select the delivery branch' mod='correoargentino'}</div>
            <div class="col-sm-12">
                <input type="hidden" name="correoargentino_branch_postcode_{$carrier_id|intval}" class="correoargentino_branch_postcode">
                <select id="correoargentino_state_select_{$carrier_id|intval}" class="correoargentino_state_select"
                        name="correoargentino_state_id_{$carrier_id|intval}" style="width:100%">
                        {foreach from=$states item=state}
                            <option value="{$state.iso_code}"{if $state.iso_code == $iso_code} selected="selected" {/if}>{$state.name}</option>
                        {/foreach}
                </select>
                <select id="correoargentino_branch_select_{$carrier_id|intval}" class="correoargentino_branch_select"
                        name="correoargentino_branch_id_{$carrier_id|intval}" style="width:100%" data-carrierid="{$carrier_id|intval}">
                        {foreach from=$results item=result}
                            <option data-postcode="{$result.postcode}" value="{$result.id}"{if $result.id == $branchSelected} selected="selected" {/if}>{$result.text}</option>
                        {/foreach}
                </select>
            </div>
        </div>
        {block name='javascript_bottom'}
            <script type="text/javascript">
                window.onload = function () {
                    $.fn.correoArgentino();
                };
            </script>
        {/block}
    {/if}
{/if}