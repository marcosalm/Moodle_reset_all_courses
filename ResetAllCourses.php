<?php
//define('CLI_SCRIPT', true);

require('../config.php');
require_once('reset_form.php');
require_once($CFG->libdir.'/clilib.php');

//Função utilizada para remover os relacionamentos do usuário com o curso

global $DB;
if (isloggedin()) {


$courses = get_courses();

?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Reset Cursos NEAD</title>
    <!--link href="img/favicon.ico" rel="icon" type="image/x-icon"-->

    <!-- CSS -->
    <link href="resetcourse/css/bootstrap.min.css" rel="stylesheet">
    <link href="resetcourse/css/bootstrap-theme.min.css" rel="stylesheet">
    <link href="resetcourse/css/font-awesome.min.css" rel="stylesheet">
    <link href="resetcourse/css/estilo.css" rel="stylesheet">

</head>


<body hola-ext-player="1">

    <!-- Navigation -->
    <nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
        <div class="container">
            <!-- Brand and toggle get grouped for better mobile display -->
            <div class="navbar-header">
                <a class="navbar-brand" href="#">Reset de cursos do NEAD</a>
            </div>

        </div>
        <!-- /.container -->
    </nav>

    <!-- Page Content -->
    <div class="container conteudo">

        <div class="row">
            <div class="col-md-6 col-md-offset-3">
                <form id="target" method="POST" action="">
                    <div class="form-group">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                Selecione os cursos para serem resetados
                                <div class="pull-right">
                                    <label><input style="margin-top:-3px;" type="checkbox" onclick="marcardesmarcar();" id="ckbTodosCursos"><i class="fa fa-graduation-cap"></i> Selecionar todos os cursos</label>
                                </div>
                            </div>
                            <div class="panel-body caixa">
                                <table class="table table-hover">
                                    <thead>
                                        <tr class="active">
                                            <th width="30%"><i class="fa fa-graduation-cap"></i> Curso</th>

                                        </tr>
                                    </thead>
									<thead>
									<label><input id="chkUser" type="checkbox" name="chkUser" value="1"> <i class="fa fa-users"></i> Apagar todos Alunos </label>
									</thead>
                                    <tbody>
                                       <?php foreach ($courses as $course){
												if ($course->id != 1){
												echo '
													<tr>
														<td colspan="2"><label><input type="checkbox" class="curso" name="ckbCurso[]" value="'.$course->id.'"> '.$course->fullname.'</label></td>
													</tr>';
													}
												}
												?>
                                    </tbody>
                                </table>

                                <p></p>

                            </div>
                            
                        </div>
                    </div>
                    <button type="button" class="btn btn-warning btn-block" id="btReset" >Resetar</button>
                </form>
				
			<?php
			    //Verifica se a variável post esta preenchida 
			if(isset($_POST["ckbCurso"])){
                    //Verifica se a variável post esta preenchida 
					
					$courseids = $_POST["ckbCurso"];

					//Para cada ids de curso executa as ações abaixo o reconfigurando
				foreach ($courseids as $value) {

						$data->MAX_FILE_SIZE = 8097152;
						$data->reset_start_date = 1251781200;
						$data->reset_events = 1;
						$data->reset_logs = 1;
						$data->reset_notes = 1;
						$data->reset_roles = Array(5);
						$data->mform_showadvanced_last = 0;
						$data->reset_roles_local = 1;
						$data->reset_gradebook_grades = 1;
						$data->reset_assignment_submissions = 1;
						$data->reset_forum_all = 1;
						$data->id = $value;

						$status = reset_course_userdata($data);
						//Verifica se houve algum erro na reconfiguração 
						if (empty($status->error)){
						echo "Curso id:".$value." - Executado com sucesso! <br><br>";
						}
						else{
						  die("Curso id:".$value." - Erro ao reconfigurar o curso! <br><br>");
				        }
				}
			}
                    //Verifica se a opção de excluir usuários foi selecionada 
					
					if (isset($_POST['chkUser']) && $_POST['chkUser'] == 1){  
						//Marca todos usuários com id acima de 20000 para serem excluídos
						$stm = "UPDATE {user}
								SET deleted = 1 WHERE id > 20000";

						 $DB->execute($stm);
						   
					   
						/*$sql = "SELECT *
								  FROM {user}
								 WHERE deleted = 1";
						$rs = $DB->get_recordset_sql($sql);
						foreach ($rs as $user) {
							echo "Redeleting user $user->id: $user->username ($user->email)\n";
							delete_user($user);
						}*/
						//cli_heading('Deleting all leftovers');
						//Recebe um array com o status da função que exclui 
						//os dados dos usuários nos cursos 
						
						$status["field"] = $DB->set_field('user', 'idnumber', '', array('deleted'=>1));
						$status["role_assignments"] = $DB->delete_records_select('role_assignments', "userid IN (SELECT id FROM {user} WHERE deleted = 1)");
						$status["cohort_members"] = $DB->delete_records_select('cohort_members', "userid IN (SELECT id FROM {user} WHERE deleted = 1)");
						$status["groups_members"] = $DB->delete_records_select('groups_members', "userid IN (SELECT id FROM {user} WHERE deleted = 1)");
						$status["user_enrolments"] = $DB->delete_records_select('user_enrolments', "userid IN (SELECT id FROM {user} WHERE deleted = 1)");
						$status["user_preferences"] = $DB->delete_records_select('user_preferences', "userid IN (SELECT id FROM {user} WHERE deleted = 1)");
						$status["user_info_data"] = $DB->delete_records_select('user_info_data', "userid IN (SELECT id FROM {user} WHERE deleted = 1)");
						$status["user_lastaccess"] = $DB->delete_records_select('user_lastaccess', "userid IN (SELECT id FROM {user} WHERE deleted = 1)");
						$status["external_tokens"] = $DB->delete_records_select('external_tokens', "userid IN (SELECT id FROM {user} WHERE deleted = 1)");
						$status["external_services_users"] = $DB->delete_records_select('external_services_users', "userid IN (SELECT id FROM {user} WHERE deleted = 1)");
											
						//Percorre o array e verifica se houve algum erro na 
						//Remoção dos dados de usuários nos cursos.
						
						foreach($status as $key => $value){
							if ($value){
								echo $key." - Dados dos usuários nos cursos excluídos com sucesso! <br><br>";
							} else{
								die("Error - ".$key);	
							}
						}
						
						//Remove todos usuários marcados para a exclusão    
						$stm="DELETE FROM {user} WHERE deleted = 1";
						if ($DB->execute($stm)){
							echo "Usuários excluídos com sucesso!";
						} else {
							echo "Erro ao excluir usuários";
						}
						
					}		
          				
			?>
				
            </div>
        </div>
        <!-- /.row -->

    </div>
    <!-- /.container -->
    <!-- JavaScript -->
    <script src="resetcourse/js/jquery-1.10.2.js"></script>
    <script src="resetcourse/js/bootstrap.min.js"></script>
    <script src="resetcourse/js/script.js"></script>
    <script type="text/javascript">
        //Função para marcar ou desmarcar   todos os cursos
        function marcardesmarcar() {
            if ($("#ckbTodosCursos").prop("checked")) {
                $('.curso').each(function () {
                    $(this).prop('checked', true);
                })
            }
            else {
                $('.curso').each(function () {
                    $(this).prop('checked', false);
                })
            }	
        }
		//Função chamada pelo btReset para limpar os dados dos cursos e a exclusão dos usuários
		$("#btReset").click(function() {
		      var _curso=$('.curso').is(':checked');
			  var _usr=$('#chkUser').is(':checked');
			  var msg="Você tem certeza que deseja excluir todos os usuários?";
			//Verifica se foi selecionado um curso ou a exclusão de usuários  
		    if(_curso||_usr){
			    //Se foi selecionada a reconfiguração dos cursos e a exclusão dos usuários
			   	if(_curso&&_usr){
				 msg="Você tem certeza que deseja excluir os dados dos cursos e todos usuários?";
				}
                else if(_curso){
				msg="Você tem certeza que deseja excluir os dados dos cursos?";
				}
				//Se houve a confirmação de reconfiguração continua a ação.
			   if(confirm(msg)){
			    $("#target").submit();
			   } 
			}
			else{
			   alert("Selecione pelo menos um curso ou a exclusão de usuários!");
			}
			
		});
    </script>
</body>

</html>
<?php 
} 
else {echo "<script>window.location.href = 'http://nead.uvv.br/ensino/login/index.php';</script>";}
?>