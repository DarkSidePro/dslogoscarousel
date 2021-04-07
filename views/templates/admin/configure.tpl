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

<div id='mainCst' class="panel" data-token='{$token}'>
	<h3><i class="icon-list-ul"></i>{l s='Carousel items' mod='dslogoscarousel'}
        <span class="panel-heading-action">
            <a id="desc-product-new" class="list-toolbar-btn" href="{$link->getAdminLink('AdminModules')|escape:'htmlall':'UTF-8'}&configure={$namemodules}&addNew=1">
                <span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="Add new" data-html="true">
                    <i class="process-icon-new "></i>
                </span>
            </a>
        </span>
    </h3>
	<form method='POST'>
		<!--<input type='hidden' name='deleteItem'>-->
		<table class='table'>
			<thead class='thead-default'>
				<tr class='column-headers'>
					<th>#</th>
					<th>{l s='Logo' mod='dslogoscarousel'}</th>
					<th>{l s='Title' mod='dslogoscarousel'}</th>
					<th>{l s='Link' mod='dslogoscarousel'}</th>
					<th>{l s='Edit' mod='dslogoscarousel'}
					<th>{l s='Delete' mod='dslogoscarousel'}</th>
				</tr>
			</thead>
			<tbody id="slides">
					{foreach $items key=i item=$item}
						<tr>
							<th class='renumerated' data-order='{$item.id_logoCarousel}'>{$i+1}</th>
							<td style='width: 250px'><img class='img-responsive' src='/modules/dslogoscarousel/views/img/carousel_items/{$item.file_name}' style='width: 250px;'></td>
							<td>{$item.logo_title}</td>
							<td>{$item.logo_link}</td>
							<td><a href='{$link->getAdminLink('AdminModules')|escape:'htmlall':'UTF-8'}&configure={$namemodules}&editItem={$item.id_logoCarousel}'>{l s='Edit' mod='dslogoscarousel'}</a></td>
							<td>																	
								<input type='checkbox' class='form-check-input' name='deleteItems[]' value='{$item.id_logoCarousel}'>
							</td>
						</tr>
					{/foreach}
			</tbody>
		</table>
		<div class='panel-footer'>
			<button name='deleteButton' class='btn btn-default pull-right'><i class="process-icon-save"></i>{l s='Delete' mod='dslogoscarousel'}</button>
		</div>
	</form>
</div>
{literal}
<script type="text/javascript">
    $(function() {
        var $mySlides = $("#slides");
            $mySlides.sortable({
				opacity: 0.6,
                    cursor: "move",
                    update: function() {
						var myArray = new Array();
						$(".renumerated").each(function (i) {
							var humanNum = i + 1;
							$(this).html(humanNum + '');
						});
						$(".renumerated").each(function (i) {
							myArray.push($(this).data('order'));
						});
						var myToken = $('#mainCst').data('token');
						  $.ajax({
							type: "POST",
							url: baseAdminDir+'index.php',
							data: {
								ajax: true,
								controller: 'AdministratorDsLogosCarousel',
								action: 'call',
								array: myArray,
								token: myToken
							},
							success: function(data)
							{
								html = '<div class="bootstrap"><div class="module_confirmation conf confirm alert alert-success">';
								html += '<button type="button" class="close" data-dismiss="alert">×</button>Settings updated.';
								html += '</div></div>';
								$('#mainCst').before(html);
								
							},
							error: function (xhr, ajaxOptions, thrownError) {
								alert(xhr.status);
								alert(xhr.responseText);
								alert(thrownError);
							}
						});
					}
				});
                $mySlides.hover(function() {
                    $(this).css("cursor","move");
                    },
                    function() {
                    $(this).css("cursor","auto");
                });
            });					
</script>
{/literal}