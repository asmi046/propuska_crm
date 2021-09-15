<?

define("BI_SERVICE_DB_NAME", "manist1h_auscrm");
define("BI_SERVICE_USER_NAME", "manist1h_auscrm");
define("BI_SERVICE_USER_PASS", "N%034F0y");
define("BI_SERVICE_DB_HOST", "localhost");

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

add_action( 'rest_api_init', function () {
	register_rest_route( 'lscrm/v2', '/add_one_numbers', array(
		'methods'  => 'POST',
		'callback' => 'add_one_numbers',
		// 'args' => array(
		// 	'numbersfile' => array(
		// 		'default'           => null,
		// 		'required'          => true,        		
		// 	)
		// ),
	) );
});

// https://propuska-mkad-ttk-sk.ru/wp-json/lscrm/v2/add_one_numbers
function add_one_numbers( WP_REST_Request $request ){
	
	// $r = print_r( $_FILES["numbersfile"], true);

	return $request->get_file_params();
	// return array("send" => $r);
}


?>