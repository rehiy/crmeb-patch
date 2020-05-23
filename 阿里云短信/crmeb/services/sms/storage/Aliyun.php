<?php

namespace crmeb\services\sms\storage;

use think\facade\Config;
use crmeb\basic\BaseSms;
use crmeb\services\HttpService;


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

    protected $apiUrl = 'http://dysmsapi.aliyuncs.com/';

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
            return $this->setError('手机号码不能为空');
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
        $method = 'POST';
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

        if (isset($params['TemplateParam']) && is_array($params['TemplateParam'])) {
            $params['TemplateParam'] = json_encode($params['TemplateParam'], JSON_UNESCAPED_UNICODE);
        }

        ksort($params);
        $sortedQuery = http_build_query($params, null, '&', PHP_QUERY_RFC3986);

        $signature = "{$method}&%2F&" . rawurlencode($sortedQuery);
        $signature = rawurlencode(
            base64_encode(hash_hmac('sha1', $signature, $this->accessKeySecret . '&', true))
        );

        $body = "Signature={$signature}&{$sortedQuery}";
        $data = $this->api_request($this->apiUrl, $method, $body);
        if ($data === false) {
            return false;
        }

        list($obj, $content) = $data;
        return array(
            'data' => array(
                'id' => $obj->RequestId,
                'content' => $content,
                'template' => $params['TemplateCode']
            )
        );
    }

    private function api_request(string $url, string $method, string $body)
    {
        $header = array('x-sdk-client' => 'php/2.0.0');
        $content = HttpService::request($url, $method, $body, $header);

        if ($content === false) {
            return $this->setError(HttpService::getCurlError());
        }

        $json = json_decode($content);

        if ($json === false) {
            return $this->setError(json_last_error_msg());
        }
        if ($json->Code != 'Code') {
            return $this->setError($json->Message);
        }

        return [$json, $content];
    }
}
