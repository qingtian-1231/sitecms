<?php
namespace app\common\model;

class Article extends App
{
    public $display = ['title', 'content', 'tags'];
    public $parentModel = 'Menu';
    public $cname = '文章';


    public $assoc = array(
        'Menu' => array(
            'type' => 'belongsTo'
        ),
        'ArticlePicture'=>array(
            'type'=>'hasMany',
            'foreignKey'=>'foreign_id',
            'where'=> [
                ['module', '=', 'ArticlePicture']
            ]
        ),
        'User1' => array(
            'foreign' => 'User',//真正关联得模型名，不写默认是键
            'type' => 'belongsTo'
        ),
    );

    public function initialize()
    {
        $this->form = array(
            'id' => array(
                'type' => 'integer',
                'name' => 'ID',
                'elem' => 'hidden',
            ),
            'menu_id' => array(
                'type' => 'integer',
                'name' => '所属栏目',
                'elem' => 'nest_select.Menu',
                'foreign' => 'Menu.title',
                'list' => 'assoc'
            ),
            'user_id' => array(
                'type' => 'integer',
                'name' => '所属用户',
                'foreign' => 'User1.username',
                // 这里得User1必须是assoc中定义得键（也必须再assoc中先定义关联），不再支持User.username
                'elem' => 0,//'assoc_select',
                'assoc_options' => [
                    'where' => [
                        ['id' ,'>', 1]
                    ],
                    'order' => ['id' => 'DESC'],
                    'limit' => 10//每页数量 默认10
                ],
                'list' => 'assoc'
            ),
            'title' => array(
                'type' => 'string',
                'name' => '标题',
                'elem' => 'text',
                'list' => 'show'
            ),
            'ex_title' => array(
                'type' => 'string',
                'name' => '副标题',
                'elem' => 'text',
                //字段多图式例
                /*
                'elem' => 'multi_image',
                'image' => [
                    'resize' => [ //如果未定义就是直接使用上传的原图（如果非上传图片如woofinder选择的图片，即使定义了resize也不会改变图片大小）；图片上传强制尺寸定义，如要使用原图无需定义该属性；定义以后，无论用户上传多大图片都会被剪裁至指定大小
                        'width' => 400,//强制尺寸宽度
                        'height' => 400,//强制尺寸高度
                        'method' => 3,//强制剪裁类型（1-6的类型请参见TP手册）
                        'customable' => true// 是否显示可自定义图片resize尺寸的输入框，未定义不显示
                    ],
                    'maxLength' => 5 //该字段最多可以上传或选择5张图片，如果未定义表示不限制
                ],
                'upload' => [
                    'maxSize' => 2048,//允许文件上传的最大值（先受PHP配置文件约束），单位：KB
                    'validExt' => ['jpg', 'png', 'gif'],//允许上传的文件扩展名，不区分大小写
                ],*/
                'list' => 'multi_image'//列表使用multi_image配合图片类型显示
            ),
            'tags' => array(
                'type' => 'string',
                'name' => '文章标签',
                'elem' => 'tag',
                'list' => 'tag',
                'tag_length' => 6
            ),
            'date' => array(
                'type' => 'date',
                'name' => '发布日期',
                'elem' => 'date',//这里就固定不变了，需要做不同类型的在options的type中设置
                'list' => 'date',
                'options' => [
                    'type' => 'date',//类型，默认date(Y-m-d)、datetime（Y-m-d H:i:s）、year（年份选择器）、month（年-月选择器）、time （H:i:s）
                    //'range' => true,//时间范围选择，默认false，开启true，也可以写 '-'用来自定义2个时间点的分隔符（如果true，默认用~分割符）
                    //'min' => '2018-01-01',//日历可选择的最小时间，默认不限制
                    //'max' => date('Y-m-d'),//日历可选择的最大时间，默认不限制
                    //'calendar' => true//假日备注，也可以自定义
                ]
            ),
//            'author' => array(
//                'type' => 'string',
//                'name' => '作者',
//                'elem' => 'text',
//                'list' => 'show',
//                'quick' => true
//            ),
            /*
             // transfer 测试代码 可以借鉴
            'author' => [
                'type' => 'array',
                'name' => '作者',
                'elem' => 'transfer',
                // 选项  键值对 可以动态对options进行赋值
                'options' => [
                    'a' => "成都",
                    'b' => '北京',
                    'c' => '天津',
                    'd' => '重庆',
                    'e' => '杭州',
                    'f' => '深圳',
                    'g' => '广州',
                    'h' => '厦门'
                ],
                'transfer' => [
                    'title' => ['右列表', '左列表'],
                    'showSearch' => true,
                    'width' => 200,
                    'height' => 320,
                ]
            ],
            */
            /* formSelects 测试代码 可以借鉴
            'author' => array(
                'type' => 'string',
                'name' => 'aaa',
                'elem' => 'formSelects',
                'options' => [
                    'a' => '成都',
                    'b' => '北京',
                    'c' => '天津',
                    'd' => '重庆',
                    'e' => '杭州',
                    'f' => '深圳',
                    'g' => '广州',
                    'h' => '厦门'
                ],
                'select4' => [
                    'type' => 'checkbox',//支持checkbox多选、radio单选  默认radio单选
                    'skin' => 'default',//皮肤default | primary | normal | warm | danger 默认default
                    'height' => '36px', //标记select高度是否固定 建议是36的倍数 ；默认36px 自动换行写 null 或去掉该配置
                    'direction' => 'auto', //下拉方向  "auto|up|down", 自动, 上, 下,；默认自动模式
                    'search_type' => 'dl',// 支持 title 和 dl  "title" 在下拉选title部分显示,"dl" 在选项的第二条显示 ；默认dl
                    'select_max' => 0, //多选最多选择数量 不显示请填写0；默认0不显示
                ]
            ),
            */

//            'from' => array(
//                'type' => 'string',
//                'name' => '来源',
//                'elem' => 'text',
//                'list' => 'show',
//                'quick' => true
//            ),
            'is_verify' => array(
                'type' => 'boolean',
                'name' => '审核',
                'elem' => 'checker',
                'list' => 'checker',
                'sortable' => true
            ),
            'image' => array(
                'type' => 'string',
                'name' => '封面图片',
                'elem' => 'image.upload',
                'list' => 'image',
                'image' => array(
                    'thumb' => array(
                        'field' => 'thumb'
                    ),
                ),
                'upload' => array(
                    'maxSize' => 512,
                    'validExt' => array('jpg', 'png', 'gif')
                )
            ),
            'thumb' => array(
                'type' => 'string',
                'name' => '缩略图',
                'elem' => 0,
                'list' => 0,
                'detail' => 0
            ),
            'video' => array(
                'type' => 'string',
                'name' => '视频文件',
                'elem' => 'file',
                'list' => 'file',
                'upload' => array(
                    'maxSize' => 1024 * 20,
                    'validExt' => array('mp4')
                )
            ),

            'content' => array(
                'type' => 'text',
                'name' => '内容',
                'elem' => 'editor',
                'length' => 80,
                'list' => 0
            ),
            'visit_count' => array(
                'type' => 'integer',
                'name' => '访问计数',
                'elem' => 0,
                'list' => 'show',
            ),
            'summary' => array(
                'type' => 'text',
                'name' => '摘要说明',
                'elem' => 'textarea',
                'list' => 'show',
                'elem_group' => 'advanced',
            ),
            'link' => array(
                'type' => 'string',
                'name' => '外部链接',
                'elem' => 'text',
                'list' => 'show',
                'elem_group' => 'advanced',
            ),
            'is_index' => array(
                'type' => 'boolean',
                'name' => '首页优先',
                'elem' => 'checker',
                'list' => 'checker',
                'elem_group' => 'advanced',
            ),
            'is_recommend' => array(
                'type' => 'boolean',
                'name' => '推荐',
                'elem' => 'checker',
                'list' => 'checker',
                'elem_group' => 'advanced',
            ),
            'created' => array(
                'type' => 'datetime',
                'name' => '添加时间',
                'elem' => 0,
                'list' => 'datetime',
                'elem_group' => 'advanced',
            ),
            'modified' => array(
                'type' => 'datetime',
                'name' => '修改时间',
                'elem' => 0,
                'list' => 'datetime',
                'elem_group' => 'advanced',
            ),
            'list_order' => array(
                'type' => 'integer',
                'name' => '排序权重',
                'elem' => 'number',
                'list' => 'edit.text',
                'elem_group' => 'advanced',
            ),
            'picture_count' => array(
                'type' => 'integer',
                'name' => '图片数量',
                'elem' => 0,
                'list' => 'counter',
                'counter' => 'ArticlePicture',
                'elem_group' => 'advanced',
            ),
            'keywords' => array(
                'type' => 'string',
                'name' => 'SEO关键字',
                'elem' => 'text',
                'elem_group' => 'advanced',
            ),
            'description' => array(
                'type' => 'string',
                'name' => 'SEO描述',
                'elem' => 'textarea',
                'elem_group' => 'advanced',
            )
        );
        call_user_func_array(array('parent', __FUNCTION__), func_get_args());
    }

    public $formGroup = array(
        'advanced' => '高级选项'
    );

    protected $validate = array(
        'title' => array(
            'rule' => 'require',
            'message' => '请填写标题'
        ),
        'menu_id' => array(
            array(
                'rule' => array('egt', 1),
                'message' => '请选择父级导航'
            ),
            array(
                'rule' => array('call', 'checkTypeOfMenu')
            )
        )
    );

}
