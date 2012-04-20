<?php

header('Content-type: text/plain; charset=utf-8');
echo '** ip **'."\r\n\r\n";
$start = microtime(true);
/** start */

$dir = dirname(__FILE__).'/'; // задаем директорию
$file = $dir.'cidr_optim.txt'; // откуда брать данные
$handle = fopen($file, 'r'); // открываем файл
$cityf = $dir.'cities.txt'; // файл со списком регионов

/** Считаем количество строк */
$i=0;
while(fgets($handle)!==false) {
	$i++;
}

$cf = ceil(sqrt($i)); // количество дополнительных файлов, а так же количество записей в этих и в индексном файлах


// обрабатываем файл списка регионов - создаем массив соответствия номера региона соответствующиму смешению указателя файла
$carr = array(); // массив соответствий регион - смещение указателя
$hf = fopen($cityf, 'r'); // открываем файл
while(!feof($hf)) {
	$pos = ftell($hf); // смещение указателя начала строки
	$data = fgetcsv($hf, 10000, '	'); // разбитая на массив строка
	$carr[(int)$data[0]] = $pos; // элемент id региона => смещение указателя
}
fclose($hf);

/** Создаем дополнительные файлы */
$i=0;
$j=0;
rewind($handle); // на начало файла
$dir_data = $dir.'data/';
if (!is_dir($dir_data))
	mkdir($dir_data);
$f = fopen($dir_data.$j, 'w+');
$a = array();
$index = array();
while($strd = fgetcsv($handle, 10000, '	')) {
	if ($i%$cf===0) {
		$index[$j] = array($strd[0]);
	}
	$a[$i]=array(
		$strd[0], // старт
		$strd[1], // стоп
		$strd[3], // страна
		$carr[(int)$strd[4]], // смещение указателя файла со списком регионов
	);
	if ($i>0 && ($i+1)%$cf===0) {
		fwrite($f, serialize($a));
		fclose($f);
		$index[$j][1]=$strd[1];
		$j++;
		$a = array();
		$f = fopen($dir_data.$j, 'w+');
	}
	$i++;
}
fwrite($f, serialize($a));
fclose($f);
fclose($handle);
$fi = fopen($dir_data.'index', 'a+'); // создаем индексный файл
fwrite($fi, serialize($index));
fclose($fi);

/** stop */
$stop = microtime(true);
echo "\r\n\r\n".'Time: '.round($stop-$start,6).' sec'."\r\n";
echo 'Memory: '.round(memory_get_peak_usage() / (1024 * 1024), 3).' Mb'."\r\n";