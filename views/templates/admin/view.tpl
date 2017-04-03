{*
* 2007-2015 PrestaShop
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
*  @copyright 2007-2015 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

<div class="panel">
	<div class="row">
		<div class="col-lg-2">
			<p>{l s='Select a customer:'}</p>
			<div class="input-group">
				<input type="text" id="customer" value="">
				<span class="input-group-addon">
					<i class="icon-search"></i>
				</span>
			</div>
		</div>

		<div class="col-lg-6">
			<p>{l s='Select the cart products:'}</p>
			<div id="productsInputSelect" class="input-group" style="float: left;">
				<select id="productsSelect" class="" name="productsSelect">
					<option>You have to select a customer first!</option>
				</select>
			</div>
			<button id="addToCart" type="button" data-customer="'+this.id_customer+'" class="setup-customer btn btn-default pull-right">
				Add to cart
			</button>

			<div class="row">
				<div class="col-lg-12">
					<div class="actualCart">
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="row">
		<div id="customers" class="col-lg-12">
			
		</div>
	</div>
</div>

<script type="text/javascript">

	// TODO vigilar els tokens i generar-los automàticament

	function searchCustomers()
	{
		$.ajax({
			type:"POST",
			url : "index.php?controller=AdminCustomers&token=59e49e9169f55327c937d0be78cd318b",
			async: true,
			dataType: "json",
			data : {
				ajax: "1",
				tab: "AdminCustomers",
				action: "searchCustomers",
				customer_search: $('#customer').val()
			},
			success : function(res)
			{
				if(res.found)
				{
					var html = '';
					$.each(res.customers, function() {
						html += '<div class="customerCard col-lg-4">';
						html += '<div class="panel">';
						html += '<div class="panel-heading">'+this.firstname+' '+this.lastname;
						html += '<span class="pull-right">#'+this.id_customer+'</span></div>';
						html += '<span>'+this.email+'</span><br/>';
						html += '<span class="text-muted">'+((this.birthday != '0000-00-00') ? this.birthday : '')+'</span><br/>';
						html += '<div class="panel-footer">';
						html += '<a href="index.php?controller=AdminCustomers&token=59e49e9169f55327c937d0be78cd318b&id_customer='+this.id_customer+'&viewcustomer&liteDisplaying=1" class="btn btn-default fancybox"><i class="icon-search"></i> Detalles</a>';
						html += '<button type="button" data-customer="'+this.id_customer+'" class="setup-customer btn btn-default pull-right"><i class="icon-arrow-right"></i> Elegir</button>';
						html += '</div>';
						html += '</div>';
						html += '</div>';
					});
				}
				else
					html = '<div class="alert alert-warning"><i class="icon-warning-sign"></i>&nbsp;No se encontraron clientes</div>';
				$('#customers').html(html);
				$('#customer').val('');

				$(".setup-customer").click(function(){
					console.log("Click customer");
					// TODO code to select customer
					selectCustomer(this.id_customer);
				});
			}
		});
	}

	function selectCustomer(customer) {
		console.log(customer);

		$.ajax({
			type:"POST",
			url : "index.php?controller=AdminCarrierTester&token=d477215ae7a2c34ab6276253562284cd",
			async: true,
			dataType: "json",
			data : {
				ajax: "1",
				tab: "AdminCarrierTester",
				action: "getProducts",
				customerId: customer
			},
			success : function(res)
			{
				console.log(res);
				$.each(res, function(key, product){
					$("#productsSelect").append('<option value="' + product.id_product + '">' + product.name + ' -- ' + parseFloat(product.price).toFixed(2) + '€</option>');
				});

				$("#productsSelect").chosen().change(function () {
					var value = $(this).val();
					var text = $("#productsSelect option[value='" + value + "']").text();
					$("#productsSelect option").attr("selected", null);
					$("#productsSelect option").each(function (key, option) {
						if(text == $(this).text())
							$(this).attr("selected", "selected");
					});
				});
			}
		});
	}

	$(document).ready(function () {
		$('#customer').typeWatch({
			captureLength: 3,
			highlight: true,
			wait: 100,
			callback: function(){ 
				searchCustomers(); 
			}
		});

		$("#addToCart").click(function () {
			$(".actualCart").append('<div><span>' + $("#productsSelect option[selected='selected']").text() + '</span></div>')
		});
	});
	
</script>