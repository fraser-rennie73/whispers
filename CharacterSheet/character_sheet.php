<?php

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
	case 'read':
		/*
		$checkboxes = array();
		$checkboxes['skill-Listen'] = true;
		$checkboxes['firearm-Handgun(.455)'] = true;
		$checkboxes['skill-Psychology'] = true;
		$radios = array();
		$radios['sanity-points-group'] = 77;
		$radios['magic-points-group'] = 10;
		$radios['handgun-group'] = 3;
		$radios['hit-points-group'] = 12;
		
		$result['checkboxes'] = $checkboxes;
		$result['radios'] = $radios;
		*/
		
		$result = ReadValues( $input['investigator'] );
		break;

	case 'readAll':
		$result = ReadAllValues();
		break;
			
	case 'save':
		SaveValue( $input['investigator'], $input['saveType'], $input['saveName'], $input['saveValue'], $input['saveText'] );
		break;

	case 'update':
		UpdateValue( $input['investigator'], $input['saveType'], $input['saveName'], $input['saveValue'], $input['saveText'] );
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

function GetConnection() : mysqli
{
	$host = "sql100.epizy.com";
	$username = "epiz_26236293";
	$password = "SN1Yegu4Lsg";
	$dbname = "epiz_26236293_michael";

	// Create connection
	$conn = new mysqli($host, $username, $password, $dbname);

	// Check connection
	if ($conn->connect_error) {
	  die("Connection failed: " . $conn->connect_error);
	}

	return $conn;
}

function CloseConnection(mysqli $conn)
{
	$conn->close();
}

/* **************************************************************************************************** */

function ReadAllValues()
{
	$sql =  "SELECT ";
	$sql .= "InvestigatorID, SavedValueType, SavedValueName, SavedValueValue, SavedValueText ";
	$sql .= "FROM SavedValue ";
	$sql .= "ORDER BY InvestigatorID, SavedValueType, SavedValueName";

	$conn = GetConnection();
	$dbResult = $conn->query($sql);

	$result = array();
	
	if ($dbResult->num_rows > 0) {

		$current = "";
		$investigator = null;
		$checkboxes = array();
		$radios = array();

		// output data of each row
		while($row = $dbResult->fetch_assoc()) {
			
			$investigatorID = $row["InvestigatorID"];

			if( $investigatorID !== $current ) {
			
				if( $current !== "" ) {
					$investigator["checkboxes"] = $checkboxes;
					$investigator["radios"] = $radios;
					array_push($result, $investigator);
				}

				$investigator = array();
				$investigator["id"] = $investigatorID;
				$checkboxes = array();
				$radios = array();

				$current = $investigatorID;
			}

			$type = $row["SavedValueType"];
			$name = $row["SavedValueName"];
			$value = (int)$row["SavedValueValue"];
						
			if ($type === 'checkbox')
				$checkboxes[$name] = ( $value == 1 );
			elseif ($type === 'radio')
				$radios[$name] = $value;
			else
			{
				$text = $row["SavedValueText"];
				$investigator[$name] = $text;
			}
		}
	}

	if( $current !== "" ) {
		$investigator["checkboxes"] = $checkboxes;
		$investigator["radios"] = $radios;
		array_push($result, $investigator);
	}

	CloseConnection( $conn );
	return $result;
}

/* **************************************************************************************************** */

function ReadValues( $investigator )
{
	$sql = "SELECT SavedValueType, SavedValueName, SavedValueValue, SavedValueText FROM SavedValue WHERE InvestigatorID = '$investigator'";

	$conn = GetConnection();
	$dbResult = $conn->query($sql);

	$result = array();
	$checkboxes = array();
	$radios = array();
	
	if ($dbResult->num_rows > 0) {

		// output data of each row
		while($row = $dbResult->fetch_assoc()) {
			
			$type = $row["SavedValueType"];
			$name = $row["SavedValueName"];
			$value = (int)$row["SavedValueValue"];
						
			if ($type === 'checkbox')
				$checkboxes[$name] = ( $value == 1 );
			elseif ($type === 'radio')
				$radios[$name] = $value;
			else
			{
				$text = $row["SavedValueText"];
				$result[$name] = $text;
			}
		}
	}

	CloseConnection( $conn );

	$result['checkboxes'] = $checkboxes;
	$result['radios'] = $radios;
	
	return $result;
}

/* **************************************************************************************************** */

function SaveValue( $investigator, $type, $name, $value, $text )
{
	$conn = GetConnection();

	$sql = "INSERT INTO SavedValue ( InvestigatorID, SavedValueType, SavedValueName, SavedValueValue, SavedValueText ) VALUES (?, ?, ?, ?, ?)";
	
	$id = 0;
	
	$stmt = $conn->prepare($sql);
	$stmt->bind_param("sssis", $saveInv, $saveType, $saveName, $saveValue, $saveText);

	// set parameters and execute
	$saveInv = $investigator;
	$saveType = $type;
	$saveName = $name;
	
	if ($type == "checkbox" )
		$saveValue = ( $value ? 1 : 0 );
	elseif ($type == "radio" )
		$saveValue = $value;
	else
		$saveText = $text;
		
	$result = $stmt->execute();
	$id = $stmt->insert_id;
	
	$stmt->close();
	CloseConnection( $conn );
	
	return $id;
}

/* **************************************************************************************************** */

function UpdateValue( $investigator, $type, $name, $value, $text )
{
	$conn = GetConnection();

	$sql = "UPDATE SavedValue ";
	$sql .= "SET SavedValueValue = ?, SavedValueText = ?, LastUpdated = NOW() ";
	$sql .= "WHERE InvestigatorID = ? AND SavedValueType = ? AND SavedValueName = ?";
	
	$id = 0;
	
	$stmt = $conn->prepare($sql);
	$stmt->bind_param("issss", $saveValue, $saveText, $saveInv, $saveType, $saveName);

	// set parameters and execute
	if ($type == "checkbox" )
		$saveValue = ( $value ? 1 : 0 );
	elseif ($type == "radio" )
		$saveValue = $value;
	else
		$saveText = $text;
		
	$saveInv = $investigator;
	$saveType = $type;
	$saveName = $name;
	
	$result = $stmt->execute();
	$id = $stmt->insert_id;
	
	$stmt->close();
	CloseConnection( $conn );
	
	return $id;
}	

?>