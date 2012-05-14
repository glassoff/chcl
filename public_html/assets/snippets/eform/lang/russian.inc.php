<?php
/**
* snippets/eform/russian.inc.php
* Russian language file for eForm
* local revision: $Id: russian.inc.php,v 1.2 08.06.2007 yentsun (������ �������) Exp $
*/


$_lang["ef_thankyou_message"] = "<h3>�������!</h3><p>���� ���������� �������.</p>";
$_lang["ef_no_doc"] = "�� ������ �������� ��� ���� � ��=";
//$_lang["ef_no_chunk"] = ""; //deprecated
//$_lang["ef_validation_message"] = "<strong>Some errors were detected in your form:</strong><br />";
$_lang["ef_validation_message"] = "<div class=\"errors\"><strong>��� ���������� ����� ���� ������� ��������� ������:</strong><br />[+ef_wrapper+]</div>"; //changed
$_lang['ef_rule_passed'] = '������� ��������� ������� [+rule+] (input="[+input+]").';
$_lang['ef_rule_failed'] = '<span style="color:red;">������!</span> �� ��������� ������� [+rule+] (input="[+input+]")';
$_lang["ef_required_message"] = " �� ��������� ��������� ������������ ����: {fields}<br />";
$_lang['ef_error_list_rule'] = '������ � ���������� ����! ������� #LIST ������, �� �������� ������ �� �������: ';
$_lang["ef_invalid_number"] = " ���������� ������";
$_lang["ef_invalid_date"] = " ���� ������ �������";
$_lang["ef_invalid_email"] = " ����� e-mail ������������";
$_lang["ef_upload_exceeded"] = " �������� ���������� �����.";
$_lang["ef_upload_error"] = ": ������ ��� �������� �����."; //NEW
$_lang["ef_failed_default"] = "������������ ��������.";
$_lang["ef_failed_vericode"] = "�������� ��� ������������.";
$_lang["ef_failed_range"] = "�������� �� ������ � ���������� ��������";
$_lang["ef_failed_list"] = "�������� �� ���������� � ������ ����������";
$_lang["ef_failed_eval"] = "�������� �� ������ ��������";
$_lang["ef_failed_ereg"] = "�������� �� ������ ��������";
$_lang["ef_failed_upload"] = "�������� ��� �����.";
$_lang["ef_error_validation_rule"] = "����������� �������";
$_lang["ef_error_filter_rule"] = "������ ����������";
$_lang["ef_tamper_attempt"] = "���������� ������� �����!";
$_lang["ef_error_formid"] = "�������� ������������� �����.";
$_lang["ef_debug_info"] = "���������� ����������: ";
$_lang["ef_is_own_id"] = "<span class=\"ef-form-error\">������ ����� ��������� �� �������� (����), ���������� ����� �������� eForm! �� �� ������ �������� ������� � ��� �� ��������� (�����), � ������� ��������� ������ �����.</span> id=";
$_lang["ef_sql_no_result"] = " ������ ��������. <span style=\"color:red;\"> SQL �� ����� �����������!</span> ";
$_lang['ef_regex_error'] = '������ � ���������� ��������� ';
$_lang['ef_debug_warning'] = '<p style="color:red;"><span style="font-size:1.5em;font-weight:bold;">�������� - ������� ����� �������!</span> <br />���������, ��� ��������� ����� ������� ����� �������� �����!</p>';
$_lang['ef_mail_abuse_subject'] = '������������� ������ ���������� � ����� id=';
$_lang['ef_mail_abuse_message'] = '<p>����� �� ����� ����� �������� ����� �������� ����� ���������������. ������ ������������ ������ ������� ����. �������������� ����� �������� � ���� \[..]\.  </p>';
$_lang['ef_mail_abuse_error'] = '<strong>�������� ��� ������������ �������� ���������� � �����</strong>.';
$_lang['ef_eval_deprecated'] = "������� #EVAL ������ �� ������������ � ����� �� �������� � ����������� �������. ����������� #FUNCTION.";
$_lang['ef_multiple_submit'] = "<p>������ ������� ����������. ��� ����� ���������� ������ ��������� ���.</p>";
$_lang['ef_submit_time_limit'] = "<p>������ ���� ��� ������� ����������. ��������� �������� ������ ���������� �������� ".($submitLimit/60)." �����.</p>";
$_lang['ef_version_error'] = "<strong>��������!</strong> ������ �������� eForm ($version) ���������� �� inc-����� ($fileVersion). ����������, ��������� � ���, ��� ������ ���������.";
$_lang['ef_thousands_separator'] = ''; //leave empty to use (php) locale, only needed if you want to overide locale setting!
$_lang['ef_date_format'] = '%d-%b-%Y %H:%M:%S';
$_lang['ef_mail_error'] = '��������� �� ������ �������� �����';
?>
