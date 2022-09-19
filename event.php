<?
// /usr/local/bin/php7.3 ~/propuska-mkad-ttk-sk.ru/public_html/bot/event.php
// php ../var/www/back2/event/event.php
// require __DIR__ . "/../vendor/autoload.php";
require_once __DIR__ . "/../wp-config.php";

define("COMPANY_NAME", "Пропуска на МКАД");
define("MAIL_RESEND", "zakaz@propuska-mkad-ttk-sk.ru");

$headersMn = array(
	'From: '.COMPANY_NAME.' <'.MAIL_RESEND.'>',
	'content-type: text/html',
);



$serviceBase = new wpdb(BI_SERVICE_USER_NAME, BI_SERVICE_USER_PASS, BI_SERVICE_DB_NAME, BI_SERVICE_DB_HOST);


function checkBaseEvent($event_name, $event_date, $number, $pass_number) {
	global $email_tosendMn, $headersMn, $serviceBase;
	
	$rez = $serviceBase->get_results("SELECT * FROM `events` WHERE `event_name` = '".$event_name."' AND `event_date` = '".$event_date."' AND `number` = '".$number."' AND `pass_number` = '".$pass_number."'");

	if (empty($rez)) {
		$serviceBase->insert('events', array ( 
			"event_name" => $event_name,
			"event_date" => $event_date,
			"number" => $number,
			"pass_number" => $pass_number
		));

		return false;
	}
	else return true;
}

function checRazoviPropusk($number, $info, $email_tosendMn) {
	
	global $headersMn, $serviceBase;

	if ($info->param->seria !== "ББ") return 0;
	$now = date("Y-m-d 00:00:00");
	$start = date("Y-m-d 00:00:00", strtotime($info->param->start_data));
	
	if (strtotime($now) !== strtotime($start)) return 0;

	if (!checkBaseEvent("Выпущен временный пропуск", date("Y-m-d"), $number, $info->param->pass_number)) {

		add_filter('wp_mail_content_type',function( $content_type ) {return 'text/html';});
		
		$mailSabj = "Вышел разовый пропуск для ".$number." до ".$info->param->end_data;
		$mailContent = "Здравствуйте, ".$number." - вышел разовый пропуск на ".$info->param->type." с ".date("Y-m-d", strtotime($info->param->start_data))." по ".date("Y-m-d", strtotime($info->param->end_data))." включительно.  Серия и номер пропуска ".$info->param->seria." ".$info->param->pass_number." (".$info->param->time.")";
		$mailContent .= "<br/>";
		$mailContent .= "<br/>";
		$mailContent .= "Вы получили это письмо так как для вашего номера подключены уведомления, если вы хотите отказаться от уведомлений нажмите <a href = '#'>отписаться от уведомлений</a> но тогда в случае аннуляции Вашего пропуска, уведомление к вам не придет.";
		wp_mail($email_tosendMn, $mailSabj, $mailContent, $headersMn);

		return 1;
	}
}

function checRazoviPropusk_end($number, $info, $email_tosendMn) {
	
	global $headersMn, $serviceBase;

	if ($info->param->seria !== "ББ") return 0;
	$now = date("Y-m-d 00:00:00");
	$start = date("Y-m-d 00:00:00", strtotime($info->param->end_data));
	
	if (strtotime($now) !== strtotime($start)) return 0;

	if (!checkBaseEvent("Выпущен временный пропуск заканчивается сегодня", date("Y-m-d"), $number, $info->param->pass_number)) {

		add_filter('wp_mail_content_type',function( $content_type ) {return 'text/html';});
		
		$mailSabj = "Временный пропуск на машину ".$number." заканчивается сегодня";
		$mailContent .= "Уважаемый клиент!<br/>";
		$mailContent = "Действие разового пропуска ".$info->param->seria." ".$info->param->pass_number." (".$info->param->time.") для автомобиля , ".$number." заканчивается сегодня.";
		$mailContent .= "<br/>";
		$mailContent .= "<br/>";
		$mailContent .= "Вы получили это письмо так как для вашего номера подключены уведомления, если вы хотите отказаться от уведомлений нажмите <a href = '#'>отписаться от уведомлений</a> но тогда в случае аннуляции Вашего пропуска, уведомление к вам не придет.";
		wp_mail($email_tosendMn, $mailSabj, $mailContent, $headersMn);

		return 1;
	}
}

function checPostPropusk($number, $info, $email_tosendMn) {
	
	global $headersMn, $serviceBase;

	if ($info->param->seria !== "БА") return 0;
	$now = date("Y-m-d 00:00:00");
	// $now = date("Y-m-d", strtotime("2021-09-15"));
	$start = date("Y-m-d 00:00:00", strtotime($info->param->start_data));
	
	if (strtotime($now) !== strtotime($start)) return 0;

	if (!checkBaseEvent("Выпущен постоянный пропуск", date("Y-m-d"), $number, $info->param->pass_number)) {

		add_filter('wp_mail_content_type',function( $content_type ) {return 'text/html';});
		
		$mailSabj = "Вышел постоянный пропуск для ".$number." до ".$info->param->end_data;
		$mailContent = "Здравствуйте, ".$number." - вышел постоянный пропуск на ".$info->param->type." с ".$info->param->start_data." по ".$info->param->end_data." включительно.  Серия и номер пропуска ".$info->param->seria." ".$info->param->pass_number." (".$info->param->time.")";
		$mailContent .= "<br/>";
		$mailContent .= "<br/>";
		$mailContent .= "Вы получили это письмо так как для вашего номера подключены уведомления, если вы хотите отказаться от уведомлений нажмите <a href = '#'>отписаться от уведомлений</a> но тогда в случае аннуляции Вашего пропуска, уведомление к вам не придет.";
		wp_mail($email_tosendMn, $mailSabj, $mailContent, $headersMn);

		return 1;
	}
}


function checOutPropusk($number, $info, $email_tosendMn) {
	
	global  $headersMn, $serviceBase;

	if ($info->param->seria !== "БА") return 0;
	$now = date("Y-m-d 00:00:00");
	$end = date("Y-m-d 00:00:00", strtotime($info->param->end_data));
	
	$deycount = floor((strtotime($end) - strtotime($now) ) / (60 * 60 * 24));
	

	// if (($deycount != 27)&&($deycount != 28)&&($deycount != 29)&&($deycount != 30)) return 0;
	if ($deycount != 30) return 0;

	
	if (!checkBaseEvent("До окончания пропуска осталось 30 дней", date("Y-m-d"), $number, $info->param->pass_number)) {

		add_filter('wp_mail_content_type',function( $content_type ) {return 'text/html';});
		
		$mailSabj = "До окончания пропуска на ".$number." осталось 30 дней";
		$mailContent = "Здравствуйте, пропуск на ".$number."  заканчивается ".$info->param->end_data.". Для повторного продления свяжитесь с нами по почте zakaz@propuska-mkad-ttk-sk.ru или по телефонам: <br/>+7 (499) 404-21-19 <br/>+7 (916) 006-52-77";
		$mailContent .= "<br/>";
		$mailContent .= "Серия и номер пропуска ".$info->param->seria." ".$info->param->pass_number." (".$info->param->time.")";
		$mailContent .= "<br/>";
		$mailContent .= "<br/>";
		$mailContent .= "Вы получили это письмо так как для вашего номера подключены уведомления, если вы хотите отказаться от уведомлений нажмите <a href = '#'>отписаться от уведомлений</a> но тогда в случае аннуляции Вашего пропуска, уведомление к вам не придет.";
		wp_mail($email_tosendMn, $mailSabj, $mailContent, $headersMn);

		return 1;
	}
}

function checAnul($number, $info, $email_tosendMn) {
	
	global  $headersMn, $serviceBase;

	if (!isset($info->param->anul_data)) return 0;
	
	$now = date("Y-m-d");
	// $now = date("Y-m-d", strtotime("2022-09-15"));
	$start = date("Y-m-d", strtotime($info->param->anul_data));
	
	if (strtotime($now) !== strtotime($start)) return 0;

	if (!checkBaseEvent("Аннулирован пропуск", date("Y-m-d"), $number, $info->param->pass_number)) {

		add_filter('wp_mail_content_type',function( $content_type ) {return 'text/html';});
		
		$mailSabj = "Пропуск ".$number." аннулирован";
		$mailContent = "Здравствуйте, пропуск ".$number." - Аннулирован ".$info->param->anul_data." Рекомендуем ограничить поездки по МКАД и внутри Москвы для избежания штрафов.";
		$mailContent .= "<br/>";
		$mailContent .= "Серия и номер пропуска ".$info->param->seria." ".$info->param->pass_number." (".$info->param->time.")";
		$mailContent .= "<br/>";
		$mailContent .= "<br/>";
		$mailContent .= "Вы получили это письмо так как для вашего номера подключены уведомления, если вы хотите отказаться от уведомлений нажмите <a href = '#'>отписаться от уведомлений</a> но тогда в случае аннуляции Вашего пропуска, уведомление к вам не придет.";
		wp_mail($email_tosendMn, $mailSabj, $mailContent, $headersMn);

		return 1;
	}
}

function update_number_info_ev($number, $type) {
	$url = "https://back2.propuska-mkad-ttk-sk.ru/wp-json/lscrm/v2/update_number?number=".urlencode($number); 
	
	if (!empty($type))
		$url = $url."&passtype=".$type;

	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
	$str = curl_exec($curl);
	curl_close($curl);

    return json_decode($str);
}


$chec_id = "CH_".rand(10000, 90000);

$serviceBase->insert('cheking_log', array('action' => "Начата проверка", "chec_id" => $chec_id));

$numbers =  $serviceBase->get_results("SELECT * FROM `service_number`");

$index = 0;

$RazoviPropuskCount = 0;
$RazoviPropuskCount_en = 0;
$PostoyanniPropuskCount = 0;
$OutPropuskCount = 0;
$anulPropuskCount = 0;

foreach ($numbers as $elem) {
	
	// if ($elem->number !== "М155ОВ67") continue;

	$info = update_number_info_ev($elem->number, "");
	
   if (empty($info->param->dey_count)) continue;
   echo "# ".$index."\n\r";
   echo "№ авто: ".$elem->number;
   echo "\n\r";
   print_r($info);
   echo "\n\r";
   
   $email_tosendMn = array("info@propusk247.ru", "asmi046@gmail.com", $elem->email);

	if (!empty($elem->email_dop)) {
		$dop_number = explode(",", $elem->email_dop);

		for ($i =0; $i<count($dop_number); $i++){
			$email_tosendMn[] = trim($dop_number[$i]); 
		}
	}


	$RazoviPropuskCount += checRazoviPropusk($elem->number, $info, $email_tosendMn);
	$RazoviPropuskCount_en += checRazoviPropusk_end($elem->number, $info, $email_tosendMn);
   $PostoyanniPropuskCount += checPostPropusk($elem->number, $info, $email_tosendMn);
   $OutPropuskCount += checOutPropusk($elem->number, $info, $email_tosendMn);
   $anulPropuskCount += checAnul($elem->number, $info, $email_tosendMn);
   $index++;

   if (($info->param->time == "Дневной")&&($info->param->exist_night == "Да")) {
		$info = update_number_info_ev($elem->number, "Ночной");
		
		echo "# ---- Ночной\n\r";
		echo "№ авто: ".$elem->number;
		echo "\n\r";
		print_r($info);
		echo "\n\r";
	 
		$RazoviPropuskCount += checRazoviPropusk($elem->number, $info, $email_tosendMn);
		$RazoviPropuskCount_en += checRazoviPropusk_end($elem->number, $info, $email_tosendMn);
		$PostoyanniPropuskCount += checPostPropusk($elem->number, $info, $email_tosendMn);
		$OutPropuskCount += checOutPropusk($elem->number, $info, $email_tosendMn);
		$anulPropuskCount += checAnul($elem->number, $info, $email_tosendMn);
		$index++;

   }

   if (($info->param->time == "Ночной")&&($info->param->exist_dey == "Да")) {
		$info = update_number_info_ev($elem->number, "Дневной");

		echo "# ---- Дневной\n\r";
		echo "№ авто: ".$elem->number;
		echo "\n\r";
		print_r($info);
		echo "\n\r";
	 
		$RazoviPropuskCount += checRazoviPropusk($elem->number, $info, $email_tosendMn);
		$RazoviPropuskCount_en += checRazoviPropusk_end($elem->number, $info, $email_tosendMn);
		$PostoyanniPropuskCount += checPostPropusk($elem->number, $info, $email_tosendMn);
		$OutPropuskCount += checOutPropusk($elem->number, $info, $email_tosendMn);
		$anulPropuskCount += checAnul($elem->number, $info, $email_tosendMn);
		$index++;
   }
   
}

$serviceBase->insert('cheking_log', array(
	'action' => "Окончена проверка", 
	"chec_id" => $chec_id, 
	"razovie" => $RazoviPropuskCount,
	"postoyannie" => $PostoyanniPropuskCount,
	"out30" => $OutPropuskCount,
	"anul" => $anulPropuskCount,
));

?>