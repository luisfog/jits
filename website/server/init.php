<?php
	if( isset($_POST['order']) && isset($_POST['server']) && isset($_POST['user']) && isset($_POST['pass']) ){
		$servername = $_POST["server"];
		$username = $_POST["user"];
		$password = $_POST["pass"];

		ini_set('display_errors', '0');
		if(strcmp($_POST['order'], "permissions") == 0 || strcmp($_POST['order'], "clients") == 0 ||
			strcmp($_POST['order'], "views") == 0 || strcmp($_POST['order'], "alarms") == 0 ||
			strcmp($_POST['order'], "webUuser") == 0){
				
			include("./dbinfo.php");
			
			$conn = new mysqli($servername, $username, $password, $database);
		}else
			$conn = new mysqli($servername, $username, $password);
		if ($conn->connect_error) {
			header("HTTP/1.1 500 Internal Server Error");
			echo "Connection failed: " . $conn->connect_error;
			include("./logs.php");
			insertToLog("init.php", "Connection failed: " . $conn->connect_error);
			return;
		}

		if(strcmp($_POST['order'], "dbinfo") == 0){
			if( !isset($_POST['database'])){
				header("HTTP/1.1 500 Internal Server Error");
				echo "Error creating MySQL information, missing parameters.";
				include("./logs.php");
				insertToLog("init.php", "Error creating MySQL information, missing parameters.");
				return;
			}
			file_put_contents("dbinfo.php", "<?php\n", FILE_APPEND);
			file_put_contents("dbinfo.php", "\$databaseHost = \"".$servername."\";\n", FILE_APPEND);
			file_put_contents("dbinfo.php", "\$database = \"".$_POST['database']."\";\n", FILE_APPEND);
			file_put_contents("dbinfo.php", "\$user = \"".$username."\";\n", FILE_APPEND);
			file_put_contents("dbinfo.php", "\$pass = \"".$password."\";\n", FILE_APPEND);
			file_put_contents("dbinfo.php", "?>", FILE_APPEND);
			
			header("HTTP/1.1 200 OK");
			echo "Database info stored successfully";
			return;
		}if(strcmp($_POST['order'], "database") == 0){
			$sql = "CREATE DATABASE jitsdb";
			
			if ($conn->query($sql) === TRUE) {
				header("HTTP/1.1 200 OK");
				echo "Database created successfully";
				$conn->close();
				return;
			} else {
				header("HTTP/1.1 500 Internal Server Error");
				echo "Error creating database: " . $conn->error;
				include("./logs.php");
				insertToLog("init.php", "Error creating database: " . $conn->error);
				$conn->close();
				return;
			}
		}else if(strcmp($_POST['order'], "user") == 0){
			
			$rawKey = hash('ripemd160', "user".microtime()).hash('ripemd160', "user".microtime());
			$username = substr($rawKey, 0, 15);
			$rawKey = hash('ripemd160', "pass".microtime()).hash('ripemd160', "pass".microtime());
			$password = substr($rawKey, 0, 15);
			
			$sql = "CREATE USER '$username'@'$servername' IDENTIFIED BY '$password';";
			
			file_put_contents("dbinfo.php", "<?php\n", FILE_APPEND);
			file_put_contents("dbinfo.php", "\$databaseHost = \"".$servername."\";\n", FILE_APPEND);
			file_put_contents("dbinfo.php", "\$database = \"jitsdb\";\n", FILE_APPEND);
			file_put_contents("dbinfo.php", "\$user = \"".$username."\";\n", FILE_APPEND);
			file_put_contents("dbinfo.php", "\$pass = \"".$password."\";\n", FILE_APPEND);
			file_put_contents("dbinfo.php", "?>", FILE_APPEND);
			
			if ($conn->query($sql) === TRUE) {
				header("HTTP/1.1 200 OK");
				echo "User created successfully";
				$conn->close();
				return;
			} else {
				header("HTTP/1.1 500 Internal Server Error");
				echo "Error creating user: " . $conn->error;
				include("./logs.php");
				insertToLog("init.php", "Error creating user: " . $conn->error);
				$conn->close();
				return;
			}
		}else if(strcmp($_POST['order'], "permissions") == 0){
			$sql = "GRANT ALL PRIVILEGES ON $database.* TO '$user'@'$databaseHost';";
			if ($conn->query($sql) === TRUE) {
				header("HTTP/1.1 200 OK");
				echo "Permissions successfully granted";
				$conn->close();
				return;
			} else {
				header("HTTP/1.1 500 Internal Server Error");
				echo "Error granted permissions: " . $conn->error;
				include("./logs.php");
				insertToLog("init.php", "Error granted permissions: " . $conn->error);
				$conn->close();
				return;
			}
		}else if(strcmp($_POST['order'], "clients") == 0){
			$sql = "CREATE TABLE clients (
					id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
					creation TIMESTAMP,
					name VARCHAR(30) NOT NULL,
					aes VARCHAR(3) NOT NULL,
					type VARCHAR(10) NOT NULL,
					connection_key VARCHAR(30) NOT NULL,
					aes_key VARCHAR(36) NOT NULL,
					aes_iv VARCHAR(36),
					date_last_iv TIMESTAMP
					)";
			if ($conn->query($sql) === TRUE) {
				header("HTTP/1.1 200 OK");
				echo "Clients table created successfully";
				$conn->close();
				return;
			} else {
				header("HTTP/1.1 500 Internal Server Error");
				echo "Error creating clients table: " . $conn->error;
				include("./logs.php");
				insertToLog("init.php", "Error creating clients table: " . $conn->error);
				$conn->close();
				return;
			}
		}else if(strcmp($_POST['order'], "views") == 0){
			$sql = "CREATE TABLE views (
					id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
					name VARCHAR(30) NOT NULL,
					connection_key VARCHAR(30) NOT NULL,
					client_name VARCHAR(30) NOT NULL,
					value VARCHAR(30) NOT NULL,
					column_name VARCHAR(30) NOT NULL
					)";
			if ($conn->query($sql) === TRUE) {
				header("HTTP/1.1 200 OK");
				echo "Views table created successfully";
				$conn->close();
				return;
			} else {
				header("HTTP/1.1 500 Internal Server Error");
				echo "Error creating views table: " . $conn->error;
				include("./logs.php");
				insertToLog("init.php", "Error creating views table: " . $conn->error);
				$conn->close();
				return;
			}
		}else if(strcmp($_POST['order'], "configurations") == 0){
			$sql = "CREATE TABLE configurations (
					id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
					type VARCHAR(10) NOT NULL,
					id_client_view INT(6) NOT NULL,
					dataset VARCHAR(10) NOT NULL,
					datasetType VARCHAR(10) NOT NULL,
					valuesS TEXT,
					yyMin INT NOT NULL,
					yyMax INT NOT NULL,
					avgOn BIT NOT NULL
					)";
			if ($conn->query($sql) === TRUE) {
				header("HTTP/1.1 200 OK");
				echo "Views table created successfully";
				$conn->close();
				return;
			} else {
				header("HTTP/1.1 500 Internal Server Error");
				echo "Error creating views table: " . $conn->error;
				include("./logs.php");
				insertToLog("init.php", "Error creating views table: " . $conn->error);
				$conn->close();
				return;
			}
		}else if(strcmp($_POST['order'], "alarms") == 0){
			$sql = "CREATE TABLE alarms (
					id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
					name VARCHAR(30) NOT NULL,
					connection_key VARCHAR(30) NOT NULL,
					client_name VARCHAR(30) NOT NULL,
					value VARCHAR(30) NOT NULL,
					cond INT(6) NOT NULL,
					target FLOAT(15,7) NOT NULL,
					time_target INT(6) NOT NULL
					)";
			if ($conn->query($sql) === TRUE) {
				header("HTTP/1.1 200 OK");
				echo "Alarm table created successfully";
				$conn->close();
				return;
			} else {
				header("HTTP/1.1 500 Internal Server Error");
				echo "Error creating alarms table: " . $conn->error;
				include("./logs.php");
				insertToLog("init.php", "Error creating alarms table: " . $conn->error);
				$conn->close();
				return;
			}
		}else if(strcmp($_POST['order'], "webUuser") == 0){
			if( !isset($_POST['webUser']) || !isset($_POST['webPass']) || !isset($_POST['email']) || !isset($_POST['timezone'])){
				header("HTTP/1.1 500 Internal Server Error");
				echo "Error inserting web user, missing parameters.";
				include("./logs.php");
				insertToLog("init.php", "Error inserting web user, missing parameters.");
				return;
			}
			$webUser = $_POST["webUser"];
			$webPass = $_POST["webPass"];
			$email = $_POST["email"];
			$timezone = $_POST["timezone"];
			
			$sql = "CREATE TABLE webUsers (
					id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
					user VARCHAR(30) NOT NULL,
					pass VARCHAR(255) NOT NULL,
					email VARCHAR(255) NOT NULL,
					timezoneset VARCHAR(255) NOT NULL
					)";
			if ($conn->query($sql) === TRUE) {
				$hash = password_hash($webPass, PASSWORD_DEFAULT);
				$sql = "INSERT INTO webUsers (user, pass, email, timezoneset) VALUES ('$webUser', '$hash', '$email', '$timezone')";
				if ($conn->query($sql) === TRUE) {
					session_start();
					$_SESSION["name"] = $webUser;
				
					header("HTTP/1.1 200 OK");
					echo "Web user created successfully";
					$conn->close();
					return;
				} else {
					header("HTTP/1.1 500 Internal Server Error");
					echo "Error inserting web user: " . $conn->error;
					include("./logs.php");
					insertToLog("init.php", "Error inserting web user: " . $conn->error);
					$conn->close();
					return;
				}
			} else {
				header("HTTP/1.1 500 Internal Server Error");
				echo "Error creating table for web users: " . $conn->error;
				include("./logs.php");
				insertToLog("init.php", "Error creating table for web users: " . $conn->error);
				$conn->close();
				return;
			}
		}else if(strcmp($_POST['order'], "deleteInit") == 0){
			header("HTTP/1.1 200 OK");
			unlink('../js/init.js');
			unlink('./init.php');
			return;
		}

		$conn->close();
	}
	header("HTTP/1.1 500 Internal Server Error");
	echo "Unknown order.";
	include("./logs.php");
	insertToLog("init.php", "Wrong GET request parameters.");
	return;
?>