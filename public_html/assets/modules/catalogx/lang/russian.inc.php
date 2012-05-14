<?php
/**
 * Document Manager Module - language strings for use in the module
 *
 * Filename:       assets/modules/docmanager/lang/russian.inc.php
 * Language:       Russian
 * Encoding:       Windows-1251
 * Translated by:  Jaroslav Sidorkin
 * Date:           10 Nov 2006
 * Version:        1.0
 */
 
//-- RUSSIAN LANGUAGE FILE
 
//-- titles
$_lang['1C_module_title'] = '1C прайс-лист';
$_lang['1C_action_title'] = 'Выберите действие';
$_lang['1C_update_title'] = 'Обновление завершено';

//-- tabs
$_lang['1C_tab_info'] = 'Инфо';
$_lang['1C_tab_upload_data'] = 'Загрузка прайс-лист';
$_lang['1C_tab_change_settings'] = 'Настройки';

 
//-- buttons
$_lang['1C_close'] = 'Закрыть 1C прайс-лист';
$_lang['1C_cancel'] = 'Назад';
$_lang['1C_go'] = 'Вперёд';
$_lang['1C_save'] = 'Сохранить';


//-- info tab
$_lang['1C_info_desc'] = 'Выберите в таблице шаблон.';
$_lang['1C_info_no_records'] = 'Нет записов';
$_lang['1C_info_column_num'] = '№';
$_lang['1C_info_column_id'] = 'ID магазина в 1C базе';
$_lang['1C_info_column_name'] = 'Название';
$_lang['1C_info_column_model_quantity'] ='Количество моделей';
$_lang['1C_info_column_total_quantity'] ='Наличие';
$_lang['1C_info_amount'] = 'шт.';
$_lang['1c_info_last_upload_date'] = 'Дата последной загрузки';

//-- Data Upload tab
$_lang['1C_upload_form'] = 'Выберите (txt) экспорт файл чтобы загрузить прайс-лист.';



$_lang['1C_results_message']= 'Если Вы хотите сделать еще какие-то изменения, воспользуйтесь кнопкой "Назад". Кэш будет очищен автоматически.';


$_lang['1C_date_notset'] = ' (не установлена)';
//deprecated
$_lang['1C_date_dateselect_label'] = 'Выберите дату: ';

//-- document select section
$_lang['1C_select_submit'] = 'Отправить';
$_lang['1C_select_range'] = 'Вернуться у выбору диапазона ID документов';

//-- process tree/range messages
$_lang['1C_process_noselection'] = 'Ничего не выбрано. ';
$_lang['1C_process_novalues'] = 'Никаких значений не задано.';
$_lang['1C_process_invalid_error'] = 'Недопустимое значение:';
$_lang['1C_process_update_success'] = 'Изменение прошло успешно.';
$_lang['1C_process_update_error'] = 'Изменение завершено с ошибками:';
$_lang['1C_process_back'] = 'Назад';

$_lang['1c_change_settings_desc'] = 'Выберите магазин чтобы изменить прайс-лист по умолчанию для каталога.';


//File upload class messages
$_lang[18] = "There were erros on uploading file!";
$_lang[1] = "The uploaded file exceeds the max. upload filesize directive in the server configuration.";
$_lang[2] = "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the html form.";
$_lang[3] = "The uploaded file was only partially uploaded";
$_lang[4] = "No file was uploaded";
$_lang[0] = "File successfully uploaded!";

// end  http errors
$_lang[10] = "Please select a file for upload.";
$_lang[11] = "Only files with the following extensions are allowed: <b>.txt</b>";
$_lang[12] = "Sorry, the filename contains invalid characters. Use only alphanumerical chars and separate parts of the name (if needed) with an underscore. <br>A valid filename ends with one dot followed by the extension.";
$_lang[13] = "The filename exceeds the maximum length of 100 characters.";
$_lang[14] = "Sorry, the upload directory doesn't exist!";
$_lang[15] = "Sorry, a file with this name already exitst.";
$_lang[17] = "The file does not exist.";
?>
