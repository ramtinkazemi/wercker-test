<?php
/**
 * Created by PhpStorm.
 * User: mavperi
 * Date: 19/12/2017
 * Time: 11:18
 */

use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\CliDumper;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Output\OutputInterface;
use Illuminate\Support\Facades\Log;

if (! function_exists('array_orderby')) {
    /**
     * ref : http://www.php.net/manual/en/function.array-multisort.php#100534
     * example : $sortec = array_orderby($data, 'volume', SORT_DESC, 'edition', SORT_ASC);
     *
     * @return mixed
     */
    function array_orderby()
    {
        $args = func_get_args();
        $data = array_shift($args);
        foreach ($args as $n => $field) {
            if (is_string($field)) {
                $tmp = array();
                foreach ($data as $key => $row)
                    $tmp[$key] = $row[$field];
                $args[$n] = $tmp;
            }
        }
        $args[] = &$data;
        call_user_func_array('array_multisort', $args);
        return array_pop($args);
    }
}

if (! function_exists('array_column_sum')) {
    /**
     * @param $input
     * @param $column_key
     * @return float|int
     */
    function array_column_sum($input, $column_key)
    {
        return array_sum(array_column($input, $column_key));
    }
}

if (! function_exists('color_dump')) {

    /**
     *
     * ref : (new CliDumper())->dump((new VarCloner)->cloneVar($value));
     * example : color_dump($your_arr);
     * @TODO: It needs option not to truncate data.
     *
     * @param array ...$args
     */
    function color_dump(...$args)
    {
        foreach ($args as $x) {
            if (class_exists(CliDumper::class)) {
                $dumper = 'cli' === PHP_SAPI ? new CliDumper : new HtmlDumper;

                $cloner = new VarCloner();
                $dumper->dump($cloner->cloneVar($x));
            } else {
                var_dump($x);
            }
        }
    }
}

if (! function_exists('echo_ex')) {
    /**
     *
     * example : echo_ex('<info>'.'TEST'.'</info>');
     * @param array ...$args
     */
    function echo_ex(...$args)
    {
        $output = new BufferedOutput(OutputInterface::VERBOSITY_VERY_VERBOSE, true);
        //$style = new OutputFormatterStyle('red', 'yellow', array('bold', 'blink'));
        //$output->getFormatter()->setStyle('fire', $style);

        foreach ($args as $x) {
            //$output->writeln('<info>'.'TEST'.'</info>');
            $output->writeln($x);

        }
        echo $output->fetch();
    }
}

if (! function_exists('CRLog')) {
    /**
     *
     * example : echo_ex('<info>'.'TEST'.'</info>');
     * @param array ...$args
     */
    function CRLog($logLevel, $description, $message, $class, $method, $line)
    {
        if($logLevel == ""){
            $logLevel = "debug";
        }
        call_user_func('Log::'.$logLevel, $description, ["class" => $class,  'method' => $method, 'line'=>$line, 'message' => $message]); // >5.2.3
    }
}



if (! function_exists('getColourString')) {
    /**
     *
     * 31 red
     * 32 green
     * 34 blue
     * 33 yellow
     *
     * Some CLI colouring
     *
     * @param $string
     * @param $colourCodedefault
     * @param $colourCodeIfTrue
     * @param $val1
     * @param $val2
     * @param $operator
     * @return string
     */
    function getColourString($string, $colourCodedefault, $colourCodeIfTrue, $val1, $val2, $operator)
    {

        $colours = ['red' => 31, 'green' => 32, 'blue' => 34, 'yellow' => 33];
        if(!array_key_exists($colourCodedefault, $colours)){
            $colourCodedefault = "green";
        }
        if(!array_key_exists($colourCodeIfTrue, $colours)){
            $colourCodeIfTrue = "green";
        }
        $colourCode = $colours[$colourCodedefault];
        if ($operator == '>' && $val1 > $val2) {
            $colourCode = $colours[$colourCodeIfTrue];
        }
        if ($operator == '==' && $val1 > $val2) {
            $colourCode = $colours[$colourCodeIfTrue];
        }
        if ($operator == '<' && $val1 < $val2) {
            $colourCode = $colours[$colourCodeIfTrue];
        }
        if ($operator == '') { //just use the colour requested
            $colourCode = $colours[$colourCodeIfTrue];
        }
        $colourCode = $colourCode . "m";
        return "\033[$colourCode $string\033[0m";
    }
}


/**
 * @param $params array $params['data'][] = array('Date' => '20130320', 'Users' => '1')
 *                      $params['onduplicate'] = array('Users' => 'replace')
 *                      $params['color'] = array('Users' => '#52ff7f')
 *                      $params['type'] = array('Users' => 'line');
 *                      $params['endpoint'] example https://app.cyfe.com/api/push/5a46105ae0e663525741213915974
 *
 * docuentation https://www.cyfe.com/api for push
 */
if (! function_exists('sendToCyfe')) {
    function sendToCyfe($params, $endpoint)
    {
        /*
        $endpoint = '.../api/push/5151e3ec53783701321099125233'; //https://app.cyfe.com/api/push/5a46105ae0e663525741213915974
        $data = array();
        $data['data'][] = array('Date' => '20130320', 'Users' => '1');
        $data['onduplicate'] = array('Users' => 'replace');
        $data['color'] = array('Users' => '#52ff7f');
        $data['type'] = array('Users' => 'line');
        */

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $endpoint);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if (stripos($status, '200') !== false)
            CRLog("debug", "success posting to Cyfe", "", __CLASS__, __FUNCTION__, __LINE__);
        else
            CRLog("error", "error posting to Cyfe", "status: $status, response: ".$output, __CLASS__, __FUNCTION__, __LINE__);
    }
}

if (! function_exists('cyfeGetArraySettings')) {
    function cyfeGetArraySettings($widgetHeaders, $cumulative, $replace, $fieldToReturn)
    {
        $params = [];
        foreach ($widgetHeaders as $header) {
            $params['onduplicate'][$header] = $replace;
            $params['cumulative'][$header] = $cumulative;
        }
        return $params[$fieldToReturn];
    }
}



