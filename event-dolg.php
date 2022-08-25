<?php
// php ../var/www/back2/event/event-dolg.php

require_once __DIR__ . "/../wp-config.php";

define("COMPANY_NAME", "Пропуска на МКАД");
define("MAIL_RESEND", "zakaz@propuska-mkad-ttk-sk.ru");

$headersMn = array(
	'From: '.COMPANY_NAME.' <'.MAIL_RESEND.'>',
	'content-type: text/html',
);

$serviceBase = new wpdb(BI_SERVICE_USER_NAME, BI_SERVICE_USER_PASS, BI_SERVICE_DB_NAME, BI_SERVICE_DB_HOST);

$numbers =  $serviceBase->get_results("SELECT * FROM `dolg`");

add_filter('wp_mail_content_type',function( $content_type ) {return 'text/html';});

foreach ($numbers as $elem) {

    $email_tosendMn = array("info@propusk247.ru", "asmi046@gmail.com", $elem->email);

    $deycount = countDaysBetweenDates(date("Y-m-d H:i:s"), $elem->adding_data);
	$deycount = (($deycount+1)<0)?0:($deycount); 

    $serviceBase->update('dolg', [
        "checing_data" => date("Y-m-d H:i:s"),
        "deys" => $deycount
    ], ["id" => $elem->id]);

    if ($deycount == 11) {

        $mailSabj = "Нет оплаты за пропуск ".$number." более 10 дней";
		$mailContent = "Здравствуйте, за пропуск на автомобиль ".$number." нет оплаты более 10 дней.";

		wp_mail($email_tosendMn, $mailSabj, $mailContent, $headersMn);

    }

    if ($deycount == 15) {
        $mailSabj = "Нет оплаты за пропуск ".$number." более двух недель";
		$mailContent = "Здравствуйте, за пропуск на автомобиль ".$number." более двух недель";

		wp_mail($email_tosendMn, $mailSabj, $mailContent, $headersMn);
    }

    if ($deycount == 20) {
        $mailSabj = "Нет оплаты за пропуск ".$number.". Отправляем на аннуляцию.";
		$mailContent = "Здравствуйте, за пропуск на автомобиль ".$number.". Отправляем на аннуляцию.";

		wp_mail($email_tosendMn, $mailSabj, $mailContent, $headersMn);
    }

    echo $elem->number." -> ".$deycount."\n\r";
}

?>