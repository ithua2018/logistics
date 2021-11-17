<?php

declare(strict_types=1);

namespace Ithua\Logistics;
use Ithua\Logistics\Exceptions\ConfigNotFoundException;
use Ithua\Logistics\Exceptions\ConfigValidateException;

/**
 * 配置类.
 */
class Config
{
    /**
     * 配置.
     *
     * @var array
     */
    protected static $config;

    /**
     * 配置验证规则.
     *
     * @var array
     */
    protected $validateRule = [
        'yuantong' => ['app_key', 'app_secret', 'request_param'],

    ];

    /**
     * 验证规则.
     *
     * @throws ConfigNotFoundException
     * @throws ConfigValidateException
     */
    protected function validate(string $channel)
    {
        if (!in_array($channel, array_keys($this->validateRule))) {
            throw new ConfigNotFoundException('没找到相对应配置规则');
        }
        $keys = array_keys(static::$config[$channel]);
        $intersect = array_intersect($this->validateRule[$channel], $keys);
        if (count($intersect) !== count($this->validateRule[$channel])) {
            throw new ConfigValidateException('规则验证失败');
        }
    }

    /**
     * 设置配置.
     *
     * @throws ConfigNotFoundException
     * @throws ConfigValidateException
     */
    public function setConfig(array $params)
    {
        static::$config = $params;
        foreach (static::$config as $channel => $param) {
            $this->validate($channel);
        }
    }

    /**
     * 获取配置.
     */
    public function getConfig(string $key): array
    {
        return static::$config[$key];
    }
}
