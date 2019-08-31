<?php
namespace app\common\model;

use woo\utility\Hash;

class Menu extends App
{
    public $display = 'title';
    public $parentModel = 'parent';
    public $cache = [];
    public $assoc = [

    ];

    public function initialize()
    {
        $this->form = array(
            'id' => array(
                'type' => 'integer',
                'name' => 'ID',
                'elem' => 'hidden',
            ),
            'parent_id' => array(
                'type' => 'integer',
                'name' => '父级栏目',
                'elem' => 'nest_select.Menu'
            ),
            'family' => array(
                'type' => 'string',
                'name' => '家族',
                'elem' => 0,
            ),
            'level' => array(
                'type' => 'integer',
                'name' => '层级深度',
                'elem' => 0
            ),
            'child_count' => array(
                'type' => 'integer',
                'name' => '子栏目数',
                'elem' => 0
            ),

            'title' => array(
                'type' => 'string',
                'name' => '栏目标题',
                'elem' => 'text'
            ),
            'title_alias' => array(
                'type' => 'string',
                'name' => '标题别名',
                'elem' => 'text'
            ),
            'ex_title' => array(
                'type' => 'string',
                'name' => '栏目副标题',
                'elem' => 'text'
            ),
            'alias' => array(
                'type' => 'string',
                'name' => 'URL别名',
                'elem' => 'text',
                'info' => '别名只允许字母组成，用别名代替自动生成的栏目链接'
            ),
            'type' => array(
                'type' => 'string',
                'name' => '栏目类型',
                'elem' => 'formSelects',
                'select4' => [
                    'type' => 'radio',
                    'skin' => 'default',
                    'height' => '36px'
                ],
                'options' => $GLOBALS['Model_map'],
            ),
            'ex_link' => array(
                'type' => 'string',
                'name' => '链接地址',
                'elem' => 'text',
                'info' => '当选择类型为"链接"时,在此填入链接的地址',
            ),
            'list_count' => array(
                'type' => 'integer',
                'name' => '列表页条数',
                'elem' => 'number'
            ),
            'is_nav' => array(
                'type' => 'integer',
                'name' => '导航显示',
                'elem' => 'checker',
                'info' => '选中该项则在导航中显示'
            ),
            'is_redirect' => array(
                'type' => 'integer',
                'name' => '自动跳转',
                'elem' => 'checker',
                'info' => '内容为空时自动跳转到第一个子栏目,不为空或者子了栏目不存在时不跳转'
            ),
            'is_map' => array(
                'type' => 'integer',
                'name' => '地图显示',
                'elem' => 'checker',
                'info' => '选中该项则在“网站地图”中显示'
            ),
            'list_order' => array(
                'type' => 'integer',
                'name' => '排序权重',
                'elem' => 'number',
                'list' => 'edit'
            ),
            'image' => array(
                'type' => 'string',
                'name' => '图片',
                'elem' => 'image',
                'upload' => array(
                    'maxSize' => 1024,
                    'validExt' => array('jpg', 'gif', 'png'),
                ),
                'elem_group' => 'advanced',
            ),
            'mobile_image' => array(
                'type' => 'string',
                'name' => '手机图片',
                'elem' => 'image',
                'upload' => array(
                    'maxSize' => 1024,
                    'validExt' => array('jpg', 'gif', 'png'),
                ),
                'elem_group' => 'advanced',
            ),
            'thumb_width' => array(
                'type' => 'integer',
                'name' => '缩略图宽度',
                'elem' => 'text',
                'info' => '留空表示使用全局设置',
                'elem_group' => 'advanced',
            ),
            'thumb_height' => array(
                'type' => 'integer',
                'name' => '缩略图高度',
                'elem' => 'text',
                'info' => '留空表示使用全局设置',
                'elem_group' => 'advanced',
            ),
            'thumb_method' => array(
                'type' => 'string',
                'name' => '缩略图方式',
                'elem' => 'formSelects',
                'options' => array(
                    0 => '使用全局设置',
                    1 => '等比例缩放',
                    2 => '缩放后填充',
                    3 => '居中裁剪',
                    4 => '左上角裁剪',
                    5 => '右下角裁剪',
                    6 => '固定尺寸缩放'
                ),
                'select4' => [
                    'type' => 'radio',
                    'skin' => 'default',
                    'height' => '36px'
                ],
                'elem_group' => 'advanced',
            ),
            'template' => array(
                'type' => 'string',
                'name' => '单独模板',
                'elem' => 'keyvalue',
                'info' => '此栏仅供开发人员使用,如果您不清楚请将此栏留空,随意填写会导致这个栏目无法显示!',
                'elem_group' => 'advanced',
            ),
            'list_style' => array(
                'type' => 'string',
                'name' => '列表风格',
                'elem' => 'formSelects',
                'select4' => [
                    'type' => 'radio',
                    'skin' => 'default',
                    'height' => '36px'
                ],
                'elem_group' => 'advanced',
                'options' => [
                    '纯文风格' => [
                        'show_case_1' => '简约(默认)【show_case_1】',
                        'show_case_16' => '标题+简介【show_case_16】',
                    ],
                    '九谷风格' => [
                        'show_case_jiugu_list' => '九谷三组列表风格',
                        'show_case_jiugu_list1' => '九谷新闻列表风格',
                        'show_case_jiugu_list2' => '九谷产品列表风格',
                        'show_case_jiugu' => '九谷封面简介风格',
                    ],
                    '封面+标题风格' => [
                        'show_case_3' => '列表一【show_case_3】',
                        'show_case_4' => '列表二【show_case_4】',
                        'show_case_5' => '列表三【show_case_5】',
                        'show_case_6' => '列表四【show_case_6】',
                        'show_case_7' => '列表五【show_case_7】',
                        'show_case_8' => '列表六(瀑布流)【show_case_8】',
                        'show_case_9' => '列表七【show_case_9】',
                        'show_case_17' => '列表八(瀑布流)【show_case_17】'

                    ],
                    '封面+简介风格' => [
                        'show_case_2' => '列表一【show_case_2】',
                        'show_case_10' => '列表二【show_case_10】',
                        'show_case_11' => '列表三【show_case_11】',
                        'show_case_12' => '列表四【show_case_12】',
                        'show_case_13' => '列表五【show_case_13】',
                        'show_case_14' => '列表六【show_case_14】',
                        'show_case_15' => '列表七【show_case_15】',
                    ]
                ],
                'info' => '如果列表为单独模板或定制开发，设置可能无效'
            ),
            'page_style' => array(
                'type' => 'string',
                'name' => '列表翻页方式',
                'elem' => 'radio',

                'options' => array(
                    '1' => '页码翻页',
                    '2' => '手动瀑布流',
                    '3' => '自动瀑布流'
                ),
                'elem_group' => 'advanced',
            ),
            'summary' => array(
                'type' => 'string',
                'name' => '文字简介',
                'elem' => 'textarea',
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
            ),

        );

        call_user_func(array('parent', __FUNCTION__));
    }

    public $formGroup = [
        'advanced' => '高级选项'
    ];

    public $fieldRespond = [
        'type' => [
            'RespondField' => ['ex_link'],
            'Exlink' => ['ex_link']
        ]
    ];


    protected $validate = [
        'title' => [
            'rule' => 'require',
            'message' => '请填写栏目标题'
        ],
        'parent_id' => array(
            [
                'rule' => array('egt', 1),
                'message' => '请选择父级导航'
            ],
            [
                'rule' => array('call', 'checkParent'),
                'on' => 'edit'
            ]
        ),
        'type' => [
            'rule' => 'require',
            'message' => '请选择栏目类型'
        ]
    ];

    public function checkParent($value, $rule, $data)
    {
        if ($value == $data['id'] || in_array($value, (array)menu('children', $data['id']))) {
            return '不能选择本栏目以及其子栏目做为父级';
        }
        return true;
    }

    public function beforeInsertCall()
    {
        $parent_rslt = call_user_func(array('parent', __FUNCTION__));
        $this['level'] = 0;
        $this['family'] = '';
        $this['child_count'] = 0;
        return $parent_rslt;
    }

    public function afterWriteCall()
    {
        $parent_rslt = call_user_func(array('parent', __FUNCTION__));
        if (empty($this->oldData)) {
            if (isset($this['parent_id'])) {
                $new_data['id'] = $this['id'];
                $parent_family = menu($this['parent_id'], 'family') ? menu($this['parent_id'], 'family') : $this->get($this['parent_id'])->family;
                $new_data['family'] = $parent_family . $this['id'] . ',';
                $new_data['level'] = intval(menu($this['parent_id'], 'level')) + 1;
                if (is_array(menu('children', $this['parent_id']))) {
                    $parent_data['id'] = $this['parent_id'];
                    $parent_data['child_count'] =  count(menu('children', $this['parent_id'])) + 1;
                }
            }
        } else {
            if (isset($this['parent_id'])) {
                if ($this['parent_id'] != $this->oldData['parent_id']) {
                    $new_data['id'] = $this['id'];
                    $parent_family = menu($this['parent_id'], 'family') ? menu($this['parent_id'], 'family') : $this->get($this['parent_id'])->family;
                    $new_data['family'] = $parent_family . $this['id'] . ',';
                    $new_data['level'] = menu($this['parent_id'], 'level') + 1;

                    if (is_array(menu('children', $this['parent_id']))) {
                        $parent_data['id'] = $this['parent_id'];
                        $parent_data['child_count'] =  count(menu('children', $this['parent_id'])) + 1;
                    }
                    if (is_array(menu('children', $this->oldData['parent_id']))) {
                        $old_parent_data['id'] = $this->oldData['parent_id'];
                        $old_parent_data['child_count'] = count(menu('children', $this->oldData['parent_id'])) - 1;
                    }
                }
            }
        }
        if (isset($new_data)) {
            $this->isValidate(false)->where('id', '=', $new_data['id'])->update($new_data);
        }
        if (isset($parent_data)) {
            $this->isValidate(false)->where('id', '=', $parent_data['id'])->update($parent_data);
        }
        if (isset($old_parent_data)) {
            $this->isValidate(false)->where('id', '=', $old_parent_data['id'])->update($old_parent_data);
        }
        $this->writeToFile();
        if (isset($this['type'])) {
            if ($this['type'] === 'Page') {
                $pageModel = model('Page');
                $pageExists = $pageModel->where(['menu_id' => $this['id']])->find();
                if (empty($pageExists)) {
                    $pageData = array(
                        'title' => $this['title'],
                        'menu_id' => $this['id'],
                        'user_id' => helper('Auth')->user('id'),
                        'is_verify' => 1
                    );
                    $pageModel->createData($pageData);
                }
            }
        }
        return $parent_rslt;
    }

    public function afterDeleteCall()
    {
        $parent_rslt = call_user_func(array('parent', __FUNCTION__));
        if (isset($this['id'])) {
            $delete_data = $this->getData();
            $parent_data['child_count'] = count(menu('children', $delete_data['parent_id'])) - 1;
            $this->where('id', '=', $delete_data['parent_id'])->update($parent_data);
            $this->where('family', 'LIKE', "%,{$delete_data['id']},%")->delete();
        }
        $this->writeToFile();
        return $parent_rslt;
    }

    public function writeToFile()
    {
        $list = $this
            ->order(['level' => 'ASC', 'list_order' => 'ASC', 'id' => 'ASC'])
            ->select()
            ->toArray();
        //$this->multiSelect($list, 'menu');  如果选择栏目的方式为multi_select.menu，必须调用
        $list = $full = Hash::combine($list, '{n}.id' , '{n}');
        $first = reset($list);
        $this->cache['threaded'][$first['id']] = $this->threaded($first['id'], $list);
        $this->getChildren($this->cache['threaded'], 'children');

        $list = $this
            ->where([
                ['is_nav', '=', 1],
                ['level', '>=', 1]
            ])
            ->order(['level' => 'ASC', 'list_order' => 'ASC', 'id' => 'ASC'])
            ->select()
            ->toArray();
        $this->cache['nav'] = $this->threaded($first['id'], $list);
        $this->getChildren($this->cache['nav'], 'nav_children');
        $this->cache['list'] = $full;

        $alias = $this
            ->field(['id', 'alias'])
            ->where('alias', '<>', '')
            ->select()
            ->toArray();
        $alias = Hash::combine($alias, '{n}.id', '{n}.alias');
        $this->cache['alias'] = $alias;
        write_file_cache('Menu', $this->cache);

        return true;
    }
}
