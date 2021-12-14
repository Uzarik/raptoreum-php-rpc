<?php

namespace Krevedko\RaptoreumPhpRpc;

use Monolog\Formatter\JsonFormatter;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use app\models\User;
use Exception;

class Request
{
    public function send(string $method, array $params = [])
    {
        $curl = curl_init();

        $request = '{"jsonrpc": "1.0", "id":"curltest", "method": "' . $method . '", "params": ' . json_encode($params) . ' }';
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'http://test:test@127.0.0.1:8777/',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $request,
            CURLOPT_HTTPHEADER => array(
                'content-type: text/plain;'
            ),
        ));

        // if($method == 'protx') {
        //     die('{"jsonrpc": "1.0", "id":"curltest", "method": "' . $method . '", "params": ' . json_encode($params) . ' }');
        // }

        $response = curl_exec($curl);
        curl_close($curl);

        $result = ['result' => json_decode($response), 'raw' => $response];
        $this->checkError($method, $request, $result);

        return $result;
    }

    /**
     * проверка ответа и выкидывание ошибки
     */
    protected function checkError($method, $request, $result)
    {
        switch (true) {
            case isset($result['result']->error->message) && $result['result']->error->message:
            case !isset($result['result']->result) || !$result['result']->result:
                $error = true;
                break;
            default:
                $error = false;
                break;
        }

        if ($error) {
            // create a log channel
            $log = new Logger('rpc');
            $stream = new StreamHandler(\Yii::getAlias('@app') . '/logs/fail-rpc.log', Logger::WARNING);
            $stream->setFormatter(new JsonFormatter());
            $log->pushHandler($stream);
            

            $log->error($method, [
                'request' => $request,
                'result' => $result
            ]);

            // если есть активная транзакция, откатим
            if ($transaction = User::getDb()->getTransaction()) {
                $transaction->rollBack();
            }

            throw new Exception('Возникла ошибка при вызове RPC метода ' . $method, 500);
        }
    }
}