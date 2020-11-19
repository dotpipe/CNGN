<?php

    class CNGN {

        public $FO = [];
        public $sigma = [];
        public $condition = "";
        public $results = [];
        public $messages = [];
        public $f = "";
        public $g = "";
        public $vars;

        function __construct($index_cnt)
        {
            $this->messages[] = "Error: " ;
            $this->register_vars($index_cnt);
            
        }

        public function string_replace($replacements, $template) {
            return preg_replace_callback('/{(.+?)}/',
                     function($matches) use ($replacements) {
                return $replacements[$matches[1]];
            }, $template);
        }

        public function load_vars(array $placements) : void
        {
            foreach ($placements as $k => $v)
            {
                $hex = 'x' . dechex($k);
                $this->vars[$hex] = $v;
            }
            return;
        }

        public function register_vars($index_cnt)
        {
            $x = 0;
            while ($x < $index_cnt)
            {
                $hex = 'x' . dechex($x);
                $this->vars[$hex] = false;
                $x++;
            }
        }

        public function add_vars($index_cnt)
        {
            $x = count($this->vars);
            $s = $x;
            do
            {
                $hex = 'x' . dechex($s);
                $this->vars[$hex] = false;
                $s++;
            } while ($s < $x + $index_cnt);
        }

        /**
         * 
         * This will be joining together conditions in if statements
         * 
         */
        public function join(string $j)
        {
            if (substr($j,0,2) == "00")
                $this->condition += "&&";
            if (substr($j,0,2) == "01")
                $this->condition += "||";
            if (substr($j,0,2) == "10")
                $this->condition += "^";
            $j = substr($j,2);
            return;
        }

        /*
        *
        * Parse string of {xFA} x-hex values
        * and replace with $vars values 
        * 
        */
        public function stringParse(string $string)
        {
            if ($string == "")
            {
                $this->msg(0, 'Empty string given, try string_parse(string)\n\tUse a valid {x00} to place the variable\n\tThese are keys in $vars');
                return false;
            }
            $x = 0;
            while (strpos($string, "{x") !== false)
            {
                $c = 'x' . dechex($x);
                $string = $this->string_replace($this->vars, $string);
                $x++;
            }

            return $string;
        }

        /*
        *
        * Echo message at $msg_id
        * 
        */
        public function msg(int $msg_id, string $arb_msg = "")
        {
            echo $this->messages[$msg_id] . $arb_msg;
            return;
        }

        public function _n(string $j, array $sequence)
        {
            while (strlen($j) > 4)
            {
                if (bindec(substr($j,0,5)) < 8 && sizeof($sequence) > 1)
                {
                    if (substr($j,0,5) == "00000")   // s1 * s2
                        $this->sigma[] = $this->sum_rule(array_slice($sequence,0,1));
                    else if (substr($j,0,5) == "00001")   // s1 - s2
                        $this->sigma[] = $this->diff_rule(array_slice($sequence,0,1));
                    else if (substr($j,0,5) == "00010" && sizeof($sequence) >= 2)   // s1 ^ s2
                    {
                        $this->sigma[] = $this->power_rule(array_slice($sequence,0,2));
                        array_shift($sequence);
                    }                    
                    else if (substr($j,0,5) == "00011")   // s1 * s2
                        $this->sigma[] = $this->product_rule(array_slice($sequence,0,1));
                    else if (substr($j,0,5) == "00100")   // s1 / s2
                        $this->sigma[] = $this->quotient_rule(array_slice($sequence,0,1));
                    else if (substr($j,0,5) == "00101")   // s1 * s2
                        $this->sigma[] = $this->chain_rule(array_slice($sequence,0,1));
                    array_shift($sequence);
                }
                else if (bindec(substr($j,0,5)) < 13 && sizeof($sequence) > 2)
                {
                    if (substr($j,0,5) == "01000")   // ^2
                        $this->sigma[] = pow(array_slice($sequence,0,1), $sequence[1]);
                    else if (substr($j,0,5) == "01001")   // s1 + s2
                        $this->sigma[] = $sequence[0] + $sequence[1];
                    else if (substr($j,0,5) == "01010")   // s1 - s2
                        $this->sigma[] = $sequence[0] - $sequence[1];
                    else if (substr($j,0,5) == "01011")   // s1 * s2
                        $this->sigma[] = $sequence[0] * $sequence[1];
                    else if (substr($j,0,5) == "01100" && $sequence[1] !== 0)   // s1 / s2
                        $this->sigma[] = $sequence[0] / $sequence[1];
                    array_shift($sequence);
                    array_shift($sequence);
                }
                else if (bindec(substr($j,0,5)) < 20 && sizeof($sequence) > 2)
                {
                    if (substr($j,0,5) == "01101")   // s1 + s2
                        $this->condition += $sequence[0] > $sequence[1];
                    else if (substr($j,0,5) == "01110")   // s1 - s2
                        $this->condition += $sequence[0] < $sequence[1];
                    else if (substr($j,0,5) == "01111")   // s1 * s2
                        $this->condition += $sequence[0] >= $sequence[1];
                    else if (substr($j,0,5) == "10000")   // s1 / s2
                        $this->condition += $sequence[0] > $sequence[1];
                    else if (substr($j,0,5) == "10001")   // s1 - s2
                        $this->condition += $sequence[0] <= $sequence[1];
                    else if (substr($j,0,5) == "10010")   // s1 * s2
                        $this->condition += $sequence[0] == $sequence[1];
                    else if (substr($j,0,5) == "10011")   // s1 / s2
                        $this->condition += $sequence[0] != $sequence[1];
                    array_shift($sequence);
                    array_shift($sequence);
                }
                else if (bindec(substr($j,0,5)) < 22)
                {
                    if (substr($j,0,5) == "10100")   // s1 + s2
                        $this->get_f_of((int)array_slice($sequence,0,1));
                    else if (substr($j,0,5) == "10101")
                        $this->get_g_of((int)array_slice($sequence,0,1));
                    array_shift(array_slice($sequence,0,1));
                }
                else
                {
                    $this->msg(0, "No code for " . substr($j,0,5) . ". Calls in this function go 0 thru 21 (00000-10101).");
                    return 0;
                }
                $j = substr($j,5);

            }
        }

        /*
        *
        * get function of g() -- Use {x} wherever you need your variable
        * 
        */
        public function get_f_of(int $x)
        {
            if ($this->f == "")
            {
                $this->msg(0, "No function given, try set_f_of(string x)\n\tUse {x} to place the variable.");
                exit(0);
            }
            $y = $x;
            if (is_array($x))
                return eval($this->string_replace(['x' => $y], $this->f));
            
            exit(0);
        }

        /*
        *
        * set function of f() -- Use {x} wherever you need your variable
        * 
        */
        public function set_f_of(string $ev)
        {
            $this->f = $ev;
        }

        /*
        *
        * get function of g() -- Use {x} wherever you need your variable
        * 
        */
        public function get_g_of(int $x)
        {
            if ($this->g == "")
            {
                $this->msg(0, "No function given, try set_g_of(string x)\n\tUse {x} to place the variable");
                return;
            }
            $y = $x;
            if (is_array($x))
                return eval($this->string_replace(['x' => $y], $this->g));
        }

        /*
        *
        * set function of g()
        * 
        */
        public function set_g_of(string $ev)
        {
            $this->g = $ev;
        }

        /*
        *
        * Condition d/dx [f(x)+g(x)]
        * 
        */
        public function sum_rule(array $sequence)
        {
            $tmp1 = $this->get_f_of((int)array_slice($sequence,0,1));
            $tmp2 = $this->get_g_of((int)array_slice($sequence,0,1));
            $j = "01001";
            $v = [$tmp1, $tmp2];
            return $this->_n($j, $v);
        }

        /*
        *
        * Condition d/dx [f(x)-g(x)]
        * 
        */
        public function diff_rule(array $sequence)
        {
            $tmp1 = $this->get_f_of((int)array_slice($sequence,0,1));
            $tmp2 = $this->get_g_of((int)array_slice($sequence,0,1));
            //array_shift(array_slice($sequence,0,1));
            $j = "01010";
            $v = [$tmp1, $tmp2];
            return $this->_n($j, $v);
        }

        /*
        *
        * Condition d/dx [x^n]
        * 
        */
        public function power_rule(array $sequence)
        {
            $tmp = $sequence;
            var_dump($tmp);
            return (float)(pow((int)$tmp[0],(int)$tmp[1]-1) * (float)$tmp[1]);
        }

        /*
        *
        * Condition d/dx [f(x)g(x)]
        * 
        */
        public function product_rule(array $sequence)
        {

            // f'(x)                // f(x)
            $tmp_f = $this->get_f_of((int)array_slice($sequence,0,1));
            // g'(x)                // g(x)
            $tmp_g = $this->get_g_of((int)array_slice($sequence,0,1));
            
            $tmp_ff = $this->get_f_of($tmp_f);
            $tmp_gg = $this->get_g_of($tmp_g);
            $j = "01011";
            $final1a = $this->_n($j, [$tmp_ff, $tmp_g]);
            $final1b = $this->_n($j, [$tmp_f, $tmp_gg]);
            $j = "01100";
            $answer = $this->_n($j, [$final1a, $final1b]);
            return $answer;
        }

        /*
        *
        * Condition d/dx [f(g(x))]
        * 
        */
        public function chain_rule(array $sequence)
        {
            // g'(x)                // g(x)
            $tmp_g = $this->get_g_of((int)array_slice($sequence,0,1));
            
            // f'(x)                // f(x)
            $tmp_f = $this->get_f_of($tmp_g);
            echo " " . $tmp_f;
            $tmp_ff = $this->get_f_of($tmp_f);
            $tmp_gg = $this->get_g_of($tmp_f);
            $j = "01011";
            $answer = $this->_n($j, [$tmp_ff, $tmp_gg]);
            return $answer;
        }

        /*
        *
        * Condition d/dx [f(x)/g(x)]
        * 
        */
        public function quotient_rule(array $sequence)
        {

            $tmp_f = $this->get_f_of((int)array_slice($sequence,0,1));
            $tmp_g = $this->get_g_of((int)array_slice($sequence,0,1));

            $tmp_ff = $this->get_f_of($tmp_f);
            $tmp_gg = $this->get_g_of($tmp_g);
            $j = "01011";
            $final1a = $this->_n($j, [$tmp_ff, $tmp_g]);
            $final1b = $this->_n($j, [$tmp_f, $tmp_gg]);
            $i = "01011";
            $final2 = $this->_n($i, [$final1a, $final1b]);
            $m = "01100";
            $answer = $this->_n($m, [$final2, $this->_n($m, [$tmp_g, 2])]);
            return $answer;
        }
    }
?>