<?php

require('./cngn.php');

$x = new CNGN(5); 



$string = "inadeio {x0} {x1} {x2} {x3} {x4}";
$x->load_vars([25, 2, 3, 4, 60]);
$string = $x->stringParse($string, $x->vars);
echo $string . " ";
$x->add_vars(3);
// quotient rule, divide, >=, $s1 || $s2 
$j = "000100 001010 001101 100100";
$x->set_f_of('1 - 3 + {x0}');
$x->set_g_of('1 + {x0} + 20');
$seq = [6, 10, 35, 30, 10, 4, 5];

$x->register_fn_x(2);
$seq = ["{x2} + {x5}", 22, "{x1} + 3", 30, 10, 4, 5];
$x->load_vars($seq);
$f = [40, 62];
$x->load_fn_x(["{x0}", "{x5} * {c010011} - {x2} +  {x3} + 5 + {x1}"]);
echo json_encode($x->fn_x);
$t = $x->mathParse($x->fn_x[0], $f);
echo "\**********" . $t;

$t = $x->mathParse($x->fn_x[1], $f);
//$m = eval("return ".$x->sigma);
echo "\n\rMMM" . $t;
echo "\n\r" . $x->condition;
?>