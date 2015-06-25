<?php
namespace Home\Model;
use Think\Model;

class InfoCommentModel extends Model{

	protected $_validate = array(
		array('user_id', 'require', "请先登录"),
		array('school_id', 'require', "请选学校"),
		array('college_id', 'require', "请选学院"),
		array('course_id', 'require', "请选课程"),
		array('teacher_id', 'require', "请选老师"),
		array('comment_time', 'require', '发表时间获取出错'),
		array('comment_content', 'require', '评论内容不能为空'),
		array('comment_content', '1,500', '评论内容不能超过500字数', self::EXISTS_VALIDATE, 'length'),
	);

	public function transform($result) {
		$comments = array();
		foreach($result as $m => $record) {
			$tmp["content"] = $record["comment_content"];
			$tmp["time"] = $record["comment_time"];
			$tmp["username"] = getUserNameById($record["user_id"]);
			$tmp["teacher"] = getTeacherNameById($record["teacher_id"]);
			$tmp["course"] = getCourseNameById($record["course_id"]);
			$tmp["school"] = getSchoolNameById($record["school_id"]);
			$tmp["college"] = getCollegeNameById($record["college_id"]);
			$tmp["user_id"] = $record["user_id"];
			$tmp["comment_id"] = $record["comment_id"];
			array_push($comments, $tmp);
		}
		return $comments;
	}



	/*
	添加评论
	$param
		user_id
		course_id
		teacher_id
		school_id
		college_id
		comment_content
		comment_time
	*/
	public function addComment($data){
	
		if ($this->create($data)){
			if ($this->add($data))
				return true;
			else {
				$this->error = "发表失败";
				return false;
			}
		}
		else{
			return false;
		}
	}

	/*

		读取最新的前30条记录，显示在home/index中
	*/
	public function getRecentComment() {
		$user_id = $_SESSION['user_id'];
		if ($user_id != null && $user_id != ""){
			$mycourse = M("InfoMycourse");
			$collegeIds = $mycourse->where("user_id='$user_id'")->getField("course_id", true);

			$result = array();
			foreach($collegeIds as $key => $id){ //优先选跟自己相关的课程的评论
				$tmp = $this->where("course_id='$id'")->order('comment_time desc')->limit(30)->select();
				$result = array_merge($result, $tmp);
			}

			if (count($result) < 15){
				if ($collegeIds != null){
					$cond = "course_id !=" . implode(" AND course_id !=", $collegeIds);
					$tmp = $this->where($cond)->order('comment_time desc')->limit(30)->select();
				}
				else
					$tmp = $this->order('comment_time desc')->limit(30)->select();
				$result = array_merge($result, $tmp);
			}
		}
		else
			$result = $this->order('comment_time desc')->limit(30)->select();
		if(is_array($result)) {
			return $this->transform($result);
		}
		else {
			$this->error = "搜索评论失败";
			return false;
		}
	}

	/*
		优先选跟自己相关的课程的评论
	*/
	public function followComment($follow_id){
		$user_id = $_SESSION['user_id'];
		//取出自己的所有课程
		$collegeIds = $this->where("user_id='$user_id'")->getField("course_id", true);

		$result = array();
		foreach($collegeIds as $key => $id){  //优先选跟自己相关的课程的评论
			$tmp = $this->where("course_id='$id' AND user_id='$follow_id'")->order('comment_time desc')->select();
			$result = array_merge($result, $tmp);
		}
		if ($collegeIds != null) {
			$cond = "course_id !=" . implode(" AND course_id !=", $collegeIds);
			$cond = $cond." AND user_id = ".$follow_id;
			$tmp = $this->where($cond)->order('comment_time desc')->select();
		}
		else
			$tmp = $this->where("user_id = '$follow_id'")->order('comment_time desc')->select();
		$result = array_merge($result, $tmp);
		return $result;

	}

	public function searchCommentByTeacher($teacher_id) {
		$cond = "teacher_id=" . implode(" OR teacher_id=", $teacher_id);//condition: teacher_id=a OR teacher_id=b OR teacher_id=c
		$result = $this->where($cond)->select();
		if(is_array($result)) {
			return $this->transform($result);
		}
		else {
			$this->error = "搜索评论失败";
			return false;
		}
	}

	public function searchCommentByCourse($course_id) {
		$cond = "course_id=" . implode(" OR course_id=", $course_id);
		$result = $this->where($cond)->select();
		if(is_array($result)) {
			return $this->transform($result);
		}
		else {
			$this->error = "搜索评论失败";
			return false;
		}
	}
	
}
?>

