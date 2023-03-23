<?

// header('Access-Control-Allow-Origin: *');
// header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');
// header('Access-Control-Allow-Methods: POST, GET, OPTIONS, DELETE, PUT');
// header('Access-Control-Max-Age: 600');

function get_number_info($number) {

	$url = BI_SERVICE_URL."?apikey=".BI_SERVICE_TOKEN."&truck_num=".urlencode($number);

	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	// curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
	// curl_setopt($curl, CURLOPT_SSLVERSION, 3);
	// curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
	// curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
	$str = curl_exec($curl);

	if($str === false)
	{
   	 	echo 'Ошибка curl: ' . curl_error($curl);
		return false;
	}

	curl_close($curl);

	return json_decode($str);
	

	// return json_decode(file_get_contents($url."?apikey=".$token."&truck_num=".urlencode($number)));
}


function get_number_info_new($number) {

	$url = BI_SERVICE_URL_NEW."?key=".BI_SERVICE_TOKEN_NEW."&plate=".urlencode($number);

	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
	$str = curl_exec($curl);

	if($str === false)
	{
   	 	echo 'Ошибка curl: ' . curl_error($curl);
		return false;
	}

	curl_close($curl);

	return json_decode($str);
	

	// return json_decode(file_get_contents($url."?apikey=".$token."&truck_num=".urlencode($number)));
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
// Обновление информации о номере
// 

add_action( 'rest_api_init', function () {
	register_rest_route( 'lscrm/v2', '/update_number_info', array(
		'methods'  => 'GET',
		'callback' => 'update_number_info',
		'args' => array(
			'id' => array(
				'default'           => "",
				'required'          => true,        		
			),

			'number' => array(
				'default'           => "",
				'required'          => true,        		
			),

			'mail' => array(
				'default'           => "",
				'required'          => true,        		
			),
			
			'email_dop' => array(
				'default'           => "",        		
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
function update_number_info( WP_REST_Request $request ){

	$serviceBase = new wpdb(BI_SERVICE_USER_NAME, BI_SERVICE_USER_PASS, BI_SERVICE_DB_NAME, BI_SERVICE_DB_HOST);

	$addResult = $serviceBase->update('service_number', array(
		"number" => $request["number"],
		"email" => $request["email"],
		"email_dop" => $request["email_dop"],
		"phone" => $request["phone"],
		"sts" => $request["sts"]
	),

	array("id" => $request["id"])
);
	
	if (empty($addResult))
		return new WP_Error( 'no_update_number', 'При обновлении информации о номере возникла ошибка', [ 'status' => 403 ] );
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
			'element' => array(
				'default'           => "0",
				'required'          => true,            		
			),
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
	$deycount = (($deycount+1)<0)?0:($deycount+1);
	

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
	
	$serviceBase = new wpdb(BI_SERVICE_USER_NAME, BI_SERVICE_USER_PASS, BI_SERVICE_DB_NAME, BI_SERVICE_DB_HOST);
	
	$addetCount = 0;

	$numbers = [];

	foreach ($request["element"] as $elem) {
	
	if (!isset($elem[0])) continue;
	$mail = "";
	if (isset($elem[1])) $mail = $elem[0];


	 $addingArray = array(
	 		"number" => $elem[0],
	 		"email" => $mail
	 );

	
	$addResult = $serviceBase->insert('service_number', $addingArray);

	if (!empty($addResult)) $addetCount++;
	 $numbers[] = array(
	 	"number" => $elem[0],
	 	"email" => $mail,
	 	"result" => $addResult
	);

	
	}

	return array("result" => $numbers, "count" => $addetCount, "inn_data" => $request["element"]);

	
}

// 
// Проверка номера при массовом добавлении
// 

add_action( 'rest_api_init', function () {
	register_rest_route( 'lscrm/v2', '/ch_number_after_add', array(
		'methods'  => 'GET',
		'callback' => 'ch_number_after_add',
		'args' => array(
			'number' => array(
				'default'           => "0",
				'required'          => true,            		
			)
		),
	) );
});

// https://propuska-mkad-ttk-sk.ru/wp-json/lscrm/v2/ch_number_after_add?number=В228ТМ26
function ch_number_after_add( WP_REST_Request $request ){
	
	$serviceBase = new wpdb(BI_SERVICE_USER_NAME, BI_SERVICE_USER_PASS, BI_SERVICE_DB_NAME, BI_SERVICE_DB_HOST);
	$statuses = array();

	$addingArray = array();
		
	$aus_chec_rez = get_number_info($request["number"]);

	if (count($aus_chec_rez->passes) !== 0) 
	{	

	$statuses = get_status($aus_chec_rez->passes[count($aus_chec_rez->passes) - 1]);

	$addingArray["status"] =  $aus_chec_rez->passes[count($aus_chec_rez->passes) - 1]->status;
	$addingArray["seria"] =  $aus_chec_rez->passes[count($aus_chec_rez->passes) - 1]->series;
	$addingArray["type"] =  $aus_chec_rez->passes[count($aus_chec_rez->passes) - 1]->pass_zone;
	$addingArray["time"] =  empty($aus_chec_rez->passes[count($aus_chec_rez->passes) - 1]->type_pass)?"":$aus_chec_rez->passes[count($aus_chec_rez->passes) - 1]->type_pass;
	$addingArray["pass_number"] =  $aus_chec_rez->passes[count($aus_chec_rez->passes) - 1]->pass_number;
	$addingArray["chec_time"] =  date("Y-m-d H:i:s");
	$addingArray["start_data"] =  date("Y-m-d H:i:s", strtotime($aus_chec_rez->passes[count($aus_chec_rez->passes) - 1]->valid_from));
	$addingArray["end_data"] =  date("Y-m-d H:i:s", strtotime($aus_chec_rez->passes[count($aus_chec_rez->passes) - 1]->valid_to));
	
	if (!empty($aus_chec_rez->passes[count($aus_chec_rez->passes) - 1]->cancel_date))
		$addingArray["anul_data"] =  date("Y-m-d H:i:s", strtotime($aus_chec_rez->passes[count($aus_chec_rez->passes) - 1]->cancel_date));
	
	$addingArray["dey_count"] =  $statuses["deycount"];
	$addingArray["sys_status"] =  $statuses["sys_status"];
	} else {
		$addingArray["dey_count"] =  0;
		$addingArray["sys_status"] =  "Не найден";
	}		
		

	$addResult = $serviceBase->update('service_number', $addingArray, array("number" => $request["number"]));

	return array("result" => $addingArray, "nb" => $request["number"]);
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
	
	//https://back2.propuska-mkad-official.ru/wp-json/lscrm/v2/number_info?number=Х983ХК750
	function number_info( WP_REST_Request $request) {

		$info = get_number_info($request["number"]);

		if (empty($info)) return [];
		
		$rez = array_reverse($info->passes);
		
		foreach ($rez as $element) {
			$statuses = get_status($element);
			$element->sys_status = $statuses["sys_status"];
			$element->deycount = $statuses["deycount"];
			$element->mms = "ZZZZ";
		}

		return $rez; 
	}

// 
// Проверка номера через сервис new
// 

add_action( 'rest_api_init', function () {
	register_rest_route( 'lscrm/v2', '/number_info_new', array(
		'methods'  => 'GET',
		'callback' => 'number_info_new',
		'args' => array(
			'number' => array(
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


function cmp_function($a, $b){
	return (strtotime($a['valid_to']) < strtotime($b['valid_to']));
}
 
function reCAP($token) {
	$sendData = array(
		'response' => $token,
		'secret' => '6LdXHxAkAAAAAJvZCM_S1q9suO-rtsaEWER7OxUM'
	);
	
	$oCurl = curl_init();
	curl_setopt($oCurl, CURLOPT_URL, "https://www.google.com/recaptcha/api/siteverify");
	curl_setopt($oCurl, CURLOPT_POST, true);
	curl_setopt($oCurl, CURLOPT_HEADER, false);
	curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($oCurl, CURLOPT_POSTFIELDS, $sendData);
	$result = curl_exec($oCurl);
	curl_close($oCurl);

	return json_decode($result);
}

//https://back2.propuska-mkad-official.ru/wp-json/lscrm/v2/number_info_new?number=Х983ХК750
function number_info_new( WP_REST_Request $request) {

	$result = reCAP($request["token"]);

	if ((!$result->success)||($result->score < 0.5)) wp_die("GO TO->", 403);

	$number = strtr($request["number"], [
		'A' => 'А', 
		'B' => 'В', 
		'E' => 'Е', 
		'K' => 'К', 
		'M' => 'М', 
		'H' => 'Н', 
		'O' => 'О', 
		'P' => 'Р', 
		'C' => 'С', 
		'T' => 'Т', 
		'Y' => 'У', 
		'X' => 'Х',

		'a' => 'А', 
		'b' => 'В', 
		'e' => 'Е', 
		'k' => 'К', 
		'm' => 'М', 
		'h' => 'Н', 
		'o' => 'О', 
		'p' => 'Р', 
		'c' => 'С', 
		't' => 'Т', 
		'y' => 'У', 
		'x' => 'Х',
	]);

	// M015KA13
	// М015КА13

	$info = get_number_info_new($number);

	if (empty($info->passes))
		$info = get_number_info_new($number);

	if (empty($info->passes))

	if (empty($info)) return [];
		if ((!$result->success)||($result->score < 0.5)) wp_die("Ошибка CH попробуйте снова", 403);

	// $rez = array_reverse($info->passes);
	$rez = $info->passes;

	// uksort($rez, 'cmp_function');
	
	foreach ($rez as $element) {
		$statuses = get_status($element);
		$element->sys_status = $statuses["sys_status"];
		$element->deycount = $statuses["deycount"];
		$element->mms = "ZZZZ";
	}

	return $rez; 
}

// 
// Проверка номера через сервис ЗАГРАННОМЕРА
// 

add_action( 'rest_api_init', function () {
	register_rest_route( 'lscrm/v2', '/number_info_zag', array(
		'methods'  => 'GET',
		'callback' => 'number_info_zag',
		'args' => array(
			'number' => array(
				'default'           => "",
				'required'          => true,        		
			)
		),
	) );
});

//
// Все варианты загран номеров 
//

function get_all_number_variant($number, &$all_var) {
	$i=0;
	do { 
		if ((ctype_digit($number[$i]) && ctype_alpha($number[$i+1]))||(ctype_alpha($number[$i]) && ctype_digit($number[$i+1])) ) {
			$ns = str_replace($number[$i].$number[$i+1],$number[$i]." ".$number[$i+1],$number);
			$all_var[] = $ns;
			get_all_number_variant($ns, $all_var);
		}
		
		$i++;
	} while ($i<strlen($number)-2);
	
	return $all_var;
}

//https://back2.propuska-mkad-official.ru/wp-json/lscrm/v2/number_info_zag?number=Х983ХК750
function number_info_zag( WP_REST_Request $request) {

	$all_var = [];
	$all_var[] = $request["number"];
	$rez_var = get_all_number_variant($request["number"], $all_var);

	$passes=[];
	foreach ($rez_var as $number_elem)
	{
		$info = get_number_info($number_elem);
		$passes = array_merge($passes, $info->passes);
	}

	if (empty($passes)) return [];
	

	$rez = array_reverse($passes);
	
	foreach ($rez as $element) {
		$statuses = get_status($element);
		$element->sys_status = $statuses["sys_status"];
		$element->deycount = $statuses["deycount"];
		$element->mms = "ZZZZ";
	}

	return $rez; 
}
	
// 
// Поиск номера в базе
// 

add_action( 'rest_api_init', function () {
	register_rest_route( 'lscrm/v2', '/search_number_in_base', array(
		'methods'  => 'GET',
		'callback' => 'search_number_in_base',
		'args' => array(
			'number' => array(
				'required'          => true, 
			)
			
		),
	) );
	});
	
	//https://back2.propuska-mkad-official.ru/wp-json/lscrm/v2/search_number_in_base?number=1
	function search_number_in_base( WP_REST_Request $request) {
		$serviceBase = new wpdb(BI_SERVICE_USER_NAME, BI_SERVICE_USER_PASS, BI_SERVICE_DB_NAME, BI_SERVICE_DB_HOST);

		$q = "SELECT * FROM `service_number` WHERE `number` = '".$request["number"]."'";
		
		$result = $serviceBase->get_results($q);
		
		if (!empty($result)) $result = $result[0];

		return $result;
	}	


// 
// Добавление должника
// 

add_action( 'rest_api_init', function () {
	register_rest_route( 'lscrm/v2', '/add_dolgnik', array(
		'methods'  => 'GET',
		'callback' => 'add_dolgnik',
		'args' => array(
			'number' => array(
				'required'          => true, 
			),

			'email' => array(
				'required'          => true, 
			),

			'name' => array(
				'default'           => "", 
			)
			
		),
	) );
	});
	
	//https://back2.propuska-mkad-official.ru/wp-json/lscrm/v2/add_dolgnik?number=1
	function add_dolgnik( WP_REST_Request $request) {
		$serviceBase = new wpdb(BI_SERVICE_USER_NAME, BI_SERVICE_USER_PASS, BI_SERVICE_DB_NAME, BI_SERVICE_DB_HOST);

		$info = get_number_info($request["number"]);
		if (empty($info))
			return new WP_Error( 'no_dolg_insert', 'Не удалось определить дату выпуска пропуска', [ 'status' => 403 ] );
		$rez = array_reverse($info->passes);

		$add_result = $serviceBase->insert( "dolg", [
			"number" => $request["number"],
			"email" => $request["email"],
			"adding_data" => (string)date('Y-m-d H:i:s', strtotime($rez[0]->valid_from)),
			"name" => $request["name"],
		], ["%s", "%s", "%s"] );

		if (!$add_result)
			return new WP_Error( 'no_dolg_insert', 'При добавлении возникла ошибка попробуйте еще раз.', [ 'status' => 403 ] );

		return $add_result;
	}	


// 
// Список всех должников
// 

add_action( 'rest_api_init', function () {
	register_rest_route( 'lscrm/v2', '/get_all_dolgnik', array(
		'methods'  => 'GET',
		'callback' => 'get_all_dolgnik',
		'args' => array(
			'search' => array(
				'default'           => "", 
			),			
		),
	) );
	});
	
	//https://back2.propuska-mkad-official.ru/wp-json/lscrm/v2/get_all_dolgnik?number=1
	function get_all_dolgnik( WP_REST_Request $request) {
		$serviceBase = new wpdb(BI_SERVICE_USER_NAME, BI_SERVICE_USER_PASS, BI_SERVICE_DB_NAME, BI_SERVICE_DB_HOST);

		$q = "SELECT * FROM `dolg`";
		$array_all = $serviceBase->get_results($q);

		return $array_all;
	}	


// 
// Удаление должника
// 

add_action( 'rest_api_init', function () {
	register_rest_route( 'lscrm/v2', '/dell_dolgnik', array(
		'methods'  => 'GET',
		'callback' => 'dell_dolgnik',
		'args' => array(
			'id' => array(
				'required'          => true, 
			),

			'mail' => array(
				'required'          => true, 
			),

			'token' => array(
				'required'          => true, 
			)
		),
	) );
	});
	
	//https://propuska-mkad-ttk-sk.ru/wp-json/lscrm/v2/dell_dolgnik?id=
	function dell_dolgnik( WP_REST_Request $request) {
		
		$serviceBase = new wpdb(BI_SERVICE_USER_NAME, BI_SERVICE_USER_PASS, BI_SERVICE_DB_NAME, BI_SERVICE_DB_HOST);
		
		$token = $serviceBase->get_results("SELECT `autorizeKey` FROM `service_users` WHERE `mail` = '".$request["mail"]."'");
	

		if (empty($token))
			return new WP_Error( 'no_token_incorrect', 'Токен некорректен', [ 'status' => 403 ] );

		if ($token[0]->autorizeKey !== $request["token"]) 
			return new WP_Error( 'no_token_incorrect', 'Токен некорректен', [ 'status' => 403 ] );
		
		

		$dellRez = $serviceBase->delete( 'dolg', array("id" => $request["id"]) );
	
		if ($dellRez === false) 
			return new WP_Error( 'no_delete_dolg', 'При удалении должника возникли ошибки', [ 'status' => 403 ] );
		
		 
		return array("dell_count" => $dellRez);
	}	


// 
// Массовое добавление должников
// 

add_action( 'rest_api_init', function () {
	register_rest_route( 'lscrm/v2', '/mass_add_dolgnik', array(
		'methods'  => 'GET',
		'callback' => 'mass_add_dolgnik',
		'args' => array(
			'list' => array(
				'required' => true, 
			),
			
		),
	) );
	});
	
	//https://back2.propuska-mkad-official.ru/wp-json/lscrm/v2/mass_add_dolgnik?number=1
	function mass_add_dolgnik( WP_REST_Request $request) {
		$serviceBase = new wpdb(BI_SERVICE_USER_NAME, BI_SERVICE_USER_PASS, BI_SERVICE_DB_NAME, BI_SERVICE_DB_HOST);

		$number_list = explode("\n", $request["list"]);

		$addin_rez_array = [];
		
		foreach ($number_list as $nm) {
			$searchin_nm = str_replace(" ", '',trim($nm));
			
			if (empty($searchin_nm)) continue;
			$q = "SELECT * FROM `service_number` WHERE `number` = '".$searchin_nm."'";
			$array_info = $serviceBase->get_results($q);
		
			if (empty($array_info)) {
				
				$addin_rez_array[] = [
					"number" => $searchin_nm,
					"adding"=> 0,
					"email" => "", 
					"msg" => "Не найден в базе"
				];

				continue;
			}

			$info = get_number_info($searchin_nm);
				if (empty($info))
					return new WP_Error( 'no_dolg_insert', 'Не удалось определить дату выпуска пропуска', [ 'status' => 403 ] );
				$rez = array_reverse($info->passes);
				

			$add_result = $serviceBase->insert( "dolg", [
				"number" => $searchin_nm,
				"email" => $array_info[0]->email,
				"adding_data" => (string)date('Y-m-d H:i:s', strtotime($rez[0]->valid_from))
			], ["%s", "%s"] );

			if (!$add_result) {
				$addin_rez_array[] = [
					"number" => $searchin_nm,
					"adding"=> 0,
					"email" => $array_info[0]->email,
					"msg" => "Ошибка при добавлении"
				];
			} else {
				$addin_rez_array[] = [
					"number" => $searchin_nm,
					"adding"=> 1,
					"email" => $array_info[0]->email,
					"msg" => "Номер добавлен"
				];
			}
		}

		return $addin_rez_array;

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

		$q = "SELECT * FROM `service_number` WHERE `sys_status` LIKE '".$status."' AND `seria` LIKE '".$type."' AND `pass_number` LIKE '".$searchstr."' AND `number` LIKE '".$searchstr."'  AND `email` LIKE '".$searchstr."' ORDER BY `start_data` DESC";
		
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
			),
			'passtype' => array(
				'default'           => "", 
			),
			
		),
	) );
	});
	
	function counting_dey_night($pases) {
		$count_activ = 0;
		$night = "Нет";
		$dey = "Нет";
		
		foreach ($pases->passes as $elem) {
			$statuses = get_status($elem);
			$elem->sys_status = $statuses["sys_status"];
			$elem->deycount = $statuses["deycount"];

			
			if ($elem->sys_status == "Действует") $count_activ++;
			if (($elem->type_pass == "Дневной") && ($elem->sys_status == "Действует")) $dey = "Да";
			if (($elem->type_pass == "Ночной") && ($elem->sys_status == "Действует")) $night = "Да";
		}

		return array(
			"active_count" => $count_activ,
			"exist_dey" => $dey,
			"exist_night" => $night,
		);
	}

	function index_pass($pases, $type) {
		$index = 0;
		foreach ($pases->passes as $elem) { 
			$statuses = get_status($elem);
			$elem->sys_status = $statuses["sys_status"];

			if (($elem->type_pass == $type) && ($elem->sys_status == "Действует")) return $index;
			
			$index++;
		}

		return $index;
	}

	//https://propuska-mkad-ttk-sk.ru/wp-json/lscrm/v2/dell_number?number=Е268КР53&token=291327&mail=asmi046@gmail.com
	function update_number( WP_REST_Request $request) {
		
		$serviceBase = new wpdb(BI_SERVICE_USER_NAME, BI_SERVICE_USER_PASS, BI_SERVICE_DB_NAME, BI_SERVICE_DB_HOST);
		
		$addResult = [];

		$aus_chec_rez = get_number_info($request["number"]);
		
		if (empty($request["passtype"]))
			$getIndex = count($aus_chec_rez->passes) - 1;
		else 
			$getIndex = index_pass($aus_chec_rez, $request["passtype"]);

		if (count($aus_chec_rez->passes) !== 0) 
		{
			$statuses = get_status($aus_chec_rez->passes[$getIndex]);

			$addingArray["status"] =  $aus_chec_rez->passes[$getIndex]->status;
			$addingArray["seria"] =  $aus_chec_rez->passes[$getIndex]->series;
			$addingArray["type"] =  $aus_chec_rez->passes[$getIndex]->pass_zone;
			$addingArray["time"] =  empty($aus_chec_rez->passes[$getIndex]->type_pass)?"":$aus_chec_rez->passes[$getIndex]->type_pass;
			$addingArray["pass_number"] =  $aus_chec_rez->passes[$getIndex]->pass_number;
			$addingArray["chec_time"] =  date("Y-m-d H:i:s");
			$addingArray["start_data"] =  date("Y-m-d H:i:s", strtotime($aus_chec_rez->passes[$getIndex]->valid_from));
			$addingArray["end_data"] =  date("Y-m-d H:i:s", strtotime($aus_chec_rez->passes[$getIndex]->valid_to));
			
			if (!empty($aus_chec_rez->passes[$getIndex]->cancel_date))
				$addingArray["anul_data"] =  date("Y-m-d H:i:s", strtotime($aus_chec_rez->passes[$getIndex]->cancel_date));
			
			$addingArray["dey_count"] =  $statuses["deycount"];
			$addingArray["sys_status"] =  $statuses["sys_status"];

			$counting = counting_dey_night($aus_chec_rez);

			$addingArray["active_count"] =  $counting["active_count"];
			$addingArray["exist_dey"] =  $counting["exist_dey"];
			$addingArray["exist_night"] =  $counting["exist_night"];

		} else {
				$addingArray["dey_count"] =  0;
				$addingArray["sys_status"] =  "Не найден";
		}		
			
			
			$addResult = $serviceBase->update('service_number', $addingArray, array("number" => $request["number"]));
		

		return array("param" => $addingArray, "result" => $addResult);
	}	

	add_action( 'rest_api_init', function () {
		register_rest_route( 'lscrm/v2', '/mass_alert', array(
			'methods'  => 'GET',
			'callback' => 'mass_alert',
			'args' => array(
				'number' => array(
					'default'           => "",
					'required'          => true, 
				),
		
			),
		) );
		});
		
		//https://propuska-mkad-ttk-sk.ru/wp-json/lscrm/v2/mass_alert?number=Е268КР53&token=291327&mail=asmi046@gmail.com
		function mass_alert( WP_REST_Request $request) {
			
			$serviceBase = new wpdb(BI_SERVICE_USER_NAME, BI_SERVICE_USER_PASS, BI_SERVICE_DB_NAME, BI_SERVICE_DB_HOST);
			
			$pn = mb_substr($request["number"], 2);

			$numberinfo = $serviceBase->get_results("SELECT * FROM `service_number` WHERE `pass_number` = '".$pn."'");
		
			$headersMnT = array(
				'From: Сайт Пропуска на МКАД <noreply@propuska-mkad-ttk-sk.ru>',
				'content-type: text/html',
			);

			add_filter('wp_mail_content_type',function( $content_type ) {return 'text/html';});

			if (empty($numberinfo))
				return array(
					"pass_number" => $request["number"],
					"pass_number_m" => $pn,
					"carr_number" => "",
					"email" => "",
					"rt" => false,
					"result" => "Такой номер пропуска не найден в базе",
				);
			
			if (empty($numberinfo[0]->email)) {
				
				
				$mailSabj = "Для автомобиля ноер ".$numberinfo[0]->number." нет e-mail";
				$mailContent = "<p>Внимание! Добавьте e-mail для автомобиля с номером ".$numberinfo[0]->number."</p>";
				
				wp_mail("asmi046@gmail.com, info@propusk247.ru", $mailSabj, $mailContent, $headersMnT);			


				return array(
					"pass_number" => $request["number"],
					"pass_number_m" => $pn,
					"carr_number" => $numberinfo[0]->number,
					"email" => $numberinfo[0]->email,
					"rt" => false,
					"result" => "Нет e-mail в базе",
				);
			}
	



			
				$mailSabj = "Пропуск на автомобиль ".$numberinfo[0]->number." будет аннулирован завтра";
				$mailContent = "<p>Внимание! Завтра будет аннулирован пропуск на автомобиль с номером ".$numberinfo[0]->number.". Серия и номер пропуска ".$numberinfo[0]->seria." ".$numberinfo[0]->pass_number." (".$numberinfo[0]->time."). Просьба завтра ограничить поездки по Москве. </p>";
				$mailContent .= "<p>Пропуск будет восстановлен. Также на время изготовления могут быть предоставлены два временных пропуска по 5 дней каждый.</p>";
				$mailContent .= "<p>Свяжитесь с нами, если необходимо восстановление годовых, и оформление разовых пропусков.</p>";
				$mailContent .= "Наша почта: zakaz@propuska-mkad-ttk-sk.ru <br/> Наши телефоны: <br/>+7 (499) 404-21-19 <br/>+7 (916) 006-52-77";
				$mailContent .= "<br/>";
				$mailContent .= "<br/>";
				$mailContent .= "Вы получили это письмо так как для вашего номера подключены уведомления, если вы хотите отказаться от уведомлений нажмите <a href = '#'>отписаться от уведомлений</a> но тогда в случае аннуляции Вашего пропуска, уведомление к вам не придет.";
				
				wp_mail($numberinfo[0]->email.", asmi046@gmail.com, info@propusk247.ru", $mailSabj, $mailContent, $headersMnT);			
				
			return array(
				"pass_number" => $request["number"],
				"pass_number_m" => $pn,
				"carr_number" => $numberinfo[0]->number,
				"email" => $numberinfo[0]->email,
				"rt" => true,
				"result" => "Оповещение отправлено",
			);
		}	

	add_action( 'rest_api_init', function () {
		register_rest_route( 'lscrm/v2', '/mass_check', array(
			'methods'  => 'GET',
			'callback' => 'mass_check',
			'args' => array(
				'number' => array(
					'default'           => "",
					'required'          => true, 
				),
		
			),
		) );
		});
		
		//https://propuska-mkad-ttk-sk.ru/wp-json/lscrm/v2/mass_check?number=Е268КР53
		function mass_check( WP_REST_Request $request) {
			
			$serviceBase = new wpdb(BI_SERVICE_USER_NAME, BI_SERVICE_USER_PASS, BI_SERVICE_DB_NAME, BI_SERVICE_DB_HOST);
			

			$numberInfo = get_number_info($request["number"]);

			// if (empty($numberInfo->passes))
			// 	$numberInfo = get_number_info_new($request["number"]);

			$numberInfo = $numberInfo->passes;

			$rezArray = [];

			for ($i = 0; $i<count($numberInfo); $i++) {
				if (!empty($numberInfo[$i]->record_updated_on) && ($numberInfo[$i]->series === "БА")){
					$dataUp = date("Y-m-d",strtotime($numberInfo[$i]->record_updated_on));
					$nData = date("Y-m-d");
					// $nData = date("Y-m-d", strtotime("2019-12-12T00:49:41"));
	

					if ($dataUp == $nData) {
						$rezArray = array(
							"number" => $request["number"],
							"status" =>  "Выдан сегодня",
							"data" =>  $numberInfo[$i]
						);
						break;	
					}
				} 
				
			}

			if (empty($rezArray))
				$rezArray = array(
					"number" => $request["number"],
					"status" =>  "Выдан другой датой",
					"data" =>  array()
				);

			return $rezArray;
		}
		
		
// 
// Проверка номера снаружи
// 

add_action( 'rest_api_init', function () {
	register_rest_route( 'lscrm/v2', '/get_number_info_out', array(
		'methods'  => 'GET',
		'callback' => 'get_number_info_out',
		'args' => array(
			'number' => array(
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
	
	//https://back2.propuska-mkad-official.ru/wp-json/lscrm/v2/get_number_info_out?number=Х983ХК750
	function get_number_info_out( WP_REST_Request $request) {

		$serviceBase = new wpdb(BI_SERVICE_USER_NAME, BI_SERVICE_USER_PASS, BI_SERVICE_DB_NAME, BI_SERVICE_DB_HOST);

		$user = $serviceBase->get_results("SELECT * FROM `api_users` WHERE `pass` = '".$request["token"]."'");

		if (empty($user))
			return new WP_Error( 'no_token_incorrect', 'Токен некорректен', [ 'status' => 403 ] );
		
		$info = get_number_info($request["number"]);

		if (empty($info)) return [];

		$serviceBase->insert( "api_log", [
			"car_number" => $request["number"],
			"user_id" => $user[0]->id,
			"user_name" => $user[0]->name
		], ["%s", "%s", "%s"] );
		
		$rez = array_reverse($info->passes);
		
		foreach ($rez as $element) {
			$statuses = get_status($element);
			$element->sys_status = $statuses["sys_status"];
			$element->deycount = $statuses["deycount"];
			$element->mms = "ZZZZ";
		}

		return $rez; 
	}

?>