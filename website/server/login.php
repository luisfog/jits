<?php
	ini_set('display_errors', '0');
	if(isset($_POST['username']) && isset($_POST['password'])){
		
		$username = $_POST['username'];
		$password = $_POST['password'];
		
		require("./database.php");
		$conn = getConnectionBack();

		$sql = "SELECT user, pass FROM webUsers WHERE user LIKE '$username'";
		$result = $conn->query($sql);

		if ($result && $result->num_rows > 0) {
			$row = $result->fetch_assoc();
			$hash = $row["pass"];
			$name = $row["user"];
			
			if (password_verify($password, $hash)) {
				session_start();
				$_SESSION["name"] = $name;
				header('Location: ../index.php' );
				return;
			} else {
				header('Location: ../login.html' );
				return;
			}
		}		
	}
	header('Location: ../login.html' );
?>
