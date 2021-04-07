{*
* 2007-2019 PrestaShop
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
*  @copyright 2007-2019 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}
<style>
    .logocarousel h3.text-center {
        color: {$color};
    }
</style>

{if isset($items)}
    <div class="container-fluid logocarousel">
        <h3 class='text-center'>{$header[0].logo_header}</h3>
        <div id="logoCarousel" class='owl-carousel owl-theme cstCarousel'>
            {foreach $items key=i  item=$item}
                    <div class='item'>
                        <a href="{$item.logo_link}" title="{$item.logo_title}" rel='nofollow'>
                            <img src="{$path}views/img/carousel_items/{$item.file_name}" alt="{$item.logo_alt}">
                        </a>                            
                    </div>
            {/foreach}
        </div>
    </div>
{/if}

<script>
    var mobileItems = {$mobileItems};
    var tabletItems = {$tabletItems};
    var desktopItems = {$desktopItems};
</script>