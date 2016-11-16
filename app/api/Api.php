<?php
/**
 * Created by PhpStorm.
 * User: lidanyang
 * Date: 16/6/15
 * Time: 上午11:08
 */

namespace api;

use api\cache\Cache;
use api\identify\Identifier;
use api\playurl\Core;
use api\playurl\Display;
use api\playurl\Utils;
use api\playurl\VideoLoad;
use base\async\AsyncRedis;
use sdk\config\Config;
use base\promise\PromiseGroup;
use GuzzleHttp\Promise\Promise;
use api\RateController\RateController;

class Api
{
    /**
     * @param \swoole_http_request $request
     * @param \swoole_http_response $response
     * @return array|string
     * @throws \Exception
     */
    public static function playurl(\swoole_http_request $request, \swoole_http_response $response)
    {
        $params = $request->get;
        $header_arr = array();
        $response->header('Cache-Control', 'no-cache');
        $response->header('Access-Control-Allow-Origin', 'http://www.bilibili.com');
        $response->header('Access-Control-Allow-Credentials', 'true');

        if (!isset($request->get['otype']))
        {
            $params['otype'] = 'xml';
        }
        if (!isset($request->get['cid'])) {
            $video_info = Display::output($params, array(
                'result' => 'error',
                'message' => 'cid not found'
            ));
            return $video_info;
        }
        $cid_r = [];
        if (preg_match('/^([0-9]+)$/', $params['cid'], $cid_r)) {
            $params['cid'] = intval($cid_r[1]);
        } else {
            $video_info = Display::output($params, array(
                'result' => 'error',
                'message' => 'cid not illege'
            ));
            return $video_info;
        }

        foreach ($header_arr as $header_type => $header_value) {
            $response->header( strtolower($header_type) , $header_value);
        }

        $ip_address = null;
        $ip_address = isset( $request->header['x-backend-bili-real-ip'] )
            ? $request->header['x-backend-bili-real-ip'] : null;

        $ip_m = [];
        if($ip_address == null && isset($request->header['client-ip'])
            && is_string($request->header['client-ip'])
            && ( preg_match('/([0-9]+\.[0-9]+\.[0-9]+\.[0-9]+)/', $request->header['client-ip'], $ip_m))) {
            $ip_address = $ip_m[1];
        }

        $ip_m = [];
        if($ip_address == null && isset($request->header['x-forwarded-for'])
            && is_string($request->header['x-forwarded-for'])
            && ( strrpos( '220.181.118.', $ip_address ) == 0 || strrpos( '123.126.50.', $ip_address ) == 0 )
            && ( preg_match('/([0-9]+\.[0-9]+\.[0-9]+\.[0-9]+)/', $request->header['x-forwarded-for'], $ip_m))
            ) {
            $ip_address = $ip_m[1];
        }
        if ($ip_address == null) {
            $ip_address = $request->server[lcfirst('remote_addr')];
        }

        if($ip_address == '127.0.0.1' && isset($params['q_ip']))
        {
            $ip_address = $params['q_ip'];
        }
        $response->header('remote_addr',$ip_address);

        if ($ip_address == "172.16.1.51" || $ip_address == "172.16.1.53")
        {
            $ip_address = "101.226.163.160";
        }
        if (isset($request->header['user-agent'])) {
            $params['user-agent'] = $request->header['user-agent'];
        }
        if (isset($request->header)) {
            $params['header'] = $request->header;
        }
        $ip_info = Core::queryIP($ip_address);
        if( !$ip_info )
        {
           return "";
        }
        if($ip_info['country'] == '共享地址' && isset($request->header['x-cache-server-addr']) )
        {
            $ip_info = Core::queryIP($request->header['x-cache-server-addr']);
        }
        $params['ip_info'] = $ip_info;

        $city_code = $ip_info['country'];

        if( in_array( $ip_info['province'] , Config::get('in_province') ) )
        {
            $city_code = $ip_info['province'];
        }
        if (!isset($params['platform'])) {
            $params['platform'] = 'pc';
        }
        if (isset($params['player']) && $params['player'] == 1) {
            $params['platform'] = 'pc';
        }



        $response->header('zone_id',Core::ipDataToZone($ip_info));	// using traditional zone_id for youku
        $zone_id = Core::geoToZone($ip_info);
        if (isset( $params['force_zone_id'] )){
            $zone_id = intval($params['force_zone_id']);
        }

        $params['zone'] = $zone_id;
        $params['req_fmt'] = isset($params['type']) ? $params['type']: "flv";
        $params['vupload'] = 'vupload';
        $params['ip'] = $ip_address;
        if( isset($request->server['http_x_cache_server_addr']) )
        {
            $params['http_x_cache_server_addr'] = $request->server['http_x_cache_server_addr'];
        }

        if (isset($params['otype']) && $params['otype'] == "json") {
            $response->header('content-type', 'application/json; charset=utf-8');
        } else {
            $response->header('content-type', 'application/xml; charset=utf-8');
        }

        if( Config::getField('bili', 'force_open_file_cache'))
        {
            $video_info = VideoLoad::offlineVideoLoad($params);
            return $video_info;
        } else {
            $promise_group = new PromiseGroup();

            $promise_group->add("count" , function(Promise $promise) use($zone_id, $params) {
                $req_fmt = isset($params['type']) ? $params['type']: "flv";
                foreach(Cache::getInstance()->getDispatchZoneGroups() as $group )
                {
                    if (Utils::check_in_zone($zone_id, $group))
                    {
                        $tmp_fmt = $req_fmt;
                        if( $req_fmt == 'mp4' && isset( $params['quality']) && intval($params['quality']) >= 2 )
                        {
                            $tmp_fmt = 'hdmp4';
                        }

                        $key = "zreq:" . $tmp_fmt . ":" . $group . ":" . $params['cid'];
                        AsyncRedis::getInstance()->incrBy($key, 1, function($client, $result){

                        });
                    }
                }
                $promise->resolve(true);
            });


            $promise_group->add("ip" , function(Promise $promise) use($ip_info, $params, $response){
                $result = [];
                $flag = Identifier::getInstance()->check_ip_valid($ip_info, $result);
                $promise->resolve([$flag, $result]);
            });

            $promise_group->add("player" , function(Promise $promise) use($request, $params, $response){
                if (isset($params['player'])) {
                    // check flash player
                    $result = [];
                    $flag = Identifier::getInstance()->check_flash($request->get, $result);
                    $promise->resolve([$flag, $result]);
                } else {
                    Identifier::getInstance()->check_appkey($request->get, $promise);
                }
            });

            $promise_group->add("member" , function(Promise $promise) use($request, $params, $response){

                Identifier::getInstance()->check_member($request, $response, $params, $promise);
            });

            $cid_promise = new Promise();
            $cid_promise->then(function($value) use($response, $params) {

                if(!$value['ip'][0])
                {
                    $result = $value['ip'][1];
                    $video_info = Display::output($params, $result);
                    self::display($response, $video_info);
                }
                if(!$value['player'][0])
                {
                    $result = $value['player'][1];
                    $video_info = Display::output($params, $result);
                    self::display($response, $video_info);
                }
                if( empty($value['member']))
                {
                    $params['is_member'] = false;
                } else {
                    $params['is_member'] = true;
                    $mid = $value['member'];
                }

                $rate = RateController::getInstance()->getRate($params);
                $params['rate'] = $rate;

                $promise = new Promise();
                VideoLoad::load_by_cid($params, $promise);
                return $promise;
            })->then(function($video_info) use($params, $ip_info, $ip_address, $request, $response){
                if( empty($video_info) )
                {
                    $video_info = Display::output($params, array(
                        'result' => 'error',
                        'message' => 'wl'
                    ));
                    self::display($response, $video_info);
                }
                $result = array();

                $areaFlag = 0;
                $realAreaFlag = 0;

                if (in_array( $ip_info['province'] , Config::get('in_province') )){
                    $areaFlag = 2;
                }else if ($ip_info['country'] == "中国")
                {
                    $areaFlag |= 1;
                }else if ($ip_info['country'] == "日本")
                {
                    $areaFlag |= 4;
                }else if ($ip_info['country'] == "美国")
                {
                    $areaFlag |= 8;
                }else
                {
                    $areaFlag |= 16;
                }
                if ($ip_address == "173.252.248.1" || !isset($request->header['user-agent']) )
                {
                    $areaFlag = 1;
                }
                if ($ip_info['country'] == "局域网")
                {
                    $areaFlag = 0;
                }

                if ($ip_info['province'] == "香港" || $ip_info['province']== "澳门" || $ip_info['province'] == "台湾"){
                    $realAreaFlag = 2;
                }else if ($ip_info['country'] == "中国")
                {
                    $realAreaFlag |= 1;
                }else if ($ip_info['country'] == "日本")
                {
                    $realAreaFlag |= 4;
                }else if ($ip_info['country'] == "美国")
                {
                    $realAreaFlag |= 8;
                }else
                {
                    $realAreaFlag |= 16;
                }
                if ($ip_info['country'] == "局域网") {
                    $realAreaFlag = 0;
                }

                if (Identifier::getInstance()->check_arcflag($params, $video_info, $result) == false) {
                    $video_info = Display::output($params, $result);
                    self::display($response, $video_info);
                }
                // After account check arc rank check
                $result = array();
                if (Identifier::getInstance()->check_arcrank($params, $video_info, $result) == false) {
                    $video_info = Display::output($params, $result);
                    self::display($response, $video_info);
                }
                $result = array();
                $video_info["pc_area_flag"] = $areaFlag;
                $video_info["real_area_flag"] = $realAreaFlag;
                if (Identifier::getInstance()->check_area_limit($params, $video_info, $result) == false) {
                    $video_info = Display::output($params, $result);
                    self::display($response, $video_info);
                }

                $promise = new Promise();
                VideoLoad::load_dp($params, $promise);

                return $promise;
            })->then(function($video_info) use ($response){
                $response->end(json_encode($video_info, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
            });

            $promise_group->setPromise($cid_promise);
            $promise_group->run();

            return -1;
        }
    }

    private static function display($response, $data)
    {
        $response->end($data);
        throw new \Exception();
    }
}
