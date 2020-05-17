<?php
declare (strict_types = 1);

namespace App\Controller;

use Hyperf\Di\Annotation\Inject;

abstract class AbstractController
{
    /**
     * @Inject
     * @var \Psr\Container\ContainerInterface
     */
    protected $container;

    public static $staticObject = [];

    public function __get($name)
    {
        if (empty(self::$staticObject[$name])) {
            $object = getObject($name);

            if ($object) {
                self::$staticObject[$name] = $object;
            }
        }

        return self::$staticObject[$name];
    }

    //处理
    public function handler($cmaName, $fd, $param = [])
    {
        $objectInfo = $this->MsgRegister->msgList[$cmaName] ?? [];
        if ($objectInfo) {
            if (is_array($objectInfo[0])) {
                foreach ($objectInfo[0] as $key => $value) {
                    $func = $value[1];
                    if ($packetInfo = $this->container->get($value[0])->$func($fd, $param)) {
                        $this->SendMsg->sendPacketData($fd, $this->SendMsg->packetData($packetInfo), $packetInfo);
                    }
                }
            } else {
                $func = $objectInfo[1];
                if ($packetInfo = $this->container->get($objectInfo[0])->$func($fd, $param)) {
                    $this->SendMsg->sendPacketData($fd, $this->SendMsg->packetData($packetInfo), $packetInfo);
                }
            }
        }
    }

    /**
     * [getConnections 获取连接数]
     * ------------------------------------------------------------------------------
     * @author  github
     * ------------------------------------------------------------------------------
     * @version date:2020-04-09
     * ------------------------------------------------------------------------------
     * @return  [type]          [description]
     */
    public function getConnections(): int
    {
        return count($this->Server->connections);
    }

    /**
     * [returnJson 接口数据]
     * ------------------------------------------------------------------------------
     * @author  github
     * ------------------------------------------------------------------------------
     * @version date:2020-03-16
     * ------------------------------------------------------------------------------
     * @param   int|integer     $code [description]
     * @param   string          $msg  [description]
     * @param   array           $data [description]
     * @return  [type]                [description]
     */
    public function returnJson(int $code = 2000, string $msg = '', array $data = []): object
    {
        return $this->response->json(
            [
                'code' => $code,
                'msg'  => $msg,
                'data' => $data,
            ]
        );
    }
}
