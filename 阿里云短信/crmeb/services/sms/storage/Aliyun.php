<?php

namespace crmeb\services\sms\storage;

use crmeb\basic\BaseSms;
use think\facade\Config;


/**
 * 阿里云短信服务
 * Class SMSService
 * @package crmeb\services\sms\storage
 * @author 若海<https://github.com/anrip/crmeb-patch>
 */
class Aliyun extends BaseSms
{
    protected $signName = '';
    protected $templateCode = '';
    protected $accessKeyId = '';
    protected $accessKeySecret = '';

    protected function initialize(array $config)
    {
        parent::initialize($config);

        $conf = Config::get('sms.stores.aliyun', []);

        $this->signName = $conf['signName'];
        $this->templateCode = $conf['templateCode'];
        $this->accessKeyId = $conf['accessKeyId'];
        $this->accessKeySecret = $conf['accessKeySecret'];
    }

    public function send(string $phone, string $templateId, array $data = [])
    {
        if (empty($phone)) {
            return $this->setError('Mobile number cannot be empty');
        }

        if ($templateId != 'VERIFICATION_CODE') {
            return $this->setError('暂不支持其他类型消息');
        }

        return $this->send_core($phone, array(
            'TemplateParam' => array(
                'code' => $data['code']
            )
        ));
    }

    private function send_core(string $phone, array $params = [])
    {
        $params = array_merge(array(
            'AccessKeyId' => $this->accessKeyId,
            'Action' => 'SendSms',
            'Format' => 'JSON',
            'PhoneNumbers' => $phone,
            'RegionId' => 'cn-hangzhou',
            'SignName' => $this->signName,
            'SignatureMethod' => 'HMAC-SHA1',
            'SignatureNonce' => uniqid(mt_rand(0, 0xffff), true),
            'SignatureVersion' => '1.0',
            'TemplateCode' => $this->templateCode,
            'Timestamp' => gmdate('Y-m-d\TH:i:s\Z'),
            'Version' => '2017-05-25',
        ), $params);

        if (!empty($params['TemplateParam']) && is_array($params['TemplateParam'])) {
            $params['TemplateParam'] = json_encode($params['TemplateParam'], JSON_UNESCAPED_UNICODE);
        }

        ksort($params);
        $sortedQueryString = '';
        foreach ($params as $key => $value) {
            $sortedQueryString .= '&' . $this->url_encode($key) . '=' . $this->url_encode($value);
        }

        $signature = "POST&%2F&" . $this->url_encode(substr($sortedQueryString, 1));
        $signature = $this->url_encode(
            base64_encode(hash_hmac('sha1', $signature, $this->accessKeySecret . '&', true))
        );

        try {
            $body = "Signature={$signature}{$sortedQueryString}";
            $content = $this->api_request('http://dysmsapi.aliyuncs.com/', 'POST', $body);
            if ($content) {
                $res = json_decode($content);
                return array('data' => array(
                    'id' => $res->BizId,
                    'content' => $content,
                    'template' => $params['TemplateCode']
                ));
            }
        } catch (\Exception $e) {
            $this->setError($e->getMessage());
        }

        return false;
    }

    private function api_request(string $url, string $method, string $body)
    {
        $ch = curl_init();

        if ($method == 'POST') {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        } else {
            $url .= '?' . $body;
        }

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'x-sdk-client' => 'php/2.0.0'
        ));

        if (substr($url, 0, 5) == 'https') {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }

        $rtn = curl_exec($ch);

        if ($rtn === false) {
            $err = '[CURL_' . curl_errno($ch) . ']: ' . curl_error($ch);
            return $this->setError($err);
        }

        curl_close($ch);

        return $rtn;
    }

    private function url_encode(string $str)
    {
        $res = urlencode($str);
        $res = preg_replace('/\+/', '%20', $res);
        $res = preg_replace('/\*/', '%2A', $res);
        $res = preg_replace('/%7E/', '~', $res);
        return $res;
    }
}
