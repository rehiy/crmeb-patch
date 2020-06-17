<?php
// +----------------------------------------------------------------------
// | 短信配置
// +----------------------------------------------------------------------

return [
    //默认短信驱动
    'default' => 'aliyun',
    //单个手机每日发送上限
    'maxPhoneCount' => 10,
    //验证码每分钟发送上线
    'maxMinuteCount' => 20,
    //单个IP每日发送上限
    'maxIpCount' => 50,
    //驱动模式
    'stores' => [
        //云信
        'yunxin' => [
            //短信模板id
            'template_id' => [
                //验证码
                'VERIFICATION_CODE' => 518076,
                //支付成功
                'PAY_SUCCESS_CODE' => 520268,
                //发货提醒
                'DELIVER_GOODS_CODE' => 520269,
                //确认收货提醒
                'TAKE_DELIVERY_CODE' => 520271,
                //管理员下单提醒
                'ADMIN_PLACE_ORDER_CODE' => 520272,
                //管理员退货提醒
                'ADMIN_RETURN_GOODS_CODE' => 520274,
                //管理员支付成功提醒
                'ADMIN_PAY_SUCCESS_CODE' => 520273,
                //管理员确认收货
                'ADMIN_TAKE_DELIVERY_CODE' => 520422,
                //改价提醒
                'PRICE_REVISION_CODE' => 528288,
                //订单未支付
                'ORDER_PAY_FALSE' => 528116,
            ],
        ],
        //阿里云
        'aliyun' => [
            //短信模板id
            'template_id' => [
                //验证码
                'VERIFICATION_CODE' => 'YOUR-TEMPLATE-ID',
                //支付成功
                'PAY_SUCCESS_CODE' => '',
                //发货提醒
                'DELIVER_GOODS_CODE' => '',
                //确认收货提醒
                'TAKE_DELIVERY_CODE' => '',
                //管理员下单提醒
                'ADMIN_PLACE_ORDER_CODE' => '',
                //管理员退货提醒
                'ADMIN_RETURN_GOODS_CODE' => '',
                //管理员支付成功提醒
                'ADMIN_PAY_SUCCESS_CODE' => '',
                //管理员确认收货
                'ADMIN_TAKE_DELIVERY_CODE' => '',
                //改价提醒
                'PRICE_REVISION_CODE' => '',
                //订单未支付
                'ORDER_PAY_FALSE' => '',
            ],
            //短信签名
            'sign_name' => 'YOUR-SIGN-NAME',
            //认证密钥
            'access_key_id' => 'YOUR-ACCESS-KEY-ID',
            'access_key_secret' => 'YOUR-ACCESS-KEY-SECRET',
        ]
    ]
];
