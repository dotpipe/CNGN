<?php

    class CNGN {

        public $FO = [];
        public $sigma = "";
        public $condition = "";
        public $results = [];
        public $messages = [];
        public $x_of = [];
        public $fn_x = [];
        public $f = "";
        public $g = "";
        public $vars;
        public $seq = [];
        function __construct(int $index_cnt)
        {
            $this->messages[] = "Error: " ;
            $this->register_vars($index_cnt);
        }

        public function string_replace_x($replacements, $template) {
            return preg_replace_callback('/{(.+?)}/',
                     function($matches) use ($replacements) {
                return $replacements[$matches[1]];
            }, $template);
        }

        public function string_replace_n($replacements, $template) {
            return preg_replace_callback('/{z(.+?)}/',
                     function($matches) use ($replacements) {
                return $replacements[$matches[1]];
            }, $template);
        }

        public function string_replace_b(string $template, array $sequence) {
            $this->seq = $sequence;
            return preg_replace_callback('/{c(.+?)}/',
                     function($matches) {
                return $this->x($matches[1], $this->seq);
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

        public function load_fn_x(array $placements) : void
        {
            foreach ($placements as $k => $v)
            {
                $hex = dechex($k);
                $this->fn_x[$hex] = $v;
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

        public function register_fn_x($index_cnt)
        {
            $x = 0;
            while ($x < $index_cnt)
            {
                $hex = dechex($x);
                $this->fn_x[$hex] = false;
                $x++;
            }
        }

        public function add_vars(int $index_cnt)
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

        public function add_fn_x(int $index_cnt)
        {
            $x = count($this->fn_x);
            $s = $x;
            do
            {
                $hex = dechex($s);
                $this->fn_x[$hex] = false;
                $s++;
            } while ($s < $x + $index_cnt);
        }

        /*
        *
        * Parse string of {xFA} x-hex values
        * and replace with $vars values 
        * 
        */
        public function mathParse(string $formula, array $sequence = [])
        {
            if ($formula == "")
            {
                $this->msg(0, 'Empty string given, try mathParse(string)\n\tUse a valid {x00} to place the variable\n\tThese are keys in $vars');
                return false;
            }
            $string = $formula;
            $x = 0;
            while (strpos($string, "{c") !== false)
            {
                $string = $this->string_replace_b($string, $this->vars);
                //$this->x($string, $sequence);
                $string .= " " . $this->sigma;
                
            }echo " " . $string;
            while (strpos($string, "{z") !== false)
            {
                $string = $this->string_replace_n($this->vars, $string);
                //echo json_encode($string);
            }
            while (strpos($string, "{x") !== false)
            {
                $string = $this->stringParse($string, $this->vars);
                //echo json_encode($string);
            }
            return eval("return $string;");
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
                $this->msg(0, 'Empty string given, try stringParse(string)\n\tUse a valid {x00} to place the variable\n\tThese are keys in $vars');
                return false;
            }
            while (strpos($string, "{x") !== false)
            {
                $string = $this->string_replace_x($this->vars, $string);
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

        /**
         * the X function. Because the other letters are dumb.
         * 
         * use a space between each binary command
         * 
         */
        public function x(string $j, array $sequence)
        {
            $j = explode(" ", $j);
            // array_unshift($sequence, "0");
            while (count($j) > 0)
            {
                $t = $j[0];
                array_shift($j);
                // array_shift($sequence);
                if ($t == "000000")   // s1 * s2 
                {
                    return cosh($sequence[0]);
                    
                    continue;
                }
                if ($t == "000001")   // s1 * s2 
                {
                    return cos($sequence[0]);
                    continue;
                }
                if ($t == "000010")   // s1 * s2 
                {
                    return sinh($sequence[0]);
                    continue;
                }
                if ($t == "000011")   // s1 * s2 
                {
                    return sin($sequence[0]);
                    continue;
                }
                if ($t == "000100")   // s1 * s2 
                {
                    return tanh($sequence[0]);
                    continue;
                }
                if ($t == "000101")   // s1 * s2 
                {
                    return tan($sequence[0]);
                    continue;
                }
                if ($t == "000110")   // secant
                {
                    return 1 / sin($sequence[0]);
                    continue;
                }
                if ($t == "000111")   // cosecant
                {
                    return 1 / cos($sequence[0]);
                    continue;
                }
                if ($t == "001000")   // cotangent
                {
                    return 1 / tan($sequence[0]);
                    continue;
                }
                if ($t == "001001")   // arcsine
                {
                    return asin($sequence[0]);
                    continue;
                }
                if ($t == "001010")   // arccosine
                {
                    return acos($sequence[0]);
                    continue;
                }
                if ($t == "001011")   // arctangent
                {
                    return atan($sequence[0]);
                    continue;
                }
                if ($t == "001100")   // inverse sine
                {
                    return 1 / (1 / cos($sequence[0]));
                    continue;
                }
                if ($t == "001101")   // inverse cosine
                {
                    return sin($sequence[0]) / cos($sequence[0]);
                    continue;
                }
                if ($t == "001110")   // inverse cotangent
                {
                    return cos($sequence[0]) / sin($sequence[0]);
                    continue;
                }
                if ($t == "001111")   // constant rule
                {
                    return 0;
                    continue;
                }
                if ($t == "010000")   // s1 * s2 
                {
                    return $this->sum_rule($sequence[0]);
                    continue;
                }
                else if ($t == "010001")   // s1 - s2
                {
                    return $this->diff_rule($sequence[0]);
                    continue;
                }
                else if ($t == "010010" && sizeof($sequence) >= 2)   // s1 ^ s2
                    return $this->power_rule(array_slice($sequence,0,2));
                else if ($t == "010011")   // s1 * s2
                {
                    return $this->product_rule($sequence[0]);
                    continue;
                }
                else if ($t == "010100")   // s1 / s2
                {
                    return $this->quotient_rule($sequence[0]);
                    continue;
                }
                else if ($t == "010101")   // s1 * s2
                {
                    return $this->chain_rule($sequence[0]);
                    continue;
                }
                else if ($t == "010110")   // ^2
                    return pow($sequence[0], $sequence[1]);
                else if ($t == "010111")   // s1 + s2
                {
                    return " + ";
                }
                else if ($t == "011000")   // s1 - s2
                {
                    return " - ";
                }
                else if ($t == "011001")   // s1 * s2
                {
                    return " * ";
                }
                else if ($t == "011010")   // denominator
                {
                    return " / ";
                }
                else if ($t == "011100")   // s1 > s2
                    $this->condition .= ($sequence[0] > $sequence[1]);
                else if ($t == "011101")   // s1 < s2
                    $this->condition .= ($sequence[0] < $sequence[1]);
                else if ($t == "011110")   // s1 * s2
                    $this->condition .= ($sequence[0] >= $sequence[1]);
                else if ($t == "011111")   // s1 >= s2
                    $this->condition .= ($sequence[0] <= $sequence[1]);
                else if ($t == "100000")   // s1 != s2
                    $this->condition .= ($sequence[0] != $sequence[1]);
                else if ($t == "100001")   // s1 != s2
                    $this->condition .= ($sequence[0] == $sequence[1]);
                else if ($t == "100010")   // s1 && s2
                    $this->condition .= ((bool)substr($this->condition,-1) && $sequence[0] == $sequence[1]);
                else if ($t == "100011")   // s1 && s2
                    $this->condition .= ((bool)substr($this->condition,-1) && $sequence[0] != $sequence[1]);
                else if ($t == "100100")   // s1 && s2
                    $this->condition .= ((bool)substr($this->condition,-1) && $sequence[0] > $sequence[1]);
                else if ($t == "100101")   // s1 && s2
                    $this->condition .= ((bool)substr($this->condition,-1) && $sequence[0] < $sequence[1]);
                else if ($t == "100110")   // s1 && s2
                    $this->condition .= ((bool)substr($this->condition,-1) && $sequence[0] >= $sequence[1]);
                else if ($t == "100111")   // s1 && s2
                    $this->condition .= ((bool)substr($this->condition,-1) && $sequence[0] <= $sequence[1]);
                else if ($t == "101000")   // s1 || s2
                    $this->condition .= ((bool)substr($this->condition,-1) || $sequence[0] == $sequence[1]);
                else if ($t == "101001")   // s1 || s2
                    $this->condition .= ((bool)substr($this->condition,-1) || $sequence[0] != $sequence[1]);
                else if ($t == "101010")   // s1 || s2
                    $this->condition .= ((bool)substr($this->condition,-1) || $sequence[0] > $sequence[1]);
                else if ($t == "101011")   // s1 || s2
                    $this->condition .= ((bool)substr($this->condition,-1) || $sequence[0] < $sequence[1]);
                else if ($t == "101100")   // s1 || s2
                    $this->condition .= ((bool)substr($this->condition,-1) || $sequence[0] >= $sequence[1]);
                else if ($t == "101101")   // s1 || s2
                    $this->condition .= ((bool)substr($this->condition,-1) || $sequence[0] <= $sequence[1]);
                else if ($t == "101110")   // s1 ^ s2
                    $this->condition .= ((bool)substr($this->condition,-1) ^ $sequence[0] == $sequence[1]);
                else if ($t == "101111")   // s1 ^ s2
                    $this->condition .= ((bool)substr($this->condition,-1) ^ $sequence[0] != $sequence[1]);
                else if ($t == "110000")   // s1 ^ s2
                    $this->condition .= ((bool)substr($this->condition,-1) ^ $sequence[0] > $sequence[1]);
                else if ($t == "110001")   // s1 ^ s2
                    $this->condition .= ((bool)substr($this->condition,-1) ^ $sequence[0] < $sequence[1]);
                else if ($t == "110010")   // s1 ^ s2
                    $this->condition .= ((bool)substr($this->condition,-1) ^ $sequence[0] >= $sequence[1]);
                else if ($t == "110011")   // s1 ^ s2
                    $this->condition .= ((bool)substr($this->condition,-1) ^ $sequence[0] <= $sequence[1]);
                else if ($t == "110100")    // factorial
                {
                    return $this->mathFact($sequence[0]);
                    continue;
                }
                else if ($t == "110101")   // ln()
                {
                    return exp($sequence[0]);
                    continue;
                }
                else if ($t == "110110")   // ln()
                {
                    return log($sequence[0]);
                    continue;
                }
                else if ($t == "110111")   // log_base()
                    return log($sequence[0], $sequence[1]);
                array_shift($sequence);
            }
            echo $this->sigma . "\n\n";
            if (strlen($this->sigma) > 0)
                return eval("return $this->sigma;");
        }

        /**
         * 
         * Factorials
         * 
         */
        function mathFact( $s )
        {
            $r = (int) $s;

            if ( $r < 2 )
                $r = 1;
            else {
                for ( $i = $r-1; $i > 1; $i-- )
                    $r = $r * $i;
            }

            return( $r );
        }
        /*
        *
        * get function of g() -- Use {x} wherever you need your variable
        * 
        */
        public function get_f_of(float $x)
        {
            if ($this->f == "")
            {
                $this->msg(0, "No function given, try set_f_of(string x)\n\tUse {x} to place the variable.");
                exit(0);
            }
            $arry = array("x" => $x);
            $v = ($this->string_replace_x($arry, $this->f));
            return eval("return $v;");
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
        public function get_g_of(float $x)
        {
            if ($this->g == "")
            {
                $this->msg(0, "No function given, try set_g_of(string x)\n\tUse {x} to place the variable");
                exit(0);
            }
            $arry = array("x" => $x);
            $v = ($this->string_replace_x($arry, $this->g));

            return eval("return $v;");
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
        public function sum_rule(int $sequence)
        {
            $tmp1 = $this->get_f_of((float)$sequence);
            $tmp2 = $this->get_g_of((float)$sequence);

            return $tmp1 + $tmp2;
        }

        /*
        *
        * Condition d/dx [f(x)-g(x)]
        * 
        */
        public function diff_rule(int $sequence)
        {
            $tmp1 = $this->get_f_of((float)$sequence);
            $tmp2 = $this->get_g_of((float)$sequence);
            
            return $tmp1 - $tmp2;
        }

        /*
        *
        * Condition d/dx [x^n]
        * 
        */
        public function power_rule(array $sequence)
        {
            $tmp = $sequence;

            return (float)(pow((int)$tmp[0],(int)$tmp[1]-1) * (float)$tmp[1]);
        }

        /*
        *
        * Condition d/dx [f(x)g(x)]
        * 
        */
        public function product_rule(float $sequence)
        {

            // f'(x)                // f(x)
            $tmp_f = $this->get_f_of((float)$sequence);
            // g'(x)                // g(x)
            $tmp_g = $this->get_g_of((float)$sequence);
            
            $tmp_ff = $this->get_f_of((float)$tmp_f);
            $tmp_gg = $this->get_g_of((float)$tmp_g);
            $final1a = $tmp_ff * $tmp_g;
            $final1b = $tmp_f * $tmp_gg;
            return $final1b + $final1a;
        }

        /*
        *
        * Condition d/dx [f(g(x))]
        * 
        */
        public function chain_rule(int $sequence)
        {
            
            // g'(x)                // g(x)
            $tmp_g = (float)($this->get_g_of($sequence));
            
            // f'(x)                // f(x)
            $tmp_f = (float)($this->get_f_of($tmp_g));

            $tmp_ff = ($this->get_f_of($tmp_f));
            $tmp_gg = ($this->get_g_of($tmp_f));

            return $tmp_ff * $tmp_gg;
        }


        /*
        *
        * Condition d/dx [f(x)/g(x)]
        * 
        */
        public function quotient_rule(int $sequence)
        {

            $tmp_f = (float)$this->get_f_of((float)$sequence);
            $tmp_g = (float)$this->get_g_of((float)$sequence);

            $tmp_ff = (float)$this->get_f_of($tmp_f);
            $tmp_gg = (float)$this->get_g_of($tmp_g);
            
            $final1a = $tmp_ff * $tmp_g;
            $final1b = $tmp_f * $tmp_gg;
            
            $final2 = $final1a * $final1b;
            $answer = $final2 / ($tmp_g * $tmp_g);
            return ($answer);
        }
    }
?>