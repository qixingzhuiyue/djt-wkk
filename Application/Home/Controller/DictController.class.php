<?php
namespace Home\Controller;
use Home\Controller\CommonController;
class DictController extends CommonController {
	/**
	 * @desc 首页类型分类
	 */
	public function index() {
		$condstr = 1;
		$keyword = _get('keyword');
		if ($keyword) {
			$condstr .= " AND name LIKE '%$keyword%'";
		}

		$status = _get('status');
		if (is_numeric($status)) {
			$condstr .= " AND status=$status";
		}

		$type = _get('type');
		if (is_numeric($type)) {
			$condstr .= " AND type=$type";
		}

		$DictIndex = M('dict_index');
		$count      = $DictIndex->where($condstr)->count();
		$Page       = new \Think\Page($count, 15);
		$show       = $Page->show();

		$lists = $DictIndex->where($condstr)->order('id')->limit($Page->firstRow.','.$Page->listRows)->select();
		$this->assign('lists', $lists);
		$this->assign('page', $show);
		$this->display();
	}

	/**
	 * @desc 首页类型分类编辑
	 */
	public function indexView($id = 0) {
		if (IS_POST) {
			$id = I('post.id');

			$name = I('post.name');
			if (empty($name)) {
				$this->error('请填写名称');
			}
			$type = I('post.type');
			if (empty($type)) {
				$this->error('请选择类型');
			}
			$status = I('post.status');
			$weight = I('post.weight');

			$data = array(
				'name' => $name,
				'type' => $type,
				'status' => $status,
				'weight' => $weight
			);

			$DictIndex = M('dict_index');
			if ($id) {
				$DictIndex->where('id='.$id)->save($data);
			} else {
				$DictIndex->add($data);
			}
			$this->success('操作成功',U('Admin/Dict/index'));


		} else {
			if ($id) {
				$DictIndex = M('dict_index');
				$dictIndex = $DictIndex -> where('id='.$id) -> find();
				$this->assign('index', $dictIndex);
			}
			$this->display();
		}
	}

	/**
	 * @desc 地区
	 * 2017-2-8
	 */
	public function area() {
		$condstr = 1;
		$keyword = _get('keyword');
		if ($keyword) {
			$condstr .= " AND name LIKE '%$keyword%'";
		}

		$status = _get('status');
		if (is_numeric($status)) {
			$condstr .= " AND status=$status";
		}

		$pid = _get('pid');
		if (is_numeric($pid)) {
			$condstr .= " AND pid=$pid";
		}
		$DictArea = M('dict_area');
		$count      = $DictArea->where($condstr)->count();
		$Page       = new \Think\Page($count, 15);
		$show       = $Page->show();
		$lists = $DictArea->where($condstr)->order('id')->limit($Page->firstRow.','.$Page->listRows)->select();
		$this->assign('lists', $lists);
		$this->assign('page', $show);

		//父
		$plists = $DictArea->where("pid=0")->order('id')->select();
		$this->assign('plists', $plists);

		$this->display();
	}

	/**
	 * @desc 配置
	 * 2017-2-10
	 */
	public function other() {
		$condstr = 1;
		$keyword = _get('keyword');
		if ($keyword) {
			$condstr .= " AND name LIKE '%$keyword%'";
		}

		$status = _get('status');
		if (is_numeric($status)) {
			$condstr .= " AND status=$status";
		}

		$pid = _get('pid');
		if (is_numeric($pid)) {
			$condstr .= " AND pid=$pid";
		}
		$Dis = M('dict_disposition');
		$count      = $Dis->where($condstr)->count();
		$Page       = new \Think\Page($count, 15);
		$show       = $Page->show();
		$lists = $Dis->where($condstr)->order('id')->limit($Page->firstRow.','.$Page->listRows)->select();
		$this->assign('lists', $lists);
		$this->assign('page', $show);

		//父
		$plists = $Dis->where("pid=0")->order('id')->select();
		$this->assign('plists', $plists);

		$this->display();
	}

	/**
	 * @desc 其他配置编辑
	 */
	public function otherView($id = 0) {
		if (IS_POST) {
			$id = I('post.id');

			$name = I('post.name');
			if (empty($name)) {
				$this->error('请填写名称');
			}
			$pid = I('post.pid');
			if(empty($pid)){
				$pid = 0;
			}
			$status = I('post.status');
			$weight = I('post.weight');

			$data = array(
				'name' => $name,
				'pid' => $pid,
				'status' => $status,
				'weight' => $weight
			);

			$Dis = M('dict_disposition');
			if ($id) {
				$Dis->where('id='.$id)->save($data);
			} else {
				$Dis->add($data);
			}
			$this->success('操作成功',U('Admin/Dict/other'));


		} else {
			$Dis = M('dict_disposition');

			//父
			$plists = $Dis->where("pid=0")->order('id')->select();
			$this->assign('plists', $plists);

			if ($id) {
				$dis = $Dis -> where('id='.$id) -> find();
				$this->assign('dis', $dis);
			}
			$this->display();
		}
	}


	/**
	 * @desc 地区编辑
	 */
	public function areaView($id = 0) {
		if (IS_POST) {
			$id = I('post.id');

			$name = I('post.name');
			if (empty($name)) {
				$this->error('请填写名称');
			}
			$pid = I('post.pid');
			$sid = I("post.sid");
			if(!empty($sid)){
				$pid = $sid;
			}
			if(empty($sid)&&empty($pid)){
				$pid = 0;
			}
			$status = I('post.status');
			$weight = I('post.weight');

			$data = array(
				'name' => $name,
				'pid' => $pid,
				'status' => $status,
				'weight' => $weight
			);

			$DictArea = M('dict_area');
			if ($id) {
				$DictArea->where('id='.$id)->save($data);
			} else {
				$DictArea->add($data);
			}
			$this->success('操作成功',U('Admin/Dict/area'));


		} else {
			$DictArea = M('dict_area');

			//父
			$plists = $DictArea->where("pid=0")->order('id')->select();
			$this->assign('plists', $plists);

			if ($id) {
				$dictArea = $DictArea -> where('id='.$id) -> find();
				$this->assign('area', $dictArea);
			}
			$this->display();
		}
	}

	/**
	 * @desc 获取子类地区
	 * 2017-2-8
	 */
	public function getAreas() {
		$id = I("post.id");
		$Area = M('dict_area');
		$areas = $Area -> where("pid = {$id} AND status=1 ") -> select();
		$this->ajaxReturn($areas);
	}
}
?>