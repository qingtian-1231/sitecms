<?php
// 加载woo函数库
include_once WOO_PATH . 'common.php';

// 定义你自己的公共函数

function get_route_key($request) 
{
	return md5($request->url(true) . ':' . $request->method() . ':' . ($request->isAjax() ? 1 : 0));
}
