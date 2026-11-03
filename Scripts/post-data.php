<?php

require 'dba.php';
require 'category.php';

header('Content-Type: application/json');

/* **************************************************************************************************** */

$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'];

/* **************************************************************************************************** */

$result = array();
$result['extra'] = 'extra';

try
{
	switch( $action ) {
	case 'SetOrder':
		$catId = $input['categoryId'];
		$order = $input['listOrder'];
		
		/*
		$result['catId'] = $catId;
		$result['listOrder'] = $order;
		*/
		
		$conn = GetConnection();

		$bus = new CategoryBus($conn);
		$cat = $bus->UpdateArticleOrder( $catId, $order );
		
		CloseConnection( $conn );
		
		break;
		
	default:
		$result['error'] = 'No Action Found - '.$action.'.';
		break;
	};
}
catch( Exception $e )
{
	$result['error'] = $e->getMessage();
}

echo json_encode($result);

/* **************************************************************************************************** */

?>