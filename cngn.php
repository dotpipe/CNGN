<?php

    class CNGN {

        public $FO = [];
        public $sigma = 0;
        public $condition = "";
        public $results = [];
        public $messages = [];
        public $f = "";
        public $g = "";
        public $vars = [];

        function __construct($index_cnt)
        {
            $this->messages[] = "Error: " ;
            $this->load_vars($index_cnt);
        }

        public function load_vars($index_cnt)
        {
            $x = 0;
            while ($x < $index_cnt)
            {
                array_merge($this->vars, array('x' . dechex($x),false));
            }
        }

        public function add_vars($index_cnt)
        {
            $x = sizeof($this->vars);
            while (sizeof($this->vars) < $x + $index_cnt)
            {
                array_merge($this->vars, array('x' . dechex($x),false));
            }
        }

        /*
         *
         * Bit or Byte sequence is first byte
         * 
         *
         * first bit is 0 = math; or 1 = logical statement
         * 
         * Make sure to fill in $j with the right binary
         * or it will not give you the correct results.
         *
         * Each $j element will go through its conditions and math
         * and it will type out a eval for you.
         * 
         * s == single return
         * 
         */

        public function _s(string $j, array $array_numbers)
        {
            // Fill in first with 1 or 0 for math/conditionals
            // Second, if math should be 1 for high math/ 0 for arithmetic
            return ($j[0] == 1) ?
                eval($this->hi_lo($j, $array_numbers)) : 
                $this->cond($j, $array_numbers);
        } 

        /**
         * 
         * m == multiple returns;
         * 
         */
        public function _m(array $j, array $array_numbers) : array
        {
            while (sizeof($j) > 1)
            {
                // Fill in first with 1 or 0 for math/conditionals
                // Second, if math should be 1 for high math/ 0 for arithmetic
                $x = ($j[0][0] == 1) ?
                    $this->hi_lo($j, $array_numbers) :
                    $this->cond($j, $array_numbers);
                $this->results[] = ($j[0][0] == 1) ? end($x) : eval(end($x));
                array_shift($j);
            }
            return $this->results;
        }
 
        /**
         * 
         * This will be joining together conditions in if statements
         * 
         */
        public function JOIN(string &$j)
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
        public function string_parse(string $string)
        {
            if ($string == "")
            {
                $this->msg(0, 'Empty string given, try string_parse(string)\n\tUse a valid {x00} to place the variable\n\tThese are keys in $vars');
                return false;
            }
            $x = 0;
            while ($x < sizeof($this->vars) && strpos($string, "{x") !== false)
            {
                $c = "{x" . dechex($x) . "}";
                $string = str_replace($c, $this->vars["$c"], strtolower($string));
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

        /*
        *
        * Higher Math
        * 
        */
        public function hi_math(string &$j, array &$sequence) : int
        {
            if (substr($j,0,1) == "0"){
                $this->sigma = pow($sequence[0], $sequence[1]);
                $this->move($j, $sequence);
            }
            if (substr($j,0,1) == "1")
            {
                $this->sigma = $this->derivative($j,$sequence);
            }
            $j = substr($j,1);
            return $this->sigma;
        }

        /*
        *
        * Higher Math
        * 
        */
        public function hi_lo(string &$j, array &$sequence) : int
        {
            if (substr($j,0,1) == "0"){
                $this->math($j, $sequence);
            }
            if (substr($j,0,1) == "1")
            {
                $this->sigma = $this->derivative($j,$sequence);
                array_shift($sequence);
            }
            $j = substr($j,1);
            return $this->sigma;
        }

        /*
        *
        * Math
        * 
        */
        public function math(string &$j, array &$sequence)
        {
            // 00 == +
            // 01 == -
            // 10 == x
            // 11 == /
            // this function is LIFO based
            while (strlen($j) > 1 && sizeof($sequence) > 0)
            {
                if (!is_int($sequence[0]) || !is_int($sequence[0]))
                {
                    $this->msg(0, "Numeric convention not follow for function `math`");
                    return;
                }
                if (substr($j,0,2) == "00")
                    $this->sigma[] = $sequence[0] + $sequence[1];
                if (substr($j,0,2) == "01")
                    $this->sigma[] = $sequence[0] - $sequence[1];
                if (substr($j,0,2) == "10")
                    $this->sigma[] = $sequence[0] * $sequence[1];
                if (substr($j,0,2) == "11" && $sequence[1] != 0)
                    $this->sigma[] = $sequence[0] / $sequence[1];
                $j = substr($j,0,2);
                $this->move($j, $sequence);
            }
            return $this->sigma;
        }

        public function derivative(string &$j, array &$sequence) : int
        {
            
            if (substr($j,0,3) == "000")   // s1 * s2
                $this->sigma[] = $this->sum_rule($sequence);
            else if (substr($j,0,3) == "001")   // s1 - s2
                $this->sigma[] = $this->diff_rule($sequence);
            else if (substr($j,0,3) == "010")   // s1 ^ s2
                $this->sigma[] = $this->power_rule($sequence);
            else if (substr($j,0,3) == "011")   // s1 * s2
                $this->sigma[] = $this->product_rule($sequence);
            else if (substr($j,0,3) == "100")   // s1 / s2
                $this->sigma[] = $this->quotient_rule($sequence);
            else if (substr($j,0,3) == "101")   // s1 * s2
                $this->sigma[] = $this->chain_rule($sequence);
            else
                return 0;
            $j = substr($j, 3);

            return $this->sigma;
        }

        /*
        *
        * get function of g() -- Use {x} wherever you need your variable
        * 
        */
        public function get_f_of(int $x) : string
        {
            if ($this->f == "")
            {
                $this->msg(0, "No function given, try set_f_of(string x)\n\tUse {x} to place the variable");
                return "";
            }
            $y = $x;
            if (is_int($x))
                return eval(str_replace('{x}', $y, $this->f));
            return "";
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
            if (is_int($x))
                return eval(str_replace('{x}', $y, $this->g));
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
        public function sum_rule(array &$sequence) : int
        {
            $tmp1 = $this->get_f_of($sequence[0]);
            $tmp2 = $this->get_g_of($sequence[0]);
            array_shift($sequence);
            return $this->math("00", [$tmp1, $tmp2]);
        }

        /*
        *
        * Condition d/dx [f(x)-g(x)]
        * 
        */
        public function diff_rule(array &$sequence) : int
        {
            $tmp1 = $this->get_f_of($sequence[0]);
            $tmp2 = $this->get_g_of($sequence[0]);
            array_shift($sequence);
            return $this->math("01", [$tmp1, $tmp2]);
        }

        /*
        *
        * Condition d/dx [x^n]
        * 
        */
        public function power_rule(array &$sequence) : int
        {
            $tmp = [$sequence[0] , $sequence[1]];
            $this->move($j, $sequence);
            return pow($tmp[0],$tmp[1]-1) * $tmp[1];
        }

        /*
        *
        * Condition d/dx [f(x)g(x)]
        * 
        */
        public function product_rule(array &$sequence) : int
        {

            // f'(x)                // f(x)
            $tmp_f = $this->get_f_of($sequence[0]);
            // g'(x)                // g(x)
            $tmp_g = $this->get_g_of($sequence[0]);
            
            $tmp_ff = $this->get_f_of($tmp_f);
            $tmp_gg = $this->get_g_of($tmp_g);
            $final1a = $this->math("10", [$tmp_ff, $tmp_g]);
            $final1b = $this->math("10", [$tmp_f, $tmp_gg]);
            $answer = $this->math("00", [$final1a, $final1b]);
            array_shift($sequence);
            return $answer;
        }

        /*
        *
        * Condition d/dx [f(g(x))]
        * 
        */
        public function chain_rule(array &$sequence) : int
        {
            // g'(x)                // g(x)
            $tmp_g = $this->get_g_of($sequence[0]);
            
            // f'(x)                // f(x)
            $tmp_f = $this->get_f_of($tmp_g);

            $tmp_ff = $this->get_f_of($tmp_f);
            $tmp_gg = $this->get_g_of($tmp_f);
            $answer = $this->math("10", [$tmp_ff, $tmp_gg]);
            array_shift($sequence);
            return $answer;
        }

        /*
        *
        * Condition d/dx [f(x)/g(x)]
        * 
        */
        public function quotient_rule(array &$sequence) : int
        {

            $tmp_f = $this->get_f_of($sequence[0]);
            $tmp_g = $this->get_g_of($sequence[0]);

            $tmp_ff = $this->get_f_of($tmp_f);
            $tmp_gg = $this->get_g_of($tmp_g);
            $final1a = $this->math("10", [$tmp_ff, $tmp_g]);
            $final1b = $this->math("10", [$tmp_f, $tmp_gg]);
            $final2 = $this->math("01", [$final1a, $final1b]);
            $answer = $this->math("11", [$final2, $this->hi_math("00", [2, $tmp_g])]);
            array_shift($sequence);
            return $answer;
        }

        /*
        *
        * Condition
        * 
        */
        public function cond(string &$j, array &$sequence)
        {
            // condition statement
            // > is 00   -  < is 111
            // >= is 11  -  >= is 101
            // == is 010  -  != is 001
            while (strlen($j) > 5 && sizeof($sequence) > 1)
            {
                if ("000" == substr($j,0,3))
                    $this->condition += $sequence[0] > $sequence[1];
                else if ("001" == substr($j,0,3))
                    $this->condition += $sequence[0] < $sequence[1];
                else if ("010" == substr($j,0,3))
                    $this->condition += $sequence[0] <= $sequence[1];
                else if ("011" == substr($j,0,3))
                    $this->condition += $sequence[0] >= $sequence[1];
                else if ("100" == substr($j,0,3))
                    $this->condition += $sequence[0] == $sequence[1];
                else if ("101" == substr($j,0,3))
                    $this->condition += $sequence[0] != $sequence[1];
                else
                    return 0;
                $j = substr($j,3);
                if (strlen($j) >= 3 && sizeof($sequence) > 2)
                    $this->JOIN($j);
                $this->move($j, $sequence);
            }
            return 1;
        }

        /*
        *
        * Move
        * 
        */
        public function move(string &$j, array &$sequence)
        {
            if ($j[0] == "1")
                array_unshift($this->FO,[$sequence[0], $sequence[1]]);
            else if ($j[0] == "0")
                array_push($this->FO,[$sequence[0], $sequence[1]]);
            $j = substr($j,1);
            array_shift($sequence);
            array_shift($sequence);
        }
    }
?>