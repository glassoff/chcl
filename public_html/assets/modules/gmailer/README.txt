
TE Mailer v1.5beta

Author: Brandon Jones / Kevin Frey / TransEffect LLC
Copyright: TransEffect LLC (http://www.transeffect.com)
Description: Used to send bulk email (but not spam!) from within MODx

Update History:
8-21-09 v1.5beta released
9-17-07 v1.0 released


New in this version:

- Built for MODx Evolution 1.0.0

- A new mailing list manager! Now using the latest version of DrasticData's DrasticGrid (0.6.17).

- Localization features - can now be translated into different languages via a single file.

- Mail throttling - most of the more inexpensive web hosts allow you to send only a certain number of emails at once, or a certain number of emails within a certain amount of time. TE Mailer now throttles the number of messages being sent, so if you have a large mailing list, you can hit send, and go grab some coffee!

- Rudimentary resume feature. If TE Mailer gets interrupted and does not finish sending emails to your entire list, you can now send the newsletter again, and TE Mailer will resume where it left off until it gets through the entire list.

- TE Mailer is now a little prettier. The look and feel of the Administration module can be customized via CSS - currently it matches the MODx Carbon manager theme.

- Outdated code (e.g. references to scriptaculous) and superfluous files have been stripped to avoid confusion. Code has been cleaned up in general, and some parts (like the AJAX progress bar) have been updated.

TO-DO:

(What should I add to the to-do list?)


UPGRADE INSTRUCTIONS
1. Unzip the files in this archive to your computer.
2. FTP to your server, and delete all the files from your remote 'temailer' directory (./assets/modules/temailer).

*** NOTE: ***********************************************
* This is a beta release right now, so please BACK UP  *
* your current installation before deleting all the    *
* files from your ./assets/modules/temailer directory! *
********************************************************

3. Browse to the TE Mailer 1.5 directory on your local machine (where you unzipped the files in step 1).
4. Upload those files to your remote 'temailer' directory (./assets/modules/temailer). No need to upload the install directory!
5. Nothing else should change - the new version of TE Mailer should be instantly usable.


INSTALLATION INSTRUCTIONS:

*** NOTE: **************************************
* These instructions assume a new installation *
* with all default settings selected.          *
************************************************

1. Create a folder named 'temailer' in the modules directory of you MODx installation. 
2. Unzip all files into the 'temailer' directory that you just created. (./assets/modules/temailer/)
3. Point your browser to the installation directory (./assets/modules/temailer/install/)
4. Follow the directions on the installation page, select desired features, and press the install button.
5. If step 4 is successful, delete the installation directory on your web server.
6. Log out of the MODx manager, then log back in. Everything should be installed.

WHAT TO DO NOW THAT YOU'VE INSTALLED (in no particular order):
- Create new newsletters under the new 'TEMailer Newsletters' container.
- Add the Subscribe snippet to your page somewhere.
- Create an unsubscribe page that includes the unsubscribe snippet and edit your newsletter template (more below).

EDITING NEWSLETTER TEMPLATE TO INCLUDE UNSUBSCRIBE LINK:
1. Open your newsletter template.
2. Change the unsubscribe URL at the bottom to link to your new unsubscribe page. (by default the link is http://www.EXAMPLE.com/index.php?id=UNSUBSCRIBE_PAGE_ID&item=[*item*]&key=[*key*])

Note: Make sure you leave the 'item' and 'key' parameters in the URL. These are used to identify which email address to unsubscribe. If you are using mod_rewrite (aka friendly urls), you may want to specifically rewrite this link in your .htaccess file (for advanced users only)

IF YOUR SUBSCRIBE/UNSUBSCRIBE PAGE ISN'T WORKING:
Please make sure a call to MooTools is in the <head></head> section of your template on the subscribe and unsubscribe pages!