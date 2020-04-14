<?php header('Content-Type: application/json');

require_once 'vendor/autoload.php';

$read_db_key = $_GET['read_db_key'];
$write_db_key = $_GET['write_db_key'];

if (!$read_db_key) {
	die(json_encode([
		'error' => true,
		'message' => 'GET \'read_db_key\' key is required'
	]));
}

if (!$write_db_key) {
	die(json_encode([
		'error' => true,
		'message' => 'GET \'write_db_key\' key is required'
	]));
}

$read_conn_str = '';
$write_conn_str = '';

try {
	$client = new \GuzzleHttp\Client();
	
	$MSI_ENDPOINT = 'http://169.254.169.254/metadata/identity/oauth2/token?api-version=2018-02-01&resource=https%3A%2F%2Fvault.azure.net';
	$r = $client->request('GET', $MSI_ENDPOINT, [
		'headers' => [
			'Metadata' => 'true'
		]
	]);
	$data = json_decode($r->getBody(), true);
	$access_token = $data['access_token'];

	$KV_ENDPOINT = 'https://lab304keyvault123.vault.azure.net/secrets/' . $read_db_key  . '?api-version=2016-10-01';
	$r = $client->request('GET', $KV_ENDPOINT, [
		'headers' => [
			'Authorization' => 'Bearer ' . $access_token
		]
	]);
	$data = json_decode($r->getBody(), true);
	$read_conn_str = $data['value'];

	$KV_ENDPOINT = 'https://lab304keyvault123.vault.azure.net/secrets/' . $write_db_key  . '?api-version=2016-10-01';
	$r = $client->request('GET', $KV_ENDPOINT, [
		'headers' => [
			'Authorization' => 'Bearer ' . $access_token
		]
	]);
	$data = json_decode($r->getBody(), true);
	$write_conn_str = $data['value'];

} catch (Exception $e) {
	die(json_encode([
		'error' => true,
		'message' => $e->getMessage()
	]));
}

$read_conn_arr 		= explode(',', $read_conn_str);
$read_db_url     	= $read_conn_arr[0];
$read_db_user    	= $read_conn_arr[1];
$read_db_pass    	= $read_conn_arr[2];
$read_db_schema	 	= $read_conn_arr[3];
$read_db_table   	= $read_conn_arr[4];

$read_conn = mysqli_connect($read_db_url, $read_db_user, $read_db_pass, $read_db_schema);

// Check connection
if (!$read_conn) {
    die(json_encode([
        'error' => true,
        'message' => 'Database read connection failed | ' . mysqli_connect_error(),
        'conn' => $read_conn_arr
    ]));
}


$write_conn_arr		= explode(',', $write_conn_str);
$write_db_url     	= $write_conn_arr[0];
$write_db_user    	= $write_conn_arr[1];
$write_db_pass    	= $write_conn_arr[2];
$write_db_schema	= $write_conn_arr[3];
$write_db_table		= $write_conn_arr[4];

$write_conn = mysqli_connect($write_db_url, $write_db_user, $write_db_pass, $write_db_schema);

// Check connection
if (!$write_conn) {
    die(json_encode([
        'error' => true,
        'message' => 'Database write connection failed | ' . mysqli_connect_error(),
        'conn' => $write_conn_arr
    ]));
}

// ?operation=get
if ($_GET['operation'] === 'get') {
	$sql = 'SELECT * from ' . $read_db_table;

	$result = $read_conn->query($sql);

	$products = [];

    while($row = mysqli_fetch_assoc($result)) {
        $products[] = $row;
    }

	die(json_encode([
		'error' => false,
		'products' => $products
	]));


// ?operation=add&product_name=soap&product_quantity=5&product_price=45
} elseif ($_GET['operation'] === 'add') {
	if (!isset($_GET['product_name']) || !isset($_GET['product_quantity']) || !isset($_GET['product_price'])) {
		die(json_encode([
	    	'error' => true,
	    	'message' => 'one or more missing params'
	    ]));
	}

	$sql = 'INSERT into ' . $write_db_table . '(name, quantity, price) values("'. htmlentities($_GET['product_name']) . '", "' . htmlentities($_GET['product_quantity']) . '", "' . htmlentities($_GET['product_price']) . '")';

	if ($write_conn->query($sql) === true) {
		die(json_encode([
			'error' => false,
			'message' => 'Data inserted successfully'
		]));
	} else {
		die(json_encode([
			'error' => true,
			'message' => mysqli_error($write_conn)
		]));
	}


// ?operation=update&product_id=1&product_name=pant&product_quantity=5&product_price=45
} elseif ($_GET['operation'] === 'update') {
	if (!isset($_GET['product_id']) || !isset($_GET['product_name']) || !isset($_GET['product_quantity']) || !isset($_GET['product_price'])) {
		die(json_encode([
	    	'error' => true,
	    	'message' => 'one or more missing params'
	    ]));
	}

	$sql = 'UPDATE ' . $write_db_table . ' SET name="'. htmlentities($_GET['product_name']) . '", quantity="' . htmlentities($_GET['product_quantity']) . '", price="' . htmlentities($_GET['product_price']) . '" WHERE id = ' . htmlentities($_GET['product_id']);

	if ($write_conn->query($sql) === true) {
		die(json_encode([
			'error' => false,
			'message' => 'Data updated successfully'
		]));
	} else {
		die(json_encode([
			'error' => true,
			'message' => mysqli_error($write_conn)
		]));
	}


// ?operation=delete&product_id=1
} elseif ($_GET['operation'] === 'delete') {
	if (!isset($_GET['product_id'])) {
		die(json_encode([
	    	'error' => true,
	    	'message' => 'one or more missing params'
	    ]));
	}

	$sql = 'DELETE FROM ' . $write_db_table . ' WHERE id=' . htmlentities($_GET['product_id']);

	if ($write_conn->query($sql) === true) {
		die(json_encode([
			'error' => false,
			'message' => 'Data deleted successfully'
		]));
	} else {
		die(json_encode([
			'error' => true,
			'message' => mysqli_error($write_conn)
		]));
	}
	
} else {
	die(json_encode([
		'error' => true,
		'message' => 'invalid/missing operation param'
	]));
}

// close connection
mysqli_close($read_conn);
mysqli_close($write_conn);