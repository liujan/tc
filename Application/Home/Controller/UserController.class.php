<?php

namespace Home\Controller;
use Think\Controller;
use Think\Model;

class UserController extends Controller{
	/*
		用户登录
		@param  POST
			user_email
			user_pwd
	*/
	public function login(){
		
		if (IS_POST){
			$data["user_email"] = I("user_email");
			$data["user_pwd"] = I("user_pwd");

			$user = D("InfoUser");
			$login_result = $user->login($data); //登录结果，成功为true，失败为false

			$user_level = $_SESSION["user_level"];

			if ($login_result){
				if ($user_level == 1)
					$this->success("登录成功", U("Home/Index/index"), 2);
				else if ($user_level == 2)
					$this->success("登录成功", U("Admin/Admin/manageUser"), 2);
			}
			else{
				$this->error($user->getError());
			}
		} else if (IS_AJAX) {  //Ajax
			if (I("action") == 'a') {
				$school = D("InfoSchool");
				$school_id = I("school_id"); //通过选择的学校返回学校的所有学院名称
				$all_college = $school->getCollege($school_id);
				$json = json_encode($all_college);
				$this->ajaxReturn($json);	
			}
			else if (I("action") == 'b'){
				$school_id = I("school_id");					
				$college_id = I("college_id");
				$teacher = D("InfoTeacher");
				$all_teacher = $teacher->getTeacher($school_id, $college_id);
				$json = json_encode($all_teacher);
				$this->ajaxReturn($json);	
			} else if (I("action") == 'c') {
				$school_id = I("school_id");
				$college_id = I("college_id");
				$teacher_id = I("teacher_id");
				$teacher = D("InfoTeacher");
				$all_course = $teacher->getCourse($school_id, $college_id, $teacher_id);
				$json = json_encode($all_course);
				$this->ajaxReturn($json);
			}
			$this->display();
		}
		else{
			$school = D("InfoSchool"); //连接学校信息表，以获取全部学校名称
			$all_school = $school->getSchool(); //在Model中的定义，得到所有学校的ID和名称
			$this->assign("school", $all_school); //传给view
			$this->display();
		}
	}
	
	/*
		用户登出
	*/
	public function logout(){
		$user = D("InfoUser");
		$user->logout();
		$this->success("注销成功", U("Home/Index/index"));
	}

	/*
		用户注册，注册完后需到中大邮箱进行激活
		@param  POST
			user_name
			user_email
			user_pwd
	*/
	public function register(){
		if (IS_POST){
			$data["user_name"] = I("user_name");
			$data["user_pwd"] = I("user_pwd");
			$data["user_repwd"] = I("user_repwd");
			$data["user_email"] = I("user_email");
			$data['school_id'] = I("school_id");
			$data['college_id'] = I("college_id");

			$rule  = "/^([a-zA-Z0-9_-])+@(mail2.sysu.edu.cn)$/"; 
			$result = 1; 
        	$result = preg_match($rule, $data["user_email"] );

        	$tranDb = new Model;
        	$tranDb->startTrans();

        	$upload = new \Think\Upload();// 实例化上传类
        	$upload->autoSub = false;
        	$upload->replace = true;
        	$upload->saveName = $data["user_name"];
        	$upload->maxSize   = 3145728 ;// 设置附件上传大小
        	$upload->exts      = array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
        	$upload->rootPath  = './Public/images/'; // 设置附件上传根目录
        	$info   =   $upload->upload();

        	if($info){
        		$ext = $info["photo"]['ext'];
	        	if ($result){
					$user = D("InfoUser");
					$register_result = $user->register($data, $ext); //注册结果，成功为true，失败为false
					if ($register_result){
						$tranDb->commit();
						$this->success("注册成功，请在24小时内前往您的邮箱激活账户", U("Home/Index/index"),3);
					}
					else{
						$tranDb->rollback();
						$this->error($user->getError());

					}
				}
				else{
					$tranDb->rollback();
					$this->error("注册失败，只能使用中山大学邮箱进行注册!");
				}
			}
			else{
				$tranDb->rollback();
				$this->error($upload->getError());
			}
		}	
	}

	/*
		激活注册用户
	*/
	public function active(){
		if (IS_GET){
			$verify_code = I("verify_code");
			
			$user = D("InfoUser");
			$result = $user->active($verify_code);
		
			if ($result){
				$this->success("激活成功，跳转到登录页面", U("User/login"));
			}
			else{
				$this->error("激活失败，请重新注册", U("User/login"));
			}
		}
	}

	
	/*	
		显示当前用户个人信息
	*/
	public function showInfo(){
		if (isLogin()){

			$user_id = $_SESSION["user_id"];

			if (S($user_id."info")){
				$data = S($user_id."info");
			}
			else{
				$user = M("InfoUser");
				$data = $user->where("user_id='$user_id'")->find();
				$data["schoolname"] = getSchoolNameById($data["school_id"]);
				$data["collegename"] = getCollegeNameById($data["college_id"]);
				S($user_id."info", $data, array('type' => 'Memcache'));
			}
			$this->assign("userdata", $data);
			$this->display();
		}
		else{
			$this->error("请先登录", U("User/login"));
		}
	}

	/*
		修改个人信息
		只能修改用户名和密码
	*/
	public function updateInfo(){
		if (isLogin()){
			$user = D("InfoUser");
			$user_id = $_SESSION['user_id'];
			$userdata = $user->where("user_id='$user_id'")->find();
			$this->assign("userdata", $userdata);
			if (IS_POST){
				$data["user_id"] = $_SESSION['user_id'];
				$data["user_name"] = I("user_name");
				$data["user_pwd"] = I("user_pwd");
				
				$result = $user->updateInfo($data);

				if ($result){
					$newdata = $user->where("user_id='$user_id'")->find();
					$newdata["schoolname"] = getSchoolNameById($newdata["school_id"]);
					$newdata["collegename"] = getCollegeNameById($newdata["college_id"]);
					S($user_id."info", $newdata, array('type' => 'Memcache'));
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
		else{
			$this->error("请先登录", U("User/login"));
		}
	}

	/*
		关注一个用户
		@param
			被关注用户的user_id
	*/
	public function follow(){
		if (isLogin()){
			$data['follow_id'] = I("user_id");
			$data['user_id'] = $_SESSION['user_id'];
			$follow_id = $data["follow_id"];
			$user_id = $data["user_id"];
			$follow = D("InfoFollow");
			$hasFollow = $follow->where("user_id='$user_id' and follow_id='$follow_id'")->select();
			
			if (count($hasFollow) == 0 and $follow_id != $user_id){
				$result = $follow->add($data);
			}
			$this->redirect("Home/Index/index");
		}
		else{
			$this->error("请先登录", U("User/login"));
		}
	}

	/*
		取消关注一个用户
		@param
			被关注用户的user_id
	*/
	public function cancelFollow(){
		if (isLogin()){
			$user_id = $_SESSION['user_id'];
			$follow_id = I('user_id');  //被关注者user_id

			if (M("InfoFollow")->where("user_id='$user_id' and follow_id='$follow_id'")->delete())
				$this->success("取消关注成功");
			else
				$this->error("取消关注失败");
		}
		else
			$this->error("请先登录", U("User/login"));
	}

	/*
		获取当前用户关注用户的列表
	*/
	public function followList(){
		if (isLogin()){
			$user_id = $_SESSION["user_id"];
			$follow = M("InfoFollow");
			$followlist = $follow->where("user_id='$user_id'")->select();
			$user = M("InfoUser");

			foreach($followlist as &$value){
				$value["username"] = getUserNameById($value['follow_id']);
				$follow_id = $value["follow_id"];
				$data = $user->where("user_id='$follow_id'")->find();
				$value["schoolname"] = getSchoolNameById($data["school_id"]);
				$value["collegename"] = getCollegeNameById($data['college_id']);
				$value["user_email"] = $data["user_email"];
			}
			$this->assign("data", $followlist);

			$this->display();
		}
		else{
			$this->error("请先登录", U("User/login"));
		}
	}
}

?>
