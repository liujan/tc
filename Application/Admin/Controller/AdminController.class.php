<?php
namespace Admin\Controller;
use Think\Controller;

class AdminController extends Controller{
	/*

	显示所有用户信息
	*/
	public function manageUser(){
		if (isLogin() and $_SESSION['user_level'] == 2){
			
			if (S("all_user")){
				$userdata = S("all_user");
			}
			else{
				$user = M("InfoUser");
				$userdata = $user->where("user_level=1")->select();

				foreach ($userdata as  &$value) {
					$value["schoolname"] = getSchoolNameById($value["school_id"]);
					$value["collegename"] = getCollegeNameById($value['college_id']);
				}
				S("all_user", $userdata, array('type' => 'Memcache'));
			}
			
			
			$this->assign("userdata", $userdata);
			$this->display();
		}
		else if (!isLogin()){
			$this->error("请先登录", U("Home/User/login"));
		}
		else{
			$this->error("您的权限不够");
		}
	}

	/*
		删除一条普通用户信息，同时删除该用户的所有评论
		@param
			user_id
	*/
	public function deleteUser1(){
		
		if (isLogin() and $_SESSION['user_level'] == 2){
			$user = M("InfoUser");
			$user_id = I("user_id");
			$comment = M("InfoComment");

			$username = $user->where("user_id='$user_id'")->getField("user_name", true)[0];

			$photo = M("InfoPhoto");
			//删除跟该用户相关的头像
			$photo->where("user_name='$username'")->delete();
			//删除跟该用户相关的被举报的评论
			M("InfoReport")->where("user_id='$user_id'")->delete();
			//删除该用户的所有课程
			M("InfoMycourse")->where("user_id='$user_id'")->delete();
			
			$comment->where("user_id='$user_id'")->delete();
			$user->where("user_id='$user_id'")->delete();
			
			$userdata = $user->where("user_level=1")->select();


			foreach ($userdata as  &$value) {
				$value["schoolname"] = getSchoolNameById($value["school_id"]);
				$value["collegename"] = getCollegeNameById($value['college_id']);
			}
			S("all_user", $userdata, array('type' => 'Memcache'));

			$this->success("删除成功", U("manageUser"));
			
		}
		else if (!isLogin()){
			$this->error("请先登录", U("Home/User/login"));
		}
		else{
			$this->error("您的权限不够");
		}
	}


	/*
		删除一条普通用户信息，用于被举报评论用户的删除，同时删除该用户的所有评论
		@param
			user_id
	*/
	public function deleteUser2(){
		
		if (isLogin() and $_SESSION['user_level'] == 2){
			$user = M("InfoUser");
			$user_id = I("user_id");
			$comment = M("InfoComment");

			$username = $user->where("user_id='$user_id'")->getField("user_name", true)[0];

			$photo = M("InfoPhoto");
			//删除跟该用户相关的头像
			$photo->where("user_name='$username'")->delete();

			//删除跟该用户相关的被举报的评论
			M("InfoReport")->where("user_id='$user_id'")->delete();
			
			//删除该用户的所有课程
			M("InfoMycourse")->where("user_id='$user_id'")->delete();

			$comment->where("user_id='$user_id'")->delete();
			$user->where("user_id='$user_id'")->delete();
			
			$userdata = $user->where("user_level=1")->select();

			foreach ($userdata as  &$value) {
				$value["schoolname"] = getSchoolNameById($value["school_id"]);
				$value["collegename"] = getCollegeNameById($value['college_id']);
			}
			S("all_user", $userdata, array('type' => 'Memcache'));

			$report = M("InfoReport");
			$report->where("user_id='$user_id'")->delete();
			$commentIds = $report->select();

			$data = array();

			foreach($commentIds as $value){
				$comment_id = $value["comment_id"];
				$comment_data = $comment->where("comment_id='$comment_id'")->find();

				$comment_data['teacher'] = getTeacherNameById($comment_data['teacher_id']);
				$comment_data['school'] = getSchoolNameById($comment_data['school_id']);
				$comment_data['username'] = getUserNameById($comment_data['user_id']);
				$comment_data['course'] = getCourseNameById($comment_data['course_id']);
				$comment_data['college'] = getCollegeNameById($comment_data['college_id']);

				$tmp = $comment_data;
				$tmp["user_id"] = $value["user_id"];
				array_push($data, $tmp);
			}

			$this->assign("data", $data);

			$this->success("删除成功", U("manageComment"));
		}
		else if (!isLogin()){
			$this->error("请先登录", U("Home/User/login"));
		}
		else{
			$this->error("您的权限不够");
		}
	}

	/*
		列出所有用户的评论
	*/
	public function allComments(){
		if (isLogin() and $_SESSION["user_level"] == 2){
			//$report = M("InfoReport");
			$comment = M("InfoComment");
			$commentIds = $comment->select();

			$data = array();

			foreach($commentIds as $value){
				$comment_id = $value["comment_id"];
				$comment_data = $comment->where("comment_id='$comment_id'")->find();

				$comment_data['teacher'] = getTeacherNameById($comment_data['teacher_id']);
				$comment_data['school'] = getSchoolNameById($comment_data['school_id']);
				$comment_data['username'] = getUserNameById($comment_data['user_id']);
				$comment_data['course'] = getCourseNameById($comment_data['course_id']);
				$comment_data['college'] = getCollegeNameById($comment_data['college_id']);

				$tmp = $comment_data;
				$tmp["user_id"] = $value["user_id"];
				array_push($data, $tmp);
			}
			$user = M("InfoPhoto")->getField("user_name, image_path", true);
			$this->assign("usernames", $user);
			$this->assign("data", $data);

			$this->display();
		}
		else if (!isLogin()){
			$this->error("请先登录", U("Home/User/login"));
		}
		else{
			$this->error("您的权限不够");
		}
	}


	/*
		列出所有被举报的评论
	*/
	public function manageComment(){
		if (isLogin() and $_SESSION["user_level"] == 2){
			$report = M("InfoReport");
			$comment = M("InfoComment");
			$commentIds = $report->select();

			$data = array();

			foreach($commentIds as $value){
				$comment_id = $value["comment_id"];
				$comment_data = $comment->where("comment_id='$comment_id'")->find();

				$comment_data['teacher'] = getTeacherNameById($comment_data['teacher_id']);
				$comment_data['school'] = getSchoolNameById($comment_data['school_id']);
				$comment_data['username'] = getUserNameById($comment_data['user_id']);
				$comment_data['course'] = getCourseNameById($comment_data['course_id']);
				$comment_data['college'] = getCollegeNameById($comment_data['college_id']);

				$tmp = $comment_data;
				$tmp["user_id"] = $value["user_id"];
				array_push($data, $tmp);
			}
			$user = M("InfoPhoto")->getField("user_name, image_path", true);
			$this->assign("usernames", $user);
			$this->assign("data", $data);

			$this->display();
		}
		else if (!isLogin()){
			$this->error("请先登录", U("Home/User/login"));
		}
		else{
			$this->error("您的权限不够");
		}
	}

	public function showInfo(){
		if (isLogin() and $_SESSION["user_level"] == 2){
			$user = M("InfoUser");
			$user_id = $_SESSION["user_id"];
			$data = $user->where("user_id='$user_id'")->find();

			$this->assign("userdata", $data);
			$this->display();
		}
		else if (!isLogin()){
			$this->error("请先登录", U("Home/User/login"));
		}
		else{
			$this->error("您的权限不够");
		}
	}

	public function updateInfo(){
		if (isLogin() and $_SESSION["user_level"] == 2){
			$user = D("InfoUser");

			$user_id = $_SESSION['user_id'];
			$userdata = $user->where("user_id='$user_id'")->find();
			$this->assign("userdata", $userdata);
			if (IS_POST){
				$data["user_id"] = $_SESSION['user_id'];
				$data["user_name"] = I("user_name");
				$data["user_pwd"] = I("user_pwd");
				$data["user_email"] = I("user_email");

				
				$result = $user->updateInfo($data);

				if ($result){
					$_SESSION['user_name'] = $data['user_name'];
					$this->success("修改成功", U("User/showInfo"));
				}
				else{
					$this->error($user->getError());
				}
			}
			else{
				$this->display();
			}
		}
		else if (!isLogin()){
			$this->error("请先登录", U("Home/User/login"));
		}
		else{
			$this->error("您的权限不够");
		}
	}

	/*
		删除一条被举报的评论
	*/
	public function deleteComment(){
		if (isLogin() and $_SESSION["user_level"] == 2){
			$comment = M("InfoComment");
			$report = M("InfoReport");
			$comment_id = I("comment_id");
			$comment->where("comment_id='$comment_id'")->delete();
			$report->where("comment_id='$comment_id'")->delete();
			$commentIds = $report->select();

			$data = array();

			foreach($commentIds as $value){
				$comment_id = $value["comment_id"];
				$comment_data = $comment->where("comment_id='$comment_id'")->find();

				$comment_data['teacher'] = getTeacherNameById($comment_data['teacher_id']);
				$comment_data['school'] = getSchoolNameById($comment_data['school_id']);
				$comment_data['username'] = getUserNameById($comment_data['user_id']);
				$comment_data['course'] = getCourseNameById($comment_data['course_id']);
				$comment_data['college'] = getCollegeNameById($comment_data['college_id']);

				$tmp = $comment_data;
				$tmp["user_id"] = $value["user_id"];
				array_push($data, $tmp);
			}

			$this->assign("data", $data);

			$this->success("删除成功");

		}
		else if (!isLogin()){
			$this->error("请先登录", U("Home/User/login"));
		}
		else{
			$this->error("您的权限不够");
		}
	}
}