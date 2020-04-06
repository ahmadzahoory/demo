<!DOCTYPE html>
<html>
<head>
	<title>Azure Lab 1</title>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.4.1/semantic.min.css" integrity="sha256-9mbkOfVho3ZPXfM7W8sV2SndrGDuh7wuyLjtsWeTI1Q=" crossorigin="anonymous" />
	<style type="text/css">
		.center {
			text-align: center!important;
		}
	</style>
</head>
<body style="background-color: #E0E0E0">
	<div class="ui container">
		<div class="ui segment" style="max-width: 800px; margin: 0 auto;">
			<h5 class="ui header center">IP <?= $_SERVER['SERVER_ADDR'] . ':' . $_SERVER['SERVER_PORT'] ?></h5>
			<h4 class="ui header"><i class="icon setting"></i></h4>
			<h2 class="ui header center">Welcome to Azure Environment</h2>
			<h3 class="ui header center">Project 1</h3>
			<form class="ui form" style="width: 400px; margin: 0 auto">
				<h3 class="ui header"></h3>
				<div class="field">
					<label>Product name</label>
					<input type="text" name="product_name" placeholder="Product name" id="new_product_name" required>
				</div>
				<div class="field">
					<label>Product quantity</label>
					<input type="number" name="first-name" placeholder="Product Qty." id="new_product_quantity" required>
				</div>
				<div class="field">
					<label>Product price</label>
					<input type="number" name="first-name" placeholder="Product price" id="new_product_price" required>
				</div>
				<div class="field">
					<button class="ui button" id="product_add">Add</button>
				</div>
			</form>
			<table class="ui celled table">
				<thead>
					<tr>
						<th class="center">ID</th>
						<th class="center">Name</th>
						<th class="center">Qty.</th>
						<th class="center">Price</th>
						<th></th>
						<th></th>
					</tr>
				</thead>
				<tbody></tbody>
			</table>
		</div>
	</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.4.1/jquery.min.js" integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo=" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.4.1/semantic.min.js" integrity="sha256-t8GepnyPmw9t+foMh3mKNvcorqNHamSKtKRxxpUEgFI=" crossorigin="anonymous"></script>
<script type="text/javascript">
	let read_key = ''
		write_key = '';

	$('.setting').click(() => {
		read_key = '';
		write_key = '';

		while (read_key.length === 0) 
			read_key = prompt('Enter read key');

		while (write_key.length === 0)
			write_key = prompt('Enter write key');

		console.log(`read_key ${read_key}`);
		console.log(`write_key ${write_key}`);

		get_data();
	});

	function get_data() {
		$.ajax({
			url: `data.php?operation=get&read_db_key=${read_key}&write_db_key=${write_key}`,
			success: res => {
				if (res.error) {
					alert(res.message);
					return;
				}

				$('tbody').empty();
				res.products.forEach(function(product) {
					$('tbody').append(`
						<tr data-id="${product.id}" data-name="${product.name}" data-quantity="${product.quantity}" data-price="${product.price}"> 
							<td class="center">${product.id}</td>
							<td class="center">${product.name}</td>
							<td class="center">${product.quantity}</td>
							<td class="center">${product.price}</td>
							<td class="center"><i class="edit green icon"></i></td>
							<td class="center"><i class="trash alternate red icon"></i></td>
						</tr>
					`)
				});
			},
			error: err => {
				console.log(err);
				alert(err.message);
			}
		});

	}

	// add
	$('form').submit(event => {
  		event.preventDefault();

		$.ajax({
			url: `data.php?operation=add&product_name=${$('#new_product_name').val()}&product_quantity=${$('#new_product_quantity').val()}&product_price=${$('#new_product_price').val()}&read_db_key=${read_key}&write_db_key=${write_key}`,
			success: res => {
				if (res.error) {
					alert(res.message);
					return;
				}
				get_data();
			},
			error: err => {
				console.log(err);
				alert(err.message);
			}
		});
	});

	//update
	$(document).on('click', '.edit.icon', function() {
		let tr = $(this).closest('tr')[0];
		let product_id = $(tr).data('id'),
			product_name = undefined,
			product_quantity = undefined,
			product_price = undefined;

		while (!product_name || product_name.length === 0)
			product_name = prompt('Product name');

		while (!product_quantity || isNaN(product_quantity))
			product_quantity = prompt('Product quantity');

		while (!product_price || isNaN(product_price))
			product_price = prompt('Product price');

		$.ajax({
			url: `data.php?operation=update&product_id=${product_id}&product_name=${product_name}&product_quantity=${product_quantity}&product_price=${product_price}&read_db_key=${read_key}&write_db_key=${write_key}`,
			success: res => {
				if (res.error) {
					alert(res.message);
					return;
				}
				get_data();
			},
			error: err => {
				console.log(err);
				alert(err.message);
			}
		});
	});

	// delete
	$(document).on('click', '.trash.icon', function() {
		let tr = $(this).closest('tr')[0]
		let product_id = $(tr).data('id')

		$.ajax({
			url: `data.php?operation=delete&product_id=${product_id}&read_db_key=${read_key}&write_db_key=${write_key}`,
			success: res => {
				if (res.error) {
					alert(res.message);
					return;
				}	
				get_data();
				// $(tr).remove();
			},
			error: err => {
				console.log(err);
				alert(err.message);
			}
		});
	});
</script>
</body>
</html>