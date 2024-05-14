<?php
//$kontent = file_get_contents ('https://www.cbr.ru/scripts/XML_daily.asp?date_req=02/03/2002'); // почитать

//$date = $_GET['date']; // $_GET супер глобальная переменная
//echo $kontent;

/*try
{
	$conn = new PDO("mysql:host=localhost;dbname=myBase1", "root", ""); // подключаемся к серверу
	$sql = "SELECT * FROM Users";
    $result = $conn->query($sql);
    echo "<table><tr><th>Id</th><th>Name</th><th>Age</th></tr>";
    while($row = $result->fetch()){
        echo "<tr>";
            echo "<td>" . $row["id"] . "</td>";
            echo "<td>" . $row["name"] . "</td>";
            echo "<td>" . $row["age"] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}
catch (PDOException) {
	echo "Database error:";
}*/

if (isset($_GET['date'])) {
    $date = $_GET['date'];
    $dataObjects = DateTime::createFromFormat('d.m.Y', $date);
    if ($dataObjects === false) {
        echo 'Введите дату в формате 01.01.2001';
        exit;
    }
    $date = $dataObjects->format('d/m/Y');  
} else {
    $date = date('d/m/Y');
}

$url = 'https://www.cbr.ru/scripts/XML_daily.asp?date_req='.$date;
$kontent = file_get_contents($url);
//$kontent = iconv('windows-1251', 'UTF-8', $kontent);

$xml = simplexml_load_string($kontent);

$table = [];

foreach($xml->Valute as $value) {
    $name = (string)$value->Name;
    $code = (string)$value->CharCode;
    $rate = (string)$value->VunitRate;
    $rate = str_replace(',','.', $rate);
    $rate = (float)$rate;
    $table[] = [
        'name' => $name,
        'code' => $code,
        'rate' => $rate,
    ];
}
var_dump($table);

$delimiter = ';';
$csv = fopen('file1.csv', 'w+');
fprintf($csv, chr(0xEF) . chr(0xBB) . chr(0xBF));
fputcsv($csv, [
    'Наименование валюты',
    'Код валюты',
    'Курс валюты',
], $delimiter);
foreach($table as $currency) {
    $rate = (string)$currency['rate'];
    $rate = str_replace('.', ',', $rate);
    fputcsv($csv, [
        $currency ['name'],
        $currency ['code'],
        $rate,
    ], $delimiter);
}

fclose($csv);

