<?php

const HOST = "localhost";
const USER = "root";
const PASSWORD = "";
const DB_NAME = "INNs";

function add_inn($inn, $message, $date)
{
	$connection = connect_db();
	
	$query =
	"
		INSERT INTO inns (inn, message, date)
		VALUES ('$inn', '$message', '$date');
	";
	
	$sql = mysqli_query($connection, $query) or die(mysqli_error($connection));
}

function connect_db()
{
	$db = mysqli_connect(HOST, USER, PASSWORD, DB_NAME);
	return $db;
}

function get_inn($inn)
{
	$connection = connect_db();
	
	$query = 
	"
		SELECT * FROM inns
		WHERE inn = '$inn';
	";
	
	$sql = mysqli_query($connection, $query) or die(mysqli_error($connection));
	$result = mysqli_fetch_assoc($sql);
	if ($result === null)
	{
		return false;
	}
	else if ($result["date"] != date("Y-m-d"))
	{
		$query = 
		"
		DELETE FROM inns WHERE inn = '$inn';
		";
		mqsqli_query($connection. $query) or die(mysqli_error($connection));
		return false;
	}
	echo "взят из ДБ<br>";
	return $result["message"];
}