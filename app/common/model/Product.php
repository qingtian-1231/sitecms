<?php
namespace app\common\model;

class Product extends App
{
    public $display = 'title';
    public $parentModel = 'Menu';

    public $assoc = array(
        'Menu' => array(
            'type' => 'belongsTo'
        ),
        'User' => array(
            'type' => 'belongsTo'
        ),
        'ProductPicture'=>array(
            'type'=>'hasMany',
            'foreignKey'=>'foreign_id',
            'where'=>['module'=>'ProductPicture']
        )
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
                'foreign' => 'User.username',
                'elem' => 0,
                'list' => 'assoc'
            ),
            'title' => array(
                'type' => 'string',
                'name' => '产品名称',
                'elem' => 'text',
                'list' => 'show',
            ),
            'ex_title' => array(
                'type' => 'string',
                'name' => '产品别名',
                'elem' => 'text',
                'list' => 'show',
            ),
            'date' => array(
                'type' => 'date',
                'name' => '发布日期',
                'elem' => 'date',
                'list' => 'date',
            ),
            'price' => array(
                'type' => 'float',
                'name' => '价格',
                'elem' => 'text',
                'list' => 'show',
            ),

            'original_price' => array(
                'type' => 'float',
                'name' => '原价',
                'elem' => 'text',
                'list' => 'show',
            ),

            'specification' => array(
                'type' => 'string',
                'name' => '型号/规格',
                'elem' => 'text',
                'list' => 'show',
            ),

            'brand' => array(
                'type' => 'string',
                'name' => '品牌',
                'elem' => 'text',
                'list' => 'show',
            ),

            'unit' => array(
                'type' => 'string',
                'name' => '单位',
                'elem' => 'text',
                'list' => 'show',
            ),

            'quantity' => array(
                'type' => 'integer',
                'name' => '数量',
                'elem' => 'number',
                'list' => 'show',
            ),

            'is_verify' => array(
                'type' => 'boolean',
                'name' => '审核',
                'elem' => 'hidden',
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
                    )
                ),
                'upload' => array(
                    'maxSize' => 2048,
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
                'list' => 'show',
                'elem_group' => 'advanced',
            ),
            'picture_count' => array(
                'type' => 'integer',
                'name' => '图片数量',
                'elem' => 0,
                'list' => 'counter',
                'counter' => 'ProductPicture',
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
