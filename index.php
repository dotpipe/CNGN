<?php

include('./cngn.php');

$x = new CNGN(5); 

$string = "inadeio {x0} {x1} {x2} {x3} {x4}";
$x->load_vars([0 => "f", 1 => "123", 2 => "efd", 3 => "3", 4 => "d"]);
$string = $x->stringParse($string);
echo $string . " " . sizeof($x->vars);
$x->add_vars(3);
echo "\n" . sizeof($x->vars);
$j = "000100";
$x->set_f_of("{x} - 2");
$x->set_g_of("{x} + 1");
$seq = [7,22];
$x->_n($j, $seq); 
echo $x->sigma[0];
?>