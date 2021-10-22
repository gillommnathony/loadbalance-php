<?php
class login
{
	private $db;

	function __construct()
	{
		global $db;
		$this->db = $db;
	}

	function cek_login()
	{
		/*kondisi user dinyatakan login adalah : 
		1. Memiliki $_COOKIE['adv_token']; (yang dibuat di method true_login() tadi)
		2. $_COOKIE['adv_token'] terdaftar di tabel tb_sessions, dan dalam keadaan masih belum expired
		3. IP dan User Agent sesuai dengan token yang terdaftar
		*/
		if (isset($_COOKIE['adv_token'])) {
			$token = $_COOKIE['adv_token'];
			$now = time();

			$cek = $this->db->prepare("SELECT * FROM tb_sessions WHERE token = ? AND expired > ? AND useragent = ?");
			$cek->execute(array(
				$token, $now, $_SERVER['HTTP_USER_AGENT']
			));
			$row = $cek->fetch(PDO::FETCH_ASSOC);
			if ($row) {
				//kondisi bisa disesuaikan utk kebutuhan dengan ATAU / DAN
				//kondisi DAN boleh dipakai, tapi terlalu strict.. Lebih baik pakai ATAU saja.
				$username = $row['username'];

				//kembalikan data user yg sedang login,, siapa tahu nanti ingin diolah
				$user = $this->db->prepare("SELECT id, `name`, user, email, `role`, `status` FROM tb_users WHERE user=? OR email=?");
				$user->execute(array(
					$username, $username
				));
				$data = $user->fetch(PDO::FETCH_ASSOC);

				$_SESSION['user'] = $data;

				return $data;
			}
		}
		return false;
	}

	function salah_login_action($username = '')
	{
		//logic : dipanggil saat user salah memasukkan username/password.
		//username, created, ip, dan user agent dicatat dengan FLAG=0.

		$created = time();
		$ip = $_SERVER['REMOTE_ADDR'];
		$useragent = $_SERVER['HTTP_USER_AGENT'];

		//memasukkan data ke tb_sessions dengan flag STAT = 0.
		$save = $this->db->prepare("INSERT INTO tb_sessions VALUES (NULL, ?, ?, ?, ?, 0, ?, 0)");
		$execute = $save->execute(array($ip, $useragent, $created, $username, ''));
		if ($execute) {
			return TRUE;
		}
		return FALSE;
	}

	function cek_salah_login($limit = 5)
	{
		#method ini dipanggil sekali di login-proses paling atas. 
		#$limit bisa disesuaikan sesuai kebutuhan kita. 
		//cek apakah di tabel tb_sessions ada 5 IP yang sama dalam keadaan salah login (STAT = 0)

		$ip = $_SERVER['REMOTE_ADDR'];

		$cek = $this->db->prepare("SELECT * FROM tb_sessions WHERE stat = 0 AND ip = ?");
		$cek->execute(array($ip));

		if ($cek->rowCount() >= $limit) {
			return true;
		}
		return false;
	}

	function true_login($username, $expired)
	{
		#method yang dipanggil ketika username dan password sudah tepat dimasukkan

		$created = time();
		if ($expired !== 0) {
			#kalau remember me dicentang, tanggal expirenya adalah 1 tahun dari sekarang.
			$expireddb = strtotime($expired);
		} else {
			#kalau remember me tidak dicentang, secara default user dapat login selama 6 jam saja.
			$expireddb = strtotime("+6 hours");
		}

		$ip = $_SERVER['REMOTE_ADDR'];
		$useragent = $_SERVER['HTTP_USER_AGENT'];

		$token = sha1($ip . $expireddb . "kbDx-120_MzWkl" . microtime()); //intinya membuat karakter acak saja
		//$token ini penting, nantinya akan disimpan sebagai COOKIE

		//apabila ada kesalahan login sebelumnya dengan IP & user agent yang sama sebelumnya harus ditandai dulu 
		//penandaan dilakukan dengan mengubah FLAG dari 0 menjadi 9, sehingga di pengecekan selanjutnya data ini tidak akan dianggap
		$upd = $this->db->prepare("UPDATE tb_sessions SET stat = 9 WHERE token = '' AND ip = ? AND useragent = ?");
		$upd->execute(array(
			$ip, $useragent
		));

		//memasukkan data lengkap ke tb_sessions dengan flag STAT = 1.
		$save = $this->db->prepare("INSERT INTO tb_sessions VALUES (NULL, ?, ?, ?, ?, ?, ?, 1)");
		$save->execute(array(
			$ip, $useragent, $created, $username, $expireddb, $token
		));

		//simpan token ke cookie
		$expr = 0;
		if ($expired !== 0) {
			$expr = intval(strtotime($expired));
		}
		setcookie("adv_token", $token, $expr, "/");

		//kembalikan data user yg sedang login,, siapa tahu nanti ingin diolah
		$user = $this->db->prepare("SELECT id, `name`, user, email, `role`, `status` FROM tb_users WHERE user=? OR email=?");
		$user->execute(array(
			$username, $username
		));
		$data = $user->fetch(PDO::FETCH_ASSOC);

		$_SESSION['user'] = $data;

		#kalau remember me tidak dicentang, cookie akan otomatis bertindak sebagai session
		#kalau dicentang, cookie akan terus disimpan

		return TRUE;
	}

	function logout()
	{
		#dipanggil saat user logout dari sistem.

		if (isset($_COOKIE['adv_token'])) {
			$token = $_COOKIE['adv_token'];

			//cara menghapus cookie adalah dengan mengubah tanggal expirednya menjadi sekarang
			$now = time();
			unset($_COOKIE['adv_token']);
			setcookie("adv_token", null, $now, "/");

			#jangan lupa tanggal expired di database diupdate juga, supaya session token yang sudah logout tidak dihijack
			$upd = $this->db->prepare("UPDATE tb_sessions SET expired = ? WHERE token = ?");
			$upd->execute(array(
				$now, $token
			));
		}
		return true;
	}

	function login_redir()
	{
		//method yang akan selalu dipanggil di seluruh halaman non index dan non login, 
		//untuk mengecek apabila user tidak memiliki akses langsung diredirect ke halaman login
		if (!$this->cek_login()) {
			header("location: " . BASE_URL . "administrator/");
		}
	}
}
