<?php
/**
 * Created by PhpStorm.
 * User: Dodi
 * Date: 7/20/2018
 * Time: 11:01 AM
 */

namespace Verzth\TCXClient;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Ixudra\Curl\Facades\Curl;

class TCX{
    const TCX_TYPE = 'X-TCX-TYPE';
    const TCX_APP_ID = 'X-TCX-APP-ID';
    const TCX_APP_PASS = 'X-TCX-APP-PASS';
    const TCX_TOKEN = 'X-TCX-TOKEN';

    const TCX_TYPE_OWTC = 'OWTC';
    const TCX_TYPE_TWTC = 'TWTC';
    const TCX_TYPE_FTC = 'FTC';

    private $options;
    public function __construct(array $options){
        $this->options = $options;
    }

    public function getID(){
        return $this->options['application']['id'];
    }
    public function getSecret(){
        return $this->options['application']['secret'];
    }
    public function getPublic(){
        return $this->options['application']['public'];
    }
    public function getTokenType(){
        return $this->options['token']['type'];
    }

    public function isDebug(){
        return $this->options['debug'];
    }

    private function getTCXClientToken(){
        return tcxRandomString(32);
    }

    /**
     * @param bool|array|string $params params or datetime
     * @return bool|string
     */
    public function getTCXPass(&$params=false){
        $clientToken = $this->getTCXClientToken();
        if($this->getTokenType()=='none'){
            $token = '';
        }elseif($this->getTokenType()=='time'){
            $token = $params['tcx_datetime'] = tcxDatetime();
        }else{
            ksort($params,SORT_STRING);
            $token = '';
            foreach($params as $k=>$v){
                $token.=$k.'='.$v.'&';
            }
            rtrim($token,'&');
        }
        return SHA1($token.$this->getPublic().$clientToken).':'.$clientToken;
    }

    public function getTCXMasterKey($encrypt=true){
        return $encrypt?base64_encode($this->options['application']['masterKey']):$this->options['application']['masterKey'];
    }

    public function getTCXAccess($pass,$datetime=false,$encrypt=true){
        if(Cache::has('tcx.token')) return Cache::get('tcx.token');
        if(Cache::has('tcx.expired_at')){
            if(Carbon::createFromFormat("Y-m-d H:i:s",Cache::get('tcx.expired_at'))->greaterThan(Carbon::now())){
                if($refreshToken=$this->getTCXRefreshAccess()){
                    $content = [
                        'app_id' => $this->getID(),
                        'token' => $refreshToken
                    ];

                    $curl = Curl::to($this->options['url'].'tcx/reauthorize')
                        ->withData($content)
                        ->asJson()
                        ->asJsonResponse()
                        ->post();
                    tcxLogFile($curl);
                    if($curl){
                        if($curl->status==1){
                            $data = $curl->data;
                            Cache::put('tcx.token',(string)$data->token,1440); //(int)$data->result->timeout
                            Cache::put('tcx.refresh',(string)$data->refesh,1440); //(int)$data->result->timeout
                            Cache::put('tcx.expired_at',(string)$data->expired_at,1440); //(int)$data->result->timeout
                            if($encrypt) return base64_encode(Cache::get('tcx.token'));
                            else return Cache::get('tcx.token');
                        }else return false;
                    }else return false;
                }else GOTO REQUEST;
            }else GOTO REQUEST;
        }else{
            REQUEST:
            $content = [
                'app_id' => $this->getID(),
                'app_pass' => $pass
            ];
            if($datetime) $content['tcx_datetime'] = $datetime;

            $curl = Curl::to($this->options['url'].'tcx/authorize')
                ->withData($content)
                ->asJson()
                ->asJsonResponse()
                ->post();
            tcxLogFile($curl);
            if($curl){
                if($curl->status==1){
                    $data = $curl->data;
                    Cache::put('tcx.token',(string)$data->token,1440); //(int)$data->result->timeout
                    Cache::put('tcx.refresh',(string)$data->refesh,1440); //(int)$data->result->timeout
                    Cache::put('tcx.expired_at',(string)$data->expired_at,1440); //(int)$data->result->timeout
                    if($encrypt) return base64_encode(Cache::get('tcx.token'));
                    else return Cache::get('tcx.token');
                }else return false;
            }else return false;
        }
    }
    private function getTCXRefreshAccess($encrypt=true){
        if(Cache::has('tcx.refresh')){
            if($encrypt) return base64_encode(Cache::get('tcx.refresh'));
            else return Cache::get('tcx.refresh');
        } else return false;
    }
}