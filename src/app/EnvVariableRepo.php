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
    public $defaultValue;
    protected $m; //instance of EnvVariables

    /**
     * EnvVariable constructor.
     *
     * @param $variable, mixed
     * @param $value
     * @param $default
     */
    public function __construct($variable){
        $this->defaultValue = null;
        if(!is_array($variable)){
            $this->variable = $variable;
        }elseif(is_array($variable)){ // support passing of a default value
            $this->variable = $variable[0];
            $this->defaultValue = $variable[1];
        }else{
            CRLog("error", "variable type not recognised", json_encode($variable), __CLASS__, __FUNCTION__, __LINE__);
        }
        $this->get();
    }


    public function get(){
        // check if this is a db managed env variable
        $this->m = Cache::remember('env:'.$this->variable, env('REDIS_CACHE_EnvVariableRepo', 5), function () {
            echo getRedString("get from db\n");
            return  \App\EnvVariables::where('variable', $this->variable)->first();
        });

        // check what value to return
        if($this->m != null){ //  value exists in db
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
            $data = ['value' => env($this->variable), 'enabled' => false];
            if($this->defaultValue != null){
                $data['value'] = $this->value;
                $data['enabled'] = true;
            }
            //check if we got a default value we can use
            if($this->defaultValue != null){
                $data['value'] = $this->defaultValue;
            }
            $v =  \App\EnvVariables::updateOrCreate(
                ['variable' => $this->variable],
                $data
            );
            Cache::put('env:'.$this->variable, $v, env('REDIS_CACHE_EnvVariableRepo', 5));
        }else{
            CRLog("error", getRedString("no m instance"), $this->variable, __CLASS__, __FUNCTION__, __LINE__);
        }
        $this->value = env($this->variable);
    }

}