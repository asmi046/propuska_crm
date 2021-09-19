<?

define("BI_SERVICE_DB_NAME", "manist1h_auscrm");
define("BI_SERVICE_USER_NAME", "manist1h_auscrm");
define("BI_SERVICE_USER_PASS", "N%034F0y");
define("BI_SERVICE_DB_HOST", "localhost");

function get_number_info($number) {
	$token = "T2yJ1XQqmL2HYMCwZkUysagGl4x6htqm";
	$url = "https://ap.mosbot.ru/api/passes.json";

	return json_decode(file_get_contents($url."?apikey=".$token."&truck_num=".urlencode($number)));
}

add_action( 'rest_api_init', function () {
register_rest_route( 'lscrm/v2', '/userautorization', array(
		'methods'  => 'GET',
		'callback' => 'user_autorization',
		'args' => array(
			'autinfo' => array(
				'default'           => null,
				'required'          => true,        		
			)
		),
) );
});

//https://propuska-mkad-ttk-sk.ru/wp-json/lscrm/v2/userautorization?autinfo=null
//https://propuska-mkad-ttk-sk.ru/wp-json/lscrm/v2/userautorization?autinfo[mail]=asmi046@gmail.com&autinfo[pass]=1111
function user_autorization( WP_REST_Request $request) {
	
	
	$autinfo = json_decode($request["autinfo"], true);
	

	if (empty($autinfo)) return new WP_Error( 'no_user_data', 'Учетные данные не переданы.', [ 'status' => 408 ] );
	
	$mail = $autinfo["mail"];
	$password = $autinfo["pass"];
	$passwordSalt = md5($password."dssff3fxx");

	$token = rand(200000, 300000);

	$serviceBase = new wpdb(BI_SERVICE_USER_NAME, BI_SERVICE_USER_PASS, BI_SERVICE_DB_NAME, BI_SERVICE_DB_HOST);
	
	$user_feeld =  $serviceBase->get_results("SELECT * FROM `service_users` WHERE `mail` = '".$mail."' AND `pass` =  '".$passwordSalt."'");

		if (!empty($user_feeld)) {
			if (empty($user_feeld[0]->autorize))
			return new WP_Error( 'no_checed_user', 'Ваша учетная запись еще не активирована администартором.', [ 'status' => 401 ] );
			
			$updateRez = $serviceBase->update("service_users",
				array(
					"autorizeKey" => $token,
				), 
				array(
					"id" => $user_feeld[0]->id, 
				)
			 );   

			return array(
				"fio" => $user_feeld[0]->fio,
				"podrazdelenie" => $user_feeld[0]->podrazdelenie,
				"mail" => $user_feeld[0]->mail,
				"dolgnost" => $user_feeld[0]->dolgnost,
				"seans_length" => $user_feeld[0]->seans_length,
				"token" => $token
			);
			

		} else {
			return new WP_Error( 'no_user', 'Пользоватея с такими данными нет в системе.', [ 'status' => 402 ] );
		}
}

add_action( 'rest_api_init', function () {
register_rest_route( 'lscrm/v2', '/relogin', array(
	'methods'  => 'GET',
	'callback' => 'relogin',
	'args' => array(
		'mail' => array(
			'default'           => null,
			'required'          => true,        		
		)
	),
) );
});

//https://propuska-mkad-ttk-sk.ru/wp-json/lscrm/v2/relogin?mail=asmi046@gmail.com
function relogin( WP_REST_Request $request) {
	$serviceBase = new wpdb(BI_SERVICE_USER_NAME, BI_SERVICE_USER_PASS, BI_SERVICE_DB_NAME, BI_SERVICE_DB_HOST);
	
	$updateRez = $serviceBase->update("service_users",
		array(
			"autorizeKey" => 0,
		), 
		array(
			"mail" => $request["mail"], 
		)
	);  
	if (!empty($updateRez))
		return array("dell"=> true);
	else 
	return new WP_Error( 'no_token', 'Токен не найден или пользователь уже разлогинен.', [ 'status' => 403 ] ); 

}

add_action( 'rest_api_init', function () {
	register_rest_route( 'lscrm/v2', '/passrec', array(
		'methods'  => 'GET',
		'callback' => 'pass_rec',
		'args' => array(
			'mail' => array(
				'default'           => null,
				'required'          => true,        		
			)
		),
	) );
});

//https://propuska-mkad-ttk-sk.ru/wp-json/lscrm/v2/passrec?mail=asmi046@gmail.com
function pass_rec( WP_REST_Request $request) {
	$serviceBase = new wpdb(BI_SERVICE_USER_NAME, BI_SERVICE_USER_PASS, BI_SERVICE_DB_NAME, BI_SERVICE_DB_HOST);
	
	$user_feeld =  $serviceBase->get_results("SELECT * FROM `service_users` WHERE `mail` = '".$request["mail"]."'");

	if (empty($user_feeld)) return new WP_Error( 'no_user', 'Пользоватея с такими данными нет в системе.', [ 'status' => 403 ] );

	if (empty($user_feeld[0]->autorize)) return new WP_Error( 'no_checed_user', 'Ваша учетная запись еще не активирована администратором.', [ 'status' => 403 ] );

	$newPass = gen_password(4);
	$newPassHesh = md5($newPass."dssff3fxx");
	$updateRez = $serviceBase->update("service_users",
                                   array(
									   "pass" => $newPassHesh,
                                   ), 
                                   array(
                                       "id" => $user_feeld[0]->id, 
                                   )
                                );    

	if (!empty($updateRez))
	{
		$headers = array(
			'From: Корпоративные сервисы RubEx Group <RubExGroup@yandex.ru>', 
			'content-type: text/html',
		);

		add_filter('wp_mail_content_type', create_function('', 'return "text/html";'));
	   
		$mail_message = 
			"<h1>Восстановление пароля</h1>".
			"<p>Ваш логин:<p>".
			"<p>".$request["mail"]."</p>".
			"<p>Ваш новый пароль:<p>".
			"<p>".$newPass."</p>".
			"<a href = 'https://bi.rubexgroup.ru'>Перейти в сервис.</a>";
	  
		if (wp_mail($user_feeld[0]->mail, "Восстановление пароля", $mail_message, $headers))
		{
				return array("send" => true );
		}
			else 
				return new WP_Error( 'no_send_mail', 'Письмо с новым паролем не отправлено, попробуйте позднее или обратитесь к администратору. ', [ 'status' => 403 ] ); 

			return array("dell"=> true);
	}
	else 
		return new WP_Error( 'no_update_base', 'Пароль не изменен обратитесь к администратору.', [ 'status' => 403 ] ); 

}


add_action( 'rest_api_init', function () {
	register_rest_route( 'lscrm/v2', '/getregister', array(
		'methods'  => 'GET',
		'callback' => 'get_getregister',
		'args' => array(
			'reginfo' => array(
				'default'           => null,
				'required'          => true,        		
			)
		),
	) );
});

// https://propuska-mkad-ttk-sk.ru/wp-json/lscrm/v2/getregister?reginfo=null
function get_getregister( WP_REST_Request $request ){

	$serviceBase = new wpdb(BI_SERVICE_USER_NAME, BI_SERVICE_USER_PASS, BI_SERVICE_DB_NAME, BI_SERVICE_DB_HOST);

	$reginfo = json_decode($request["reginfo"], true);

	$addResult = $serviceBase->get_results("SELECT * FROM `service_users` WHERE `mail` = '".$reginfo["mail"]."'");

	if (!empty($addResult))
	return new WP_Error( 'user_exist', 'Пользователь с таким e-mail уже зарегистрирован.', [ 'status' => 403 ] );

	$addResult = $serviceBase->insert('service_users', array(
		"fio" => $reginfo["fio"],
		"mail" => $reginfo["mail"],
		"dolgnost" => $reginfo["dolgnost"],
		"pass" => md5($reginfo["pass"]."dssff3fxx")
	));
	
	if (empty($addResult))
		return new WP_Error( 'no_inser_user', 'При регистрации возникли ошибки попробуйте позднее', [ 'status' => 403 ] );
	else 
		return array("result" => true);
}

add_action( 'rest_api_init', function () {
	register_rest_route( 'lscrm/v2', '/add_one_number', array(
		'methods'  => 'GET',
		'callback' => 'add_one_number',
		'args' => array(
			'number' => array(
				'default'           => "",
				'required'          => true,        		
			),

			'mail' => array(
				'default'           => "",
				'required'          => true,        		
			),

			'phone' => array(
				'default'           => "",      		
			),

			'sts' => array(
				'default'           => "",      		
			),
		),
	) );
});

// https://propuska-mkad-ttk-sk.ru/wp-json/lscrm/v2/add_one_number?number=м048уе46&mail=asmi046@gmail.com
function add_one_number( WP_REST_Request $request ){

	$serviceBase = new wpdb(BI_SERVICE_USER_NAME, BI_SERVICE_USER_PASS, BI_SERVICE_DB_NAME, BI_SERVICE_DB_HOST);

	$addResult = $serviceBase->insert('service_number', array(
		"number" => $request["number"],
		"email" => $request["mail"],
		"phone" => $request["phone"],
		"sts" => $request["sts"]
	));
	
	if (empty($addResult))
		return new WP_Error( 'no_inser_number', 'При добавлении номера возникла ошибка', [ 'status' => 403 ] );
	else 
		return array("result" => true);
}

// 
// Массовая проверка номеров
// 

add_action( 'rest_api_init', function () {
	register_rest_route( 'lscrm/v2', '/add_one_numbers', array(
		'methods'  => 'POST',
		'callback' => 'add_one_numbers',
		'args' => array(
			'chec' => array(
				'default'           => "0",        		
			),
		),
	) );
});

function countDaysBetweenDates($d1, $d2)
{
    $d1_ts = strtotime($d1);
    $d2_ts = strtotime($d2);

    $seconds = $d1_ts - $d2_ts;
    
    return floor($seconds / 86400);
}

function get_status($element) {
	$deycount = countDaysBetweenDates($element->valid_to, date("Y-m-d H:i:s"));
	$deycount = ($deycount<0)?0:$deycount;
	

	$status = "Действует";

	if (!empty($element->cancel_date)) $status = "Анулирован";
	if (!empty($element->cancel_date) && (strtotime($element->valid_to) == strtotime(date("Y-m-d")))) $status = "Анулирован сегодня";
	if (empty($deycount)) $status = "Закончился";
	if (strtotime($element->valid_to) == strtotime(date("Y-m-d"))) $status = "Заканчивается сегодня";
	if (strtotime($element->valid_to) == (strtotime(date("Y-m-d"))+86400)) $status = "Заканчивается завтра";
	if (strtotime($element->valid_from) == strtotime(date("Y-m-d"))) $status = "Начинается сегодня";
	if (strtotime($element->valid_from) == (strtotime(date("Y-m-d"))+86400)) $status = "Начинается завтра";

	return array(
		"sys_status" => $status,
		"deycount" => $deycount
	);
}

// https://propuska-mkad-ttk-sk.ru/wp-json/lscrm/v2/add_one_numbers
function add_one_numbers( WP_REST_Request $request ){
	
	$fileinfo = $request->get_file_params();

	$serviceBase = new wpdb(BI_SERVICE_USER_NAME, BI_SERVICE_USER_PASS, BI_SERVICE_DB_NAME, BI_SERVICE_DB_HOST);

	$numbers = array();

	$row = 1;
	if (($handle = fopen($fileinfo["numbersfile"]["tmp_name"], "r")) !== FALSE) {
		while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
			
			$number = mb_convert_encoding($data[1], "UTF-8", "WINDOWS-1251");
			$email = mb_convert_encoding($data[0], "UTF-8", "WINDOWS-1251");

			$addingArray = array(
				"number" => $number,
				"email" => $email,
			);

			$statuses = array();

			if (!empty($request["chec"])) {

				$aus_chec_rez = get_number_info($number);

				$statuses = get_status($aus_chec_rez->passes[count($aus_chec_rez->passes) - 1]);

				$addingArray["status"] =  $aus_chec_rez->passes[count($aus_chec_rez->passes) - 1]->status;
				$addingArray["seria"] =  $aus_chec_rez->passes[count($aus_chec_rez->passes) - 1]->series;
				$addingArray["type"] =  $aus_chec_rez->passes[count($aus_chec_rez->passes) - 1]->pass_zone;
				$addingArray["time"] =  empty($aus_chec_rez->passes[count($aus_chec_rez->passes) - 1]->type_pass)?"":$aus_chec_rez->passes[count($aus_chec_rez->passes) - 1]->type_pass;
				$addingArray["pass_number"] =  $aus_chec_rez->passes[count($aus_chec_rez->passes) - 1]->pass_number;
				$addingArray["chec_time"] =  date("Y-m-d H:i:s");
				$addingArray["start_data"] =  date("Y-m-d H:i:s", strtotime($aus_chec_rez->passes[count($aus_chec_rez->passes) - 1]->valid_from));
				$addingArray["end_data"] =  date("Y-m-d H:i:s", strtotime($aus_chec_rez->passes[count($aus_chec_rez->passes) - 1]->valid_to));
				$addingArray["anul_data"] =  date("Y-m-d H:i:s", strtotime($aus_chec_rez->passes[count($aus_chec_rez->passes) - 1]->cancel_date));
				$addingArray["dey_count"] =  $statuses["deycount"];
				$addingArray["sys_status"] =  $statuses["sys_status"];
			
			}

			$addResult = $serviceBase->insert('service_number', $addingArray);

			$numbers[] = array(
				"number" => $number,
				"email" => $email,
				"result" => $addResult,
				"statuses" => $statuses
			);
		}
		fclose($handle);
	}

	return $numbers;
}

// 
// Проверка номера через сервис
// 

add_action( 'rest_api_init', function () {
	register_rest_route( 'lscrm/v2', '/number_info', array(
		'methods'  => 'GET',
		'callback' => 'number_info',
		'args' => array(
			'number' => array(
				'default'           => "",
				'required'          => true,        		
			)
		),
	) );
	});
	
	//https://propuska-mkad-ttk-sk.ru/wp-json/lscrm/v2/number_info?number=Х983ХК750
	function number_info( WP_REST_Request $request) {

		return get_number_info($request["number"]); 
	
	}


// 
// Получение фильтрованного списка номеров
// 

add_action( 'rest_api_init', function () {
	register_rest_route( 'lscrm/v2', '/get_number_table', array(
		'methods'  => 'GET',
		'callback' => 'get_number_table',
		'args' => array(
			'status' => array(
				'default'           => "",
			),
			'type' => array(
				'default'           => "",
			),

			'searchstr' => array(
				'default'           => "",
			),
			
		),
	) );
	});
	
	//https://propuska-mkad-ttk-sk.ru/wp-json/lscrm/v2/get_number_table?page=1&countinpage=5
	function get_number_table( WP_REST_Request $request) {
		$serviceBase = new wpdb(BI_SERVICE_USER_NAME, BI_SERVICE_USER_PASS, BI_SERVICE_DB_NAME, BI_SERVICE_DB_HOST);
		
		$count = $serviceBase->get_results("SELECT count(*) as `ncount` FROM `service_number`");

		
		$status = empty($request["status"])?"%":$request["status"];
		$type = empty($request["type"])?"%":$request["type"];
		$searchstr = empty($request["searchstr"])?"%":$request["searchstr"];

		$q = "SELECT * FROM `service_number` WHERE `sys_status` LIKE '".$status."' AND `seria` LIKE '".$type."' AND `pass_number` LIKE '".$searchstr."' AND `number` LIKE '".$searchstr."'  AND `email` LIKE '".$searchstr."'";
		
		$result = $serviceBase->get_results($q);

		$statuses = $serviceBase->get_results("SELECT `sys_status` as `text`, count(*) as `count` FROM `service_number` GROUP BY `sys_status`");
		
		return array(
			"statuses" => $statuses,
			"count" => $count[0]->ncount,
			"result" => $result, 
			"q" => $q
		);
	}	

// 
// Удаление номера
// 

add_action( 'rest_api_init', function () {
	register_rest_route( 'lscrm/v2', '/dell_number', array(
		'methods'  => 'GET',
		'callback' => 'dell_number',
		'args' => array(
			'number' => array(
				'default'           => "",
				'required'          => true, 
			),
			'mail' => array(
				'default'           => "",
				'required'          => true, 
			),

			'token' => array(
				'default'           => "",
				'required'          => true, 
			)
		),
	) );
	});
	
	//https://propuska-mkad-ttk-sk.ru/wp-json/lscrm/v2/dell_number?number=Е268КР53&token=291327&mail=asmi046@gmail.com
	function dell_number( WP_REST_Request $request) {
		
		$serviceBase = new wpdb(BI_SERVICE_USER_NAME, BI_SERVICE_USER_PASS, BI_SERVICE_DB_NAME, BI_SERVICE_DB_HOST);
		
		$token = $serviceBase->get_results("SELECT `autorizeKey` FROM `service_users` WHERE `mail` = '".$request["mail"]."'");
	

		if (empty($token))
			return new WP_Error( 'no_token_incorrect', 'Токен некорректен', [ 'status' => 403 ] );

		if ($token[0]->autorizeKey !== $request["token"]) 
			return new WP_Error( 'no_token_incorrect', 'Токен некорректен', [ 'status' => 403 ] );
		
		

		$dellRez = $serviceBase->delete( 'service_number', array("number" => $request["number"]) );
	
		if ($dellRez === false) 
			return new WP_Error( 'no_delete_number', 'При удалении номера возникли ошибки', [ 'status' => 403 ] );
		
		 
		return array("dell_count" => $dellRez);
	}	

// 
// Обновление информации по номеру
// 

add_action( 'rest_api_init', function () {
	register_rest_route( 'lscrm/v2', '/update_number', array(
		'methods'  => 'GET',
		'callback' => 'update_number',
		'args' => array(
			'number' => array(
				'default'           => "",
				'required'          => true, 
			)
			
		),
	) );
	});
	
	//https://propuska-mkad-ttk-sk.ru/wp-json/lscrm/v2/dell_number?number=Е268КР53&token=291327&mail=asmi046@gmail.com
	function update_number( WP_REST_Request $request) {
		
		$serviceBase = new wpdb(BI_SERVICE_USER_NAME, BI_SERVICE_USER_PASS, BI_SERVICE_DB_NAME, BI_SERVICE_DB_HOST);
		
		

		$aus_chec_rez = get_number_info($request["number"]);

		$statuses = get_status($aus_chec_rez->passes[count($aus_chec_rez->passes) - 1]);

		$addingArray["status"] =  $aus_chec_rez->passes[count($aus_chec_rez->passes) - 1]->status;
		$addingArray["seria"] =  $aus_chec_rez->passes[count($aus_chec_rez->passes) - 1]->series;
		$addingArray["type"] =  $aus_chec_rez->passes[count($aus_chec_rez->passes) - 1]->pass_zone;
		$addingArray["time"] =  empty($aus_chec_rez->passes[count($aus_chec_rez->passes) - 1]->type_pass)?"":$aus_chec_rez->passes[count($aus_chec_rez->passes) - 1]->type_pass;
		$addingArray["pass_number"] =  $aus_chec_rez->passes[count($aus_chec_rez->passes) - 1]->pass_number;
		$addingArray["chec_time"] =  date("Y-m-d H:i:s");
		$addingArray["start_data"] =  date("Y-m-d H:i:s", strtotime($aus_chec_rez->passes[count($aus_chec_rez->passes) - 1]->valid_from));
		$addingArray["end_data"] =  date("Y-m-d H:i:s", strtotime($aus_chec_rez->passes[count($aus_chec_rez->passes) - 1]->valid_to));
		$addingArray["anul_data"] =  date("Y-m-d H:i:s", strtotime($aus_chec_rez->passes[count($aus_chec_rez->passes) - 1]->cancel_date));
		$addingArray["dey_count"] =  $statuses["deycount"];
		$addingArray["sys_status"] =  $statuses["sys_status"];
		
		
		$addResult = $serviceBase->update('service_number', $addingArray, array("number" => $request["number"]));
		 
		return $addResult;
	}	

?>