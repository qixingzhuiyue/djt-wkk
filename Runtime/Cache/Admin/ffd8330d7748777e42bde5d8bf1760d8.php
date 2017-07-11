<?php if (!defined('THINK_PATH')) exit();?><!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>维沃珂后台管理系统</title>
<meta name="keywords" content="">
<meta name="description" content="">
<link href="favicon.ico" type="image/x-icon" rel=icon>
<link rel="stylesheet" href="/Application/Admin/View/Public/Css/public.css"/>
<link rel="stylesheet" href="/Application/Admin/View/Public/Css/node.css">
<script src="/Application/Admin/View/Public/Js/jquery.js"></script>
    <script src="/Application/Admin/View/Public/Js/jquery-1.8.0.min.js"></script>


</head>
<body>
<div class="template">
    <div class="template_top">
        <div class="template_top_left">
            <h1>人才管理>企业简章列表</h1>
        </div>
        <div class="template_top_right">
            <a href="<?php echo U('Admin/Job/addCompanyJob');?>" class="ul_button">添加职位推荐</a>
        </div>
        <div class="template_top_right">
        	<script>
		      function del(){
		      	if(window.confirm('请确认是否删除？')){
		         	document.adminForm.submit();
		        }
		      }
			</script>
        </div>
        <div class="fn-clear"></div>
    </div>
    <div class="template_middle">
    <table class="ul_table">
         	<tbody class="ul_table">
    		<tr>
    		<td width="60%" style="text-align:left">
	    		<form action="<?php echo U('Admin/Job/company');?>" method="get">
				企业名称：<input type="text" name="keyword" value="<?php echo ($_GET['keyword']); ?>" />
				<select name="status">
				<option value="">状态</option>
				<option value="1">正常</option>
				<option value="0">屏蔽</option>
				</select>
				<input type="submit" value="查询" />
				<input type="reset" value="清空" />
	      		</form>
    		</td>
      	</tr></tbody>
     	</table>
        <table class="ul_table">
            <tbody class="ul_table">
            <tr class="ul_box">
            	<th style="width:5%">ID</th>
                <th style="width:15%">企业名称</th>
                <th style="width:10%">类型</th>
                <!--<th style="width:15%">规模</th>-->
                <th style="width:10%">负责人</th>
                <th style="width:10%">负责人电话</th>
                <th style="width:10%">负责人QQ</th>
                <th style="width:10%">日期</th>
                <th style="width:10%">状态</th>
                <th style="width:10%">操作</th>
            </tr>
                <?php if(is_array($job)): foreach($job as $key=>$v): ?><tr class="ul_box1">
                        <td><?php echo ($v["jobid"]); ?></td>
                        <td><?php echo ($v["name"]); ?></td>
                        <td><?php echo ($v["type"]); ?></td>
                        <td><?php echo ($v["contact"]); ?></td>
                        <td><?php echo ($v["phone"]); ?></td>
                        <td><?php echo ($v["qq"]); ?></td>
                        <td><?php echo (date('y-m-d H:i',$v["created"])); ?></td>
                        <td><?php if($v["status"] == 1): ?>正常<?php else: ?>屏蔽<?php endif; ?></td>
                        <td><a href="<?php echo U('Admin/Job/view', array('id' => $v['jobid']));?>">编辑</a></td>
                    </tr><?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
    <div class="template_page">
		<?php echo ($page); ?>    
    </div>
</div>
</body>
</html>