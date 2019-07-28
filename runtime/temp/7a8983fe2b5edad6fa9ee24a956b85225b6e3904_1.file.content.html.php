<?php /* Smarty version 3.1.27, created on 2019-07-28 22:29:02
         compiled from "/var/www/html/site/app/run/view/menu/content.html" */ ?>
<?php
/*%%SmartyHeaderCode:4382767845d3db12e686e22_76880157%%*/
if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '7a8983fe2b5edad6fa9ee24a956b85225b6e3904' => 
    array (
      0 => '/var/www/html/site/app/run/view/menu/content.html',
      1 => 1562144776,
      2 => 'file',
    ),
    'f166472f4915fc043f5b161b11a76382ed9b893c' => 
    array (
      0 => '/var/www/html/site/app/run/view/base.html',
      1 => 1562678954,
      2 => 'file',
    ),
    '76714b4494d395fbfe9447370f133ee36f99059c' => 
    array (
      0 => '/var/www/html/site/app/run/view/global.html',
      1 => 1547178652,
      2 => 'file',
    ),
    'a65c657aafb0621875cbbb17e9f2fc29e4e835e8' => 
    array (
      0 => '/var/www/html/site/app/run/view/functions.html',
      1 => 1547890714,
      2 => 'file',
    ),
    '6a2ca03266b3e2ec3d1578e0c86908526be42adb' => 
    array (
      0 => '6a2ca03266b3e2ec3d1578e0c86908526be42adb',
      1 => 0,
      2 => 'string',
    ),
    '4179da4fb2635054eea08d6ab9e36d58a01e70a6' => 
    array (
      0 => '4179da4fb2635054eea08d6ab9e36d58a01e70a6',
      1 => 0,
      2 => 'string',
    ),
    'a189f76d4ef72df41ae637a909ce759ab8780606' => 
    array (
      0 => 'a189f76d4ef72df41ae637a909ce759ab8780606',
      1 => 0,
      2 => 'string',
    ),
  ),
  'nocache_hash' => '4382767845d3db12e686e22_76880157',
  'variables' => 
  array (
    'is_response' => 0,
    'meta' => 0,
    'is_favicon' => 0,
    'root' => 0,
    'css' => 0,
    'html' => 0,
    'absroot' => 0,
    'params' => 0,
    'js' => 0,
    'deferJs' => 0,
  ),
  'has_nocache_code' => false,
  'version' => '3.1.27',
  'unifunc' => 'content_5d3db12e7cb7d3_98243013',
  'tpl_function' => 
  array (
    'url' => 
    array (
      'called_functions' => 
      array (
      ),
      'compiled_filepath' => '/var/www/html/site/runtime/temp/7a8983fe2b5edad6fa9ee24a956b85225b6e3904_1.file.content.html.php',
      'uid' => 'a65c657aafb0621875cbbb17e9f2fc29e4e835e8',
      'call_name' => 'smarty_template_function_url_13022898535d3db12e72d793_48825746',
    ),
    'furl' => 
    array (
      'called_functions' => 
      array (
      ),
      'compiled_filepath' => '/var/www/html/site/runtime/temp/7a8983fe2b5edad6fa9ee24a956b85225b6e3904_1.file.content.html.php',
      'uid' => 'a65c657aafb0621875cbbb17e9f2fc29e4e835e8',
      'call_name' => 'smarty_template_function_furl_13022898535d3db12e72d793_48825746',
    ),
    'menu_link' => 
    array (
      'called_functions' => 
      array (
      ),
      'compiled_filepath' => '/var/www/html/site/runtime/temp/7a8983fe2b5edad6fa9ee24a956b85225b6e3904_1.file.content.html.php',
      'uid' => 'a65c657aafb0621875cbbb17e9f2fc29e4e835e8',
      'call_name' => 'smarty_template_function_menu_link_13022898535d3db12e72d793_48825746',
    ),
    'menu' => 
    array (
      'called_functions' => 
      array (
      ),
      'compiled_filepath' => '/var/www/html/site/runtime/temp/7a8983fe2b5edad6fa9ee24a956b85225b6e3904_1.file.content.html.php',
      'uid' => '7a8983fe2b5edad6fa9ee24a956b85225b6e3904',
      'call_name' => 'smarty_template_function_menu_4382767845d3db12e686e22_76880157',
    ),
  ),
),false);
/*/%%SmartyHeaderCode%%*/
if ($_valid && !is_callable('content_5d3db12e7cb7d3_98243013')) {
function content_5d3db12e7cb7d3_98243013 ($_smarty_tpl) {

$_smarty_tpl->properties['nocache_hash'] = '4382767845d3db12e686e22_76880157';
?>
<!DOCTYPE html>
<html lang="zh-cn" >
<head>
<?php $_smarty_tpl->tpl_vars['form'] = new Smarty_Variable(helper('Form'), null, 0);
$_smarty_tpl->tpl_vars['html'] = new Smarty_Variable(helper('Html'), null, 0);?>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<?php if ($_smarty_tpl->tpl_vars['is_response']->value) {?>
<meta content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=no" name="viewport"/>
<meta content="IE=edge,chrome=1" http-equiv="X-UA-Compatible"/>
<meta name="renderer" content="webkit"/>
<meta name="HandheldFriendly" content="true"/>
<meta name="format-detection" content="telephone=no, email=no" />
<?php }?>
<meta name="Keywords" content="<?php if ($_smarty_tpl->tpl_vars['meta']->value['keywords']) {
echo implode(',',$_smarty_tpl->tpl_vars['meta']->value['keywords']);
}?>" />
<meta name="Description" content="<?php if ($_smarty_tpl->tpl_vars['meta']->value['description']) {
echo implode(',',$_smarty_tpl->tpl_vars['meta']->value['description']);
}?>" />
<?php if ($_smarty_tpl->tpl_vars['is_favicon']->value) {?><link rel="shortcut icon" href="<?php echo $_smarty_tpl->tpl_vars['root']->value;?>
favicon.ico" /><?php }?>
<title><?php echo implode(' - ',(($tmp = @$_smarty_tpl->tpl_vars['meta']->value['title'])===null||$tmp==='' ? array() : $tmp));?>
</title>
<?php echo $_smarty_tpl->tpl_vars['html']->value->css($_smarty_tpl->tpl_vars['css']->value,true);?>


<?php echo '<script'; ?>
 type="text/javascript">var wwwroot='<?php echo $_smarty_tpl->tpl_vars['root']->value;?>
';var absroot='<?php echo $_smarty_tpl->tpl_vars['absroot']->value;?>
';var module_name='<?php echo $_smarty_tpl->tpl_vars['params']->value['module'];?>
';<?php echo '</script'; ?>
>
<?php echo $_smarty_tpl->tpl_vars['html']->value->script($_smarty_tpl->tpl_vars['js']->value,true);?>


<?php /*  Call merged included template "functions.html" */
echo $_smarty_tpl->getInlineSubTemplate('functions.html', $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, '13022898535d3db12e72d793_48825746', 'content_5d3db12e726f70_90466945');
/*  End of included template "functions.html" */?>

<?php
$_smarty_tpl->properties['nocache_hash'] = '4382767845d3db12e686e22_76880157';
?>

<?php echo '<script'; ?>
 type="text/javascript">

var layer;
var is_layer_loading = <?php echo (($tmp = @setting('is_layer_loading'))===null||$tmp==='' ? 0 : $tmp);?>
;
$(function(){
    layui.use(['layer'], function(){
        layer = layui.layer;        
        layer.config({
            zIndex:10000
        });
        
        <?php if ($_smarty_tpl->tpl_vars['error']->value) {?>
          layer.msg('<?php echo $_smarty_tpl->tpl_vars['error']->value;?>
',{
            offset :['50px', '35%']
          });
        <?php }?>
    })
})

HKUC.ajax_request.defaultSuccessHandlers= {
    'success':function(rslt_msg,rslt_data){ 
       layer.alert(rslt_msg,{
            icon:1
        }); 
    },
    'error':function(rslt_msg,rslt_data){
        layer.alert(rslt_msg,{
            icon:2
        });                   
    },
    'nopower':function(msg,data){
        
        layer.alert(msg,{
            icon:2
        });
        layer.closeAll('loading');
    }
};
HKUC.ajax_request.defaultErrorHandlers={
    403:function(text,rerun){
        layer.alert('登录超时，请刷新重新登录',{
            icon:2
        });
	},
    404:function(text,rerun){
        layer.alert('页面不存在',{
            icon:2
        });
    }
};

<?php echo '</script'; ?>
>




</head>

<body>
<?php
$_smarty_tpl->properties['nocache_hash'] = '4382767845d3db12e686e22_76880157';
?>

<div class="admin_main">
    <div class="admin_header_fixed load-frame-hidden">
        <div class="admin_header clearfix">
            <div class="title"><i></i><?php echo $_smarty_tpl->tpl_vars['title']->value['operation'];
if ($_smarty_tpl->tpl_vars['parent_info']->value) {?><a class="parent_info" href="<?php echo url((($_smarty_tpl->tpl_vars['params']->value['controller']).('/')).('lists'),array_merge($_smarty_tpl->tpl_vars['args']->value,array('parent_id'=>null)));?>
">所属<?php echo $_smarty_tpl->tpl_vars['parent_info']->value['cname'];?>
：<?php echo $_smarty_tpl->tpl_vars['parent_info']->value['title'];?>
</a><?php }?></div>
            <?php if ($_smarty_tpl->tpl_vars['actions']->value) {?>
    		<div class="action">
                <b></b>
                <div class="layui-btn-group">
    			<?php
$_from = $_smarty_tpl->tpl_vars['actions']->value;
if (!is_array($_from) && !is_object($_from)) {
settype($_from, 'array');
}
$_smarty_tpl->tpl_vars['item'] = new Smarty_Variable;
$_smarty_tpl->tpl_vars['item']->_loop = false;
foreach ($_from as $_smarty_tpl->tpl_vars['item']->value) {
$_smarty_tpl->tpl_vars['item']->_loop = true;
$foreach_item_Sav = $_smarty_tpl->tpl_vars['item'];
if (!$_smarty_tpl->tpl_vars['item']->value) {
continue 1;
}?><a class="layui-btn  layui-btn-sm  layui-btn-shadow <?php echo $_smarty_tpl->tpl_vars['item']->value['class'];?>
 <?php if (strpos($_smarty_tpl->tpl_vars['item']->value['class'],'layui-btn') === false) {?>layui-btn-primary<?php }?>" href="<?php $_smarty_tpl->callTemplateFunction ('url', $_smarty_tpl, array('url'=>$_smarty_tpl->tpl_vars['item']->value['url']), true);?>
"><i class="fa <?php echo $_smarty_tpl->tpl_vars['item']->value['icon'];?>
"></i> <?php echo $_smarty_tpl->tpl_vars['item']->value['title'];?>
</a><?php
$_smarty_tpl->tpl_vars['item'] = $foreach_item_Sav;
}
?>
                </div>
            </div>
    		<?php }?>
        </div>
    </div>
    <div class="admin_base">
    
<?php $_smarty_tpl->tpl_vars['auth'] = new Smarty_Variable(helper('Auth'), null, 0);?>


<div id="menu_tree">
    <?php if (menu('threaded')) {
$_smarty_tpl->tpl_vars['threaded'] = new Smarty_Variable(reset(menu('threaded')), null, 0);
} else {
$_smarty_tpl->tpl_vars['threaded'] = new Smarty_Variable(array(), null, 0);
}?>
    <?php if ($_smarty_tpl->tpl_vars['threaded']->value) {?>
        <?php $_smarty_tpl->callTemplateFunction ('menu', $_smarty_tpl, array('data'=>$_smarty_tpl->tpl_vars['threaded']->value,'level'=>1), true);?>

    <?php }?>
</div>

    </div>
    <div class="admin_bottom">
    
    </div>
</div>

<?php if ($_smarty_tpl->tpl_vars['deferJs']->value) {?>
<?php echo $_smarty_tpl->tpl_vars['html']->value->script($_smarty_tpl->tpl_vars['deferJs']->value,true);?>

<?php }?>
<?php
$_smarty_tpl->properties['nocache_hash'] = '4382767845d3db12e686e22_76880157';
?>


<?php echo '<script'; ?>
>
$('#menu_tree .self').click(function(){
    var parent = $(this).parent('.menu_level') ;
    $('#menu_tree').find('.isClick').removeClass('isClick');
    parent.addClass('isClick');
    if(parent.hasClass('no_child')) return false ;
    if(parent.hasClass('isOpen'))
        parent.removeClass('isOpen').children('.menu_children').hide().end().children('.self').find('.fa-icon').removeClass('fa-minus-circle').addClass('fa-plus-circle')
    else
        parent.addClass('isOpen').children('.menu_children').show().end().children('.self').find('.fa-icon').removeClass('fa-plus-circle').addClass('fa-minus-circle')
}).find('a').click(function(e){
    e.stopPropagation();
    if($(this).hasClass('item-action-delete')){
        var href  = $(this).attr('href') ;
        layer.confirm('该操作不可逆，请确认删除该栏目及其子栏目？',function(){
            layer.closeAll();
            location.href  = href; 
        })
        return false ;
    }
})

<?php echo '</script'; ?>
>

<?php echo '<script'; ?>
>
    if (top.$('#wooBase').length ==  0) {
        $('body').append('<div onclick="location.href=\'<?php echo $_smarty_tpl->tpl_vars['absroot']->value;?>
run\'" id="goHome"><i  class="layui-icon layui-icon-home"></i></div>')
        $('#goHome').addClass('animated bounceInUp')
    }
<?php echo '</script'; ?>
>

</body>
</html><?php }
}
?><?php
/*%%SmartyHeaderCode:13022898535d3db12e72d793_48825746%%*/
if ($_valid && !is_callable('content_5d3db12e726f70_90466945')) {
function content_5d3db12e726f70_90466945 ($_smarty_tpl) {
?>
<?php
$_smarty_tpl->properties['nocache_hash'] = '13022898535d3db12e72d793_48825746';
?>



<?php
/*/%%SmartyNocache:13022898535d3db12e72d793_48825746%%*/
}
}
?><?php
/* smarty_template_function_url_13022898535d3db12e72d793_48825746 */
if (!function_exists('smarty_template_function_url_13022898535d3db12e72d793_48825746')) {
function smarty_template_function_url_13022898535d3db12e72d793_48825746($_smarty_tpl,$params) {
$saved_tpl_vars = $_smarty_tpl->tpl_vars;
$params = array_merge(array('url'=>'','item'=>array()), $params);
foreach ($params as $key => $value) {
$_smarty_tpl->tpl_vars[$key] = new Smarty_Variable($value);
}
if (is_array($_smarty_tpl->tpl_vars['url']->value)) {
if ($_smarty_tpl->tpl_vars['url']->value[1] && $_smarty_tpl->tpl_vars['url']->value['parse'] && $_smarty_tpl->tpl_vars['item']->value) {
$_from = $_smarty_tpl->tpl_vars['url']->value['parse'];
if (!is_array($_from) && !is_object($_from)) {
settype($_from, 'array');
}
$_smarty_tpl->tpl_vars['key'] = new Smarty_Variable;
$_smarty_tpl->tpl_vars['key']->_loop = false;
foreach ($_from as $_smarty_tpl->tpl_vars['key']->value) {
$_smarty_tpl->tpl_vars['key']->_loop = true;
$foreach_key_Sav = $_smarty_tpl->tpl_vars['key'];
if (!$_smarty_tpl->tpl_vars['url']->value[1][$_smarty_tpl->tpl_vars['key']->value]) {?>continue<?php }
$_smarty_tpl->createLocalArrayVariable('url', null, 0);
$_smarty_tpl->tpl_vars['url']->value[1][$_smarty_tpl->tpl_vars['key']->value] = $_smarty_tpl->tpl_vars['item']->value[$_smarty_tpl->tpl_vars['url']->value[1][$_smarty_tpl->tpl_vars['key']->value]];
$_smarty_tpl->tpl_vars['key'] = $foreach_key_Sav;
}
}
echo url($_smarty_tpl->tpl_vars['url']->value[0],(($tmp = @$_smarty_tpl->tpl_vars['url']->value[1])===null||$tmp==='' ? array() : $tmp),(($tmp = @$_smarty_tpl->tpl_vars['url']->value[2])===null||$tmp==='' ? true : $tmp));
} else {
echo $_smarty_tpl->tpl_vars['url']->value;
}
foreach (Smarty::$global_tpl_vars as $key => $value){
if ($_smarty_tpl->tpl_vars[$key] === $value) $saved_tpl_vars[$key] = $value;
}
$_smarty_tpl->tpl_vars = $saved_tpl_vars;
}
}
/*/ smarty_template_function_url_13022898535d3db12e72d793_48825746 */

?>
<?php
/* smarty_template_function_furl_13022898535d3db12e72d793_48825746 */
if (!function_exists('smarty_template_function_furl_13022898535d3db12e72d793_48825746')) {
function smarty_template_function_furl_13022898535d3db12e72d793_48825746($_smarty_tpl,$params) {
$saved_tpl_vars = $_smarty_tpl->tpl_vars;
$params = array_merge(array('url'=>''), $params);
foreach ($params as $key => $value) {
$_smarty_tpl->tpl_vars[$key] = new Smarty_Variable($value);
}
if ($_smarty_tpl->tpl_vars['url']->value) {
if (strpos($_smarty_tpl->tpl_vars['url']->value,'http://') === false && strpos($_smarty_tpl->tpl_vars['url']->value,'https://') === false) {
if ($_smarty_tpl->tpl_vars['url']->value[0] == '/') {
$_smarty_tpl->tpl_vars['url'] = new Smarty_Variable(substr($_smarty_tpl->tpl_vars['url']->value,1), null, 0);
}
$_smarty_tpl->tpl_vars['url'] = new Smarty_Variable(($_smarty_tpl->tpl_vars['root']->value).($_smarty_tpl->tpl_vars['url']->value), null, 0);
}
echo $_smarty_tpl->tpl_vars['url']->value;
}
foreach (Smarty::$global_tpl_vars as $key => $value){
if ($_smarty_tpl->tpl_vars[$key] === $value) $saved_tpl_vars[$key] = $value;
}
$_smarty_tpl->tpl_vars = $saved_tpl_vars;
}
}
/*/ smarty_template_function_furl_13022898535d3db12e72d793_48825746 */

?>
<?php
/* smarty_template_function_menu_link_13022898535d3db12e72d793_48825746 */
if (!function_exists('smarty_template_function_menu_link_13022898535d3db12e72d793_48825746')) {
function smarty_template_function_menu_link_13022898535d3db12e72d793_48825746($_smarty_tpl,$params) {
if (!is_callable('smarty_modifier_regex_replace')) require_once '/var/www/html/site/include/smarty-3.1.27/libs/plugins/modifier.regex_replace.php';
$saved_tpl_vars = $_smarty_tpl->tpl_vars;
$params = array_merge(array('id'=>0), $params);
foreach ($params as $key => $value) {
$_smarty_tpl->tpl_vars[$key] = new Smarty_Variable($value);
}
$_smarty_tpl->tpl_vars['url'] = new Smarty_Variable(menu_link($_smarty_tpl->tpl_vars['id']->value), null, 0);
echo smarty_modifier_regex_replace($_smarty_tpl->tpl_vars['url']->value,((('/\/').(config('run_module_index'))).('/')),'');
foreach (Smarty::$global_tpl_vars as $key => $value){
if ($_smarty_tpl->tpl_vars[$key] === $value) $saved_tpl_vars[$key] = $value;
}
$_smarty_tpl->tpl_vars = $saved_tpl_vars;
}
}
/*/ smarty_template_function_menu_link_13022898535d3db12e72d793_48825746 */

?>
<?php
/* smarty_template_function_menu_4382767845d3db12e686e22_76880157 */
if (!function_exists('smarty_template_function_menu_4382767845d3db12e686e22_76880157')) {
function smarty_template_function_menu_4382767845d3db12e686e22_76880157($_smarty_tpl,$params) {
$saved_tpl_vars = $_smarty_tpl->tpl_vars;
$params = array_merge(array('level'=>1), $params);
foreach ($params as $key => $value) {
$_smarty_tpl->tpl_vars[$key] = new Smarty_Variable($value);
}?>
<?php
$_from = $_smarty_tpl->tpl_vars['data']->value;
if (!is_array($_from) && !is_object($_from)) {
settype($_from, 'array');
}
$_smarty_tpl->tpl_vars['children'] = new Smarty_Variable;
$_smarty_tpl->tpl_vars['children']->_loop = false;
$_smarty_tpl->tpl_vars['self_id'] = new Smarty_Variable;
foreach ($_from as $_smarty_tpl->tpl_vars['self_id']->value => $_smarty_tpl->tpl_vars['children']->value) {
$_smarty_tpl->tpl_vars['children']->_loop = true;
$foreach_children_Sav = $_smarty_tpl->tpl_vars['children'];
?>
<?php $_smarty_tpl->tpl_vars['is_check_power'] = new Smarty_Variable($_smarty_tpl->tpl_vars['auth']->value->isCheckMenuPower(menu($_smarty_tpl->tpl_vars['self_id']->value,'type')), null, 0);?>
<?php if ($_smarty_tpl->tpl_vars['is_check_power']->value && !$_smarty_tpl->tpl_vars['auth']->value->checkMenuPower($_smarty_tpl->tpl_vars['self_id']->value,'lists')) {
continue 1;
}?>
<div class="menu_level menu_level_<?php echo $_smarty_tpl->tpl_vars['level']->value;?>
 <?php if (!$_smarty_tpl->tpl_vars['children']->value) {?>no_child<?php }?> <?php if ($_smarty_tpl->tpl_vars['level']->value > 1 && $_smarty_tpl->tpl_vars['children']->value) {?>isOpen<?php }?>">
    <div class="self clearfix">
        <i class="lline"></i>
        <span class="menu_action">
            <?php $_smarty_tpl->tpl_vars['type'] = new Smarty_Variable(menu($_smarty_tpl->tpl_vars['self_id']->value,'type'), null, 0);?>
            <?php if ($_smarty_tpl->tpl_vars['type']->value != 'Exlink') {?>
            <a href="<?php echo url(($_smarty_tpl->tpl_vars['type']->value).('/lists'),array('parent_id'=>$_smarty_tpl->tpl_vars['self_id']->value));?>
"><i class="fa fa-eye"></i>查看内容</a>
            <?php if (!$_smarty_tpl->tpl_vars['is_check_power']->value || ($_smarty_tpl->tpl_vars['is_check_power']->value && $_smarty_tpl->tpl_vars['auth']->value->checkMenuPower($_smarty_tpl->tpl_vars['self_id']->value,'create'))) {?>
            <a href="<?php echo url(($_smarty_tpl->tpl_vars['type']->value).('/create'),array('parent_id'=>$_smarty_tpl->tpl_vars['self_id']->value));?>
"><i class="fa fa-plus"></i>添加内容</a>
            <?php }?>
            <?php }?>           
            <a href="<?php $_smarty_tpl->callTemplateFunction ('menu_link', $_smarty_tpl, array('id'=>$_smarty_tpl->tpl_vars['self_id']->value), false);?>
" target="_blank"><i class="fa fa-eye"></i>预览</a>
        </span>
        <span class="menu_info clearfix">
            <?php $_smarty_tpl->tpl_vars['child_count'] = new Smarty_Variable(menu($_smarty_tpl->tpl_vars['self_id']->value,'child_count'), null, 0);?>
            <i class="fa  <?php if ($_smarty_tpl->tpl_vars['children']->value) {
if ($_smarty_tpl->tpl_vars['level']->value == 1) {?>fa-plus-circle<?php } else { ?>fa-minus-circle<?php }
} else { ?>fa-minus-circle<?php }?>  fa-lg left fa-icon"></i>
            <span class="menu_title left"><?php echo menu($_smarty_tpl->tpl_vars['self_id']->value,'title');?>
</span>
            <span class="menu_id left hover_hidden">〖ID：<?php echo $_smarty_tpl->tpl_vars['self_id']->value;?>
〗</span>
            <span class="menu_type left hover_hidden">〖栏目类型：<?php echo $_smarty_tpl->tpl_vars['mdls']->value['Menu']->form['type']['options'][menu($_smarty_tpl->tpl_vars['self_id']->value,'type')];?>
〗</span>
            <?php if ($_smarty_tpl->tpl_vars['child_count']->value > 0) {?><span class="menu_child_count left hover_hidden">〖子栏目数：<?php echo $_smarty_tpl->tpl_vars['child_count']->value;?>
〗</span><?php }?>
        </span>
    </div>
    <?php if ($_smarty_tpl->tpl_vars['children']->value) {?>
    <div class="menu_children">
        <?php $_smarty_tpl->callTemplateFunction ('menu', $_smarty_tpl, array('data'=>$_smarty_tpl->tpl_vars['children']->value,'level'=>$_smarty_tpl->tpl_vars['level']->value+1), false);?>

    </div>
    <?php }?>
</div>
<?php
$_smarty_tpl->tpl_vars['children'] = $foreach_children_Sav;
}
?>
<?php foreach (Smarty::$global_tpl_vars as $key => $value){
if ($_smarty_tpl->tpl_vars[$key] === $value) $saved_tpl_vars[$key] = $value;
}
$_smarty_tpl->tpl_vars = $saved_tpl_vars;
}
}
/*/ smarty_template_function_menu_4382767845d3db12e686e22_76880157 */

?>
