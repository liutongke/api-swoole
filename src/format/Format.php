<?php

namespace Sapi\format;

class Format
{
    /**
     * 参数处理
     * @param array $data 请求的参数
     * @param string $action 处理请求的方法
     * @param array $rules 处理的规则
     * @return array
     */
    public function Format($data, string $action, array $rules)
    {

        $rule = $rules[$action];
        $t = ["res" => false, "data" => ""];

        foreach ($rule as $k => $v) {
            if ($v['require'] && !isset($data[$k])) {//必须滴
                $t = ["res" => true, "data" => $v['message'] ?? "must require {$k}"];
            }
            //类型判断
            if (isset($data[$k])) {
                $typeAction = ucfirst($v['type']);
                $obj = "\\Sapi\\format\\{$typeAction}Format";

                if (!call_user_func([new $obj(), 'parse'], $data[$k])) {
                    $t = ["res" => true, "data" => 'type error'];
                }
            }
        }

        return $t;
    }

    /**
     * 参数处理
     * @param array $data 请求的参数
     * @param string $action 处理请求的方法
     * @param array $rules 处理的规则
     * @return array
     */
    public function HttpFormat(\Swoole\Http\Request $request, string $action, array $rules)
    {
        $t = ["res" => false, "data" => ""];
        if (!isset($rules[$action])) {
            return $t;
        }

        $rule = $rules[$action];

        foreach ($rule as $k => $v) {
            if (strcmp($v['type'], 'file') == 0) {//文件类型特殊处理下
                $data = $request->files;
            } else {
                $data = $request->{$v['source']};//通过指定的源获取值
            }

            if ($v['require'] && !isset($data[$k])) {//必须滴
                $t = ["res" => true, "data" => $v['message'] ?? "must require {$k}"];
            }
            //类型判断
            if (isset($data[$k])) {
                $typeAction = ucfirst($v['type']);
                $obj = "\\Sapi\\format\\{$typeAction}Format";

                if (!call_user_func_array([new $obj(), 'parse'], [$data[$k], $v])) {
                    $t = ["res" => true, "data" => 'type error'];
                }
            }
        }
        return $t;
    }
}