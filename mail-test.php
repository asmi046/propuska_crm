<?php
require_once "wp-config.php";

define("COMPANY_NAME", "Пропуска на МКАД");
define("MAIL_RESEND", "info@propusk-mkad-ttk-sk.ru");

$headersMn = array(
	'From: '.COMPANY_NAME.' <'.MAIL_RESEND.'>',
	'content-type: text/html',
);

$serviceBase = new wpdb(BI_SERVICE_USER_NAME, BI_SERVICE_USER_PASS, BI_SERVICE_DB_NAME, BI_SERVICE_DB_HOST);


function checOutPropusk($number, $info, $email_tosendMn) {
	    global  $headersMn, $serviceBase;

	    $deycount = 60;
	
		add_filter('wp_mail_content_type',function( $content_type ) {return 'text/html';});
		
		$mailSabj = "До окончания пропуска на ".$number." осталось ".$deycount." дней";
		$mailContent = "Здравствуйте, пропуск на ".$number."  заканчивается 22.07.2023. Для повторного продления свяжитесь с нами по почте zakaz@propuska-mkad-ttk-sk.ru или по телефонам: <br/>+7 (499) 404-21-19 <br/>+7 (916) 006-52-77";
		$mailContent .= "<br/>";
		$mailContent .= "Серия и номер пропуска БА 345423 (Дневной)";
		$mailContent .= "<br/>";
		$mailContent .= "<br/>";
		$mailContent .= "Вы получили это письмо так как для вашего номера подключены уведомления, если вы хотите отказаться от уведомлений нажмите <a href = '#'>отписаться от уведомлений</a> но тогда в случае аннуляции Вашего пропуска, уведомление к вам не придет.";
		
		echo "Отправка: ".$mailSabj."\n\r";

		$rez = wp_mail($email_tosendMn, $mailSabj, $mailContent, $headersMn);

		return $rez;
	}

    $email_tosendMn = array("info@propusk247.ru", "asmi046@gmail.com", "om@imperial-garden.ru");

    echo checOutPropusk("Р952АМ750", null, $email_tosendMn);
