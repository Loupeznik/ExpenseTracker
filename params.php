<?php
$today = date('Y-m-d');
$year = date('Y');
$month = date('m');

$servername = "localhost";
$username = "pis";
$password = "pisukol792";
$dbname = "pis_ukol";

$db = new mysqli($servername, $username, $password, $dbname);
mysqli_set_charset($db,"utf8");

function bilance_secist($db, $mesic, $typ) {
    $stmt = $db->prepare('SELECT SUM(cena) as bilance FROM items WHERE mesic = ? AND typ = ?');
    $stmt->bind_param('is', $mesic, $typ);
    $stmt->execute();
    
    $result = $stmt->get_result();
    if ($result) {
        return array_sum($result->fetch_assoc()); //query vrací array, musím konvertovat na sumu
    } else return 'Chyba';
}

function bilance_secist_rok($db, $rok, $typ) {
    $stmt = $db->prepare('SELECT SUM(cena) as bilance FROM items WHERE rok = ? AND typ = ?');
    $stmt->bind_param('is', $rok, $typ);
    $stmt->execute();
    
    $result = $stmt->get_result();
    if ($result) {
        return array_sum($result->fetch_assoc());
    } else return 'Chyba';
}

$bilance_prijmy = bilance_secist($db,$_POST['month'],'PRI');
$bilance_vydaje = bilance_secist($db,$_POST['month'],'VYD');
$bilance_celk_mesic = $bilance_prijmy - $bilance_vydaje;
$bilance_celk_rok = bilance_secist_rok($db, $year, "PRI") - bilance_secist_rok($db, $year, "VYD");