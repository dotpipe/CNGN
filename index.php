<?php

include('./cngn.php');

$x = new CNGN(5); 
$j = "00001";
$x->set_f_of("{x}");
$x->set_g_of("{x}");
$seq = [11,22];
$x->_n($j, $seq); 

?>