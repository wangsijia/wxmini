<?php
namespace Wangsijia\Wxmini;

class Wxmini
{
    private $appId;
    private $secret;
    private $code2session_url;
    private $sessionKey;

    /**
     * Wxmini constructor.
     * @param array $config
     */
    function __construct($config = [])
    {
        $this->appId = $config ? $config['appid'] : config('wxmini.default.appid', '');
        $this->secret = $config ? $config['secret'] : config('wxmini.default.secret', '');
        $this->code2session_url = config('wxmini.code2session_url', '');
    }

    /**
     * @param $code
     * @return array|bool|mixed
     * @throws \Exception
     */
    public function getLoginInfo($code){
        return $this->authCodeAndCode2session($code);
    }

    /**
     * @param $encryptedData
     * @param $iv
     * @param null $sessionKey
     * @return array|string
     */
    public function getUserInfo($encryptedData, $iv, $sessionKey = null){
        if (empty($sessionKey)) {
          $sessionKey = $this->sessionKey;
        }
        $pc = new WXBizDataCrypt($this->appId, $sessionKey);
        $decodeData = "";
        $errCode = $pc->decryptData($encryptedData, $iv, $decodeData);
        if ($errCode !=0 ) {
            return [
                'code' => 10001,
                'message' => 'encryptedData 解密失败'
            ];
        }
        return $decodeData;
    }

    /**
     * @param $code
     * @return array|bool|mixed
     */
    private function authCodeAndCode2session($code){
        $code2session_url = sprintf($this->code2session_url,$this->appId,$this->secret,$code);
        $userInfo = $this->httpRequest($code2session_url);
        if(!isset($userInfo['session_key'])){
            return [
                'code' => 10000,
                'code' => '获取 session_key 失败',
            ];
        }
        $this->sessionKey = $userInfo['session_key'];
        return $userInfo;
    }

    /**
     * @param $url
     * @param null $data
     * @return bool|mixed
     */
    private function httpRequest($url, $data = null)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        if (!empty($data)){
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($curl);
        if($output === FALSE ){
            return false;
        }
        curl_close($curl);
        return json_decode($output,JSON_UNESCAPED_UNICODE);
    }
}
