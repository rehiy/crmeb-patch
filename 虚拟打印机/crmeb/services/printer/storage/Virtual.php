<?php

namespace crmeb\services\printer\storage;

use think\facade\Config;

/**
 * Class Virtual
 * @author 若海<https://github.com/anrip/crmeb-patch>
 * @package crmeb\services\printer\storage
 */
class Virtual extends YiLianYun
{
    private $orderInfo;

    /**
     * 开始打印
     * @return bool|mixed|string
     * @throws \Exception
     */
    public function startPrinter()
    {
        if (!$this->printerContent) {
            return $this->setError('Missing print');
        }

        $conf = Config::get('printer.stores.virtual', []);
        $sdir = app()->getRuntimePath() . $conf['save_dir'];

        is_dir($sdir) || mkdir($sdir, '0755');
        $file = $this->orderInfo['order_id'] . 'html';

        file_put_contents($sdir . $file, $this->printerContent);
    }

    /**
     * 设置打印内容
     * @param array $config
     * @return YiLianYun
     */
    public function setPrinterContent(array $config): self
    {
        $this->orderInfo = $config['orderInfo'];
        return parent::setPrinterContent($config);
    }
}
