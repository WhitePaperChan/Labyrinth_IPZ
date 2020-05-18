<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">

<html>
<head>
	<title>random_int</title>
</head>

<body>
<pre>
<?php
for ($i = 0; $i < 100; ++$i) {
	echo random_int(1, 4)."<br />";
}

$max = 100; // number of random values
$test = 1000000;

$array = array_fill(0, $max, 0);

for ($i = 0; $i < $test; ++$i) {
    ++$array[random_int(0, $max-1)];
    //++$array[random_int(1, 4)];
}
print_r($array);
echo random_int(1, 4);
function arrayFormatResult(&$item) {
    global $test, $max; // try to avoid this nowdays ;)
   
    $perc = ($item/($test/$max))-1;
    $item .= ' '. number_format($perc, 4, '.', '') .'%';
}

array_walk($array, 'arrayFormatResult');

print_r($array);

?>
</pre>
</body>
</html>
