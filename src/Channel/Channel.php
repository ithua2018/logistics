<?php

declare(strict_types=1);

namespace Ithua\Logistics\Channel;
use Ithua\Logistics\Config;
use Ithua\Logistics\Traits\HttpRequest;

abstract class Channel
{
    /*
     * HTTP 请求
     */
    use HttpRequest;

    /**
     * 渠道URL.
     *
     * @var string
     */
    protected $url;

    /**
     * 请求资源.
     *
     * @var array
     */
    protected $response;

    /**
     * 请求选项.
     *
     * @var array
     */
    protected $option = [];

    /**
     * 设置请求选项.
     *
     * @return \Ithua\Logistics\Channel\Channel
     */
    public function setRequestOption(array $option): self
    {
        if (!empty($this->option)) {
            if (isset($option['header']) && isset($this->option['header'])) {
                $this->option['header'] = array_merge($this->option['header'], $option['header']);
            }
            if (isset($option['proxy'])) {
                $this->option['proxy'] = $option['proxy'];
            }
        } else {
            $this->option = $option;
        }

        return $this;
    }

    /**
     * 获取实例化的类名称.
     */
    protected function getClassName(): string
    {
        $className = basename(str_replace('\\', '/', (get_class($this))));

        return preg_replace('/Channel/', '', $className);
    }

    /**
     * 获取配置.
     */
    protected function getChannelConfig(): array
    {
        return (new Config())->getConfig(strtolower($this->getClassName()));
    }

    /**
     * 调用查询接口.
     */
    abstract public function request(string $code): array;

    /**
     * 转换为数组.
     *
     * @param string|array $response
     */
    abstract protected function toArray($response);

    /**
     * 格式物流信息.
     *
     * @return mixed
     */
    abstract protected function format();
}
