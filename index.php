<?php

require('./cngn.php');

$x = new CNGN(5); 


// Use stringParse to dynamically insert data into strings
$string = "inadeio {x0} {x1} {x2} {x3} {x4}";
$x->load_vars([25, 2, 3, 4, 60]);
$string = $x->stringParse($string, $x->vars);
echo $string . "<br>";
$x->add_vars(3);

$x->set_f_of('1 - 3 + {x0}');
$x->set_g_of('1 + {x0} + 20');
$seq = [6, 10, 35, 30, 10, 4, 5];

$x->register_fn_x(2);
$seq = ["{x2} + {x6}", 22, "{x1} + 3", 30, [10, 11, -2], 4, 5];
$x->load_vars($seq);
//               don't sweat this, 011001 is the code for 'return *';
$x->load_fn_x(["{x0}", "{x5} * {c110010,0} - {x2} +  {x3} + 5 + {x1}"]);
echo "<br>". json_encode($x->fn_x);
$t = $x->mathParse($x->fn_x[1], $x->vars);
echo "<br>" . $t;
$f = [[45, -2, 16], [23, 5, 16]];
$t = $x->mathParse($x->fn_x[1], $f);
echo "<br>" . $t;

$t = $x->integrand($f[0]);
echo "<br>" . $t;

?>