<?php
$ret = array(0, 0);
$max = 50;
$rand = rand(0, 1000000);
for ($i = 0; $i < $max; $i++) {
	$content = file_get_contents('http://www.kotchasan.com/projects/benchmark/index.php?action=hello&'.$rand);
	$rs = explode(':', $content);
	$ret[0]+=(double)$rs[1];
	$rand++;
}
for ($i = 0; $i < $max; $i++) {
	$content = file_get_contents('http://www.siamlearning.org/projects/benchmark/index.php?action=hello&'.$rand);
	$rs = explode(':', $content);
	$ret[1]+=(double)$rs[1];
	$rand++;
}

echo ($ret[0] / $max).' Kotchasan <a href="http://www.kotchasan.com/info.php"> PHP 7</a><br>';
echo ($ret[1] / $max).' Kotchasan <a href="http://www.siamlearning.org/info.php"> PHP 5.4</a><br>';
