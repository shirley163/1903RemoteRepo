<?php

header('Content-Type: text');

// 1.实例化对象; Obj是object公认缩写
$wechatObj = new WechatAPI();
// 2.对象调用方法; msg是message公认缩写
if (isset($_GET['echostr'])) {
    // 需要验证/校验
    $wechatObj->validMsg();
} else {
    // 3.调用方法: 接收用户消息, 返回消息
    $wechatObj->reponseMsg();
}

class WechatAPI
{
    /**
     * 验证消息的确来自于微信服务器
     *
     * @return String
     */
    public function validMsg()
    {
        if ($this->isCheckSignature()) {
            // 返回echostr参数值
            echo $_GET['echostr'];
            exit;
        }
    }

    /**
     * 生成加密字符串, 并和signature参数值判断
     *
     * @return Bool
     */
    private function isCheckSignature()
    {
        // 1.读取token, timestamp + nonce
        $token = 'weixin';
        $timestamp = $_GET['timestamp'];
        $nonce = $_GET['nonce'];
        $signature = $_GET['signature'];

        // 2.字典序排序; tmp是temporary临时的缩写
        $tmpArr = [$token, $timestamp, $nonce];
        sort($tmpArr);

        // 3.生成一个字符串 + sha1加密
        $tmpStr = implode($tmpArr);
        $tmpStr = sha1($tmpStr);

        // 4.加密字符串和signature对比
        if ($tmpStr == $signature) {
            // 相等, 返回echostr参数值
            return true;
        } else {
            // 不相等, 什么都不做
            return false;
        }
    }

    /**
     * 接收用户消息, 返回消息给用户
     *
     * @return String XML格式字符串
     */
    public function reponseMsg()
    {
        // 1.接收
        $xmlStr = file_get_contents('php://input');

        // 2.判断接收数据是否为空, 不为空
        if (!empty($xmlStr)) {
            // 3.转换对象
            $xmlObj = simplexml_load_string($xmlStr, 'SimpleXMLElement', LIBXML_NOCDATA);

            // 4.拼接返回XML
            $contentStr = '你发送的是文本消息, 返回输入内容:' . $xmlObj->Content;
            /*$resultStr = "<xml>
            <ToUserName><![CDATA[$xmlObj->FromUserName]]></ToUserName>
            <FromUserName><![CDATA[$xmlObj->ToUserName]]></FromUserName>
            <CreateTime>time()</CreateTime>
            <MsgType><![CDATA[text]]></MsgType>
            <Content><![CDATA[$contentStr]]></Content>
            </xml>";*/
            // %s: 字符串值填空
            $resultStr = '<xml>
<ToUserName><![CDATA[%s]]></ToUserName>
<FromUserName><![CDATA[%s]]></FromUserName>
<CreateTime>%s</CreateTime>
<MsgType><![CDATA[text]]></MsgType>
<Content><![CDATA[%s]]></Content>
</xml>';
            $result = sprintf($resultStr, $xmlObj->FromUserName, $xmlObj->ToUserName, time(), $contentStr);

            // 5.echo
            echo $result;
        }

    }
}

/**
 * 1.用户发送文本消息, 微信服务器post XML数据包(XML格式字符串)
 * ToUserName: 开发者微信号(公众号); 消息接收方
 * FromUserName: 用户微信号加密(openID); 消息发送方
 * CreateTime: 用户发送消息时间戳
 * MsgType: 用户发送消息类型; text表示文本类型(关键词)
 * Content: 用户发送消息内容
 * MsgId: 用户发送消息ID标识
<xml>
<ToUserName><![CDATA[toUser]]></ToUserName>
<FromUserName><![CDATA[fromUser]]></FromUserName>
<CreateTime>1348831860</CreateTime>
<MsgType><![CDATA[text]]></MsgType>
<Content><![CDATA[1234]]></Content>
<MsgId>1234567890123456</MsgId>
</xml>

<ToUserName>toUser</ToUserName>
特殊情况: XML标签内容包含特殊符号 < / >, 发生解析错误;
<ToUserName>bob<!/\></ToUserName>

<ToUserName><![CDATA[bob<!/\>]]></ToUserName>

<ToUserName><![CDATA[toUser]]></ToUserName>

2.公众号(新浪云), 返回文本消息XML字符串, 给用户
ToUserName: 用户微信号加密(openID); 消息接收方
FromUserName: 开发者微信号(公众号); 消息发送方
CreateTime: 返回消息时间戳
MsgType: 消息类型; text返回是文本类型(关键词)
Content: 返回给用户消息内容

<xml>
<ToUserName><![CDATA[???]]></ToUserName>
<FromUserName><![CDATA[???]]></FromUserName>
<CreateTime>???</CreateTime>
<MsgType><![CDATA[text]]></MsgType>
<Content><![CDATA[???]]></Content>
</xml>

 */
