<?php

require('./cngn.php');

$x = new CNGN(5); 



$string = "inadeio {x0} {x1} {x2} {x3} {x4}";
$x->load_vars([25, "123", 2 => "efd", 3 => "3", 4 => "d"]);
$string = $x->stringParse($string, $x->vars);
echo $string . " ";
$x->add_vars(3);
// quotient rule, divide, >=, $s1 || $s2 
$j = "000100 001010 001101 100100";
$x->set_f_of('1 - 3 + {x}');
$x->set_g_of('1 + {x} + 20');
$seq = [6, 10, 35, 30, 10, 4, 5];
$x->x($j, $seq);

$x->register_fn_x(2);
$seq = ["{x2} + {x5}", 22, "{x1} + 3", 30, 10, 4, 5];
$x->load_vars($seq);
$f = [45, 62];
$x->load_fn_x(["{x0} ", "{x5} {c011001} {x2} +  {x3} + 5 + {x1}"]);
echo json_encode($x->fn_x);
//$t = $x->mathParse($x->fn_x[0], $f);
//echo "\n" . $t;
$t = $x->mathParse($x->fn_x[1], $f);
//$m = eval("return ".$x->sigma);
echo "\n\r" . $t;
echo "\n\r" . $x->condition;
?>