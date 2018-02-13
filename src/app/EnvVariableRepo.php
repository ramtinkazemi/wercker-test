<?php
/**
 * Created by PhpStorm.
 * User: mavperi
 * Date: 12/02/2018
 * Time: 13:52
 */

namespace App;

use Illuminate\Support\Facades\Cache;

/**
 *
 * Utility class to manage env variables of less importance to be treated as feature toggles
 *
 * Class EnvVariable
 * @package App
 */
class EnvVariableRepo
{

    public $variable;
    public $value;
    protected $m; //instance of EnvVariables

    /**
     * EnvVariable constructor.
     *
     * @param $variable
     * @param $value
     * @param $default
     */
    public function __construct($variable){
        $this->variable = $variable;
        $this->get();
    }


    public function get(){
        // check if this is a db managed env variable
        $this->m = Cache::remember('env:'.$this->variable, 5, function () {
            echo getRedString("get from db\n");
            return  \App\EnvVariables::where('variable', $this->variable)->first();
        });

        // check what value to return
        if($this->m != null){ //  value in db
            //var_dump($this->m);
            if($this->m->enabled == true){
                $this->value = $this->m->value;
            }else{ //variable not enabled even though in db
                CRLog("error", "environment variable not enabled in DB", $this->variable, __CLASS__, __FUNCTION__, __LINE__);
                $this->getSystem();
            }
        }else{ // does not exist in db get from system
            CRLog("error", "environment variable not found in DB", $this->variable, __CLASS__, __FUNCTION__, __LINE__);
            $this->getSystem();
        }
        return $this->value;
    }

    /**
     * return system env variable and store to db if appropriate
     */
    public function getSystem(){
        if($this->m == null){ // no need to go to db if we got an instance already
            $v =  \App\EnvVariables::updateOrCreate(
                ['variable' => $this->variable],
                ['value' => env($this->variable), 'enabled' => false]
            );
            Cache::put('env:'.$this->variable, $v, 5);
        }
        $this->value = env($this->variable);
    }

}