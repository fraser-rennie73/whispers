<?php declare(strict_types=1); // strict requirement

function GetConnection() : mysqli
{
    /*
	$host = "sql100.epizy.com";
	$username = "epiz_26236293";
	$password = "SN1Yegu4Lsg";
	$dbname = "epiz_26236293_attachments";
    */
    
    $host = DB_HOST;
	$username = DB_USERNAME;
	$password = DB_PASSWORD;
	$dbname = DB_DBNAME;

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

?>