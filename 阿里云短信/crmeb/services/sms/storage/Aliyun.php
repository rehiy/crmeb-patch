<?php

namespace crmeb\services\sms\storage;

use think\facade\Config;
use crmeb\basic\BaseSms;
use crmeb\services\HttpService;


/**
 * 阿里云短信服务
 * Class Aliyun
 * @package crmeb\services\sms\storage
 * @author 若海<https://github.com/anrip/crmeb-patch>
 */
class Aliyun extends BaseSms
{
    protected $templates = [];

    protected $signName = '';
    protected $accessKeyId = '';
    protected $accessKeySecret = '';

    protected $apiUrl = 'http://dysmsapi.aliyuncs.com/';

    protected function initialize(array $config)
    {
        parent::initialize($config);

        $conf = Config::get('sms.stores.aliyun', []);

        $this->templates = $conf['template_id'];

        $this->signName = $conf['sign_name'];
        $this->accessKeyId = $conf['access_key_id'];
        $this->accessKeySecret = $conf['access_key_secret'];
    }

    public function send(string $phone, string $templateId, array $data = [])
    {
        if (empty($phone)) {
            return $this->setError('Mobile number cannot be empty');
        }

        if(empty($this->templates[$templateId])) {
            return $this->setError('Missing template number');
        }

        return $this->send_core($phone, [
            'TemplateCode' => $this->templates[$templateId],
            'TemplateParam' => $data
        ]);
    }

    private function send_core(string $phone, array $params = [])
    {
        $method = 'POST';
        $params = array_merge([
            'AccessKeyId' => $this->accessKeyId,
            'Action' => 'SendSms',
            'Format' => 'JSON',
            'PhoneNumbers' => $phone,
            'RegionId' => 'cn-hangzhou',
            'SignName' => $this->signName,
            'SignatureMethod' => 'HMAC-SHA1',
            'SignatureNonce' => uniqid(mt_rand(0, 0xffff), true),
            'SignatureVersion' => '1.0',
            'Timestamp' => gmdate('Y-m-d\TH:i:s\Z'),
            'Version' => '2017-05-25',
        ], $params);

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

        return [
            'data' => [
                'id' => $data->RequestId,
                'template' => $params['TemplateCode'],
                'content' => json_decode($params['TemplateParam'])
            ]
        ];
    }

    private function api_request(string $url, string $method, string $body)
    {
        $header = ['x-sdk-client' => 'php/2.0.0'];
        $result = HttpService::request($url, $method, $body, $header);

        if ($content === false) {
            return $this->setError(HttpService::getCurlError());
        }

        $json = json_decode($result);

        if ($json === false) {
            return $this->setError(json_last_error_msg());
        }

        if ($json->Code != 'OK') {
            return $this->setError($json->Message);
        }

        return $json;
    }
}
