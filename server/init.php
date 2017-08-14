<?php
	if( isset($_POST['order']) && isset($_POST['server']) && isset($_POST['user']) && isset($_POST['pass']) ){
		$servername = $_POST["server"];
		$username = $_POST["user"];
		$password = $_POST["pass"];

		ini_set('display_errors', '0');
		if(strcmp($_POST['order'], "permissions") == 0 || strcmp($_POST['order'], "clients") == 0 || strcmp($_POST['order'], "views") == 0 || strcmp($_POST['order'], "webUuser") == 0){
			include("./dbinfo.php");
			
			$conn = new mysqli($servername, $username, $password, $database);
		}else
			$conn = new mysqli($servername, $username, $password);
		if ($conn->connect_error) {
			header("HTTP/1.1 500 Internal Server Error");
			echo "Connection failed: " . $conn->connect_error;
			return;
		}

		if(strcmp($_POST['order'], "dbinfo") == 0){
			if( !isset($_POST['database'])){
				header("HTTP/1.1 500 Internal Server Error");
				echo "Error creating MySQL information, missing parameters.";
				return;
			}
			file_put_contents("dbinfo.php", "<?php", FILE_APPEND);
			file_put_contents("dbinfo.php", "\$databaseHost = \"".$servername."\";\n", FILE_APPEND);
			file_put_contents("dbinfo.php", "\$database = \"".$_POST['database']."\";\n", FILE_APPEND);
			file_put_contents("dbinfo.php", "\$user = \"".$username."\";\n", FILE_APPEND);
			file_put_contents("dbinfo.php", "\$pass = \"".$password."\";\n", FILE_APPEND);
			file_put_contents("dbinfo.php", "?>", FILE_APPEND);
			
			header("HTTP/1.1 201 Created");
			echo "Database info stored successfully";
			return;
		}if(strcmp($_POST['order'], "database") == 0){
			$sql = "CREATE DATABASE jitsdb";
			
			if ($conn->query($sql) === TRUE) {
				header("HTTP/1.1 201 Created");
				echo "Database created successfully";
				$conn->close();
				return;
			} else {
				header("HTTP/1.1 500 Internal Server Error");
				echo "Error creating database: " . $conn->error;
				$conn->close();
				return;
			}
		}else if(strcmp($_POST['order'], "user") == 0){
			
			$rawKey = hash('ripemd160', "user".microtime()).hash('ripemd160', "user".microtime());
			$username = substr($rawKey, 0, 15);
			$rawKey = hash('ripemd160', "pass".microtime()).hash('ripemd160', "pass".microtime());
			$password = substr($rawKey, 0, 15);
			
			$sql = "CREATE USER '$username'@'$servername' IDENTIFIED BY 'ilovedata';";
			
			file_put_contents("dbinfo.php", "<?php", FILE_APPEND);
			file_put_contents("dbinfo.php", "\$databaseHost = \"".$servername."\";\n", FILE_APPEND);
			file_put_contents("dbinfo.php", "\$database = \"jitsdb\";\n", FILE_APPEND);
			file_put_contents("dbinfo.php", "\$user = \"".$username."\";\n", FILE_APPEND);
			file_put_contents("dbinfo.php", "\$pass = \"".$password."\";\n", FILE_APPEND);
			file_put_contents("dbinfo.php", "?>", FILE_APPEND);
			
			if ($conn->query($sql) === TRUE) {
				header("HTTP/1.1 201 Created");
				echo "User created successfully";
				$conn->close();
				return;
			} else {
				header("HTTP/1.1 500 Internal Server Error");
				echo "Error creating user: " . $conn->error;
				$conn->close();
				return;
			}
		}else if(strcmp($_POST['order'], "permissions") == 0){
			$sql = "GRANT ALL PRIVILEGES ON $database.* TO '$user'@'$databaseHost';";
			if ($conn->query($sql) === TRUE) {
				header("HTTP/1.1 201 Created");
				echo "Permissions successfully granted";
				$conn->close();
				return;
			} else {
				header("HTTP/1.1 500 Internal Server Error");
				echo "Error granted permissions: " . $conn->error;
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
					aes_iv VARCHAR(36) NOT NULL
					)";
			if ($conn->query($sql) === TRUE) {
				header("HTTP/1.1 201 Created");
				echo "Clients table created successfully";
				$conn->close();
				return;
			} else {
				header("HTTP/1.1 500 Internal Server Error");
				echo "Error creating clients table: " . $conn->error;
				$conn->close();
				return;
			}
		}else if(strcmp($_POST['order'], "views") == 0){
			$sql = "CREATE TABLE views (
					id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
					name VARCHAR(30) NOT NULL,
					connection_key VARCHAR(30) NOT NULL,
					client_name VARCHAR(30) NOT NULL,
					value VARCHAR(30) NOT NULL
					)";
			if ($conn->query($sql) === TRUE) {
				header("HTTP/1.1 201 Created");
				echo "Views table created successfully";
				$conn->close();
				return;
			} else {
				header("HTTP/1.1 500 Internal Server Error");
				echo "Error creating clients table: " . $conn->error;
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
					cond INT(4) NOT NULL,
					target FLOAT(15,7) NOT NULL,
					time_target INT(6) NOT NULL
					)";
			if ($conn->query($sql) === TRUE) {
				header("HTTP/1.1 201 Created");
				echo "Views table created successfully";
				$conn->close();
				return;
			} else {
				header("HTTP/1.1 500 Internal Server Error");
				echo "Error creating clients table: " . $conn->error;
				$conn->close();
				return;
			}
		}else if(strcmp($_POST['order'], "webUuser") == 0){
			if( !isset($_POST['webUser']) || !isset($_POST['webPass']) || !isset($_POST['email'])){
				header("HTTP/1.1 500 Internal Server Error");
				echo "Error inserting web user, missing parameters.";
				return;
			}
			$webUser = $_POST["webUser"];
			$webPass = $_POST["webPass"];
			$email = $_POST["email"];
			
			$sql = "CREATE TABLE webUsers (
					id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
					user VARCHAR(30) NOT NULL,
					pass VARCHAR(255) NOT NULL,
					email VARCHAR(255) NOT NULL
					)";
			if ($conn->query($sql) === TRUE) {
				$hash = password_hash($webPass, PASSWORD_DEFAULT);
				$sql = "INSERT INTO webUsers (user, pass, email) VALUES ('$webUser', '$hash', '$email')";
				if ($conn->query($sql) === TRUE) {
					header("HTTP/1.1 201 Created");
					echo "Web user created successfully";
					$conn->close();
					return;
				} else {
					header("HTTP/1.1 500 Internal Server Error");
					echo "Error inserting web user: " . $conn->error;
					$conn->close();
					return;
				}
			} else {
				header("HTTP/1.1 500 Internal Server Error");
				echo "Error creating table for web users: " . $conn->error;
				$conn->close();
				return;
			}
		}else if(strcmp($_POST['order'], "deleteInit") == 0){
			//unlink('../js/init.js');
			//unlink('./init.php');
		}

		$conn->close();
	}
	header("HTTP/1.1 500 Internal Server Error");
	echo "Unknown order.";
	return;
?>