<?php
namespace Home\Model;
use Think\Model;

class InfoMycourseModel extends Model{
	protected $_validate = array(
		array('user_id', 'require', "用户名必须提供"),
		array('school_id', 'require', "学校必须提供"),
		array('college_id', 'require', "学院必须提供"),
		array('teacher_id', 'require', "教师必须提供"),
		array('course_id', 'require', "课程名必须提供"),
	);

	public function addMyCourse($data){
		$course_id = $data['course_id'];
		$user_id = $data['user_id'];
		$tmp = $this->where("course_id='$course_id' AND user_id='$user_id'")->find();
		if (!empty(tmp) && is_array($tmp) && $tmp != null) {
			$this->error ="你已添加过该课程";
			return false;
		}
		if ($this->create($data)){
			if ($this->add($data)){
				return true;
			}
			else{
				$this->error = "发生未知错误，添加失败";
				return false;
			}
		}
		else
			return false;
	}
}


?>