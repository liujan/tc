<?php

namespace Home\Controller;
use Think\Controller;

class SearchController extends Controller{
	public function SearchResult(){
		if (IS_GET){

			$keyword = trim(I("keyword"));
				$all_comment1 = array();
				$all_comment2 = array();
				if ($keyword){
				$comment = D("InfoComment");

				$teacher = M("InfoTeacher");			
				$condition['teacher_name'] = array('like', '%'.$keyword.'%');
				$teacher_ids = $teacher->where($condition)->getField('teacher_id', true);

				if (is_array($teacher_ids)){			
					$all_comment1 = $comment->searchCommentByTeacher($teacher_ids);
				}

				$course = M("InfoCourse");
				$condition1['course_name'] = array('like', '%'.$keyword.'%');
				$course_ids = $course->where($condition1)->getField("course_id", true);	

				if (is_array($course_ids)){
					$all_comment2 = $comment->searchCommentByCourse($course_ids);
				}
			}

			$user = M("InfoPhoto")->getField("user_name, image_path", true);
			$this->assign("usernames", $user);

			$this->assign("comment", array_merge($all_comment1, $all_comment2));
			$this->display();
		}
		else if (IS_POST) {

        } else {
        	
        }
	}
}
?>
