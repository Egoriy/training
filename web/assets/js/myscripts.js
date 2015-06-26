
		  function goBack() {
			window.history.back(-1);
		  }
	

$(document).ready(function()
{
$(".btn-change-days").click(function(){
	

		var id_d= $(this).data("id");
		var id_pr = $(this).data("item");
		$("tr").removeClass('alert-info');
		$.ajax({
				type: "GET",
				url:"/index.php/program/"+id_pr+"/"+ id_d,
				dataType: 'json',
			})
			.done(function( data ) {
				
				$("#exercise-"+id_pr).addClass('alert-info');
				$("#content").html( "" );
				var i=1;
				
				for (var exercise of data.exercises) {
					$("#content").append( "<tr><td>"+i+"</td><td><a href=\"/index.php/Approach/" +exercise.idExercise + "/"+ id_pr +"\">" + exercise.NameExercise+ "</a></td><td>"
					+ exercise.CountApproaches + "</td><td>" + exercise.CountRepeat + "</td><td>" + exercise.TakenWeight+"</td>" + "<td>"+
											"<form method=\"POST\" action=\"/index.php/exercise/" + exercise.idExercise + "/" + id_pr +"\">"+
												"<input type=\"hidden\" name=\"_method\" value=\"DELETE\">"+
												"<button type=\"submit\" class=\"btn btn-danger\">Удалить</button>"+
											"</form>"+
										"</td>");
					i=i+1;
				}
				
				
			})		
			.fail(function( data ) {
				
				$("#exercise-"+id_d).addClass('alert-info');
				$("#content").html( "" );
				
				$("#content").append( "<caption>Ошибка</caption>");				
				
				
			});		
	})
	
	$(".btn-change-common-days").click(function(){
	

		var id_d= $(this).data("id");
		var id_pr = $(this).data("item");
		$("tr").removeClass('alert-info');
		$.ajax({
				type: "GET",
				url:"/index.php/common_program/"+id_pr+"/"+ id_d,
				dataType: 'json',
			})
			.done(function( data ) {
				
				$("#common_exercise-"+id_pr).addClass('alert-info');
				$("#content").html( "" );
				var i=1;
				
				for (var exercise of data.exercises) {
					$("#content").append( "<tr><td>"+i+"</td><td>" + exercise.NameExercise+ "</td><td>"
					+ exercise.CountApproaches + "</td><td>" + exercise.CountRepeat + "</td>" + "<td>"+
											"<form method=\"POST\" action=\"/index.php/common_exercise/" + exercise.id_exerciseCatalog + "/" + id_pr +"\">"+
												"<input type=\"hidden\" name=\"_method\" value=\"DELETE\">"+
												"<button type=\"submit\" class=\"btn btn-danger\">Удалить</button>"+
											"</form>"+
										"</td>");
					i=i+1;
				}
			})		
			.fail(function( data ) {
				
				$("#common_exercise-"+id_d).addClass('alert-info');
				$("#content").html( "" );
				
				$("#content").append( "<caption>Ошибка</caption>");				
				
				
			});		
	})
	$(".btn-change-adddays").click(function(){
	

		var id_d= $(this).data("id");
		var id_pr = $(this).data("item");
		$("tr").removeClass('alert-info');
		$.ajax({
				type: "GET",
				url:"/index.php/program/"+id_pr+"/"+ id_d,
				dataType: 'json',
			})
			.done(function( data ) {
				
				$("#exercise-"+id_pr).addClass('alert-info');
				$("#content").html( "" );
				var i=1;
				
				for (var exercise of data.exercises) {
					$("#content").append( "<tr><td>"+i+"</td><td>"  + exercise.NameExercise+ "</td><td>"
					+ exercise.CountApproaches + "</td><td>" + exercise.CountRepeat + "</td><td>" + exercise.TakenWeight+"</td>" + "<td>"+
											"<form method=\"POST\" action=\"/exercises/" + exercise.idExercise + "/" + id_pr +"\">"+
												"<input type=\"hidden\" name=\"_method\" value=\"DELETE\">"+
												"<button type=\"submit\" class=\"btn btn-danger\">Удалить</button>"+
											"</form>"+
										"</td>");
					i=i+1;
				}
				
				
			})		
			.fail(function( data ) {
				
				$("#exercise-"+id_d).addClass('alert-info');
				$("#content").html( "" );
				
				$("#content").append( "<caption>Ошибка</caption>");				
				
				
			});		
	})
	$(".btn-change-program").click(function(){
		var str;
		var id_client = $(this).data("id");
		
		$("tr").removeClass('alert-info');
		$.ajax({
				type: "GET",
				url:"/index.php/programm/"+ id_client,
				dataType: 'json'
			})
			.fail(function( data ) {
				
				$("#exercise-"+id_client).addClass('alert-info');
				$("#content").html( "" );
				
				$("#content").append( "<caption>Ошибка</caption>");				
				
				
			})
			.done(function( data ) {
				
				$("#client-"+id_client).addClass('alert-info');
				$("#content").html( "" );
				$("#Content2").html( "" );
				$("#content_body_table").html( "" );
				$("#content_head_table").html( "" );
				$("#content_head_table").append( "<th>№</th>"+
					"<th>Наименование</th>"+
					"<th>Количество дней цикла</th>"+
					"<th>Заметка</th>"+
					"<th>Дата назначения</th>"+
					"<th></th>");
				
				$("#content_name_accordion").html( "" );
				$("#head_name_accordion").html( "" );
				$("#head_name_accordion").append("Старые программы");
				var i=1;
				$("#content").append( "<caption>Список всех программ клиента</caption>"+
					"<tr>"+
					"<th>№</th>"+
					"<th>Наименование</th>"+
					"<th>Количество дней цикла</th>"+
					"<th>Заметка</th>"+
					"<th>Дата назначения</th>"+
					"<th></th>");
				for (var program of data.programs) {
					if (program.Archive_flag == 1)
					{
						$("#content").append( "<tbody>"+
						"<td>"+1+"</td><td>"+  "<a href=\"/index.php/program/" +program.id_prog + "\">"+ program.NameProgram +"</a>"+
						"</td><td>"+ program.DayOfTheOneCycle +"</td><td>" +program.Comment + "</td><td>"+  program.DateProgram +"</td><td>"+
						"</td><td>"+
										"<form method=\"POST\" action=\"index.php/programs/"+ program.id_prog+">"+
										"<input type=\"hidden\" name=\"_method\" value=\"DELETE\">"+
										"<button type=\"submit\" class=\"btn btn-danger\">Удалить</button>"+
										"</form>"+
										"</td>"+
										"</tr>"+
										"</tbody>"+
										"</table>");
				
						
					}
					if (program.Archive_flag == 0)
					{
						$("#content_body_table").append( "<tr><td>"+i+"</td><td>"+  "<a href=\"/index.php/program/" +program.id_prog + "\">"+ program.NameProgram +"</a>"+
						"</td><td>"+ program.DayOfTheOneCycle +"</td><td>" +program.Comment + "</td><td>"+program.Comment + "</td><td>"+
						"</td><td>"+
										"<form method=\"POST\" action=\"index.php/programs/"+ program.id_prog+">"+
										"<input type=\"hidden\" name=\"_method\" value=\"DELETE\">"+
										"<button type=\"submit\" class=\"btn btn-danger\">Удалить</button>"+
										"</form>"+
										"</td>"+
										"</tr>"+
										"</tbody>"+
										"</table>");
					}
					i=i+1;
				}
				$("#Content2").append("<form method=\"get\" action=\"/index.php/add_programm/"+id_client + "\"class=\"form-inline\">"+
								"<fieldset>"+
								"<table>"+
								"<tbody>"+
								"<thead>"+
									"<tr><td><legend>Добавить программу</legend></td></tr>"+
									"<th>"+
									"<button type=\"submit\" class=\"btn btn-primary\">Добавить программу</button>"+
									"</th></tr>"+
								"</thead>"+
								"</tbody>"+
								"</table>"+
								"</fieldset>"+
							"</form>");
				
				
			});		
	})
	$(".btn-change-nutrition").click(function(){
	
		var str;
		var id_client = $(this).data("id");
		
		$("tr").removeClass('alert-info');
		$.ajax({
				type: "GET",
				url:"/index.php/nutritionj/"+ id_client
			})
			.done(function( data ) {
				
				$("#client-"+id_client).addClass('alert-info');
				$("#content").html( "" );
				$("#Content2").html( "" );
				$("#content_body_table").html( "" );
				$("#content_head_table").html( "" );
			
				$("#content_head_table").append( "<th>Приемов пищи</th>"+
					"<th>Каллорий</th>"+
					"<th>Белка</th>"+
					"<th>Углеводов</th>"+
					"<th>Жира</th>"+
					"<th>Дата назначения</th>"+
					"<th></th>");
				
				$("#content_name_accordion").html( "" );
				
				$("#head_name_accordion").html( "" );
				$("#head_name_accordion").append("Старые рационы питания");
				var i=1;
				$("#content").append( "<caption>Список всех программ клиента</caption>"+
					"<tr>"+"<th>Приемов пищи</th>"+
					"<th>Каллорий</th>"+
					"<th>Белка</th>"+
					"<th>Углеводов</th>"+
					"<th>Жира</th>"+
					"<th>Дата назначения</th>"+
					"<th></th>");
				for (var nutrition of data.nutritions) {
					if (nutrition.flag_archive == 1)
					{
						
					
					
						$("#content").append("<tbody>"+
						"<td>"+nutrition.NumberOfMeals+"</td><td>"+ nutrition.Calorie +
						"</td><td>"+ nutrition.Protein +"</td><td>" +nutrition.Carbogidrates + "</td><td>"+ nutrition.fat + "</td><td>"+ nutrition.Date + "</td><td>"+
						"</td><td>"+
										"<form method=\"POST\" action=\"/index.php/nutrition/"+id_client+ "/"+nutrition.id_Nutrition+ "\">"+
										"<input type=\"hidden\" name=\"_method\" value=\"DELETE\">"+
										"<button type=\"submit\" class=\"btn btn-danger\">Удалить</button>"+
										"</form>"+
										"</td>"+
										"</tr>"+
										"</tbody>"+
										"</table>");
					}
					
					if (nutrition.flag_archive == 0 )
					{
							$("#content_body_table").append( "<tr><td>"+nutrition.NumberOfMeals+"</td><td>"+ nutrition.Calorie +
						"</td><td>"+ nutrition.Protein +"</td><td>" +nutrition.Carbogidrates + "</td><td>"+ +nutrition.fat + "</td><td>"+  nutrition.Date + "</td><td>"+"</td><td>"+
						"</td><td>"+
										"<form method=\"POST\" action=\"/index.php/nutrition/"+id_client+ "/"+nutrition.id_Nutrition+ "\">"+
										"<input type=\"hidden\" name=\"_method\" value=\"DELETE\">"+
										"<button type=\"submit\" class=\"btn btn-danger\">Удалить</button>"+
										"</form>"+
										"</td>"+
										"</tr>"+
										"</tbody>"+
										"</table>");
										i=i+1;
					}
				}
				$("#Content2").append("<form method=\"get\" action=\"/index.php/addNutrition/"+id_client + "\"class=\"form-inline\">"+
								"<fieldset>"+
								"<table>"+
								"<tbody>"+
								"<thead>"+
									"<tr><td><legend>Добавить питание</legend></td></tr>"+
									"<th>"+
									"<button type=\"submit\" class=\"btn btn-primary\">Добавить питание</button>"+
									"</th></tr>"+
								"</thead>"+
								"</tbody>"+
								"</table>"+
								"</fieldset>"+
							"</form>");
				
				
			});		
	})
	$(".btn-change-antr").click(function(){
	
		var str;
		var id_client = $(this).data("id");
		
		$("tr").removeClass('alert-info');
		$.ajax({
				type: "GET",
				url:"/index.php/anthropometricj/"+ id_client
			})
			.done(function( data ) {
				
				$("#client-"+id_client).addClass('alert-info');
				$("#content").html( "" );
				$("#Content2").html( "" );
				$("#content_body_table").html( "" );
				$("#content_head_table").html( "" );
			
				$("#content_head_table").append( "<th>Рост</th>"+
					"<th>Вес</th>"+
					"<th>Бицепс</th>"+
					"<th>Талия</th>"+
					"<th>Бедро</th>"+
					"<th>Грудь</th>"+
					"<th>Дата</th>"+
					
					"<th></th>");
				
				$("#content_name_accordion").html( "" );
				$("#head_name_accordion").html( "" );
				$("#head_name_accordion").append("Старые антропометрические данные");
				var i=1;
				var cli = data.client;
				$("#content").append( "<caption>Антропометрические данные клиента</caption>"+
					"<thead>"+
					"<tr>"+
					"<th>Рост</th>"+
					"<th>Вес</th>"+
					"<th>Бицепс</th>"+
					"<th>Талия</th>"+
					"<th>Бедро</th>"+
					"<th>Грудь</th>"+
					"<th>Дата</th>"+
					"<th></th>"+
					"</thead>");
				for (var anthropometric of data.anthropometrics) {
					if (anthropometric.flag_archive == 1)
					{
					$("#content").append("<tbody>"+
					"<td>"+anthropometric.Height+"</td><td>"+ anthropometric.Weight +
					"</td><td>"+ anthropometric.BicepsVol +"</td><td>" +anthropometric.WaistVol + "</td><td>" +anthropometric.hipsVol + "</td><td>" +anthropometric.BustVol + "</td><td>"+
					 anthropometric.Date +"</td><td>"+
					"</td><td>"+
										"<form method=\"POST\" action=\"/index.php/anthropometric/"+id_client+"/"+anthropometric.id_AntrData+"\">"+
										"<input type=\"hidden\" name=\"_method\" value=\"DELETE\">"+
										"<button type=\"submit\" class=\"btn btn-danger\">Удалить</button>"+
										"</form>"+
										"</td>"+
										"</tr>"+
										"</tbody>"+
										"</table>");
					}
					
					if (anthropometric.flag_archive == 0 )
					{
							$("#content_body_table").append( "<tr><td>"+anthropometric.Height+"</td><td>"+ anthropometric.Weight +
						"</td><td>"+ anthropometric.BicepsVol +"</td><td>" +anthropometric.WaistVol + "</td><td>"+ anthropometric.hipsVol + 
						"</td><td>"+ anthropometric.BustVol+ "</td><td>"+anthropometric.Date +"</td><td>"+
										"<form method=\"POST\" action=\"/index.php/anthropometric/"+id_client+ "/"+anthropometric.id_AntrData+ "\">"+
										"<input type=\"hidden\" name=\"_method\" value=\"DELETE\">"+
										"<button type=\"submit\" class=\"btn btn-danger\">Удалить</button>"+
										"</form>"+
										"</td>"+
										"</tr>"+
										"</tbody>"+
										"</table>");
										i=i+1;
					}
				}
				$("#Content2").append("<form method=\"get\" action=\"/index.php/addAnthropometric/"+id_client + "\"class=\"form-inline\">"+
								"<fieldset>"+
								"<table>"+
								"<tbody>"+
								"<thead>"+
									"<tr><td><legend>Добавить замеры</legend></td></tr>"+
									"<th>"+
									"<button type=\"submit\" class=\"btn btn-primary\">Добавить замеры</button>"+
									"</th></tr>"+
								"</thead>"+
								"</tbody>"+
								"</table>"+
								"</fieldset>"+
							"</form>");
				
				
			});		
	})
})