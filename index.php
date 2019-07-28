<?php
/**
* # o(╯□╰)o大哥饶了我吧！我不是入口文件呀！我是可以被随意删除的文件。谁能比我惨呀！！！
* # 真正的入口文件在public/下，域名需要绑定到public
* # 我的作用仅仅是当本地127.0.0.1访问的时候，帮你自动跳转到public中！
*/
if (stripos($_SERVER['SERVER_NAME'], 'localhost') !== false || stripos($_SERVER['SERVER_NAME'], '127.') !== false  || stripos($_SERVER['SERVER_NAME'], '192.') !== false) {
    header('Location:public/');
} else {
    header("Content-Type:text/html;charset=utf-8");
    exit('o(╯□╰)o大哥饶了我吧！我[<span style="color:red;">' . __FILE__ . '</span>]不是入口文件，可随意删除。域名需要绑定到public目录下！！！');
}
