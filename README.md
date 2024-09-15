# CNGN
Computational Machine Language Engine

For a ridiculously cool look at binary coding reinvented, give this a peep. It's you, math and strings :)

Looking for other languages to be done this way. Just add a pull request!

$x = new CNGN(5);

// Use stringParse to dynamically insert data into strings

$string = "inadeio {x0} {x1} {x2} {x3} {x4}";

$x->load_vars([25, 2, 3, 4, 60]);

$string = $x->stringParse($string, $x->vars);

echo $string . "<br />";

$x->add_vars(3);

$x->set_f_of('1 - 3 + {x0}');

$x->set_g_of('1 + {x0} + 20');

$seq = [6, 10, 35, 30, 10, 4, 5];

$x->register_fn_x(2);

$seq = ["{x2} + {x6}", 22, "{x1} + 3", 30, [10, 11, -2], 4, 5, "101010"];

$x->load_vars($seq);

//               don't sweat this, 011001 is the code for 'return *';

$x->load_fn_x(["{x0}", "{x5} {c011001,0} - {x2} +  {x3} + 5 + {x1}"]);

echo json_encode($x->fn_x);

$t = $x->mathParse($x->fn_x[1], $x->vars);

$t = -28

$f = [[45, -2, 16], [23, 5, 16]];

$t = $x->mathParse($x->fn_x[1], $f);

$t = -28

$t = $x->integrand($f[0]);

$x->load_fn_x(["{c011110,1}{c011110,5}{c011110,0}","{c111011,7}"]);


Just use your imagination and the criteria to come up with the answer you require!
 
