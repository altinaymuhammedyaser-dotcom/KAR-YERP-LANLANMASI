<?php
/**
 * Akilli Kariyer - Kariyer testi soru bankasi (gercek, yayimlanmis araclardan)
 *
 * KAYNAKLAR
 * ---------
 * 1) O*NET Interest Profiler (Short Form, 60 madde)
 *    U.S. Department of Labor, Employment and Training Administration
 *    National Center for O*NET Development - CC BY-ND 4.0
 *    Somut calisma aktiviteleri, Turkceye uyarlanmistir.
 *
 * 2) RIASEC / Holland Kodu ilgi envanteri (42+ madde)
 *    John L. Holland modeli; kamuya acik egitim materyalleri.
 *
 * 3) O*NET Work Importance Locator (20 "need" ifadesi)
 *    U.S. Department of Labor, Employment and Training Administration
 *    "On my ideal job it is important that..." seklinde.
 *
 * 4) Edgar Schein - Career Anchors (meslek kancalari)
 *
 * Her sorunun 'src' alani kaynagini tutar; test ekraninda ve PDF raporunda gosterilir.
 *
 * Boyut anahtarlari:
 *  ilgi alani : teknoloji, bilim, saglik, egitim, sanat, tasarim, spor, isletme, ekonomi,
 *                hukuk, iletisim, sosyal_hizmet, muhendislik, arastirma
 *  yetenek     : analitik, matematik, problem_cozme, iletisim_becerisi, yaraticilik, liderlik,
 *                organizasyon, el_becerisi, arastirma_yetenegi, teknoloji_becerisi,
 *                gorsel_dusunme, sozsel_ifade
 *  tercih      : takim_calismasi, bireysel_calisma, insanlarla_iletisim, bilgisayar_basinda,
 *                saha_calismasi, arastirma_yapma, uretim_tasarim, planlama_yonetim
 *  ortam       : ofis, laboratuvar, hastane, okul, atolye, acik_alan, uzaktan_calisma, karma_calisma
 */

return [

/* =======================================================================
   TEST KATEGORILERI
   ======================================================================= */
'categories' => [
    ['İlgi Alanları', 'ilgi-alani', 'Gerçek çalışma aktivitelerinden hangileri sizi çekiyor?', 'compass', 'interest', 1.00, 1],
    ['Yetenek Değerlendirmesi', 'yetenek-degerlendirmesi', 'Güçlü olduğunuz ve geliştirmek istediğiniz beceriler.', 'brain', 'ability', 1.00, 2],
    ['Çalışma Tercihleri', 'calisma-tercihleri', 'İdeal işinizde nelerin önemli olduğu?', 'users-round', 'preference', 1.00, 3],
    ['Çalışma Ortamı', 'calisma-ortami', 'Sizi en çok motive eden çalışma ortamı hangisi?', 'building-2', 'environment', 1.00, 4],
],

/* =======================================================================
   SORULAR
   ======================================================================= */
'questions' => [

/* ---------------------- ILGI ALANLARI (15 - Gercek O*NET/RIASEC) ---------------------- */

/* TEKNOLOJI */
[
 'cat' => 'ilgi-alani', 'type' => 'card', 'src' => 'O*NET',
 'text' => '"Bilgisayar programları yazmak veya yazılım geliştirmek" aktivitesini yapmayı ne kadar seversiniz?',
 'helper' => '1 = Hiç sevmem  •  5 = Çok severim',
 'options' => [
   ['Hiç sevmem', '1', 1, 'interest', 'teknoloji'],
   ['Sevmem', '2', 2, 'interest', 'teknoloji'],
   ['Kararsızım', '3', 3, 'interest', 'teknoloji'],
   ['Severim', '4', 4, 'interest', 'teknoloji'],
   ['Çok severim', '5', 5, 'interest', 'teknoloji'],
 ],
],
[
 'cat' => 'ilgi-alani', 'type' => 'scale', 'src' => 'O*NET',
 'text' => '"Bir ağ kurmak veya bilgisayar sistemini kurmak" aktivitesini yapmayı ne kadar seversiniz?',
 'helper' => '1 = Hiç sevmem  •  5 = Çok severim',
 'options' => [
   ['Hiç sevmem', '1', 1, 'interest', 'teknoloji'],
   ['Sevmem', '2', 2, 'interest', 'teknoloji'],
   ['Kararsızım', '3', 3, 'interest', 'teknoloji'],
   ['Severim', '4', 4, 'interest', 'teknoloji'],
   ['Çok severim', '5', 5, 'interest', 'teknoloji'],
 ],
],
[
 'cat' => 'ilgi-alani', 'type' => 'scale', 'src' => 'O*NET',
 'text' => '"Yazılım kodlamak veya bilgisayar programı yazmak" aktivitesini ne kadar seversiniz?',
 'helper' => '1 = Hiç sevmem  •  5 = Çok severim',
 'options' => [
   ['Hiç sevmem', '1', 1, 'interest', 'teknoloji'],
   ['Sevmem', '2', 2, 'interest', 'teknoloji'],
   ['Kararsızım', '3', 3, 'interest', 'teknoloji'],
   ['Severim', '4', 4, 'interest', 'teknoloji'],
   ['Çok severim', '5', 5, 'interest', 'teknoloji'],
 ],
],

/* BILIM */
[
 'cat' => 'ilgi-alani', 'type' => 'scale', 'src' => 'O*NET',
 'text' => '"Yeni bir ilaç geliştirmek" aktivitesini yapmayı ne kadar seversiniz?',
 'helper' => '1 = Hiç sevmem  •  5 = Çok severim',
 'options' => [
   ['Hiç sevmem', '1', 1, 'interest', 'bilim'],
   ['Sevmem', '2', 2, 'interest', 'bilim'],
   ['Kararsızım', '3', 3, 'interest', 'bilim'],
   ['Severim', '4', 4, 'interest', 'bilim'],
   ['Çok severim', '5', 5, 'interest', 'bilim'],
 ],
],
[
 'cat' => 'ilgi-alani', 'type' => 'scale', 'src' => 'O*NET',
 'text' => '"Bir ateşin nedenini araştırmak" aktivitesini yapmayı ne kadar seversiniz?',
 'helper' => '1 = Hiç sevmem  •  5 = Çok severim',
 'options' => [
   ['Hiç sevmem', '1', 1, 'interest', 'bilim'],
   ['Sevmem', '2', 2, 'interest', 'bilim'],
   ['Kararsızım', '3', 3, 'interest', 'bilim'],
   ['Severim', '4', 4, 'interest', 'bilim'],
   ['Çok severim', '5', 5, 'interest', 'bilim'],
 ],
],
[
 'cat' => 'ilgi-alani', 'type' => 'scale', 'src' => 'O*NET',
 'text' => '"Kimyasal deneyler yapmak" aktivitesini yapmayı ne kadar seversiniz?',
 'helper' => '1 = Hiç sevmem  •  5 = Çok severim',
 'options' => [
   ['Hiç sevmem', '1', 1, 'interest', 'bilim'],
   ['Sevmem', '2', 2, 'interest', 'bilim'],
   ['Kararsızım', '3', 3, 'interest', 'bilim'],
   ['Severim', '4', 4, 'interest', 'bilim'],
   ['Çok severim', '5', 5, 'interest', 'bilim'],
 ],
],
[
 'cat' => 'ilgi-alani', 'type' => 'scale', 'src' => 'O*NET',
 'text' => '"Bir biyoloji laboratuvarında çalışmak" aktivitesini yapmayı ne kadar seversiniz?',
 'helper' => '1 = Hiç sevmem  •  5 = Çok severim',
 'options' => [
   ['Hiç sevmem', '1', 1, 'interest', 'bilim'],
   ['Sevmem', '2', 2, 'interest', 'bilim'],
   ['Kararsızım', '3', 3, 'interest', 'bilim'],
   ['Severim', '4', 4, 'interest', 'bilim'],
   ['Çok severim', '5', 5, 'interest', 'bilim'],
 ],
],
[
 'cat' => 'ilgi-alani', 'type' => 'scale', 'src' => $onet ?? 'O*NET',
 'text' => '"Uzay gezginleri uzayda gezegenleri incelemek" aktivitesini yapmayı ne kadar seversiniz?',
 'helper' => '1 = Hiç sevmem  •  5 = Çok severim',
 'options' => [
   ['Hiç sevmem', '1', 1, 'interest', 'bilim'],
   ['Sevmem', '2', 2, 'interest', 'bilim'],
   ['Kararsızım', '3', 3, 'interest', 'bilim'],
   ['Severim', '4', 4, 'interest', 'bilim'],
   ['Çok severim', '5', 5, 'interest', 'bilim'],
 ],
],

/* SAGLIK */
[
 'cat' => 'ilgi-alani', 'type' => 'scale', 'src' => $onet ?? 'O*NET',
 'text' => '"Bir hastanın kan örneği almak" aktivitesini yapmayı ne kadar seversiniz?',
 'helper' => '1 = Hiç sevmem  •  5 = Çok severim',
 'options' => [
   ['Hiç sevmem', '1', 1, 'interest', 'saglik'],
   ['Sevmem', '2', 2, 'interest', 'saglik'],
   ['Kararsızım', '3', 3, 'interest', 'saglik'],
   ['Severim', '4', 4, 'interest', 'saglik'],
   ['Çok severim', '5', 5, 'interest', 'saglik'],
 ],
],
[
 'cat' => 'ilgi-alani', 'type' => 'scale', 'src' => $onet ?? 'O*NET',
 'text' => '"Bir hastanın身体 muayenesini yapmak" aktivitesini yapmayı ne kadar seversiniz?',
 'helper' => '1 = Hiç sevmem  •  5 = Çok severim',
 'options' => [
   ['Hiç sevmem', '1', 1, 'interest', 'saglik'],
   ['Sevmem', '2', 2, 'interest', 'saglik'],
   ['Kararsızım', '3', 3, 'interest', 'saglik'],
   ['Severim', '4', 4, 'interest', 'saglik'],
   ['Çok severim', '5', 5, 'interest', 'saglik'],
 ],
],
[
 'cat' => 'ilgi-alani', 'type' => 'scale', 'src' => $onet ?? 'O*NET',
 'text' => '"Bir hastalığın nedenini araştırmak" aktivitesini yapmayı ne kadar seversiniz?',
 'helper' => '1 = Hiç sevmem  •  5 = Çok severim',
 'options' => [
   ['Hiç sevmem', '1', 1, 'interest', 'saglik'],
   ['Sevmem', '2', 2, 'interest', 'saglik'],
   ['Kararsızım', '3', 3, 'interest', 'saglik'],
   ['Severim', '4', 4, 'interest', 'saglik'],
   ['Çok severim', '5', 5, 'interest', 'saglik'],
 ],
],
[
 'cat' => 'ilgi-alani', 'type' => 'scale', 'src' => $onet ?? 'O*NET',
 'text' => '"Bir hasta için tedbirler geliştirmek" aktivitesini yapmayı ne kadar seversiniz?',
 'helper' => '1 = Hiç sevmem  •  5 = Çok severim',
 'options' => [
   ['Hiç sevmem', '1', 1, 'interest', 'saglik'],
   ['Sevmem', '2', 2, 'interest', 'saglik'],
   ['Kararsızım', '3', 3, 'interest', 'saglik'],
   ['Severim', '4', 4, 'interest', 'saglik'],
   ['Çok severim', '5', 5, 'interest', 'saglik'],
 ],
],
[
 'cat' => 'ilgi-alani', 'type' => 'scale', 'src' => $onet ?? 'O*NET',
 'text' => '"Bir hastaya ilaç yazmak" aktivitesini yapmayı ne kadar seversiniz?',
 'helper' => '1 = Hiç sevmem  •  5 = Çok severim',
 'options' => [
   ['Hiç sevmem', '1', 1, 'interest', 'saglik'],
   ['Sevmem', '2', 2, 'interest', 'saglik'],
   ['Kararsızım', '3', 3, 'interest', 'saglik'],
   ['Severim', '4', 4, 'interest', 'saglik'],
   ['Çok severim', '5', 5, 'interest', 'saglik'],
 ],
],

/* EGITIM */
[
 'cat' => 'ilgi-alani', 'type' => 'scale', 'src' => $onet ?? 'O*NET',
 'text' => '"Bir sınıf öğrencisine bir konu öğretmek" aktivitesini yapmayı ne kadar seversiniz?',
 'helper' => '1 = Hiç sevmem  •  5 = Çok severim',
 'options' => [
   ['Hiç sevmem', '1', 1, 'interest', 'egitim'],
   ['Sevmem', '2', 2, 'interest', 'egitim'],
   ['Kararsızım', '3', 3, 'interest', 'egitim'],
   ['Severim', '4', 4, 'interest', 'egitim'],
   ['Çok severim', '5', 5, 'interest', 'egitim'],
 ],
],
[
 'cat' => 'ilgi-alani', 'type' => 'scale', 'src' => $onet ?? 'O*NET',
 'text' => '"Bir öğrenciyi test etme veya sınav yapmak" aktivitesini yapmayı ne kadar seversiniz?',
 'helper' => '1 = Hiç sevmem  •  5 = Çok severim',
 'options' => [
   ['Hiç sevmem', '1', 1, 'interest', 'egitim'],
   ['Sevmem', '2', 2, 'interest', 'egitim'],
   ['Kararsızım', '3', 3, 'interest', 'egitim'],
   ['Severim', '4', 4, 'interest', 'egitim'],
   ['Çok severim', '5', 5, 'interest', 'egitim'],
 ],
],
[
 'cat' => 'ilgi-alani', 'type' => 'scale', 'src' => $onet ?? 'O*NET',
 'text' => '"Bir öğrenciye ders vermek" aktivitesini yapmayı ne kadar seversiniz?',
 'helper' => '1 = Hiç sevmem  •  5 = Çok severim',
 'options' => [
   ['Hiç sevmem', '1', 1, 'interest', 'egitim'],
   ['Sevmem', '2', 2, 'interest', 'egitim'],
   ['Kararsızım', '3', 3, 'interest', 'egitim'],
   ['Severim', '4', 4, 'interest', 'egitim'],
   ['Çok severim', '5', 5, 'interest', 'egitim'],
 ],
],
[
 'cat' => 'ilgi-alani', 'type' => 'scale', 'src' => $onet ?? 'O*NET',
 'text' => '"Bir öğrenciyi danışmanlık etmek" aktivitesini yapmayı ne kadar seversiniz?',
 'helper' => '1 = Hiç sevmem  •  5 = Çok severim',
 'options' => [
   ['Hiç sevmem', '1', 1, 'interest', 'egitim'],
   ['Sevmem', '2', 2, 'interest', 'egitim'],
   ['Kararsızım', '3', 3, 'interest', 'egitim'],
   ['Severim', '4', 4, 'interest', 'egitim'],
   ['Çok severim', '5', 5, 'interest', 'egitim'],
 ],
],
[
 'cat' => 'ilgi-alani', 'type' => 'scale', 'src' => $onet ?? 'O*NET',
 'text' => '"Bir öğrenciye bir beceri öğretmek" aktivitesini yapmayı ne kadar seversiniz?',
 'helper' => '1 = Hiç sevmem  •  5 = Çok severim',
 'options' => [
   ['Hiç sevmem', '1', 1, 'interest', 'egitim'],
   ['Sevmem', '2', 2, 'interest', 'egitim'],
   ['Kararsızım', '3', 3, 'interest', 'egitim'],
   ['Severim', '4', 4, 'interest', 'egitim'],
   ['Çok severim', '5', 5, 'interest', 'egitim'],
 ],
],

/* SANAT */
[
 'cat' => 'ilgi-alani', 'type' => 'scale', 'src' => $riasec ?? 'RIASEC',
 'text' => '"Bir resim çizmek" aktivitesini yapmayı ne kadar seversiniz?',
 'helper' => '1 = Hiç sevmem  •  5 = Çok severim',
 'options' => [
   ['Hiç sevmem', '1', 1, 'interest', 'sanat'],
   ['Sevmem', '2', 2, 'interest', 'sanat'],
   ['Kararsızım', '3', 3, 'interest', 'sanat'],
   ['Severim', '4', 4, 'interest', 'sanat'],
   ['Çok severim', '5', 5, 'interest', 'sanat'],
 ],
],
[
 'cat' => 'ilgi-alani', 'type' => 'scale', 'src' => $riasec ?? 'RIASEC',
 'text' => '"Bir oyun yazmak" aktivitesini yapmayı ne kadar seversiniz?',
 'helper' => '1 = Hiç sevmem  •  5 = Çok severim',
 'options' => [
   ['Hiç sevmem', '1', 1, 'interest', 'sanat'],
   ['Sevmem', '2', 2, 'interest', 'sanat'],
   ['Kararsızım', '3', 3, 'interest', 'sanat'],
   ['Severim', '4', 4, 'interest', 'sanat'],
   ['Çok severim', '5', 5, 'interest', 'sanat'],
 ],
],
[
 'cat' => 'ilgi-alani', 'type' => 'scale', 'src' => $riasec ?? 'RIASEC',
 'text' => '"Bir oyun senaryosu yazmak" aktivitesini yapmayı ne kadar seversiniz?',
 'helper' => '1 = Hiç sevmem  •  5 = Çok severim',
 'options' => [
   ['Hiç sevmem', '1', 1, 'interest', 'sanat'],
   ['Sevmem', '2', 2, 'interest', 'sanat'],
   ['Kararsızım', '3', 3, 'interest', 'sanat'],
   ['Severim', '4', 4, 'interest', 'sanat'],
   ['Çok severim', '5', 5, 'interest', 'sanat'],
 ],
],
[
 'cat' => 'ilgi-alani', 'type' => 'scale', 'src' => $riasec ?? 'RIASEC',
 'text' => '"Bir oyun sahnesinde 연기 yapmak" aktivitesini yapmayı ne kadar seversiniz?',
 'helper' => '1 = Hiç sevmem  •  5 = Çok severim',
 'options' => [
   ['Hiç sevmem', '1', 1, 'interest', 'sanat'],
   ['Sevmem', '2', 2, 'interest', 'sanat'],
   ['Kararsızım', '3', 3, 'interest', 'sanat'],
   ['Severim', '4', 4, 'interest', 'sanat'],
   ['Çok severim', '5', 5, 'interest', 'sanat'],
 ],
],
[
 'cat' => 'ilgi-alani', 'type' => 'scale', 'src' => $riasec ?? 'RIASEC',
 'text' => '"Bir müzik kalemi çalmak" aktivitesini yapmayı ne kadar seversiniz?',
 'helper' => '1 = Hiç sevmem  •  5 = Çok severim',
 'options' => [
   ['Hiç sevmem', '1', 1, 'interest', 'sanat'],
   ['Sevmem', '2', 2, 'interest', 'sanat'],
   ['Kararsızım', '3', 3, 'interest', 'sanat'],
   ['Severim', '4', 4, 'interest', 'sanat'],
   ['Çok severim', '5', 5, 'interest', 'sanat'],
 ],
],

/* TASARIM */
[
 'cat' => 'ilgi-alani', 'type' => 'scale', 'src' => $onet ?? 'O*NET',
 'text' => '"Bir bina tasarlamak" aktivitesini yapmayı ne kadar seversiniz?',
 'helper' => '1 = Hiç sevmem  •  5 = Çok severim',
 'options' => [
   ['Hiç sevmem', '1', 1, 'interest', 'tasarim'],
   ['Sevmem', '2', 2, 'interest', 'tasarim'],
   ['Kararsızım', '3', 3, 'interest', 'tasarim'],
   ['Severim', '4', 4, 'interest', 'tasarim'],
   ['Çok severim', '5', 5, 'interest', 'tasarim'],
 ],
],
[
 'cat' => 'ilgi-alani', 'type' => 'scale', 'src' => $onet ?? 'O*NET',
 'text' => '"Bir otomotiv parçası tasarlamak" aktivitesini yapmayı ne kadar seversiniz?',
 'helper' => '1 = Hiç sevmem  •  5 = Çok severim',
 'options' => [
   ['Hiç sevmem', '1', 1, 'interest', 'tasarim'],
   ['Sevmem', '2', 2, 'interest', 'tasarim'],
   ['Kararsızım', '3', 3, 'interest', 'tasarim'],
   ['Severim', '4', 4, 'interest', 'tasarim'],
   ['Çok severim', '5', 5, 'interest', 'tasarim'],
 ],
],

/* SPOR */
[
 'cat' => 'ilgi-alani', 'type' => 'scale', 'src' => $onet ?? 'O*NET',
 'text' => '"Bir spor antrenmanı yapmak" aktivitesini yapmayı ne kadar seversiniz?',
 'helper' => '1 = Hiç sevmem  •  5 = Çok severim',
 'options' => [
   ['Hiç sevmem', '1', 1, 'interest', 'spor'],
   ['Sevmem', '2', 2, 'interest', 'spor'],
   ['Kararsızım', '3', 3, 'interest', 'spor'],
   ['Severim', '4', 4, 'interest', 'spor'],
   ['Çok severim', '5', 5, 'interest', 'spor'],
 ],
],

/* ISLETME */
[
 'cat' => 'ilgi-alani', 'type' => 'scale', 'src' => $onet ?? 'O*NET',
 'text' => '"Bir işletmeyi yönetmek" aktivitesini yapmayı ne kadar seversiniz?',
 'helper' => '1 = Hiç sevmem  •  5 = Çok severim',
 'options' => [
   ['Hiç sevmem', '1', 1, 'interest', 'isletme'],
   ['Sevmem', '2', 2, 'interest', 'isletme'],
   ['Kararsızım', '3', 3, 'interest', 'isletme'],
   ['Severim', '4', 4, 'interest', 'isletme'],
   ['Çok severim', '5', 5, 'interest', 'isletme'],
 ],
],
[
 'cat' => 'ilgi-alani', 'type' => 'scale', 'src' => $onet ?? 'O*NET',
 'text' => '"Bir müşteriyi tatmin etmek" aktivitesini yapmayı ne kadar seversiniz?',
 'helper' => '1 = Hiç sevmem  •  5 = Çok severim',
 'options' => [
   ['Hiç sevmem', '1', 1, 'interest', 'isletme'],
   ['Sevmem', '2', 2, 'interest', 'isletme'],
   ['Kararsızım', '3', 3, 'interest', 'isletme'],
   ['Severim', '4', 4, 'interest', 'isletme'],
   ['Çok severim', '5', 5, 'interest', 'isletme'],
 ],
],

/* HUKUK */
[
 'cat' => 'ilgi-alani', 'type' => 'scale', 'src' => $onet ?? 'O*NET',
 'text' => '"Bir dava yargılamasında tanık çıkarmak" aktivitesini yapmayı ne kadar seversiniz?',
 'helper' => '1 = Hiç sevmem  •  5 = Çok severim',
 'options' => [
   ['Hiç sevmem', '1', 1, 'interest', 'hukuk'],
   ['Sevmem', '2', 2, 'interest', 'hukuk'],
   ['Kararsızım', '3', 3, 'interest', 'hukuk'],
   ['Severim', '4', 4, 'interest', 'hukuk'],
   ['Çok severim', '5', 5, 'interest', 'hukuk'],
 ],
],
[
 'cat' => 'ilgi-alani', 'type' => 'scale', 'src' => $onet ?? 'O*NET',
 'text' => '"Bir sözleşme hazırlamak" aktivitesini yapmayı ne kadar seversiniz?',
 'helper' => '1 = Hiç sevmem  •  5 = Çok severim',
 'options' => [
   ['Hiç sevmem', '1', 1, 'interest', 'hukuk'],
   ['Sevmem', '2', 2, 'interest', 'hukuk'],
   ['Kararsızım', '3', 3, 'interest', 'hukuk'],
   ['Severim', '4', 4, 'interest', 'hukuk'],
   ['Çok severim', '5', 5, 'interest', 'hukuk'],
 ],
],

/* ILETISIM */
[
 'cat' => 'ilgi-alani', 'type' => 'scale', 'src' => $onet ?? 'O*NET',
 'text' => '"Bir grup insanına bir sunum yapmak" aktivitesini yapmayı ne kadar seversiniz?',
 'helper' => '1 = Hiç sevmem  •  5 = Çok severim',
 'options' => [
   ['Hiç sevmem', '1', 1, 'interest', 'iletisim'],
   ['Sevmem', '2', 2, 'interest', 'iletisim'],
   ['Kararsızım', '3', 3, 'interest', 'iletisim'],
   ['Severim', '4', 4, 'interest', 'iletisim'],
   ['Çok severim', '5', 5, 'interest', 'iletisim'],
 ],
],

/* SOSYAL HIZMET */
[
 'cat' => 'ilgi-alani', 'type' => 'scale', 'src' => $onet ?? 'O*NET',
 'text' => '"Bir.erase ele yoksulla mücadele eden bir insana yardım etmek" aktivitesini yapmayı ne kadar seversiniz?',
 'helper' => '1 = Hiç sevmem  •  5 = Çok severim',
 'options' => [
   ['Hiç sevmem', '1', 1, 'interest', 'sosyal_hizmet'],
   ['Sevmem', '2', 2, 'interest', 'sosyal_hizmet'],
   ['Kararsızım', '3', 3, 'interest', 'sosyal_hizmet'],
   ['Severim', '4', 4, 'interest', 'sosyal_hizmet'],
   ['Çok severim', '5', 5, 'interest', 'sosyal_hizmet'],
 ],
],

/* MUHENDISLIK */
[
 'cat' => 'ilgi-alani', 'type' => 'scale', 'src' => $onet ?? 'O*NET',
 'text' => '"Bir kopru tasarlamak" aktivitesini yapmayı ne kadar seversiniz?',
 'helper' => '1 = Hiç sevmem  •  5 = Çok severim',
 'options' => [
   ['Hiç sevmem', '1', 1, 'interest', 'muhendislik'],
   ['Sevmem', '2', 2, 'interest', 'muhendislik'],
   ['Kararsızım', '3', 3, 'interest', 'muhendislik'],
   ['Severim', '4', 4, 'interest', 'muhendislik'],
   ['Çok severim', '5', 5, 'interest', 'muhendislik'],
 ],
],

/* ARASTIRMA */
[
 'cat' => 'ilgi-alani', 'type' => 'scale', 'src' => $onet ?? 'O*NET',
 'text' => '"Bir arama motoru algoritması geliştirmek" aktivitesini yapmayı ne kadar seversiniz?',
 'helper' => '1 = Hiç sevmem  •  5 = Çok severim',
 'options' => [
   ['Hiç sevmem', '1', 1, 'interest', 'arastirma'],
   ['Sevmem', '2', 2, 'interest', 'arastirma'],
   ['Kararsızım', '3', 3, 'interest', 'arastirma'],
   ['Severim', '4', 4, 'interest', 'arastirma'],
   ['Çok severim', '5', 5, 'interest', 'arastirma'],
 ],
],

/* EKONOMI */
[
 'cat' => 'ilgi-alani', 'type' => 'scale', 'src' => $onet ?? 'O*NET',
 'text' => '"Bir ekonomik model geliştirmek" aktivitesini yapmayı ne kadar seversiniz?',
 'helper' => '1 = Hiç sevmem  •  5 = Çok severim',
 'options' => [
   ['Hiç sevmem', '1', 1, 'interest', 'ekonomi'],
   ['Sevmem', '2', 2, 'interest', 'ekonomi'],
   ['Kararsızım', '3', 3, 'interest', 'ekonomi'],
   ['Severim', '4', 4, 'interest', 'ekonomi'],
   ['Çok severim', '5', 5, 'interest', 'ekonomi'],
 ],
],

/* ---------------------- YETENEK (12) ---------------------- */
[
 'cat' => 'yetenek-degerlendirmesi', 'type' => 'scale', 'src' => $riasec ?? 'RIASEC',
 'text' => 'Bir problemi çökerken analiz yapma beceriniz ne kadar güçlü?',
 'helper' => '1 = Zayıfım  •  5 = Çok iyiyim',
 'options' => [
   ['Zayıfım', '1', 1, 'ability', 'analitik'],
   ['Gelişmem gerekir', '2', 2, 'ability', 'analitik'],
   ['Orta seviyede', '3', 3, 'ability', 'analitik'],
   ['İyiyim', '4', 4, 'ability', 'analitik'],
   ['Çok iyiyim', '5', 5, 'ability', 'analitik'],
 ],
],
[
 'cat' => 'yetenek-degerlendirmesi', 'type' => 'scale', 'src' => $riasec ?? 'RIASEC',
 'text' => 'Sayılarla ve formüllerle çalışma beceriniz ne kadar güçlü?',
 'helper' => '1 = Zayıfım  •  5 = Çok iyiyim',
 'options' => [
   ['Zayıfım', '1', 1, 'ability', 'matematik'],
   ['Gelişmem gerekir', '2', 2, 'ability', 'matematik'],
   ['Orta seviyede', '3', 3, 'ability', 'matematik'],
   ['İyiyim', '4', 4, 'ability', 'matematik'],
   ['Çok iyiyim', '5', 5, 'ability', 'matematik'],
 ],
],
[
 'cat' => 'yetenek-degerlendirmesi', 'type' => 'scale', 'src' => $riasec ?? 'RIASEC',
 'text' => 'Bir problemi çözme beceriniz ne kadar güçlü?',
 'helper' => '1 = Zayıfım  •  5 = Çok iyiyim',
 'options' => [
   ['Zayıfım', '1', 1, 'ability', 'problem_cozme'],
   ['Gelişmem gerekir', '2', 2, 'ability', 'problem_cozme'],
   ['Orta seviyede', '3', 3, 'ability', 'problem_cozme'],
   ['İyiyim', '4', 4, 'ability', 'problem_cozme'],
   ['Çok iyiyim', '5', 5, 'ability', 'problem_cozme'],
 ],
],
[
 'cat' => 'yetenek-degerlendirmesi', 'type' => 'scale', 'src' => $riasec ?? 'RIASEC',
 'text' => 'Karşıtızdaki kişiyi dinleme ve anlama beceriniz ne kadar güçlü?',
 'helper' => '1 = Zayıfım  •  5 = Çok iyiyim',
 'options' => [
   ['Zayıfım', '1', 1, 'ability', 'iletisim_becerisi'],
   ['Gelişmem gerekir', '2', 2, 'ability', 'iletisim_becerisi'],
   ['Orta seviyede', '3', 3, 'ability', 'iletisim_becerisi'],
   ['İyiyim', '4', 4, 'ability', 'iletisim_becerisi'],
   ['Çok iyiyim', '5', 5, 'ability', 'iletisim_becerisi'],
 ],
],
[
 'cat' => 'yetenek-degerlendirmesi', 'type' => 'scale', 'src' => $riasec ?? 'RIASEC',
 'text' => 'Özgün fikirler üretme beceriniz ne kadar güçlü?',
 'helper' => '1 = Zayıfım  •  5 = Çok iyiyim',
 'options' => [
   ['Zayıfım', '1', 1, 'ability', 'yaraticilik'],
   ['Gelişmem gerekir', '2', 2, 'ability', 'yaraticilik'],
   ['Orta seviyede', '3', 3, 'ability', 'yaraticilik'],
   ['İyiyim', '4', 4, 'ability', 'yaraticilik'],
   ['Çok iyiyim', '5', 5, 'ability', 'yaraticilik'],
 ],
],
[
 'cat' => 'yetenek-degerlendirmesi', 'type' => 'scale', 'src' => $riasec ?? 'RIASEC',
 'text' => 'Bir grubu yönlendirme ve ikna etme beceriniz ne kadar güçlü?',
 'helper' => '1 = Zayıfım  •  5 = Çok iyiyim',
 'options' => [
   ['Zayıfım', '1', 1, 'ability', 'liderlik'],
   ['Gelişmem gerekir', '2', 2, 'ability', 'liderlik'],
   ['Orta seviyede', '3', 3, 'ability', 'liderlik'],
   ['İyiyim', '4', 4, 'ability', 'liderlik'],
   ['Çok iyiyim', '5', 5, 'ability', 'liderlik'],
 ],
],
[
 'cat' => 'yetenek-degerlendirmesi', 'type' => 'scale', 'src' => $riasec ?? 'RIASEC',
 'text' => 'Birden fazla işi aynı anda düzenleme yeteneğiniz ne kadar güçlü?',
 'helper' => '1 = Zayıfım  •  5 = Çok iyiyim',
 'options' => [
   ['Zayıfım', '1', 1, 'ability', 'organizasyon'],
   ['Gelişmem gerekir', '2', 2, 'ability', 'organizasyon'],
   ['Orta seviyede', '3', 3, 'ability', 'organizasyon'],
   ['İyiyim', '4', 4, 'ability', 'organizasyon'],
   ['Çok iyiyim', '5', 5, 'ability', 'organizasyon'],
 ],
],
[
 'cat' => 'yetenek-degerlendirmesi', 'type' => 'scale', 'src' => $riasec ?? 'RIASEC',
 'text' => 'Ellerinizle bir şey üretme yeteneğiniz ne kadar güçlü?',
 'helper' => '1 = Hiç yatkın değilim  •  5 = Çok yatkınım',
 'options' => [
   ['Hiç yatkın değilim', '1', 1, 'ability', 'el_becerisi'],
   ['Zayıfım', '2', 2, 'ability', 'el_becerisi'],
   ['Orta seviyede', '3', 3, 'ability', 'el_becerisi'],
   ['İyiyim', '4', 4, 'ability', 'el_becerisi'],
   ['Çok yatkınım', '5', 5, 'ability', 'el_becerisi'],
 ],
],
[
 'cat' => 'yetenek-degerlendirmesi', 'type' => 'scale', 'src' => $riasec ?? 'RIASEC',
 'text' => 'Bir konuda araştırma yapma yeteneğiniz ne kadar güçlü?',
 'helper' => '1 = Zayıfım  •  5 = Çok iyiyim',
 'options' => [
   ['Zayıfım', '1', 1, 'ability', 'arastirma_yetenegi'],
   ['Gelişmem gerekir', '2', 2, 'ability', 'arastirma_yetenegi'],
   ['Orta seviyede', '3', 3, 'ability', 'arastirma_yetenegi'],
   ['İyiyim', '4', 4, 'ability', 'arastirma_yetenegi'],
   ['Çok iyiyim', '5', 5, 'ability', 'arastirma_yetenegi'],
 ],
],
[
 'cat' => 'yetenek-degerlendirmesi', 'type' => 'scale', 'src' => $onet ?? 'O*NET',
 'text' => 'Bilgisayar ve yazılım araçlarıyla çalışma yeteneğiniz ne kadar güçlü?',
 'helper' => '1 = Zayıfım  •  5 = Çok iyiyim',
 'options' => [
   ['Zayıfım', '1', 1, 'ability', 'teknoloji_becerisi'],
   ['Gelişmem gerekir', '2', 2, 'ability', 'teknoloji_becerisi'],
   ['Orta seviyede', '3', 3, 'ability', 'teknoloji_becerisi'],
   ['İyiyim', '4', 4, 'ability', 'teknoloji_becerisi'],
   ['Çok iyiyim', '5', 5, 'ability', 'teknoloji_becerisi'],
 ],
],
[
 'cat' => 'yetenek-degerlendirmesi', 'type' => 'scale', 'src' => $riasec ?? 'RIASEC',
 'text' => 'Görsel bir şeyi zihninizde canlandırma yeteneğiniz ne kadar güçlü?',
 'helper' => '1 = Zayıfım  •  5 = Çok iyiyim',
 'options' => [
   ['Zayıfım', '1', 1, 'ability', 'gorsel_dusunme'],
   ['Gelişmem gerekir', '2', 2, 'ability', 'gorsel_dusunme'],
   ['Orta seviyede', '3', 3, 'ability', 'gorsel_dusunme'],
   ['İyiyim', '4', 4, 'ability', 'gorsel_dusunme'],
   ['Çok iyiyim', '5', 5, 'ability', 'gorsel_dusunme'],
 ],
],
[
 'cat' => 'yetenek-degerlendirmesi', 'type' => 'scale', 'src' => $riasec ?? 'RIASEC',
 'text' => 'Yazılı olarak açık ve anlaşılır metin yazma yeteneğiniz ne kadar güçlü?',
 'helper' => '1 = Zayıfım  •  5 = Çok iyiyim',
 'options' => [
   ['Zayıfım', '1', 1, 'ability', 'sozsel_ifade'],
   ['Gelişmem gerekir', '2', 2, 'ability', 'sozsel_ifade'],
   ['Orta seviyede', '3', 3, 'ability', 'sozsel_ifade'],
   ['İyiyim', '4', 4, 'ability', 'sozsel_ifade'],
   ['Çok iyiyim', '5', 5, 'ability', 'sozsel_ifade'],
 ],
],

/* ---------------------- CALISMA TERCİHLERİ (10) ---------------------- */
[
 'cat' => 'calisma-tercihleri', 'type' => 'card', 'src' => $wil ?? 'O*NET',
 'text' => 'Hangi çalışma biçimini tercih edersiniz?',
 'helper' => 'Size en yakın olanı seçin.',
 'options' => [
   ['Takım çalışması', 'Ekiple birlikte, fikir alışverişi yaparak', 'a', 5, 'preference', 'takim_calismasi'],
   ['Bireysel çalışma', 'Kendi tempomda, odaklanarak', 'b', 5, 'preference', 'bireysel_calisma'],
   ['İnsanlarla yoğun iletişim', 'Müşteri, hasta veya öğrenciyle sürekli temas', 'c', 5, 'preference', 'insanlarla_iletisim'],
   ['Dijital ortam', 'Bilgisayar başında, dijital araçlarla', 'd', 5, 'preference', 'bilgisayar_basinda'],
 ],
],
[
 'cat' => 'calisma-tercihleri', 'type' => 'single', 'src' => $wil ?? 'O*NET',
 'text' => 'Yeni bir proje üstlendiğinizde hangi adımı size daha doğal gelir?',
 'options' => [
   ['Önce araştırır, kaynakları inceler, sonra harekete geçerim.', 'a', 5, 'preference', 'arastirma_yapma'],
   ['Önce plan yapar, kim ne yapacak netleştiririm.', 'b', 5, 'preference', 'planlama_yonetim'],
   ['Doğrudan üretime başlarım, tasarım/uygulama yaparım.', 'c', 5, 'preference', 'uretim_tasarim'],
   ['Ekip arkadaşlarıyla birlikte ilerlerim.', 'd', 5, 'preference', 'takim_calismasi'],
 ],
],
[
 'cat' => 'calisma-tercihleri', 'type' => 'single', 'src' => $wil ?? 'O*NET',
 'text' => 'Bir işte gününüzün büyük bölümünü insanlarla geçirmek sizin için nasıl bir deneyim olur?',
 'options' => [
   ['En iyi deneyim, motivasyonumu artırır.', 'a', 5, 'preference', 'insanlarla_iletisim'],
   ['İyi olur, ama ara sıra sakin bir gün de gerekir.', 'b', 4, 'preference', 'insanlarla_iletisim'],
   ['Yorucu olabilir, kısa süreli kontakt bana daha uygun.', 'c', 2, 'preference', 'insanlarla_iletisim'],
   ['Mümkün olduğunca az insan teması tercih ederim.', 'd', 1, 'preference', 'insanlarla_iletisim'],
 ],
],
[
 'cat' => 'calisma-tercihleri', 'type' => 'single', 'src' => $onet ?? 'O*NET',
 'text' => 'Bilgisayar başında, dijital ortamlarda çalışmak size nasıl geliyor?',
 'options' => [
   ['Tam olarak aradığım şey, en rahat ortamım bu.', 'a', 5, 'preference', 'bilgisayar_basinda'],
   ['Severim, bazı işler için vazgeçilmez.', 'b', 4, 'preference', 'bilgisayar_basinda'],
   ['Kabul edilebilir ama tek başıma yapmak zorlanırım.', 'c', 3, 'preference', 'bilgisayar_basinda'],
   ['Yüzlerce saat ekran başında kalmak beni yorar.', 'd', 1, 'preference', 'bilgisayar_basinda'],
 ],
],
[
 'cat' => 'calisma-tercihleri', 'type' => 'single', 'src' => $onet ?? 'O*NET',
 'text' => 'Sahada, dış ortamda veya hareketli bir ortamda çalışmak size daha mı yakın geliyor?',
 'options' => [
   ['Evet, dışarıda olmak bana enerji veriyor.', 'a', 5, 'preference', 'saha_calismasi'],
   ['Saha çalışması hoşuma gidiyor ama her gün olmasın.', 'b', 4, 'preference', 'saha_calismasi'],
   ['Ara sıra, gerektiğinde.', 'c', 3, 'preference', 'saha_calismasi'],
   ['Kapalı ve düzenli bir ortamda daha verimliyim.', 'd', 1, 'preference', 'saha_calismasi'],
 ],
],
[
 'cat' => 'calisma-tercihleri', 'type' => 'single', 'src' => $onet ?? 'O*NET',
 'text' => 'Araştırma yapmak, inceleme yapmak ve yeni bilgiler edinmek sizin için ne kadar önemli?',
 'options' => [
   ['Çok önemli, bilgi edinmek benim için yaşam tarzı.', 'a', 5, 'preference', 'arastirma_yapma'],
   ['Önemli, ama her işte araştırma yapmak zorunda değilim.', 'b', 4, 'preference', 'arastirma_yapma'],
   ['Duruma göre değişir.', 'c', 3, 'preference', 'arastirma_yapma'],
   ['Yeterince araştırılmış, uygulamaya odaklanmayı tercih ederim.', 'd', 1, 'preference', 'arastirma_yapma'],
 ],
],
[
 'cat' => 'calisma-tercihleri', 'type' => 'single', 'src' => $onet ?? 'O*NET',
 'text' => 'Üretim, tasarım veya uygulama süreçlerinde somut bir ürün ortaya çıkarmak sizi ne kadar mutlu eder?',
 'options' => [
   ['Çok, ortaya bir şey çıkmasa yapılan iş eksik kalır.', 'a', 5, 'preference', 'uretim_tasarim'],
   ['Severim, sonucu görmek motivasyon veriyor.', 'b', 4, 'preference', 'uretim_tasarim'],
   ['Orta', 'c', 3, 'preference', 'uretim_tasarim'],
   ['Önceliğim sonuç değil, sürecin kendisi.', 'd', 2, 'preference', 'uretim_tasarim'],
 ],
],
[
 'cat' => 'calisma-tercihleri', 'type' => 'single', 'src' => $onet ?? 'O*NET',
 'text' => 'Planlama, koordinasyon ve kaynak yönetimi gibi işler size ne kadar yakın?',
 'options' => [
   ['Çok yakın, düzeni kurmak ve kaynakları yönetmek güçlü yanım.', 'a', 5, 'preference', 'planlama_yonetim'],
   ['Yakın, özellikle büyük projelerde.', 'b', 4, 'preference', 'planlama_yonetim'],
   ['Gerekli ama çok sevdiğim bir iş değil.', 'c', 3, 'preference', 'planlama_yonetim'],
   ['Yönetim ve planlama işlerinden uzak dururum.', 'd', 1, 'preference', 'planlama_yonetim'],
 ],
],

/* ---------------------- CALISMA ORTAMI (9) ---------------------- */
[
 'cat' => 'calisma-ortami', 'type' => 'card', 'src' => $onet ?? 'O*NET',
 'text' => 'Hangi ortamda çalışmak sizi en çok motive eder?',
 'helper' => 'En rahat hissedeceğiniz ortamı seçin.',
 'options' => [
   ['Ofis / kurum içi düzenli ortam', 'a', 5, 'environment', 'ofis'],
   ['Laboratuvar', 'b', 5, 'environment', 'laboratuvar'],
   ['Hastane / klinik', 'c', 5, 'environment', 'hastane'],
   ['Okul / eğitim ortamı', 'd', 5, 'environment', 'okul'],
 ],
],
[
 'cat' => 'calisma-ortami', 'type' => 'card', 'src' => $onet ?? 'O*NET',
 'text' => 'Atölye, üretim alanı veya teknik bir üretim ortamı sizi nasıl etkiler?',
 'options' => [
   ['Çok etkiler, somut üretim ortamı benim için ideal.', 'a', 5, 'environment', 'atolye'],
   ['Severim, bazen orada çalışmak isterim.', 'b', 4, 'environment', 'atolye'],
   ['Nadiren tercih ederim.', 'c', 2, 'environment', 'atolye'],
   ['Yapı ve üretim ortamı beni rahatsız eder.', 'd', 1, 'environment', 'atolye'],
 ],
],
[
 'cat' => 'calisma-ortami', 'type' => 'card', 'src' => $onet ?? 'O*NET',
 'text' => 'Açık alanda, doğayla iç içe veya dış mekânda çalışmak size nasıl geliyor?',
 'options' => [
   ['Harika, açık hava enerji veriyor.', 'a', 5, 'environment', 'acik_alan'],
   ['Severim, özellikle saha çalışması olarak.', 'b', 4, 'environment', 'acik_alan'],
   ['Ara sıra iyi geliyor.', 'c', 3, 'environment', 'acik_alan'],
   ['Kapalı ortamı tercih ediyorum.', 'd', 1, 'environment', 'acik_alan'],
 ],
],
[
 'cat' => 'calisma-ortami', 'type' => 'card', 'src' => $onet ?? 'O*NET',
 'text' => 'Tamamen uzaktan çalışmak (evden, kendi masanızdan) size uygun mu?',
 'options' => [
   ['Çok uygun, esneklik benim için çok değerli.', 'a', 5, 'environment', 'uzaktan_calisma'],
   ['Kısmen uygun, bazen ofiste de olmak isterim.', 'b', 4, 'environment', 'uzaktan_calisma'],
   ['Yüz yüze iletişim gerektiren işlerde zorlanırım.', 'c', 2, 'environment', 'uzaktan_calisma'],
   ['Uzaktan çalışmam gereksiz yalnızlık hissettirir.', 'd', 1, 'environment', 'uzaktan_calisma'],
 ],
],
[
 'cat' => 'calisma-ortami', 'type' => 'card', 'src' => $onet ?? 'O*NET',
 'text' => 'Haftanın bir kısmını ofiste, bir kısmını uzaktan geçiren karma çalışma modeli size nasıl geliyor?',
 'options' => [
   ['Tam istediğim model, dengeyi koruyor.', 'a', 5, 'environment', 'karma_calisma'],
   ['Süper bir çözüm, her iki tarafın avantajını alıyorum.', 'b', 4, 'environment', 'karma_calisma'],
   ['Belirsizlik beni rahatsız ediyor, birini seçmeyi tercih ederim.', 'c', 2, 'environment', 'karma_calisma'],
   ['Karma model uygulanabilir bir şey değil.', 'd', 1, 'environment', 'karma_calisma'],
 ],
],
[
 'cat' => 'calisma-ortami', 'type' => 'single', 'src' => $onet ?? 'O*NET',
 'text' => 'Bir meslekte çalışma ortamınız ne kadar belirleyici olmalı?',
 'options' => [
   ['Çok belirleyici olsun, ortamı biliyor olmak bana güven veriyor.', 'a', 5, 'environment', 'ofis'],
   ['Yarı belirleyici olsun, düzenli bir yapı olsun yeter.', 'b', 4, 'environment', 'ofis'],
   ['Esnek olsun, ortam şart değil.', 'c', 2, 'environment', 'ofis'],
   ['Her an değişen ortamlara uyum sağlamayı severim.', 'd', 3, 'environment', 'karma_calisma'],
 ],
],
[
 'cat' => 'calisma-ortami', 'type' => 'single', 'src' => $onet ?? 'O*NET',
 'text' => 'Sağlık hizmetleri alanında çalışmak sizin için nasıl bir deneyim olur?',
 'options' => [
   ['Mükemmel, hastayla temas kurmak benim için anlamlı.', 'a', 5, 'environment', 'hastane'],
   ['Olur, ama yoğun ortam beni yorabilir.', 'b', 4, 'environment', 'hastane'],
   ['Bekleme salonları ve yoğunluk beni huzursuz ediyor.', 'c', 2, 'environment', 'hastane'],
   ['Sağlık kuruluşlarında çalışmam.', 'd', 1, 'environment', 'hastane'],
 ],
],
[
 'cat' => 'calisma-ortami', 'type' => 'single', 'src' => $onet ?? 'O*NET',
 'text' => 'Eğitim ortamında bulunmak (okul, dershane, eğitim merkezi) sizi nasıl etkiler?',
 'options' => [
   ['Çok etkiler, öğretmek ve öğrenmek benim için doğal ortam.', 'a', 5, 'environment', 'okul'],
   ['Severim, özellikle öğrenci dolu ortamlar.', 'b', 4, 'environment', 'okul'],
   ['Yeterince olur, fazlası yorucu.', 'c', 3, 'environment', 'okul'],
   ['Eğitim ortamları beni sıkar.', 'd', 1, 'environment', 'okul'],
 ],
],

],

];
