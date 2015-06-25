$(function(){

	/*
	* 根据选择的学校从后端获得该学校的所有
	* 学院名称和ID作为学院的选项
	*/
	$('#school').change(function() {
		var id = $('#school').val();
		$.ajax({
  			url: 'addMyCourse',
  			dataType: 'json',
  			data: {action : 'a', school_id: id}
		}).done(function(data) {
			$('#college option').each(function(i) {
					if (i != 0)
						$(this).remove();
			});
			$('#teacher option').each(function(i) {
					if (i != 0)
						$(this).remove();
			});
			$('#course option').each(function(i) {
				if (i != 0)
					$(this).remove();
			});
			var colleges = $.parseJSON(data);
			
			for (var i = 0; i < colleges.length; ++i) {
				var opt = $('<option/>').attr({"value" : colleges[i].id}).text(colleges[i].college_name);
				$('#college').append(opt);
			}
		});
	});

	/*
	* 根据选择的学校和学院从后端获得该学校学院
	* 的所有教师名称和ID作为教师的选项
	*/
	$('#college').change(function() {
		var sid = $('#school').val();
		var cid = $('#college').val();
		$.ajax({
  			url: 'addMyCourse',
  			dataType: 'json',
  			data: {action : 'b', school_id: sid, college_id: cid}
		}).done(function(data) {
			$('#teacher option').each(function(i) {
					if (i != 0)
						$(this).remove();
			});
			$('#course option').each(function(i) {
				if (i != 0)
					$(this).remove();
			});
			var teachers = $.parseJSON(data);
			for (var i = 0; i < teachers.length; ++i) {
				var opt = $('<option/>').attr({"value" : teachers[i].teacher_id}).text(teachers[i].teacher_name);
				$('#teacher').append(opt);
			}
		});
	});

	/*
	* 根据选择的学校和学院从后端获得该学校学院
	* 的所有教师名称和ID作为教师的选项
	*/
	$('#teacher').change(function() {
		var sid = $('#school').val();
		var cid = $('#college').val();
		var tid = $('#teacher').val();

		$.ajax({
  			url: 'addMyCourse',
  			dataType: 'json',
  			data: {action : 'c', school_id: sid, college_id: cid, teacher_id: tid}
		}).done(function(data) {
			$('#course option').each(function(i) {
				if (i != 0)
					$(this).remove();
			});
			var courses = $.parseJSON(data);
			for (var i = 0; i < courses.length; ++i) {
				var opt = $('<option/>').attr({"value" : courses[i].course_id}).text(courses[i].course_name);
				$('#course').append(opt);
			}
		});
	});

});





