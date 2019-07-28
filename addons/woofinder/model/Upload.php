<?php
namespace addons\woofinder\model;

use woo\model\WooModel;
use woo\utility\Hash;

class Upload extends WooModel
{
    ##创建日期字段
    protected $createTime = 'created';
    ##最后修改日期字段
    protected $updateTime = 'modified';
}
