$(function(){

	/*
	* 根据选择的学校从后端获得该学校的所有
	* 学院名称和ID作为学院的选项
	*/
	$('#school').change(function() {
		var id = $('#school').val();

		$.ajax({
  			//url: "<?php echo U('Home/User/login'); ?>",
  			url:'login',
  			dataType: 'json',
  			data: {action : 'a', school_id: id}
		}).done(function(data) {
			$('#college option').each(function(i) {
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

});





