<?php

namespace Home\Model;
use Think\Model;

class InfoSchoolModel extends Model{
	/*
		获取学校列表
	*/
	public function getSchool(){
		if (S("school_list")){
			$school_result = S("school_list");
		}
		else {
			$school_result = $this->getField('school_id,school_name', true);
			S("school_list", $school_result, array('type' => 'Memcache'));
		}
		if(is_array($school_result)) {
			return $school_result;
		}
		else {
			$this->error = "获取学校列表失败";
			return false;
		}		
	}

	/*
		获取对应学校ID所有学院名字
	*/
	public function getCollege($school_id){
		


		$college_list = strval($school_id)."college_list";
		if(S($college_list)) {
			$result = S($college_list);
		} else {
			$result = $this->where("school_id='$school_id'")->find();
			S($college_list, $result, array("type" => "Memcache"));
		}

		if(is_array($result)) {
			$college_id = explode("|", $result["school_college"]);
			$colleges = array();
			foreach($college_id as $m => $id){
				$name = getCollegeNameById($id); //根据学院ID得到学院名
				$tmp["id"] = $id;
				$tmp["college_name"] = $name;
				array_push($colleges, $tmp);
			}
			return $colleges;	
		}
		else {
			$this->error = "获取学院列表失败";
			return false;
		}		
	}
}
