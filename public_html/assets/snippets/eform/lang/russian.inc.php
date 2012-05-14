<?php
/**
* snippets/eform/russian.inc.php
* Russian language file for eForm
* local revision: $Id: russian.inc.php,v 1.2 08.06.2007 yentsun (Максим Коринец) Exp $
*/


$_lang["ef_thankyou_message"] = "<h3>Спасибо!</h3><p>Ваша информация принята.</p>";
$_lang["ef_no_doc"] = "Не найден документ или чанк с ид=";
//$_lang["ef_no_chunk"] = ""; //deprecated
//$_lang["ef_validation_message"] = "<strong>Some errors were detected in your form:</strong><br />";
$_lang["ef_validation_message"] = "<div class=\"errors\"><strong>При заполнении формы были найдены некоторые ошибки:</strong><br />[+ef_wrapper+]</div>"; //changed
$_lang['ef_rule_passed'] = 'Успешно выполнено правило [+rule+] (input="[+input+]").';
$_lang['ef_rule_failed'] = '<span style="color:red;">Ошибка!</span> Не выполнено правило [+rule+] (input="[+input+]")';
$_lang["ef_required_message"] = " Не заполнены следующие обязательные поля: {fields}<br />";
$_lang['ef_error_list_rule'] = 'Ошибка в заполнении поля! Правило #LIST задано, но значений списка не найдено: ';
$_lang["ef_invalid_number"] = " неявляется числом";
$_lang["ef_invalid_date"] = " дата задана неверно";
$_lang["ef_invalid_email"] = " адрес e-mail неправильный";
$_lang["ef_upload_exceeded"] = " превысил допустимый объем.";
$_lang["ef_upload_error"] = ": ошибка при загрузке файла."; //NEW
$_lang["ef_failed_default"] = "Неправильное значение.";
$_lang["ef_failed_vericode"] = "Неверный код безопасности.";
$_lang["ef_failed_range"] = "Значение не входит в допустимый диапазон";
$_lang["ef_failed_list"] = "Значение не содержится в списке допустимых";
$_lang["ef_failed_eval"] = "Значение не прошло проверку";
$_lang["ef_failed_ereg"] = "Значение не прошло проверку";
$_lang["ef_failed_upload"] = "Неверный тип файла.";
$_lang["ef_error_validation_rule"] = "Неизвестное правило";
$_lang["ef_error_filter_rule"] = "Фильтр неизвестен";
$_lang["ef_tamper_attempt"] = "Обнаружена попытка атаки!";
$_lang["ef_error_formid"] = "Неверный идентификатор формы.";
$_lang["ef_debug_info"] = "Отладочная информация: ";
$_lang["ef_is_own_id"] = "<span class=\"ef-form-error\">Шаблон формы указывает на документ (чанк), содержащий вызов сниппета eForm! Вы не можете вызывать сниппет в том же документе (чанке), в котором находится шаблон формы.</span> id=";
$_lang["ef_sql_no_result"] = " прошел проверку. <span style=\"color:red;\"> SQL не выдал результатов!</span> ";
$_lang['ef_regex_error'] = 'ошибка в регулярном выражении ';
$_lang['ef_debug_warning'] = '<p style="color:red;"><span style="font-size:1.5em;font-weight:bold;">Внимание - включен режим ОТЛАДКИ!</span> <br />Убедитесь, что выключили режим отладки перед запуском сайта!</p>';
$_lang['ef_mail_abuse_subject'] = 'Потенциальная угроза обнаружена в форме id=';
$_lang['ef_mail_abuse_message'] = '<p>Форма на вашем сайте возможно стала объектом атаки злоумышленников. Детали отправленных данных следуют ниже. Подозрительный текст заключен в теги \[..]\.  </p>';
$_lang['ef_mail_abuse_error'] = '<strong>Неверные или небезопасные элементы обнаружены в форме</strong>.';
$_lang['ef_eval_deprecated'] = "Правила #EVAL больше не используются и могут не работать в последующих версиях. Используйте #FUNCTION.";
$_lang['ef_multiple_submit'] = "<p>Данные успешно отправлены. Нет нужды отправлять данные несколько раз.</p>";
$_lang['ef_submit_time_limit'] = "<p>Данные были УЖЕ успешно отправлены. Повторная отправка данных невозможна втечение ".($submitLimit/60)." минут.</p>";
$_lang['ef_version_error'] = "<strong>Внимание!</strong> Версия сниппета eForm ($version) отличается от inc-файла ($fileVersion). Пожалуйста, убедитесь в том, что версии идентичны.";
$_lang['ef_thousands_separator'] = ''; //leave empty to use (php) locale, only needed if you want to overide locale setting!
$_lang['ef_date_format'] = '%d-%b-%Y %H:%M:%S';
$_lang['ef_mail_error'] = 'Программа не смогла отослать почту';
?>
