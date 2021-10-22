<?php
require_once "../vendor/autoload.php";
require_once "../includes/config.php";
require_once "../includes/functions.php";
require_once "includes/functions.php";

$login = new \login();
if ($login->cek_salah_login()) {
	//kalau user salah login melebihi batas yang ditentukan, maka proses langsung berhenti
	create_alert("warning", "Sorry you can't log in again because you have logged in too many errors. Contact the Administrator for more information.", BASE_URL . "administrator/index.php");
} elseif (isset($_POST["submit"])) {
	$recaptchValidation = recaptcha_validate($_POST["captcha-response"]);
	if ($recaptchValidation) {
		$username = $_POST["username"];
		$password = $_POST["password"];
		if (!empty($username) && !empty($password)) {
			//step 1 : cek apakah username ada di tabel 
			$cek = $db->prepare("SELECT * FROM tb_users WHERE user = ? OR email = ?");
			$cek->execute(array($username, $username));
			$row = $cek->fetch(PDO::FETCH_ASSOC);
			if ($row) {
				//username ada, tangkap password yg ada di database
				$password_db = $row['password'];
				$password_verify = password_verify($password, $password_db);
				$status = intval($row['status']);

				if ($status == 2) {
					//status pending
					$login->salah_login_action($username); //pencatatan kesalahan login
					create_alert("warning", "Your account is awaiting approval! Please contact admin for more information.", BASE_URL . "administrator/index.php");
				} elseif ($status == 0) {
					//status nonaktif
					$login->salah_login_action($username); //pencatatan kesalahan login
					create_alert("danger", "Your account is currently inactive! Please contact admin for more information.", BASE_URL . "administrator/index.php");
				} else {
					if ($password_verify) {
						//password sudah cocok
						$expired = "+1 day";
						if (isset($_POST["remember"])) {
							$expired = "+2 days";
						}

						#kalau remember me dicentang, login akan expired dalam waktu 1 tahun, selain itu ya akan seperti session biasa yang hilang ketika diclose
						$login->true_login($username, $expired); //pencatatan token akan dilakukan disini
						create_alert("success", "Login successful!", BASE_URL . "administrator/admin.php");
					} else {
						//password tidak cocok
						$login->salah_login_action($username); //pencatatan kesalahan login
						create_alert("danger", "Incorrect username or password! Please try again later.", BASE_URL . "administrator/index.php");
					}
				}
			} else {
				$login->salah_login_action($username); //pencatatan kesalahan login
				create_alert("danger", "The account is not registered!", BASE_URL . "administrator/index.php");
			}
		} else {
			create_alert("danger", "Username and password must be filled in!", BASE_URL . "administrator/index.php");
		}
	} else {
		$login->salah_login_action($username);
		create_alert("danger", "The security code entered is incorrect!", BASE_URL . "administrator/index.php");
	}
} else {
	create_alert("danger", "Please login from the login form provided!", BASE_URL . "administrator/index.php");
}
