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
		<div class="col-lg-4">
			<div class="row">
				<div class="col-lg-12">
					<p>{l s='Select a customer:'}</p>
					<div class="input-group">
						<select id="customersSelect" class="" name="productsSelect">
							{foreach from=$customers item=customer name=customers}
								<option value="{$customer.id_customer}">{$customer.firstname} {$customer.lastname}</option>
							{/foreach}
						</select>
					</div>
				</div>
			</div>

			<div class="row">
				<div class="col-lg-12 productsCol">
					<p>{l s='Select the cart products:'}</p>
					<div id="productsInputSelect" class="input-group" style="float: left;">
						<select id="productsSelect" class="" name="productsSelect">
							{foreach from=$products item=product name=products}
								<option value="{$product.id_product}">{$product.name} -- {$product.price}€</option>
							{/foreach}
						</select>
					</div>
					<div class="col-lg-4">
						Quantity: <input class="form-control fixed-width-m" type="number" name="quantity" />
					</div>
				</div>

				<div class="col-lg-12 buttonCenter">
					<button id="addToCart" type="button" class="setup-customer btn btn-default">
						Add to cart
					</button>
				</div>
			</div>

			<div class="row">
				<div class="col-lg-12">
					<p>{l s='Customer addresses:'}</p>
					<div id="addressesInputSelect" class="input-group" style="float: left;">
						<select id="addressesSelect" class="" name="addressesSelect">
							<option>You have to select a customer first!</option>
						</select>
					</div>
				</div>
			</div>

			<div class="row">
				<div class="col-lg-12 buttonCenter">
					<button id="calculateCarriers" type="button" class="setup-customer btn btn-default">
						Calculate Carriers
					</button>
				</div>
			</div>
		</div>

		<div class="col-lg-4">
			<div id="actualCart" class="col-lg-10">
				<p>Actual cart:</p>
			</div>
		</div>

		<div class="col-lg-4">
			<div class="col-lg-12">
				<p>Result carriers:</p>
			</div>
			<div id="resultCarriers" class="col-lg-12">
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">

	// TODO vigilar els tokens i generar-los automàticament
	var idCustomer = 0;
	var idAddress = 0;
	var products = [];
	var quantities = [];
	var firstTime = true;

	function selectCustomer(customer) {
		console.log(customer);
		idCustomer = customer;
		$.ajax({
			type:"POST",
			url : "index.php?controller=AdminCarrierTester&token=d477215ae7a2c34ab6276253562284cd",
			async: true,
			dataType: "json",
			data : {
				ajax: "1",
				tab: "AdminCarrierTester",
				action: "getAddresses",
				customerId: customer
			},
			success : function(res)
			{
				console.log(res);
				/*resposta = JSON.parse(res);*/
				
				$("#addressesSelect").html('');
				$.each(res.addresses, function(key, address){
					$("#addressesSelect").append('<option value="' + address.id_address + '">' + address.alias + '</option>');
				});

				$('#addressesSelect').trigger("chosen:updated");

				if(firstTime){
					$("#addressesSelect").chosen().change(function () {
						var value = $(this).val();
						$("#addressesSelect option").attr("selected", null);
						$("#addressesSelect option").each(function (key, option) {
							if(value == $(this).val()){
								$(this).attr("selected", "selected");
								idAddress = value;
							}
						});
					});
				}

				firstTime = false;
			}
		});
	}

	function calculateCarriers(){
		console.log("Calculating carriers for customer ID " + idCustomer + " and products " + products);
		$.ajax({
			type:"POST",
			url : "index.php?controller=AdminCarrierTester&token=d477215ae7a2c34ab6276253562284cd",
			async: true,
			dataType: "json",
			data : {
				ajax: "1",
				tab: "AdminCarrierTester",
				action: "calculateCarriers",
				customerId: idCustomer,
				products : products,
				addressId : idAddress
			},
			success : function(res)
			{
				console.log(res);
				$("#resultCarriers").html('');
				$(res).each(function (key, carrier) {
					var price = 0;
					if(carrier.price == 0)
						price = "Free!"
					else
						price = carrier.price;
					$("#resultCarriers").append("<p>" + carrier.name + " - " + carrier.delay + " - " + price +  "€</p>");
				});
			}
		});
	}

	$(document).ready(function () {

		$("#productsSelect").chosen().change(function () {
			var value = $(this).val();
			var text = $("#productsSelect option[value='" + value + "']").text();
			$("#productsSelect option").attr("selected", null);
			$("#productsSelect option").each(function (key, option) {
				if(text == $(this).text())
					$(this).attr("selected", "selected");
			});
		});

		$("#customersSelect").chosen().change(function () {
			var value = $(this).val();
			var text = $("#customersSelect option[value='" + value + "']").text();
			$("#customersSelect option").attr("selected", null);
			$("#customersSelect option").each(function (key, option) {
				if(text == $(this).text()){
					$(this).attr("selected", "selected");
					selectCustomer(value);
				}
			});
		});

		$("#addToCart").click(function () {
			$("#actualCart").append('<div><span class="' + $("#productsSelect option[selected='selected']").val() + '">' + $("#productsSelect option[selected='selected']").text() + '</span> -- <span>Quantity: ' + $(".productsCol input[name='quantity']").val() + ' -- </span><span class="deleteProduct" value="' + products.length + '" style="color:red;cursor:pointer;">Delete</span></div>');
			
			$(".deleteProduct[value='" + products.length + "']").click(
				function() {
					$(this).parent().remove();
					products.splice($(this).val(), 1);
					quantities.splice($(this).val(), 1);
				}
			);

			products.push($("#productsSelect option[selected='selected']").val());
			quantities.push(parseInt($(".productsCol input[name='quantity']").val()));
		});

		$("#calculateCarriers").click(function () {
			calculateCarriers();
		});
	});
	
</script>