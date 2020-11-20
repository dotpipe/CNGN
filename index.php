<?php

require('./cngn.php');

$x = new CNGN(5); 

$string = "inadeio {x0} {x1} {x2} {x3} {x4}";
$x->load_vars([0 => "f", 1 => "123", 2 => "efd", 3 => "3", 4 => "d"]);
$string = $x->stringParse($string);
echo $string . " " . sizeof($x->vars);
$x->add_vars(3);
echo "\n" . sizeof($x->vars);
$j = "00101 01101 00111 10010";
$x->set_f_of('3 - 5 + {x}');
$x->set_g_of('1 + {x} + 10');
$seq = [5, 10, 30, 25, 40, 100, 100];
$x->_n($j, $seq);

echo " " . $x->sigma[0];
echo "\n" . $x->condition;
?>