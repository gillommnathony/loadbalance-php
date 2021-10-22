## ENGLISH VERSION
## System Requirements:
1. Operating System **Linux/Windows**;
2. Server **Apache/Nginx/LiteSpeed**;
3. Script **PHP 7.2+**;
4. Database **MySQL/MariaDB/SQLite3**;
5. Activate the **pdo_sqlite** extension;
6. Make sure you have provided **IP Proxy** for bypassing Google Drive, OK.ru and Files.im.

## Installation method:
1. Open the file **includes/config.php**;
2. Change the value of **BASE_URL** according to your website url (backslash at the end);
3. Change the value of **BASE_DIR** to the *public_html* or *www* directory where you saved this tool (backslash at the end);
4. Change the value of **SECURE_SALT** to your liking for the security of your website;
5. Especially for the *Apache* server, open the file **.htaccess** in the root directory of this tool;
6. Especially for the *Apache* server, please change the *cors policy* **(localhost|127.0.0.1|gdplayer.top)** on the 21th line according to the domain/subdomain you want to whitelist;
7. Especially for the *Nginx* server, to enable the rewrite url can be seen in the file **nginx.conf**;
8. Especially for the *LiteSpeed* server, to enable the rewrite url can be seen in the file **litespeed.htaccess**. Please rename the file *litespeed.htaccess* to **.htaccess**.;
9. Especially for the *LiteSpeed* server, please change the *cors policy* **(localhost|127.0.0.1|gdplayer.top)** on the 9th line according to the domain/subdomain you want to whitelist;
10. Disable HTTP/2 and HTTP/3 on the apache/nginx/litespeed or Cloudflare servers so videos can be played using the Safari browser and Apple devices.
11. Save changes made.

## Database Settings:
1. Create a new database on *MySQL/MariaDB*;
2. Import database **new_db.sql**, if there is already an old database version, please import **tb_loadbalancers.sql**;
3. Set the database connection by opening the file **administrator/includes/conn.php**;
4. Save the changes made.

**NOTE:**
- If you want to add a load balancer, please activate remote mysql on the main VPS;
- After remote mysql is enabled, open **administrator/includes/conn.php** file and set *host* with VPS IP;
- If you want to use Main VPS resources as load balancer, connect the subdomain/domain load balancer with the primary VPS.

## Load Balancer Settings:
1. After the tool is installed on the Main VPS, please copy all files on the main VPS;
2. Then paste/extract the file in the VPS Load Balancer;
3. Open **include/config.php** and set **BASE_URL** and **BASE_DIR** according to the domain/subdomain load balancer;
4. Done.

**NOTE:**
- **SECURE_SALT** must match all primary and load balancer domains/subdomains.

## How to post popup ads:
1. Copy the popup ad code that you get at the advertising service provider of your choice;
2. Please login to the control panel as admin;
3. After logging in, go to the menu **App -> Settings -> Ads**, paste your popup ad code into **Popup Ad Code**;
4. Save the changes made.

## Activating the Google Drive Library:
1. Open the link *https://console.developers.google.com/apis/library*
2. Find and click **Google Drive API**;
3. Click the **Enable** button;

## How to create an API key:
1. Open the link *https://console.developers.google.com/apis/credentials*
2. Select an existing project, if you don't have a project yet, first create it by clicking the **Create Project** button
3. Enter your **Project Name** in the field provided;
4. Select **Location** of your project or leave it **No organization**;
5. Click the **Create** button;
6. On the side menu click **Oauth consent screen**;
7. Check **External**, if you use a *G-Suite* account you can check *Internal*;
8. Enter **Application Name** as you wish;
9. Click the **Save** button;
10. On the side menu click **Credentials**;
11. Click the **+ CREATE CREDENTIAL** button;
12. Click **API Key**;
13. In the **Name** column enter the name of your API as desired;
14. In the **Application restrictions** session, please check None;
15. In the **API Restrictions**, please check **Restrict key**;
16. In the column below, please check **Google Drive API**;
17. Please copy **API Key**;
18. Click the Save button.

## How to create an OAuth client ID:
1. Open the link *https://console.developers.google.com/apis/credentials*
2. Select an existing project or if you don't have a project yet, first create it by clicking the **Create Project** button;
3. Enter your **Project Name** in the field provided;
4. Select **Location** of your project or leave it **No organization**;
5. Click the **Create** button;
6. On the side menu click **Oauth consent screen**;
7. Check **External**, if you use a *G-Suite* account you can check *Internal*;
8. Enter **Application Name** as you wish;
9. Click the **Save** button;
10. On the side menu click **Credentials**;
11. Click the **+ CREATE CREDENTIAL** button;
12. Click **OAuth client ID**;
13. In the **Application Type** section select **Web Applications**;
14. Enter **Name** application as you wish;
15. In the **Authorized redirect URIs** section, click the **+ Add URI** button;
16. In the URI column, enter the link **https://developers.google.com/oauthplayground**
17. Click the **Create** button;
18. Please copy and save *Client Id* and *Client Secret* obtained;
19. Open the link *https://developers.google.com/oauthplayground*
20. Click the **Gear** button in the upper right corner;
21. Check **Use your own OAuth credentials**;
22. Paste *Client Id* and *Client Secret* that have been copied before;
23. In **Step 1 Select & authorize APIs**, please search & click **Drive API v3**;
24. Please check/click all *child* from *Drive API v3*;
25. Click the **Authorize APIs button**;
26. Please select your account;
27. Click Allow on all popup dialogs that appear;
28. Click the Allow again button;
29. In **Step 2 Exchange authorization code for tokens**, click the button **Exchange authorization code for tokens**;
30. Please copy and save the code **Refresh Token** obtained;

## How to set the Google Drive Bypass Limit on this Tool:
1. After creating *API Key* and *Oauth client ID* Google Drive;
2. Open the file **includes/gdrive_auth/sample.json** and copy all the contents;
3. Create a new json file with the name of your Google Drive email, for example: emailgooglesaya@gmail.com;
4. Paste the contents of the previously copied sample.json into the newly created json file;
5. Fill in *email* with your Google Drive email and fill in *api_key*, *client_id*, *client_secret*, *refresh_token* with those previously created;
3. Save the changes made.

**NOTE:**
- For bypass limits you can use multiple Google accounts;
- Please create an API Key and OAuth Client on the new Google account;
- Repeat the Google Drive Bypass Limit setting steps by creating a new json file;

## How to set up the Cloudflare CDN specifically for this Tool:
1. Login with your Cloudflare account from the link *https: //dash.cloudflare.com/login*;
2. Open the **Speed ​​-> Optimization** menu and set the value **Rocket Loader** with **Off**;
3. Open the menu **Caching -> Configuration**;
4. Set **Caching Level** with **No query string**;
5. Set **Browser Cache TTL ** with ** Respect Existing Headers**;
6. Set **Always Online ** with ** Off**;
7. Open the **Page Rules** menu;
8. Create a new page rule by clicking the **Create Page Rule** button;
9. Enter the URL **yourwebsite.com/embed.php** in the column **If the URL matches:**;
10. In the **Then the settings are:**;
11. Select **Rocket Loader** and set the value to **Off**;
12. Select **Cache Level** and set the value to **Bypass**;
13. Select **Origin Cache Control** and set the value to **Off**;
14. Click the **Save & Deploy** button to save the *page rule*.
---
## VERSI BAHASA INDONESIA
## Persyaratan Sistem:
1. Sistem Operasi **Linux/Windows**;
2. Server **Apache/Nginx/LiteSpeed**;
3. Script **PHP 7+**;
4. Database **MySQL/MariaDB/SQLite3**;
5. Aktifkan ekstensi **pdo_sqlite**;
7. Pastikan kamu telah menyediakan **IP Proxy** untuk bypass Google Drive, OK.ru and Files.im.

## Cara instalasi:
1. Buka file **includes/config.php**;
2. Ubah nilai **BASE_URL** sesuai dengan url website Anda (backslash diakhir);
3. Ubah nilai **BASE_DIR** sesuai dengan direktori *public_html* atau *www* tempat Anda menyimpan tools ini (backslash diakhir);
4. Ubah nilai **SECURE_SALT** sesuai dengan keinginan Anda demi keamanan web Anda;
5. Khusus untuk server *Apache*, Buka file **.htaccess** pada root direktori tool ini;
6. Khusus untuk server *Apache*, silahkan ubah *cors policy* **(localhost|127.0.0.1|gdplayer.top)** pada baris ke-21 sesuai dengan domain/subdomain yang ingin di whitelist;
7. Khusus untuk server *Nginx*, untuk mengaktifkan url rewrite bisa dilihat pada file **nginx.conf**;
8. Khusus untuk server *LiteSpeed*, untuk mengaktifkan url rewrite bisa dilihat pada file **litespeed.htaccess**. Silahkan rename file *litespeed.htaccess* menjadi **.htaccess**.;
9. Khusus untuk server *LiteSpeed*, silahkan ubah *cors policy* **(localhost|127.0.0.1|gdplayer.top)** pada baris ke-9 sesuai dengan domain/subdomain yang ingin di whitelist;
10. Nonaktifkan HTTP/2 dan HTTP/3 pada server apache/nginx/litespeed atau Cloudflare sehingga video bisa diputar menggunakan browser Safari dan perangkat Apple.
11. Simpan perubahan yang dilakukan.

## Pengaturan Database:
1. Buat database baru pada *MySQL/MariaDB*;
2. Import database **new_db.sql**, jika sudah terdapat database versi lama silahkan import **tb_loadbalancers.sql**;
3. Atur koneksi database dengan cara buka file **administrator/includes/conn.php**;
4. Simpan perbahan yang dilakukan.

**CATATAN:**
- Jika ingin menambahkan load balancer, silahkan aktifkan remote mysql pada VPS Utama tersebut;
- Setelah remote mysql diaktifkan buka file **administrator/includes/conn.php** dan atur *host* dengan IP VPS;
- Jika ingin menggunakan resource VPS Utama sebagai load balancer maka sambungkan subdomain/domain load balancer dengan VPS utama tersebut.

## Pengaturan Load Balancer:
1. Setelah tool diinstall pada VPS Utama, silahkan copy semua file pada vps utama tersebut;
2. Kemudian paste/ekstrak pada VPS Load Balancer;
3. Buka file **includes/config.php** dan atur **BASE_URL** dan **BASE_DIR** sesuai dengan domain/subdomain load balancer;
4. Selesai.

**CATATAN:**
- **SECURE_SALT** harus sama dengan semua domain/subdomain utama dan load balancer.

## Cara pasang iklan popup:
1. Copy kode iklan popup yang Anda dapatkan pada penyedia layanan iklan pilihan Anda;
2. Silahkan login ke control panel sebagai admin;
3. Setelah login, pergi ke menu **App -> Settings -> Ads**, paste kode iklan popup Anda ke **Popup Ad Code**;
4. Simpan perubahan yang dilakukan.

## Mengaktifkan Library Google Drive:
1. Buka link *https://console.developers.google.com/apis/library*
2. Cari dan klik **Google Drive API**;
3. Klik tombol **AKTIFKAN**;

## Cara pembuatan API key:
1. Buka link *https://console.developers.google.com/apis/credentials*
2. Pilih project yang ada, jika belum punya project maka buat dulu dengan cara klik tombol **Buat Project**
3. Masukkan **Nama Project** Anda pada kolom yang tersedia;
4. Pilih **Lokasi** project Anda atau biarkan saja **Tidak ada organisasi**;
5. Klik tombol **Buat**;
6. Pada menu samping klik **Layar persetujuan Oauth**;
7. Centang **Eksternal**, jika menggunakan akun *G-Suite* Anda bisa mencentang *Internal*;
8. Masukkan **Nama Aplikasi** sesuai keinginan Anda;
9. Klik tombol **Simpan**;
10. Pada menu samping klik **Kredensial**;
11. Klik tombol **+ BUAT KREDENSIAL**;
12. Klik **Kunci API**;
13. Pada kolom **Nama** masukkan nama dari API Anda sesuai keinginan;
14. Pada sesi **Pembatasan aplikasi**, silahkan centang Tidak ada;
15. Pada sesi **Pembatasan API**, silahkan centang **Batasi kunci**;
16. Pada kolom dibawahnya silahkan centang **Google Drive API**;
17. Silahkan copy **API Key**;
18. Klik tombol Simpan. 

## Cara pembuatan OAuth client ID:
1. Buka link *https://console.developers.google.com/apis/credentials*
2. Pilih project yang ada atau jika belum punya project maka buat dulu dengan cara klik tombol **Buat Project**;
3. Masukkan **Nama Project** Anda pada kolom yang tersedia;
4. Pilih **Lokasi** project Anda atau biarkan saja **Tidak ada organisasi**;
5. Klik tombol **Buat**;
6. Pada menu samping klik **Layar persetujuan Oauth**;
7. Centang **Eksternal**, jika menggunakan akun *G-Suite* Anda bisa mencentang *Internal*;
8. Masukkan **Nama Aplikasi** sesuai keinginan Anda;
9. Klik tombol **Simpan**;
10. Pada menu samping **klik Kredensial**;
11. Klik tombol **+ BUAT KREDENSIAL**;
12. Klik **OAuth client ID**;
13. Pada bagian **Jenis Aplikasi** pilih **Aplikasi Web**;
14. Masukkan **Nama** aplikasi sesuai keinginan Anda;
15. Pada bagian **URI pengalihan yang diotorisasi**, klik tombol **+ Tambah URI**;
16. Pada kolom URI, masukkan link **https://developers.google.com/oauthplayground**
17. Klik tombol **Buat**;
18. Silahkan copy dan simpan *Client Id* dan *Client Secret* yang didapatkan;
19. Buka link *https://developers.google.com/oauthplayground*
20. Klik tombol **Gear** yang ada di pojok kanan atas;
21. Centang **Use your own OAuth credentials**;
22. Paste *Client Id* dan *Client Secret* yang telah di copy sebelumnya;
23. Pada **Step 1 Select & authorize APIs**, silahkan cari & klik **Drive API v3**;
24. Silahkan centang/klik semua *child* dari *Drive API v3* tersebut;
25. Klik tombol **Authorize APIs**;
26. Silahkan pilih akun Anda;
27. Klik Izinkan pada semua dialog popup yang muncul;
28. Klik tombol Izinkan lagi;
29. Pada **Step 2 Exchange authorization code for tokens**, klik tombol **Exchange authorization code for tokens**;
30. Silahkan copy dan simpan kode **Refresh Token** yang didapatkan;

## Cara pengaturan Bypass Limit Google Drive pada Tool ini:
1. Setelah membuat *API Key* dan *Oauth client ID* Google Drive;
2. Buka file **includes/gdrive_auth/sample.json** dan copy semua isinya;
3. Buat file json baru dengan nama email Google Drive Anda, contoh: emailgooglesaya@gmail.com;
4. Paste isi dari sample.json yang telah di copy sebelumnya ke dalam file json baru yang telah dibuat;
5. Isi *email* dengan email Google Drive Anda dan isi *api_key*, *client_id*, *client_secret*, *refresh_token* dengan yang telah dibuat sebelumnya;
3. Simpan perubahan yang dilakukan.

**CATATAN:**
- Untuk bypass limit Anda bisa menggunakan multi akun Google;
- Silahkan buat API Key dan OAuth Client pada akun Google baru;
- Ulangi langkah pengaturan Bypass Limit Google Drive dengan membuat file json baru;

## Cara pengaturan CDN Cloudflare khusus untuk Tool ini:
1. Login dengan akun Cloudflare Anda dari link *https://dash.cloudflare.com/login*
2. Buka menu **Speed -> Optimization** dan atur nilai **Rocket Loader** dengan **Off**;
3. Buka menu **Caching -> Configuration**;
4. Atur **Caching Level** dengan **No query string**;
5. Atur **Browser Cache TTL** dengan **Respect Existing Headers**;
6. Atur **Always Online** dengan **Off**;
7. Buka menu **Page Rules**;
8. Buat page rule baru dengan cara klik tombol **Create Page Rule**;
9. Masukkan URL **domainwebanda.com/embed.php** pada kolom **If the URL matches:**;
10. Pada bagian **Then the settings are:**;
11. Pilih **Rocket Loader** dan atur nilainya menjadi **Off**;
12. Pilih **Cache Level** dan atur nilainya menjadi **Bypass**;
13. Pilih **Origin Cache Control** dan atur nilainya menjadi **Off**;
14. Klik tombol **Save & Deploy** untuk menyimpan *page rule* tersebut.
