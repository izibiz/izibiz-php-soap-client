PHP - İZİBİZ ENTEGRASYON TEST PROJESİ


Bu proje Authentication ,e-Fatura, e-Arşiv,e-İrsaliye, Müstahsil ve Smm ürünlerinin izibiz web servis metodlarının testleri örnek olması için yazılmıştır. Yalnızca test web servis sisteminde çalışmaktadır.

İndirilmesi Gerekenler

https://windows.php.net/download adresinden 64bit bilgisayar için "VS16 x64 Non Thread Safe"  altındaki zip dosyası indirilir. 
Bu klasörü C dizini altına çıkarılır. Gelişmiş sistem ayarlarından system variables'a dosyanın yolu path kısmına eklenir.

KURULUM

Projemizi indirdikten sonra vs code ile proje açılır. Terminalde "composer require phpunit/phpunit" komutu çalıştırılır. Komut çalışınca vendor klasörü ve composer dosyaları oluşmaktadır.
Sonra "./vendor/bin/phpunit" komutu çalıştırılır ve ".phpunit.result.cache" dosyası oluşur. composer.json dosyasına 

													"autoload": {
															"psr-4": {
																"App\\":"app"
															}
														} 
ekleme yapılır. Terminalde "composer update" komutu çalıştırılır.Daha sonra Terminale "./vendor/bin/phpunit --testdox" yazınca testlerimiz çalışmaya başlar. 
İnvoice testlerinin sadece çalışılması isteniyorsa tests klasörünün altındaki php dosyalarının isimlerinin sonuna Case yazarak ignore edilebilir. ,
Örneğin "ESmmTest.php" ismini "ESmmTestCase.php" yapılırsa Smm'nin test senaryoları çalışmaz.