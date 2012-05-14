<?php
/**
 * TE Mailer Module - english.inc.php
 * 
 * Purpose: Contains the language strings for use in the module.
 * Author: Kevin Frey / TransEffect LLC
 * For: MODx Evolution 1.0.0 CMS (www.modxcms.com)
 * Date: 8/18/2009 Version: 1.5beta
 *
 */

//-- ENGLISH LANGUAGE FILE

//-- Installation and General Program Stuff
$_lang['programName'] = 'TE Mailer';
$_lang['programVersion'] = 'v1.5.beta';
$_lang['programDescription'] = 'Now quickly and easily send MODx documents as eMail Newsletters, and make mailing list management simple, all from within the MODx Manager!';
$_lang['programCopyright'] = 'Copyright &copy; 2007 - ' . date("Y") . ' <a href="http://www.transeffect.com/">TransEffect LLC</a>';
$_lang['wordInstaller'] = 'Installer';
$_lang['wordModule'] = 'Module';
$_lang['wordInstallProg'] = 'Installation Progress...';
$_lang['msgNotInstalled'] = 'Not installed by user.';
$_lang['msgInstalledSuccessfully'] = 'Installed successfully!';
$_lang['msgInstallError'] = 'An error occurred. This item was not installed.';
$_lang['msgInstallSuccess'] = 'Installation was successful!';
$_lang['msgInstallDelete'] = 'Please delete the installation directory.';
$_lang['msgInstallFail'] = 'One or more parts of the installation failed.';
$_lang['msgInstallSelect'] = 'Please select the items you would like to install. Only deselect an item if you know what you are doing. If this is your first time installing TEMailer, you should use all the defaults.';
$_lang['optDbTable'] = 'Mailing List DataBase Table (empty)';
$_lang['optEmailTpl'] = 'Email Template';
$_lang['optSubscrSnip'] = 'Subscribe Snippet';
$_lang['optUnsubscrSnip'] = 'Unsubscribe Snippet';
$_lang['optDefaultFromAddr'] = 'Default Email From Address';
$_lang['valueDefaultFromAddr'] = 'newsletter@mysite.com';
$_lang['optDefaultFromName'] = 'Default Email From Name';
$_lang['valueDefaultFromName'] = 'MySite Newsletter';
$_lang['optNewsletterContainerId'] = 'Newsletter Container ID';
$_lang['msgNewsletterContainerId'] = 'Leave empty to create a new folder';

//-- Main page stuff
$_lang['mainLink'] = 'Main Area';
$_lang['editMailingListLink'] = 'Edit Mailing List';

//-- Subscribe page
$_lang['subscribeThanks'] = 'You have been subscribed. Thank You!';
$_lang['subscribeName'] = 'Name';
$_lang['subscribeEmail'] = 'Email';

//-- Unsubscribe page/Errors
$_lang['error'] = 'Error';
$_lang['errorInvalidInfo'] = 'Invalid information was passed in the URL.';
$_lang['errorMissingInfo'] = 'Information is missing from the URL.';
$_lang['unsubscribeSuccess1'] = 'The email address ';
$_lang['unsubscribeSuccess2'] = ' has been unsubscribed from the list.';

//-- Newsletter mailing setup page
$_lang['newsletter'] = 'Newsletter';
$_lang['subject'] = 'Subject';
$_lang['introduction'] = 'Introduction';
$_lang['testEmailAddress'] = 'Test Email Address';
$_lang['testEmailNote'] = '<strong>Note:</strong> If a test email address is entered, no emails will be sent to the mailing list. Use commas or semi-colons to separate multiple test email addresses.';

//-- Sending email page/Success
$_lang['success'] = 'Success!';
$_lang['successMessage1'] = 'Your email was sent successfully to ';
$_lang['successMessage2'] = ' recipient(s).';
$_lang['emptyList'] = 'Your mailing list is empty';
$_lang['emptyListMessage'] = 'You can use the &quot;Test Email Address&quot; input to test this script until you have an actual mailing list, or click &quot;' . $_lang['editMailingListLink'] . '&quot; above to add to the list.';

?>