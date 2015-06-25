<?php
namespace Home\Controller;
use Think\Controller;

class CourseController extends Controller{
	public function manageCourse(){
		if (isLogin()){
			$user_id = $_SESSION['user_id'];
			$mycourse = M("InfoMycourse");
			$ids = $mycourse->where("user_id='$user_id'")->getField("course_id,teacher_id, college_id,school_id", true);

			foreach($ids as $key => &$value){
				$value['school_name'] = getSchoolNameById($value['school_id']);
				$value['college_name'] = getCollegeNameById($value['college_id']);
				$value['teacher_name'] = getTeacherNameById($value['teacher_id']);
				$value['course_name'] = getCourseNameById($value['course_id']);
			}
			$this->assign("data", $ids);
			$this->display();
		}
		else{
			$this->error("请先登录", U("User/login"));
		}
	}
	public function addMyCourse(){
		if (isLogin()){

			if (IS_POST){
				$data['user_id'] = $_SESSION['user_id'];
				$data['school_id'] = I("school_id");
				$data['college_id'] = I("college_id");
				$data['teacher_id'] = I("teacher_id");
				$data['course_id'] = I("course_id");
				$mycourse = D("InfoMycourse");
				
				if ($mycourse->addMyCourse($data)){
					$this->success("添加成功", U("manageCourse"));
				}
				else
					$this->redirect("addMyCourse", array(), 2, $mycourse->getError());
					//$this->error("添加失败", U("manageCourse"));
		
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
		else{
			$this->error("请先登录", U("User/login"));
		}
	}

	public function deleteCourse(){
		if (isLogin()){
			$course_id = I("course_id");
			$user_id = $_SESSION['user_id'];
			$mycourse = M("InfoMycourse");
			if ($mycourse->where("user_id='$user_id' AND course_id='$course_id'")->delete()){
				$this->success("删除成功", U("manageCourse"));
			}
			else{
				$this->error("删除失败", U("manageCourse"));
			}
		}
		else{
			$this->error("请先登录", U("User/login"));
		}
	}
}
?>