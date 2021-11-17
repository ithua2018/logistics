<?php

declare(strict_types=1);

namespace Ithua\Logistics;

use Ithua\Logistics\Exceptions\InvalidArgumentException;
use Ithua\Logistics\Exceptions\NoQueryAvailableException;

/**
 * 抓取物流信息.
 */
class Logistics
{
    /**
     * 渠道接口总数.
     *
     * @var int
     */
    const CHANNEL_NUMBER = 7;

    /**
     * 成功
     *
     * @var string
     */
    const SUCCESS = 'success';

    /**
     * 失败.
     *
     * @string
     */
    const FAILURE = 'failure';

    /**
     * 快递渠道工厂
     *
     * @var \Ithua\Logistics\Factory
     */
    protected $factory;

    /**
     * 构造函数.
     */
    public function __construct(array $config)
    {
        (new Config())->setConfig($config);
        $this->factory = new Factory();
    }

    private function channelMap(string $channelName): string
    {
        $channels = [
            "shunfeng" => "shunfeng",
            "shengtong" => "shengtong",
            "yuantong" => "yuantong",
            "zhongtong" => "zhongtong",
            "yunda" => "yunda",
            "jingdong" => "jingdong",
            "eMS" => "eMS",
        ];

        return $channels[$channelName];
    }

    /**
     * 通过接口获取物流信息.
     *
     * @param array $channels
     *
     * @throws \Ithua\Logistics\Exceptions\InvalidArgumentException
     * @throws \Ithua\Logistics\Exceptions\NoQueryAvailableException
     */
    public function query(string $code, $channels = ['yuantong']): array
    {
        $results = [];
        if (empty($code)) {
            throw new InvalidArgumentException('code arguments cannot empty.');
        }
        if (!empty($channels) && is_string($channels)) {
            $channels = explode(',', $channels);
        }
        foreach ($channels as $channelName) {
            $channel = $this->channelMap($channelName);
            try {
                $request = $this->factory->createChannel($channel)->request($code);
                if (1 === $request['status']) {
                    $results[$channelName] = [
                        'channel' => $channelName,
                        'status' => self::SUCCESS,
                        'result' => $request,
                    ];
                } else {
                    $results[$channelName] = [
                        'channel' => $channelName,
                        'status' => self::FAILURE,
                        'exception' => $request['message'],
                    ];
                }
            } catch (\Exception $exception) {
                $results[$channelName] = [
                    'channel' => $channelName,
                    'status' => self::FAILURE,
                    'exception' => $exception->getMessage(),
                ];
            }
        }
        $collectionOfException = array_column($results, 'exception');
        if (self::CHANNEL_NUMBER === count($collectionOfException)) {
            throw new NoQueryAvailableException('sorry! no channel class available');
        }

        return $results;
    }


}
