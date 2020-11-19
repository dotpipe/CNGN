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
            CNGN::$messages[] = "Error: " ;
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
         */

        public static function _s(string $j, array $array_numbers)
        {
            return ($j[0][0] == 1) ?
                CNGN::math($j, $array_numbers) : 
                eval(CNGN::cond($j, $array_numbers));
        } 

        /**
         * 
         * m == multiple returns;
         * 
         */
        public static function _m(array $j, array $array_numbers) : array
        {
            while (sizeof($j) > 1)
            {
                $x = ($j[0][0] == 1) ?
                    CNGN::math($j, $array_numbers) :
                    CNGN::cond($j, $array_numbers);
                CNGN::$results[] = ($j[0][0] == 1) ? end($x) : eval(end($x));
                array_shift($j);
            }
            return CNGN::$results;
        }
 
        /**
         * 
         * This will be joining together conditions in if statements
         * 
         */

        public static function JOIN(string &$j)
        {
            if (substr($j,0,2) == "00")
                CNGN::$condition += "&&";
            if (substr($j,0,2) == "01")
                CNGN::$condition += "||";
            if (substr($j,0,2) == "10")
                CNGN::$condition += "^";
            $j = substr($j,2);
            return;
        }

        /*
        *
        * Parse string of {xFA} x-hex values
        * and replace with $vars values 
        * 
        */
        public static function string_parse(string $string) : string
        {
            if ($string == "")
            {
                CNGN::msg(0, 'Empty string given, try string_parse(string)\n\tuse a valid {x00} to place the variable\n\tThese are in $vars');
                return;
            }
            $x = 0;
            while ($x < sizeof(CNGN::$vars) && strpos($string, "{x") !== false)
            {
                $c = "{x" . dechex($x) . "}";
                $string = str_replace($c, CNGN::$vars[$c], strtolower($string));
                $x++;
            }
            return $string;
        }

        /*
        *
        * Echo message at $msg_id
        * 
        */
        public static function msg(int $msg_id, string $arb_msg = "")
        {
            echo CNGN::$messages[$msg_id] . $arb_msg;
            return;
        }

        
        /*
        *
        * Higher Math
        * 
        */
        public static function hi_math(string &$j, array $sequence) : int
        {
            if (substr($j,2) == "00")
                CNGN::$sigma = pow($sequence[0], $sequence[1]);
            if (substr($j,2) == "11")
            {
                CNGN::$sigma = CNGN::derivative($j,$sequence);
            }
            $j = substr($j,2);
            return CNGN::$sigma;
        }

        /*
        *
        * Math
        * 
        */
        public static function math(string &$j, array &$sequence) : int
        {
            // 00 == +
            // 01 == -
            // 10 == x
            // 11 == /
            // this function is LIFO based
            while (strlen($j) > 1 && sizeof($sequence) > 1)
            {
                if (!is_int($sequence[0]) || !is_int($sequence[0]))
                {
                    CNGN::msg(0, "Numeric convention not follow for function `math`");
                    return;
                }
                if (substr($j,0,2) == "00")
                    CNGN::$sigma = $sequence[0] + $sequence[1];
                if (substr($j,0,2) == "01")
                    CNGN::$sigma = $sequence[0] - $sequence[1];
                if (substr($j,0,2) == "10")
                    CNGN::$sigma = $sequence[0] * $sequence[1];
                if (substr($j,0,2) == "11" && $sequence[1] != 0)
                    CNGN::$sigma = $sequence[0] / $sequence[1];
                $j = substr($j,0,2);
                CNGN::move($j, $sequence);
            }
            return CNGN::$sigma;
        }

        public static function derivative(string &$j, int &$sequence) : int
        {
            
            if (substr($j,2) == "001")   // s1 * s2
                CNGN::$sigma = CNGN::sum_rule($sequence);
            if (substr($j,2) == "011")   // s1 - s2
                CNGN::$sigma = CNGN::diff_rule($sequence);
            if (substr($j,2) == "101")   // s1 ^ s2
                CNGN::$sigma = CNGN::power_rule($sequence);
            if (substr($j,2) == "111")   // s1 / s2
                CNGN::$sigma = CNGN::quotient_rule($sequence);
            if (substr($j,2) == "110")   // s1 * s2
                CNGN::$sigma = CNGN::product_rule($sequence);
            $j = substr($j, 2);

            return CNGN::$sigma;
        }

        /*
        *
        * get function of g() -- Use {x} wherever you need your variable
        * 
        */
        public static function get_f_of(int $x) : int
        {
            if (CNGN::$f == "")
            {
                CNGN::msg(0, "No function given, try set_f_of(string x)\n\tUse {x} to place the variable");
                return;
            }
            $y = $x;
            if (is_int($x))
                return eval(str_replace('{x}', $y, CNGN::$f));
            return;
        }

        /*
        *
        * set function of f() -- Use {x} wherever you need your variable
        * 
        */
        public static function set_f_of(string $ev)
        {
            CNGN::$f = $ev;
        }

        /*
        *
        * get function of g() -- Use {x} wherever you need your variable
        * 
        */
        public static function get_g_of(int $x)
        {
            if (CNGN::$g == "")
            {
                CNGN::msg(0, "No function given, try set_g_of(string x)\n\tUse {x} to place the variable");
                return;
            }
            $y = $x;
            return eval(str_replace('{x}', $y, CNGN::$g));
        }

        /*
        *
        * set function of g()
        * 
        */
        public static function set_g_of(string $ev)
        {
            CNGN::$g = $ev;
        }

        /*
        *
        * Condition d/dx [f(x)+g(x)]
        * 
        */
        public static function sum_rule(int &$sequence) : int
        {
            $tmp1 = CNGN::get_f_of($sequence[0]);
            $tmp2 = CNGN::get_g_of($sequence[0]);
            return CNGN::math("00", [$tmp1, $tmp2]);
        }

        /*
        *
        * Condition d/dx [f(x)-g(x)]
        * 
        */
        public static function diff_rule(int &$sequence) : int
        {
            $tmp1 = CNGN::get_f_of($sequence[0]);
            $tmp2 = CNGN::get_g_of($sequence[0]);
            return CNGN::math("01", [$tmp1, $tmp2]);
        }

        /*
        *
        * Condition d/dx [x^n]
        * 
        */
        public static function power_rule(array &$sequence) : int
        {
            $tmp = [$sequence[0] , $sequence[1]];
            CNGN::move($j, $sequence);
            return pow($tmp[0],$tmp[1]-1) * $tmp[1];
        }

        /*
        *
        * Condition d/dx [f(x)g(x)]
        * 
        */
        public static function product_rule(int $sequence) : int
        {

            // f'(x)                // f(x)
            $tmp_f = CNGN::get_f_of($sequence[0]);
            // g'(x)                // g(x)
            $tmp_g = CNGN::get_g_of($sequence[0]);
            
            $tmp_ff = CNGN::get_f_of($tmp_f);
            $tmp_gg = CNGN::get_g_of($tmp_g);
            $final1a = CNGN::math("10", [$tmp_ff, $tmp_g]);
            $final1b = CNGN::math("10", [$tmp_f, $tmp_gg]);
            CNGN::$sigma = CNGN::math("00", [$final1a, $final1b]);
            return CNGN::$sigma;
        }

        /*
        *
        * Condition d/dx [f(x)/g(x)]
        * 
        */
        public static function quotient_rule(int $sequence) : int
        {

            $tmp_f = CNGN::get_f_of($sequence[0]);
            $tmp_g = CNGN::get_g_of($sequence[0]);

            $tmp_ff = CNGN::get_f_of($tmp_f);
            $tmp_gg = CNGN::get_g_of($tmp_g);
            $final1a = CNGN::math("10", [$tmp_ff, $tmp_g]);
            $final1b = CNGN::math("10", [$tmp_f, $tmp_gg]);
            $final2 = CNGN::math("01", [$final1a, $final1b]);
            CNGN::$sigma = CNGN::math("11", [$final2, CNGN::hi_math("00", [2, $tmp_g])]);
            return CNGN::$sigma;
        }

        /*
        *
        * Condition
        * 
        */
        public static function cond(string &$j, array &$sequence)
        {
            // condition statement
            // > is 00   -  < is 111
            // >= is 11  -  >= is 101
            // == is 010  -  != is 001
            while (strlen($j) > 5 && sizeof($sequence) > 1)
            {
                if ("000" == substr($j,0,3))
                    CNGN::$condition += $sequence[0] > $sequence[1];
                else if ("111" == substr($j,0,3))
                    CNGN::$condition += $sequence[0] < $sequence[1];
                else if ("101" == substr($j,0,3))
                    CNGN::$condition += $sequence[0] <= $sequence[1];
                else if ("011" == substr($j,0,3))
                    CNGN::$condition += $sequence[0] >= $sequence[1];
                else if ("010" == substr($j,0,3))
                    CNGN::$condition += $sequence[0] == $sequence[1];
                else if ("001" == substr($j,0,3))
                    CNGN::$condition += $sequence[0] != $sequence[1];
                else
                    return 0;
                $j = substr($j,3);
                if (strlen($j) >= 6 && sizeof($sequence) > 2)
                    CNGN::JOIN($j);
                CNGN::move($j, $sequence);
            }
            return 1;
        }

        /*
        *
        * Move
        * 
        */
        public static function move(string &$j, array &$sequence)
        {
            if ($j[0] == 1)
                array_unshift(CNGN::$FO,[$sequence[0], $sequence[1]]);
            else if ($j[0] == 0)
                array_push(CNGN::$FO,[$sequence[0], $sequence[1]]);
            $j = substr($j,2);
            array_shift($sequence);
            array_shift($sequence);
        }
    }
?>