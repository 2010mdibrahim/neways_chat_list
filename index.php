<?php
include('../super_home/application/config/ajax_config.php');

$_base_url = 'http://erp.superhostelbd.com/chat_list/';

if(!empty($_GET['get-mention-list']) and !empty($_GET['employee_id'])){
	$_rensonse = [];
	if($_GET['mention_query'] == ''){
		$_check_employee = $mysqli->query("select id, full_name, photo, department_name from employee where status = '1' and employee_id not in('" . rahat_decode($_GET['employee_id']) . "') order by full_name asc limit 10");
	}else{
		$_check_employee = $mysqli->query("select id, full_name, photo, department_name from employee where full_name like '%" . $_GET['mention_query'] . "%' and status = '1' and employee_id not in('" . rahat_decode($_GET['employee_id']) . "') order by full_name asc limit 10");		
	}
	while( $row = mysqli_fetch_object($_check_employee)){
		$_rensonse[] = array(
			'id' 		=> $row->id,
			'name' 		=> $row->full_name,
			'avatar' 	=> 'http://erp.superhostelbd.com/super_home/' . $row->photo,
			'info' 		=> $row->department_name,
			'href' 		=> '#',
		);
	}
	echo json_encode($_rensonse);
	exit();
}

if(!empty($_GET['get_employee_duty_status']) AND $_GET['get_employee_duty_status'] == 'true'){ session_write_close();
	$_response = [];
	if(!empty($_GET['employee_id'])){
		$_check_employee = mysqli_fetch_object($mysqli->query("select id, status from employee where employee_id = '" . $_GET['employee_id'] . "'"));
		if(!empty($_check_employee->id)){
			if($_check_employee->status == 1){
				$_check_attendance = mysqli_fetch_object($mysqli->query("select id, checkin, checkout from employee_attendence where employee_id = '" . $_GET['employee_id'] . "' and days = '" . sprintf('%00d', date("d")) . "' and month = '" . sprintf('%00d', date("m")) . "' and years = '" . date('y') . "'"));
				if(!empty($_check_attendance->id)){
					if(!empty($_check_attendance->checkout)){
						$_response = array( 'employee_id' 	=> $_GET['employee_id'], 'status' 	=> 'success', 'message' 	=> 'Off Duty', );
					}else{
						$_response = array( 'employee_id' 	=> $_GET['employee_id'], 'status' 	=> 'success', 'message' 	=> 'On Duty', );
					}
				}else{
					$_response = array( 'employee_id' 	=> $_GET['employee_id'], 'status' 	=> 'success', 'message' 	=> 'Off Duty', );
				}
			}else{
				$_response = array( 'employee_id' 	=> $_GET['employee_id'], 'status' 	=> 'success', 'message' 	=> 'Exit Employee!', );
			}
		}else{
			$_response = array( 'employee_id' 	=> null, 'status' 	=> 'error', 'message' 	=> 'Employee ID not found', );
		}
		header('Content-Type: application/json');
		echo json_encode($_response, JSON_PRETTY_PRINT);

	}else{
		$results = [];
		$query = mysqli_query($mysqli, "select id, employee_id, status from employee");
		while($row = mysqli_fetch_row($query)){
			if($row[2] == 1){
				$_check_attendance = mysqli_fetch_object($mysqli->query("select id, checkin, checkout from employee_attendence where employee_id = '" . $row[1] . "' and days = '" . sprintf('%00d', date("d")) . "' and month = '" . sprintf('%00d', date("m")) . "' and years = '" . date('y') . "'"));
				if(!empty($_check_attendance->id)){
					if(!empty($_check_attendance->checkout)){
						array_push($results, array( 'employee_id' 	=> $row[1], 'status' 	=> 'success', 'message' 	=> 'Off Duty', ));
					}else{
						array_push($results, array( 'employee_id' 	=> $row[1], 'status' 	=> 'success', 'message' 	=> 'On Duty', ));
					}
				}else{
					array_push($results, array( 'employee_id' 	=> $row[1], 'status' 	=> 'success', 'message' 	=> 'Off Duty', ));
				}
			}else{
				array_push($results, array( 'employee_id' 	=> $row[1], 'status' 	=> 'success', 'message' 	=> 'Exit Employee!', ));
			}
		}
		header('Content-Type: application/json');
		echo json_encode($results, JSON_PRETTY_PRINT);
	}
	exit();
}

if(!empty($_GET['get_employee_user']) AND $_GET['get_employee_user'] == 'active'){
	$response[] = '';
	$_employee_id = $_GET['employee_id'];
	if(!empty($_GET['query'])){
		$_sql = $mysqli->query("select employee_id, full_name, photo, department_name from employee where status = '1' and ( full_name like '%" . $_GET['query'] . "%' or employee_id like '%" . $_GET['query'] . "%' ) and employee_id not in('" . $_employee_id . "') order by full_name asc limit 100");
	}else{
		$_sql = $mysqli->query("select employee_id, full_name, photo, department_name from employee where status = '1' and employee_id not in('" . $_employee_id . "') order by full_name asc limit 100");
	}
	while ( $row = mysqli_fetch_object($_sql)){
		$response[] = [ 'id' => $row->employee_id, 'text' => $row->employee_id . ' - ' . $row->full_name . ' (' . $row->department_name . ')', 'image' => $row->photo ];
	}	
	echo json_encode($response);
	exit();
}

if(!empty($_GET['new_message_post_submit']) AND $_GET['new_message_post_submit'] == 'active'){
	$_sender = mysqli_fetch_object($mysqli->query("select employee_id, role_name, designation_name, department_name, full_name, personal_Phone, email, photo,Company_phone, company_email, status from employee where employee_id = '" . $_POST['sender_id'] . "'"));
	$_receiver = mysqli_fetch_object($mysqli->query("select employee_id, role_name, designation_name, department_name, full_name, personal_Phone, email, photo,Company_phone, company_email, status from employee where employee_id = '" . $_POST['employee_id'] . "'"));
	$_message = $_POST['message'];
	$_participants= array(
		$_sender, $_receiver
	);
	$url = "http://erp.superhomebd.com/neways_employee_mobile_application/v1/api/push-notification";
	$data = array(
		'employee_id' 	=> $_receiver->employee_id,
		'title' 		=> '',
		'message' 		=> $_sender->full_name .' has sent a message'
	);
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data)); 
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$response = curl_exec($ch);
	
	echo json_encode(array(
		'type' 			=> 'Single',		
		'participants' 	=> $_participants,
		'mtype' 		=> 'regular',
		'texts' 		=> [array(
			'type' 			=> 'text',
			'value' 		=> '',
			'text' 			=> $_message,
		)],		
		'owner' 		=> $_sender->employee_id,
	));
	exit();
}

if(!empty($_GET['new_message_post_submit_chat']) AND $_GET['new_message_post_submit_chat'] == 'active'){
	$_sender = mysqli_fetch_object($mysqli->query("select employee_id, role_name, designation_name, department_name, full_name, personal_Phone, email, photo,Company_phone, company_email, status from employee where employee_id = '" . $_POST['sender_id'] . "'"));
	$_receiver = mysqli_fetch_object($mysqli->query("select employee_id, role_name, designation_name, department_name, full_name, personal_Phone, email, photo,Company_phone, company_email, status from employee where employee_id = '" . $_POST['employee_id'] . "'"));
	$_message = $_POST['message'];
	
	$_participants= array(
		$_POST['sender_id'], $_POST['employee_id']
	);

	// $_participants= array(
	// 	$_sender, $_receiver
	// );
	
	$url = "http://erp.superhomebd.com/neways_employee_mobile_application/v1/api/push-notification";
	$data = array(
		'employee_id' 	=> $_POST['employee_id'],
		'title' 		=> '',
		'message' 		=> $_sender->full_name .' has sent a message',
	);
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data)); 
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$response = curl_exec($ch);

	echo json_encode(array(		
		'recipients' 	=> $_participants,
		'mtype' 		=> '',
		'texts' 		=> [array(
			'type' 			=> 'text',
			'text' 			=> $_message,
			'value' 		=> ''
		)],		
		'sender' 		=> $_sender->employee_id,
	));
	exit();
}
if(!empty($_GET['new_group_create_submit']) AND $_GET['new_group_create_submit'] == 'active'){
	if(isset($_FILES['group_icon'])){
		$_url = 'assets/uploads/group_photo/';
		$_generate_code = rand() * time();
		$errors			= array();
		$file_name	 	= $_FILES['group_icon']['name'];
		$file_tmp 		= $_FILES['group_icon']['tmp_name'];
		$tmp 			= explode('.', $file_name);
		$file_ext 		= end($tmp);
		$_new_file_name = $_generate_code . '.' . $file_ext;
		move_uploaded_file($file_tmp, $_url . $_new_file_name);
		$_photo = $_base_url . $_url . $_new_file_name;
	}else{
		$_photo = '';
	}
	$_employee_id = $_GET['employee_id'];
	$_sender = mysqli_fetch_object($mysqli->query("select employee_id, role_name, designation_name, department_name, full_name, personal_Phone, email, photo,Company_phone, company_email, status from employee where employee_id = '" . $_POST['sender_id'] . "'"));
	$_message = $_sender->full_name . ' is created this group!';
	$_title = $_POST['group_name'];
	$_participants = [];
	foreach($_POST['group_employee_id'] as $row_id){
		$_participants[] = mysqli_fetch_object($mysqli->query("select employee_id, role_name, designation_name, department_name, full_name, personal_Phone, email, photo,Company_phone, company_email, status from employee where employee_id = '" . $row_id . "'"));
	}	
	array_push($_participants, $_sender);
	// echo json_encode(array(
		// 'type' 				=> 'Group',
		// 'photo' 			=> $_photo,
		// 'participants' 		=> $_participants,
		// 'title' 			=> $_title,
		// 'text' 				=> $_message,
		
		
		// 'owner' 			=> $_sender->employee_id,
	// ));
	echo json_encode(array(		
		'type' 			=> 'Group',
		'participants' 	=> $_participants,
		'photo' 		=> $_photo,
		'title' 		=> $_title,
		'mtype' 		=> '',
		'texts' 		=> [array(
			'type' 			=> 'text',
			'value' 		=> '',
			'text' 			=> $_message
		)],
		'owner' 			=> $_sender->employee_id
	));
	exit();
}

if(!empty($_GET['new_attachment_post_submit']) AND $_GET['new_attachment_post_submit'] == 'active'){
	$countfiles = count($_FILES['attachment']['name']); $_attachments = array();
	if($countfiles > 0){
		$totalFileUploaded = 0; 
		for($i=0; $i < $countfiles; $i++){
			$_url = 'assets/uploads/attachment/';
			$_generate_code = rand() * time();
			$errors			= array();
			$file_name	 	= $_FILES['attachment']['name'][$i];
			$file_tmp 		= $_FILES['attachment']['tmp_name'][$i];
			$tmp 			= explode('.', $file_name);
			$file_ext 		= end($tmp);
			$_new_file_name = $_generate_code . '.' . $file_ext;
			move_uploaded_file($file_tmp, $_url . $_new_file_name);
			$_photo = $_base_url . $_url . $_new_file_name;
			if($file_ext == 'jpg' OR $file_ext == 'jpeg' OR $file_ext == 'png' OR $file_ext == 'gif' OR $file_ext == 'webp'){
				$_type = 'photo';
			}else{
				$_type = $file_ext;
			}
			$_attachments[] = array(
				'name' 	=> $file_name, 
				'type' 	=> $_type,
				'url' 	=> $_photo,
			);
		}
	}
	$_sender = mysqli_fetch_object($mysqli->query("select employee_id, role_name, designation_name, department_name, full_name, personal_Phone, email, photo,Company_phone, company_email, status from employee where employee_id = '" . $_POST['sender_id'] . "'"));
	$_receiver = mysqli_fetch_object($mysqli->query("select employee_id, role_name, designation_name, department_name, full_name, personal_Phone, email, photo,Company_phone, company_email, status from employee where employee_id = '" . $_POST['receiver_id'] . "'"));
	$_message = $_POST['message'];	
	$_participants = array(
		$_POST['sender_id'], $_POST['receiver_id']
	);	//$_sender, $_receiver
	// echo json_encode(array(
		// 'recipients' 	=> $_participants,
		// 'text' 			=> $_message,
		// 'sender' 		=> $_sender->employee_id,
		// 'attachments' 	=> $_attachments
	// ));
	echo json_encode(array(		
		'recipients' 	=> $_participants,
		'mtype' 		=> '',
		'texts' 		=> [array(
			'type' 			=> 'text',
			'value' 		=> '',
			'text' 			=> $_message
		)],		
		'sender' 		=> $_sender->employee_id,
		'attachments' 	=> $_attachments
	));
	exit();
}
$_primary_employee_id = rahat_decode($_GET['employee_id']);
?>
<!DOCTYPE html>
<html>
	<head>
		<title>Neways - Chat</title>
		<link href="assets/css/bootstrap.min.css" rel="stylesheet" id="bootstrap-css">		
		<script src="assets/js/jquery.min.js"></script>
		<script src="assets/js/bootstrap.min.js"></script>
		<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.5.0/css/all.css" integrity="sha384-B4dIYHKNBt8Bc12p+WXckhzcICo0wtJAoU8YZTY5qE0Id1GSseTk6S+L3BlXeVIU" crossorigin="anonymous">
		<link rel="stylesheet" type="text/css" href="assets/css/jquery.mCustomScrollbar.min.css">
		<script type="text/javascript" src="assets/js/jquery.mCustomScrollbar.min.js"></script>		
		<link href="assets/css/custom.css" rel="stylesheet" id="bootstrap-css">
		<link href="assets/css/select2.min.css" rel="stylesheet" />
		<script src="assets/js/select2.min.js"></script>
		
		<link rel="stylesheet" href="assets/css/jquery.mentiony.css" >
		
		
		
	</head>	
	<body style="background: none !important;">
		<div class="container-fluid h-100">
			<div class="row justify-content-center h-100">
				<div class="col-md-4 col-xl-3 chat">
					<div class="card mb-sm-3 mb-md-0 contacts_card">						
						<div class="card-header">
							<div class="input-group conversion_header">
								<input type="text" placeholder="Search..." id="search_user" name="search_user" class="form-control search">
								<div class="input-group-prepend">
									<span class="input-group-text search_btn"><i class="fas fa-search"></i></span>
								</div>
							</div>
							<div class="input-group conversion_wigert">
								<div>
									<center class="button_container">
										<div class="dropdown">
											<button type="button" onclick="myFunction()" class="dropbtn btn btn-success dropdown-toggle" style="padding: 0px 23px;" data-toggle="dropdown">
												<i class="fas fa-plus"></i>
											</button>
											<div class="dropdown-menu" id="myDropdown">
												<a class="dropdown-item" style="font-size: 17px;" href="javascript: void(0)" onclick="new_message()" data-toggle="tooltip"  title="New Message"><i class="fas fa-comment-alt"></i> New Message</a>
												<a class="dropdown-item" style="font-size: 17px;" href="javascript: void(0)" onclick="new_group()"  data-toggle="tooltip"  title="New Group"><i class="fas fa-users"></i> New Group</a>
											</div>
										</div>
									</center>
								</div>
							</div>
						</div>
						<div class="card-body contacts_body" id="contacts_body"> </div>
						<div class="card-footer"></div>
					</div>
				</div>
				<div class="col-md-8 col-xl-6 chat" style="padding-left: 0px;">
					<div class="card">
						<div class="card-header msg_head"></div>					
						<div class="card-body msg_card_body" id="msg_card_body" style="display: flex; flex-direction: column-reverse;">			
							<center style="bottom: 29%; position: absolute; width: 99%;">
								<img src="assets/img/live-chat-icon.webp" style="width: 200px;filter: invert(1) hue-rotate(305deg);"/>
								<h4 style="color: #fff;">Choose a conversation</h4>
								<span style="color: #fff;">Click on an exixting chat or click "New message" to create a new conversation</span>
							</center>
						</div>						
						<div class="card-footer message_footer"> </div>						
					</div>
				</div>
			</div>
		</div>		
	
		<div class="modal fade new_message_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
			<div class="modal-dialog modal-dialog-centered" role="document">
				<div class="modal-content new_message_container" style="background-color: #78e08f94 !important">
					<form class="new_message_send_form" action="" method="post">
						<input type="hidden" name="sender_id" value="<?php echo $_primary_employee_id; ?>"/>
						<div class="modal-header">
							<h5 class="modal-title text-white" id="exampleModalCenterTitle">New Message</h5>
							<button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"> <span aria-hidden="true">&times;</span> </button>
						</div>
						<div class="modal-body">
							<div class="row">
								<div class="col-sm-12">
									<div class="form-group">								
										<select name="employee_id"class="form-control" style="width: 100%;" required></select>
									</div>
									<div class="form-group">								
										<textarea name="message" class="form-control" placeholder="Write Message..." required></textarea>
									</div>
								</div>
							</div>
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
							<button type="submit" name="send_message" class="btn btn-primary">Send Message</button>
						</div>
					</form>
				</div>
			</div>
		</div>
		
		<div class="modal fade new_group_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
			<div class="modal-dialog modal-dialog-centered" role="document">
				<div class="modal-content new_group_create_container" style="background-color: #78e08f94 !important">
					<form class="new_group_create_form" action="" method="post">
						<input type="hidden" name="sender_id" value="<?php echo $_primary_employee_id; ?>"/>
						<div class="modal-header">
							<h5 class="modal-title text-white" id="exampleModalCenterTitle">New Group</h5>
							<button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"> <span aria-hidden="true">&times;</span> </button>
						</div>
						<div class="modal-body">
							<div class="row">
								<div class="col-sm-12">
									<div class="form-group">								
										<input type="text" name="group_name" placeholder="Group Name" class="form-control" required />
									</div>									
									<div class="form-group">								
										<select name="group_employee_id[]" multiple="multiple" class="form-control" style="width: 100%;" required></select>
									</div>
									<div class="form-group">	
										<label>Group Icon</label>
										<input type="file" name="group_icon" class="form-control" required />
									</div>									
								</div>
							</div>
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
							<button type="submit" name="send_message" class="btn btn-primary">Send Message</button>
						</div>
					</form>
				</div>
			</div>
		</div>
		
		
		<div class="modal fade new_attachment_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
			<div class="modal-dialog modal-dialog-centered" role="document">
				<div class="modal-content new_attachment_container" style="background-color: #78e08f94 !important">
					<form class="new_attachment_send_form" action="" method="post" enctype="multipart/form-data">
						<input type="hidden" name="sender_id" alt-name="sender_id" value=""/>
						<input type="hidden" name="receiver_id" alt-name="receiver_id" value=""/>
						<input type="hidden" name="conversion_id" alt-name="conversion_id" value=""/>
						<div class="modal-header">
							<h5 class="modal-title text-white" id="exampleModalCenterTitle">New Attachment</h5>
							<button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"> <span aria-hidden="true">&times;</span> </button>
						</div>
						<div class="modal-body">
							<div class="row">
								<div class="col-sm-12">
									<div class="form-group">								
										<input type="file" name="attachment[]" multiple class="form-control" required /> 
									</div>
									<div class="form-group">								
										<textarea name="message" class="form-control" placeholder="Write Message..." required></textarea>
									</div>
								</div>
							</div>
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
							<button type="submit" name="send_message" class="btn btn-primary">Send Message</button>
						</div>
					</form>
				</div>
			</div>
		</div>
	
		<script>
			var start_convertion = '<center style="bottom: 29%; position: absolute; width: 99%;"> <img src="assets/img/live-chat-icon.webp" style="width: 200px;filter: invert(1) hue-rotate(305deg);"/> <h4 style="color: #fff;">Choose a conversation</h4> <span style="color: #fff;">Click on an exixting chat or click "New message" to create a new conversation</span> </center>';
			var employee_id = '<?php echo $_primary_employee_id; ?>';
			var employee_id_encode = '<?php echo $_GET['employee_id']; ?>';
			var loader = '<center><i class="fa fa-spinner fa-pulse fa-2x fa-fw text-white" style="margin: 50px;"></i></center>';
			var message_head = $('.msg_head');
			var message_body = $('.msg_card_body');
			var message_footer = $('.message_footer');
			var base_url = 'http://erp.superhostelbd.com';
			var PORT = 5005;
			
			
			function myFunction() {
				document.getElementById("myDropdown").classList.toggle("show");
			}
			window.onclick = function(event) {
			  if (!event.target.matches('.dropbtn')) {
				var dropdowns = document.getElementsByClassName("dropdown-menu");
				var i;
				for (i = 0; i < dropdowns.length; i++) {
				  var openDropdown = dropdowns[i];
				  if (openDropdown.classList.contains('show')) {
					openDropdown.classList.remove('show');
				  }
				}
			  }
			}
			
			$('.new_attachment_container').off();
			$('.new_attachment_container').on('submit', '.new_attachment_send_form', function(e){
				var c_id = $('input[alt-name="conversion_id"]').val();
				e.preventDefault();
				$.ajax({
					type		: "POST",
					enctype		: 'multipart/form-data',
					url			: 'index.php?new_attachment_post_submit=active',
					data		: new FormData($('.new_attachment_send_form')[0]),
					processData	: false,
					contentType	: false,
					cache		: false,
					timeout		: 600000,
					beforeSend	:function(){
						
					},
					success		:function(data){						
						$.ajax({
							url: base_url + ':' + PORT + '/v1/conversation/sendMessage/?convsId=' + c_id,
							type: 'post',
							dataType: 'json',
							contentType: 'application/json',							
							data: data,
							success: function (data) {
								$('.new_attachment_modal').modal('hide');
								//var conv_id = data['_id'];
								get_conversation(c_id);
							}
						});
						
					}
				});
				return false;
			});
			
			
			$('.new_group_create_container').off();
			$('.new_group_create_container').on('submit', '.new_group_create_form', function(e){
				e.preventDefault();
				$.ajax({
					type		: "POST",
					enctype		: 'multipart/form-data',
					url			: 'index.php?new_group_create_submit=active&employee_id='+employee_id,  
					data		: new FormData($('.new_group_create_form')[0]),
					processData	: false,
					contentType	: false,
					cache		: false,
					timeout		: 600000,
					beforeSend	:function(){
						
					},
					success		:function(data){						
						$.ajax({
							url: base_url + ':' + PORT + '/v1/conversation/firstMessage',
							type: 'post',
							dataType: 'json',
							contentType: 'application/json',							
							data: data,
							success: function (data) {
								$('.new_group_modal').modal('hide');
								var conv_id = data['_id'];
								get_conversation(conv_id);
							}
						});
						
					}
				});
				return false;
			});
			
			
			
			$('.new_message_container').off();
			$('.new_message_container').on('submit', '.new_message_send_form', function(e){
				e.preventDefault();
				$.ajax({
					type		: "POST",
					enctype		: 'multipart/form-data',
					url			: 'index.php?new_message_post_submit=active',  
					data		: new FormData($('.new_message_send_form')[0]),
					processData	: false,
					contentType	: false,
					cache		: false,
					timeout		: 600000,
					beforeSend	: function(){
						
					},
					success		:function(data){						
						$.ajax({
							url: base_url + ':' + PORT + '/v1/conversation/firstMessage',
							type: 'post',
							dataType: 'json',
							contentType: 'application/json',							
							data: data,
							success: function (data) {
								$('.new_message_modal').modal('hide');
								var conv_id = data['_id'];
								get_conversation(conv_id);
							}
						});
						
					}
				});
				return false;
			});
			
			
			function new_message(){
				$('select[name="employee_id"]').val('').trigger("change");
				$('textarea[name="message"]').val('');
				$('.new_message_modal').modal('show');
			}
			
			function new_group(){
				$('select[name="group_employee_id[]"]').val('').trigger("change");
				$('input[name="group_name"]').val('');
				$('input[name="group_icon"]').val('');
				$('.new_group_modal').modal('show');
			}
			
			function new_attachment(sender_id, receiver_id, conversion_id){
				$('input[alt-name="sender_id"]').val(sender_id);
				$('input[alt-name="receiver_id"]').val(receiver_id);
				$('input[alt-name="conversion_id"]').val(conversion_id);							
				
				$('input[name="attachment[]"]').val('').trigger("change");
				$('textarea[name="message"]').val('');
				$('.new_attachment_modal').modal('show');
			}
			
			
			
			get_conversion_list();
			function get_conversion_list(conversion_id = ''){
				$.get({
					url: base_url + ':' + PORT + '/v1/conversation/getConvsData',
					data: {
						employeeId: employee_id,
						title: 1,
						photo: 1,
						messages: 1,
						owner: 1,
						admins: 1,
						skip: 0,
						limit: 1,
						participants: 1,
						type: 1
					},
					beforeSend: function(){
						//$('.contacts_body').html(loader);
					},
					success: function(data){							
						var value = data;
						var html = '<ul class="contacts" id="contacts">';
						for (var i = 0; i <= value.length; i++ ) {					
							if(value[i]){
								var message_length = value[i]['unSeen'];							
								if(message_length == 0){
									var mark_unread = '';
									var new_style = '';
								}else{
									var mark_unread = '<div class="mark_unread"></div>';
									var new_style = 'style="font-weight: bolder;"';									
								}
								var active_status = value[i]['activeStatus'];
								if(active_status == 'Online'){
									var ac_status = '<span class="online_icon" title="Online"></span>';
								}else{
									var ac_status = '<span class="online_icon offline" title="Online"></span>';
								}
								
								var last_message = ''; //value[i]['messages'][0]['texts'][0]['value'];
								for (let index = 0; index < value[i]['messages'][0]['texts'].length; index++) {
									last_message += value[i]['messages'][0]['texts'][index]['text'];
									
								}
								if(value[i]['type'] == 'Single'){
									if(value[i]['participants'][0]['employee_id'] == employee_id){								
										var p_photo = base_url + '/super_home/' + value[i]['participants'][1]['photo'];
										var c_name = value[i]['participants'][1]['full_name'];								
									} else if(value[i]['participants'][1]['employee_id'] == employee_id) {								
										var p_photo = base_url + '/super_home/' + value[i]['participants'][0]['photo'];
										var c_name = value[i]['participants'][0]['full_name'];								
									}							
									html += '<li class="chat_list chat_list_' + value[i]['_id'] + '" id="' + value[i]['_id'] + '" type="' + value[i]['type'] + '" ' + new_style + '>';
									html += '<div class="d-flex bd-highlight">';
									html += '<div class="img_cont">';
									html += '<img src="' + p_photo + '" class="rounded-circle user_img">';
									html += '' + ac_status + '';
									html += '</div>';
									html += '<div class="user_info">';
									html += '<span>' + c_name + '</span>';
									html += '<p style="max-width: 150px;overflow: hidden;">' + last_message + '</p> ' + mark_unread + '';
									html += '</div>';
									html += '</div>';
									html += '</li>';
								} else if(value[i]['type'] == 'Group'){
									html += '<li class="chat_list chat_list_' + value[i]['_id'] + '" id="' + value[i]['_id'] + '" type="' + value[i]['type'] + '" ' + new_style + '>';
									html += '<div class="d-flex bd-highlight">';
									html += '<div class="img_cont">';
									html += '<img src="' + value[i]['photo'] + '" class="rounded-circle user_img">';
									html += '' + ac_status + '';
									html += '</div>';
									html += '<div class="user_info">';
									html += '<span>' + value[i]['title'] + '</span>';
									html += '<p style="max-width: 150px;overflow: hidden;">' + last_message + '</p> ' + mark_unread + '';
									html += '</div>';
									html += '</div>';
									html += '</li>';
								}else{ //"Default"
									html += '<li class="chat_list chat_list_' + value[i]['_id'] + '" id="' + value[i]['_id'] + '" type="' + value[i]['type'] + '" ' + new_style + '>';
									html += '<div class="d-flex bd-highlight">';
									html += '<div class="img_cont">';
									html += '<img src="' + value[i]['photo'] + '" class="rounded-circle user_img">';
									html += '' + ac_status + '';
									html += '</div>';
									html += '<div class="user_info">';
									html += '<span>' + value[i]['title'] + '</span>';
									html += '<p style="max-width: 150px;overflow: hidden;">' + last_message + '</p> ' + mark_unread + '';
									html += '</div>';
									html += '</div>';
									html += '</li>';
								}
								
								//console.log(value[i]);
							}
						}
						html += '</ul>';				
						$('.contacts_body').html(html);
						
						
						
						if(conversion_id != ''){
							$('.chat_list').removeClass('active');
							$('.chat_list_' + conversion_id).addClass('active');
							
							
						}
						
					}
				});		
			}
			
			setInterval(function(){ 
				var type = 0;
				$('.contacts li').each(function(){
					var get_true_id = $(this).hasClass('active');
					if(get_true_id){
						var conv_id = $(this).attr('id');
						get_conversion_list(conv_id);
						var limit = $('.close_message_body').attr('data-message-limit');
						get_conversation_refresh(conv_id, limit);
						type = 1;
						//console.log('B');
					}		
				});
				if(type == 0){
					get_conversion_list();
					//console.log('A');
				}				
			}, 4000);		
			
			function get_conversation(convertion_id){
				$.get({
					url: base_url + ':' + PORT + '/v1/conversation/getConvsData',
					data: {
						convsId: convertion_id,
						employeeId: employee_id,
						title: 1,
						photo: 1,
						messages: 1,
						owner: 1,
						admins: 1,
						skip: 0,
						limit: 10,
						participants: 1,
						type: 1
					},
					beforeSend: function(){
						//message_body.html(loader);
					},
					success: function(data){								
						var value = data[0];
						if(value['type'] == 'Single'){
							if(value['participants'][0]['employee_id'] == employee_id){								
								var p_photo 		= base_url + '/super_home/' + value['participants'][1]['photo'];
								var c_name 			= value['participants'][1]['full_name'];
								var receiver_id 	= value['participants'][1]['employee_id'];
							} else if(value['participants'][1]['employee_id'] == employee_id) {								
								var p_photo 		= base_url + '/super_home/' + value['participants'][0]['photo'];
								var c_name 			= value['participants'][0]['full_name'];	
								var receiver_id 	= value['participants'][0]['employee_id'];
							}	
						}else{
							var p_photo 			= value['photo'];
							var c_name 				= value['title'];
							var receiver_id 		= [];
						}
						
						//console.log(value['participants']);
						
						var message_body_data = ''; var number_of_message = 0;
						for (var i = 0; i <= value['messages'].length; i++ ) {	
							if(value['messages'][i]){
								var message = value['messages'][i];
								var sender = message['sender'];	
								
								var attachment_data = '';
								for (var l = 0; l <= message['attachments'].length; l++ ) {
									if(message['attachments'][l]){
										var attachment = message['attachments'][l];
										if(attachment['type'] == 'photo'){
											attachment_data += '<a download="' + attachment['name'] + '" href="' + attachment['url'] + '" title="' + attachment['name'] + '"><img src="' + attachment['url'] + '" style="max-width: 250px;border-radius: 10px;"/></a><small style="display: block;max-width: 250px;">' + attachment['name'] + '</small><br />';
										}else{
											attachment_data += '<b><a href="' + attachment['url'] + '"  target="_blank">View Attachment</a></b><small style="display: block;max-width: 250px;">' + attachment['name'] + '</small><br />';
										}
									}
								}
								 var messageTotalText = '';
								for (let index = 0; index < message['texts'].length; index++) {

									if(message['texts'][index]['text']==null){
										messageTotalText += message['texts'][index]['value'];
									}else{
										messageTotalText += message['texts'][index]['text'];
									}
									
									//message['texts'][0]['value']
								 //linkify(messageTotalText)
								}
								if(sender['employee_id']){
									if(sender['employee_id'] == employee_id){								
										message_body_data += '<div class="d-flex justify-content-end mb-4">';
										message_body_data += '<div class="msg_cotainer_send" style="overflow: hidden;">';
										message_body_data += '' + attachment_data + ' ' + messageTotalText + '';
										message_body_data += '<span class="msg_time_send">' + message['createdAt'] + '</span>';
										message_body_data += '</div>';
										message_body_data += '<div class="img_cont_msg">';
										message_body_data += '<img title="' + sender['full_name'] + '" src="' + base_url + '/super_home/' + sender['photo'] + '" class="rounded-circle user_img_msg">';
										message_body_data += '</div>';
										message_body_data += '</div>';
									} else {								
										message_body_data += '<div class="d-flex justify-content-start mb-4">';
										message_body_data += '<div class="img_cont_msg">';
										message_body_data += '<img title="' + sender['full_name'] + '" src="' + base_url + '/super_home/' + sender['photo'] + '" class="rounded-circle user_img_msg">';
										message_body_data += '</div>';
										message_body_data += '<div class="msg_cotainer" style="overflow: hidden;">';
										message_body_data += '' + attachment_data + ' ' + messageTotalText + '';
										message_body_data += '<span class="msg_time_send" style="left: 0;">' + message['createdAt'] + '</span>';
										message_body_data += '</div>';
										message_body_data += '</div>';
									}						
									
									number_of_message = number_of_message + 1;
								}								
							}							
						}
						message_body_data += '<div class="close_message_body" data-message-limit="1" data-convertion-id="' + convertion_id + '"><i class="fa fa-times"></i></div>';
						//console.log(value);
						message_body.html(message_body_data);
						message_body.animate({
							scrollTop: document.getElementById("msg_card_body").scrollHeight
						}, 0);	
						var active_status = value['activeStatus'];
						if(active_status == 'Online'){
							var ac_status = '<span class="online_icon" title="Online"></span>';
						}else{
							var ac_status = '<span class="online_icon offline" title="Online"></span>';
						}
						
						var duty_status = value['dutyStatus'];
						if(duty_status == 'On Duty'){
							duty_status = '<span style="color: #78e08f;max-width: 100%;">' + duty_status + '</span>';
						}else{
							duty_status = '<span style="color: #f00;max-width: 100%;">' + duty_status + '</span>';
						}
						
						var message_head_data = '<div class="d-flex bd-highlight">';
						message_head_data += '<div class="img_cont">';
						message_head_data += '<img src="' + p_photo + '" class="rounded-circle user_img">';
						message_head_data += '' + ac_status + '';
						message_head_data += '</div>';
						message_head_data += '<div class="user_info">';
						message_head_data += '<span>' + c_name + '</span>';
						message_head_data += '<p>' + number_of_message + ' Messages</p>';
						message_head_data += '<p class="duty_status button_container" style="padding-left: 10px; padding-right: 10px;">' + duty_status + '</p>';
						
						message_head_data += '</div>';
						message_head_data += '</div>';
						message_head.html(message_head_data);
						
						var message_footer_data = '';
						message_footer_data += '<div class="input-group" style="flex-wrap: unset;">';
						message_footer_data += '<div class="input-group-append">';
						message_footer_data += '<span class="input-group-text attach_btn" sender_id="' + employee_id + '" receiver_id="' + receiver_id + '" conversion_id="' + convertion_id + '"><i class="fas fa-paperclip"></i></span>';
						message_footer_data += '</div>';
						message_footer_data += '<textarea type="text" name="message_input_field" id="message" sender_id="' + employee_id + '" receiver_id="' + receiver_id + '" conversion_id="' + convertion_id + '" class="form-control type_msg" placeholder="Type your message..."></textarea>';
						message_footer_data += '<div class="input-group-append">';
						message_footer_data += '<span class="input-group-text send_btn"><i class="fas fa-location-arrow"></i></span>';
						message_footer_data += '</div>';
						message_footer_data += '</div>';
						
						message_footer.html(message_footer_data);
						
						$('textarea[name="message_input_field"]').mentiony({
							onDataRequest: function (mode, keyword, onDataRequestCompleteCallback) {
								$.ajax({
									method: "GET",
									url: "index.php?employee_id=<?php echo $_GET['employee_id']; ?>&get-mention-list=true&mention_query="+ keyword,
									dataType: "json",
									success: function (response) {
										var data = response;
										data = jQuery.grep(data, function( item ) {
											return item.name.toLowerCase().indexOf(keyword.toLowerCase()) > -1;
										});
										onDataRequestCompleteCallback.call(this, data);
									}
								});
							},
							timeOut: 500,
							debug: 0,
						});
						setTimeout(function() { 
							$('div[contenteditable="true"]').focus();
						}, 500);						
						get_conversion_list(convertion_id);
					}
				});
			}
			
			message_body.on('scroll', function() {
				var scrollTop = message_body.scrollTop();
				var scrollHeight = message_body.prop('scrollHeight');
				var clientHeight = message_body.prop('clientHeight');
				var additional_height = (scrollTop * -1) + clientHeight + 100;

				if (additional_height > scrollHeight) {
					var limit = parseInt($('.close_message_body').attr('data-message-limit'), 10);
					var convertion_id = $('.close_message_body').attr('data-convertion-id');
					
					// Increment the limit for the next batch of messages
					limit = limit + 1;
					$('.close_message_body').attr('data-message-limit', limit);

					// Call the function to get more messages
					get_conversation_refresh(convertion_id, limit);
				}
				//console.log('Additional Height: ' + additional_height + ' | Scroll Height: ' + scrollHeight + ' | Limit: ' + limit);
			});
			
			function get_conversation_refresh(convertion_id, limit = 1){			
				$.get({
					url: base_url + ':' + PORT + '/v1/conversation/getConvsData',
					data: {
						convsId: convertion_id,
						employeeId: employee_id,
						title: 1,
						photo: 1,
						messages: 1,
						owner: 1,
						admins: 1,
						skip: 0,
						limit: (10 * limit),
						participants: 1,
						type: 1
					},
					beforeSend: function(){
						//message_body.html(loader);
					},
					success: function(data){
						//$('.chat_list').removeClass('active');
						//$('.chat_list_' + convertion_id).addClass('active');	

						var scrollTop = message_body.scrollTop();
						var scrollHeight = message_body.prop('scrollHeight');
						var clientHeight = message_body.prop('clientHeight');
						var isScrolledToBottom = (scrollTop + clientHeight) === scrollHeight;
						
						var value = data[0];
						if(value['type'] == 'Single'){
							if(value['participants'][0]['employee_id'] == employee_id){								
								var p_photo 		= base_url + '/super_home/' + value['participants'][1]['photo'];
								var c_name 			= value['participants'][1]['full_name'];
								var receiver_id 	= value['participants'][1]['employee_id'];
							} else if(value['participants'][1]['employee_id'] == employee_id) {								
								var p_photo 		= base_url + '/super_home/' + value['participants'][0]['photo'];
								var c_name 			= value['participants'][0]['full_name'];	
								var receiver_id 	= value['participants'][0]['employee_id'];
							}	
						}else{
							var p_photo 			= value['photo'];
							var c_name 				= value['title'];
							var receiver_id 		= [];
						}
						
						//console.log(value['messages']);
						
						var message_body_data = ''; var number_of_message = 0;
						for (var i = 0; i <= value['messages'].length; i++ ) {	
							if(value['messages'][i]){
								var message = value['messages'][i];
								var sender = message['sender'];	
								
								var attachment_data = '';
								for (var l = 0; l <= message['attachments'].length; l++ ) {
									if(message['attachments'][l]){
										var attachment = message['attachments'][l];
										if(attachment['type'] == 'photo'){
											attachment_data += '<a download="' + attachment['name'] + '" href="' + attachment['url'] + '" title="' + attachment['name'] + '"><img src="' + attachment['url'] + '" style="max-width: 250px;border-radius: 10px;"/></a><small style="display: block;max-width: 250px;">' + attachment['name'] + '</small><br />';
										}else{
											attachment_data += '<b><a href="' + attachment['url'] + '"  target="_blank">View Attachment</a></b><small style="display: block;max-width: 250px;">' + attachment['name'] + '</small><br />';
										}
									}
								}

								 var messageTotalText = '';
								for (let index = 0; index < message['texts'].length; index++) {
									if(message['texts'][index]['text']==null){
										messageTotalText += message['texts'][index]['value'];
									}else{
										messageTotalText += message['texts'][index]['text'];
									}
									//message['texts'][0]['value']
								 //linkify(messageTotalText) 
								}
								if(sender['employee_id']){
									if(sender['employee_id'] == employee_id){								
										message_body_data += '<div class="d-flex justify-content-end mb-4">';
										message_body_data += '<div class="msg_cotainer_send" style="overflow: hidden;">';
										message_body_data += '' + attachment_data + ' ' + messageTotalText + '';
										message_body_data += '<span class="msg_time_send">' + message['createdAt'] + '</span>';
										message_body_data += '</div>';
										message_body_data += '<div class="img_cont_msg">';
										message_body_data += '<img title="' + sender['full_name'] + '" src="' + base_url + '/super_home/' + sender['photo'] + '" class="rounded-circle user_img_msg">';
										message_body_data += '</div>';
										message_body_data += '</div>';
									} else {								
										message_body_data += '<div class="d-flex justify-content-start mb-4">';
										message_body_data += '<div class="img_cont_msg">';
										message_body_data += '<img title="' + sender['full_name'] + '" src="' + base_url + '/super_home/' + sender['photo'] + '" class="rounded-circle user_img_msg">';
										message_body_data += '</div>';
										message_body_data += '<div class="msg_cotainer" style="overflow: hidden;">';
										message_body_data += '' + attachment_data + ' ' + messageTotalText + '';
										message_body_data += '<span class="msg_time_send" style="left: 0;">' + message['createdAt'] + '</span>';
										message_body_data += '</div>';
										message_body_data += '</div>';
									}						
									
									number_of_message = number_of_message + 1;
								}
							}	
							
						}
						message_body_data += '<div class="close_message_body" data-message-limit="' + limit + '" data-convertion-id="' + convertion_id + '"><i class="fa fa-times"></i></div>';
						message_body.html(message_body_data);						
						if (isScrolledToBottom) {
							message_body.scrollTop(message_body.prop('scrollHeight'));
						} else {
							message_body.scrollTop(scrollTop);
						}						
						
						var duty_status = value['dutyStatus'];
						if(duty_status == 'On Duty'){
							duty_status = '<span style="color: #78e08f;max-width: 100%;">' + duty_status + '</span>';
						}else{
							duty_status = '<span style="color: #f00;max-width: 100%;">' + duty_status + '</span>';
						}

						var message_head_data = '<div class="d-flex bd-highlight">';
						message_head_data += '<div class="img_cont">';
						message_head_data += '<img src="' + p_photo + '" class="rounded-circle user_img">';
						message_head_data += '<span class="online_icon"></span>';
						message_head_data += '</div>';
						message_head_data += '<div class="user_info">';
						message_head_data += '<span>' + c_name + '</span>';
						message_head_data += '<p>' + number_of_message + ' Messages</p>';
						message_head_data += '<p class="duty_status button_container" style="padding-left: 10px; padding-right: 10px;">' + duty_status + '</p>';
						message_head_data += '</div>';
						message_head_data += '</div>';
						message_head.html(message_head_data);		
					}
				});
			}
			var key_entry = true;
			function finaly_send_message(){ 
				var message_content 	= $('textarea[name="message_input_field"]').val();
				var sender_id 			= $('textarea[name="message_input_field"]').attr('sender_id');
				var receiver_id 		= $('textarea[name="message_input_field"]').attr('receiver_id');
				var conversion_id 		= $('textarea[name="message_input_field"]').attr('conversion_id');
				var temp = $("<div>");
				temp.html(message_content);
				message_content = temp.text();
				if(message_content != '' && key_entry){
					key_entry = false;
					$.post({
						url: 'index.php?new_message_post_submit_chat=active', 
						data: {
							sender_id: sender_id,
							employee_id: receiver_id,
							message: message_content
						},
						beforeSend	: function(){
							
						},
						success		:function(data){							
							$.ajax({
								//url: base_url + ':' + PORT + '/v1/conversation/firstMessage',
								url: base_url + ':' + PORT + '/v1/conversation/sendMessage/?convsId=' + conversion_id,
								type: 'post',
								dataType: 'json',
								contentType: 'application/json',							
								data: data,
								success: function (data) {
									//$('.new_message_modal').modal('hide');
									//var conv_id = data['_id'];
									get_conversation(conversion_id);
									$('.contacts_body').animate({
										scrollTop: 0
									}, 0);
									key_entry = true;
								}
							});							
						}
					});			

					// $.post({
						// url: base_url + ':' + PORT + '/v1/conversation/sendMessage/?convsId=' + conversion_id,
						// data: {
							// texts: [message_content],
							// sender: sender_id,
							// recipients: [receiver_id],
						// },
						// beforeSend: function(){
							
						// },
						// success: function(data){
							// get_conversation(conversion_id);
							// $('.contacts_body').animate({
								// scrollTop: 0
							// }, 0);
						// }
					// });
				}else{
					
				}
				
			}
			
			function search_conversion() {
				var input, filter, ul, li, a, i, txtValue;
				input = document.getElementById("search_user");
				filter = input.value.toUpperCase();
				ul = document.getElementById("contacts");
				li = ul.getElementsByTagName("li");
				for (i = 0; i < li.length; i++) {
					a = li[i].getElementsByTagName("span")[1];
					txtValue = a.textContent || a.innerText;
					if (txtValue.toUpperCase().indexOf(filter) > -1) {
						li[i].style.display = "";
					} else {
						li[i].style.display = "none";
					}
				}
			}
			
			function linkify(text) {
				var urlRegex =/(\b(https?|ftp|file):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])/ig;
				return text.replace(urlRegex, function(url) {
					return '<a href="' + url + '" target="_blank">' + url + '</a>';
				});
			}
			
			$('.message_footer').off();
			$('.message_footer').on('keydown', 'div[contenteditable="true"]', function(event){				
				var keycode = (event.keyCode ? event.keyCode : event.which);
				if(keycode == '13'){ event.preventDefault();
					if($('.mentiony-popover:visible').length == 0) {
						finaly_send_message();
					}				
				}							
			});
			
			$('.message_footer').on('click', '.attach_btn', function(){
				var sender_id 			= $(this).attr('sender_id');
				var receiver_id 		= $(this).attr('receiver_id');
				var conversion_id 		= $(this).attr('conversion_id');				
				new_attachment(sender_id, receiver_id, conversion_id);
			});	
			

			$('.message_footer').on('click', '.send_btn', function(){
				finaly_send_message();
			});	
				
			$(document).on('click', '.chat_list', function(){
				var conversion_id = $(this).attr('id');
				get_conversation(conversion_id);
			});
			
			$(document).on('click', '.close_message_body', function(){
				window.open('' + base_url + '/chat_list/?employee_id=' + employee_id_encode + '','_self');
				//message_body.html(start_convertion);
			});
			
			//
			
			$('select[name="employee_id"]').on('open', function () {
			  self.$search.attr('tabindex', 0);
			  //self.$search.focus(); remove this line
			  setTimeout(function () { self.$search.focus(); }, 10);//add this line

			});
			
			$(document).ready(function(){	
				$('select[name="group_employee_id[]"]').select2({
					placeholder: 'Select User',
					allowClear: true,
					ajax: {
						url: 'index.php?get_employee_user=active&employee_id=' + employee_id + '',
						dataType: 'json',
						type: "GET",
						delay: 250,
						data: function (data) { return { query: data.term }; },
						
						processResults: function (response) { return { results:response }; },
						cache: true
					},
					templateResult: format,
					templateSelection: format,
					escapeMarkup: function(m) {
						return m;
					}
				});
				
				$('select[name="employee_id"]').select2({
					placeholder: 'Select User',
					allowClear: true,
					ajax: {
						url: 'index.php?get_employee_user=active&employee_id=' + employee_id + '',
						dataType: 'json',
						type: "GET",
						delay: 250,
						data: function (data) { return { query: data.term }; },
						
						processResults: function (response) { return { results:response }; },
						cache: true
					},
					templateResult: format,
					templateSelection: format,
					escapeMarkup: function(m) {
						return m;
					}
				});
				
				function format(state) {
					if(state.text){
						return '<img src="' + base_url + '/super_home/' + state.image + '" style="width: 50px; height: 50px;margin-right: 5px;border-radius: 50px;border:solid 3px #333;" />' + state.text;
					}else{
						return '';
						
					}					
				}
			
				$('input[name="search_user"]').on('input', function(e){
					search_conversion();
				});				
				
				$('#action_menu_btn').click(function(){
					$('.action_menu').toggle();
				});
				//$('[data-toggle="tooltip"]').tooltip(); 
			});
		</script>
		<script src="assets/js/jquery.mentiony.js"></script>
	</body>
</html>


