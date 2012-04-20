<?php

header('Content-type: text/plain; charset=utf-8');
echo '** ip **'."\r\n\r\n";
$start = microtime(true);
/** start */

$dir = dirname(__FILE__).'/data/';

$ip2l = sprintf("%u", ip2long($_SERVER['REMOTE_ADDR']));

echo $ip2l."\r\n";

$f = unserialize(file_get_contents($dir.'index'));
if (!$f)
	return null;
$f = key(bisectionSearch($ip2l, $f));
if (!$f)
	return null;

$f = unserialize(file_get_contents($dir.$f));
if (!$f)
	return null;
$f = array_shift(bisectionSearch($ip2l, $f));
if (!$f)
	return null;

$handle = fopen($dir.'../cities.txt','r');
fseek($handle, $f[3]);
$f = explode('	', iconv('cp1251', 'utf-8', fgets($handle, 10000)));
fclose($handle);


print_r($f);

/** stop */
$stop = microtime(true);
echo "\r\n\r\n".'Time: '.round($stop-$start,6).' sec'."\r\n";
echo 'Memory: '.round(memory_get_peak_usage() / (1024 * 1024), 3).' Mb'."\r\n";
	
function bisectionSearch($n, $a) {
	while (count($a)>1) {
		$ta = array_chunk($a, ceil(count($a)/2), true);
		$f = key($ta[1]);
		$a = $ta[1][$f][0] <= $n ? $ta[1] : $ta[0];
	}
	return (is_array($a)) ? $a : null;
}
