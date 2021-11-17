<?php
declare(strict_types=1);


namespace Ithua\logistics\Channel;

class YuantongChannel extends Channel
{
    private $code = '';
    private $config = [];
    /**
     * 构造函数.
     *
     *YuantongChannel constructor.
     */
    public function __construct()
    {
        $config = $this->getChannelConfig();
        $this->url = 'https://openapi.yto.net.cn:11443/open/track_query_adapter/v1/9Mk7PD/'.$config['app_key'];
        $this->config = $config;
    }

    /**
     * 调用圆通接口.
     *
     * @throws \Exception
     */
    public function request(string $code): array
    {
        try {
            $this->code = $code;

            $t_param = "{\"NUMBER\":\"".$code."\"}";
            $data = $t_param.$this->config['request_param'].$this->config['app_secret'];
            $sign = $this->createSign($data);
            $param = [
                "timestamp"=>time(),
                "param"=>$t_param,
                "sign"=>$sign,
                "format"=>"JSON"];

            $response = $this->post($this->url, $param, ['header' => array('Content-Type: application/json')]);

            $this->toArray($response);
            $this->format();

            return $this->response;
        } catch (\Exception $exception) {
            throw new \Exception($exception->getMessage());
        }
    }

    /**
     * 1.在POST时用“sign”字段进行签名验证。
     * 2.将 param+method（方法）+v（版本） 拼接得到 data，将 data和 客户密钥 拼接。（通过 控制台——接口管理，添加自己所需接口，即可得到相应的测试地址、客户编码、客户密钥、方法和版本）
     * 3.	假设data内容为： opentest， partnerId（客户密钥）为123456。 则要签名的内容为opentest123456，然后对opentest123456先进行MD5加密，然后转换为base64字符串。 即经过md5(16位byte)和base64后的内容就为 YLstCNa3x8ijQx16e/jqOA==
     * @param data
     */
    private function createSign($data){
        return base64_encode(pack('H*', md5($data)));;
    }


    /**
     * 转为数组.
     *
     * @param array|string $response
     */
    protected function toArray($response)
    {
        $jsonToArray = \json_decode($response, true);
        if (empty($jsonToArray)) {
            $this->response = [
                'status' => 0,
                'message' => '请求发生不知名错误, 查询不到物流信息',
                'error_code' => 0,
                'data' => [],
            ];
        } else {
            if (isset($jsonToArray['success']) && $jsonToArray['code'] === 1001) {
                $this->response = [
                    'status' => 0,
                    'message' => $jsonToArray['message'],
                    'error_code' => $jsonToArray['code'],
                    'data' => [],
                ];
            } else {
                $this->response = [
                    'status' => 1,
                    'message' => 'ok',
                    'error_code' => 0,
                    'data' => $jsonToArray,
                ];
            }
        }
    }

    /**
     * 统一物流信息.
     *
     * @return mixed|void
     */
    protected function format()
    {

        if (!empty($this->response['data'])) {
            $formatData = [];
            foreach ($this->response['data'] as $item) {
                $formatData[] = ['time' => $item['upload_Time'], 'processInfo' => $item['processInfo'], 'status' => $item['infoContent']];
            }
            $this->response['data'] = $formatData;
        }
    }
}
