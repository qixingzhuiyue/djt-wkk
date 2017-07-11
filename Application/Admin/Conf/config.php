<?php
return array(
//    //'配置项'=>'配置值'
//    'DB_TYPE'=>'mysql',// 数据库类型
//    'DB_HOST'=>'127.0.0.1',// 服务器地址
//    'DB_NAME'=>'wework',// 数据库名
//    'DB_USER'=>'root',// 用户名
//    'DB_PWD'=>'',// 密码
//    'DB_PORT'=>3306,// 端口
//    'DB_PREFIX'=>'ww_',// 数据库表前缀
//    'DB_CHARSET'=>'utf8',// 数据库字符集
    //配置每页显示数据个数
    'PAGESIZE'		=>	15,
    //URL模式
    'URL_MODEL'          => '1',

    //定义替换路径
    'TMPL_PARSE_STRING' => array(
        '__PUBLIC__' => __ROOT__. '/Application/Admin/View/Public'
    ),

    //去掉验证码链接后缀
    'URL_HTML_SUFFIX' => '',

    //设置本版块访问权限
    'RBAC_SUPERADMIN'       =>      'admin',        //超级管理员名称
    'ADMIN_AUTH_KEY'		=>		'superadmin',   //管理员用户标记
    'USER_AUTH_ON'			=>		true, 			//是否开启认证
    'USER_AUTH_TYPE'		=>		1,  			//用户认证使用SESSION标记1,登录验证，2实时验证
    'USER_AUTH_KEY'			=>		'uid',  		//设置认证SESSION的标记名称
    'NOT_AUTH_MODULE'       =>      'Common,Index,Login', //无需验证的控制器
    'NOT_AUTH_ACTION'		=>		'role,node,user',		//无需验证的动作

    //'USER_AUTH_MODEL'		=>		'User',  		//验证用户的表模型ai_user
    'AUTH_PWD_ENCODER'		=>		'md5', 			//用户认证密码加密方式
    'USER_AUTH_GATEWAY'		=>		'/Login/index', //默认的认证网关
    'REQUIRE_AUTH_MODULE'	=>		'',  			//默认需要认证的模块
    'REQUIRE_AUTH_ACTION'	=>		'',				//默认需要认证的动作
    'GUEST_AUTH_ON'			=>		false,			//是否开启游客授权访问
    'GUEST_AUTH_ID'			=>		0, 				//游客标记

    //'SHOW_PAGE_TRACE'       =>      true,           //显示调试参数

    'RBAC_ROLE_TABLE'		=>		'ww_role',      //角色表名称
    'RBAC_USER_TABLE'		=>		'ww_role_user', //角色与用户的中间表
    'RBAC_ACCESS_TABLE' 	=>	    'ww_access',    //权限表名称
    'RBAC_NODE_TABLE'		=>		'ww_node',     //节点表名称
);