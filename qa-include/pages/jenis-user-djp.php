<?php
/*
	Question2Answer by Gideon Greenspan and contributors
	http://www.question2answer.org/

	Description: Controller for user profile page, including wall


	This program is free software; you can redistribute it and/or
	modify it under the terms of the GNU General Public License
	as published by the Free Software Foundation; either version 2
	of the License, or (at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	More about this license: http://www.question2answer.org/license.php
*/

if (!defined('QA_VERSION')) { // don't allow this page to be requested directly from browser
	header('Location: ../../');
	exit;
}

require_once QA_INCLUDE_DIR . 'db/selects.php';
require_once QA_INCLUDE_DIR . 'app/format.php';
require_once QA_INCLUDE_DIR . 'app/limits.php';
require_once QA_INCLUDE_DIR . 'app/updates.php';


// $handle, $userhtml are already set by /qa-include/page/user.php - also $userid if using external user integration


// Redirect to 'My Account' page if button clicked

if (qa_clicked('doaccount'))
	qa_redirect('account');


// Find the user profile and questions and answers for this handle

$loginuserid = qa_get_logged_in_userid();
$identifier = QA_FINAL_EXTERNAL_USERS ? $userid : $handle;

list($useraccount, $userprofile, $userfields, $usermessages, $userpoints, $userlevels, $navcategories, $userrank) =
	qa_db_select_with_pending(
		QA_FINAL_EXTERNAL_USERS ? null : qa_db_user_account_selectspec($handle, false),
		QA_FINAL_EXTERNAL_USERS ? null : qa_db_user_profile_selectspec($handle, false),
		QA_FINAL_EXTERNAL_USERS ? null : qa_db_userfields_selectspec(),
		QA_FINAL_EXTERNAL_USERS ? null : qa_db_recent_messages_selectspec(null, null, $handle, false, qa_opt_if_loaded('page_size_wall')),
		qa_db_user_points_selectspec($identifier),
		qa_db_user_levels_selectspec($identifier, QA_FINAL_EXTERNAL_USERS, true),
		qa_db_category_nav_selectspec(null, true),
		qa_db_user_rank_selectspec($identifier)
	);

//jika tidak login
if (!QA_FINAL_EXTERNAL_USERS && $handle !== qa_get_logged_in_handle()) {
	foreach ($userfields as $index => $userfield) {
		if (isset($userfield['permit']) && qa_permit_value_error($userfield['permit'], $loginuserid, qa_get_logged_in_level(), qa_get_logged_in_flags()))
			unset($userfields[$index]); // don't pay attention to user fields we're not allowed to view
	}
}


// Check the user exists and work out what can and can't be set (if not using single sign-on)


// Process edit or save button for user, and other actions

// Prepare content for theme

$qa_content = qa_content_prepare();

$qa_content['title'] = qa_lang_html_sub('profile/user_x', $userhtml);
$qa_content['error'] = @$errors['page'];



// General information about the user, only available if we're using internal user management

if (!QA_FINAL_EXTERNAL_USERS) {
    $p2c = qa_load_module('process', 'Permissions2Categories');
    $jenisuser = $p2c->user_type(qa_get_logged_in_userid());

    if($jenisuser == '1') $role = 'ROLE_USER_NON_DJP';
    elseif($jenisuser == '2') $role = 'ROLE_USER_DJP';
    elseif($jenisuser == '3') $role = 'ROLE_PENGAWAS';

	$qa_content['form_profile'] = array(
		'tags' => 'method="post" action="' . qa_self_html() . '"',

		'style' => 'wide',

		'fields' => array(
			'role' => array(
				'type' => 'static',
				'label' => 'Role Anda Saat ini :',
				'value' => "<b>".$role."</b>",
				'id' => 'duration',
			),

		),
	);

	unset($qa_content['form_profile']['fields']['removeavatar']);
}

// Sub menu for navigation in user pages

$ismyuser = isset($loginuserid) && $loginuserid == (QA_FINAL_EXTERNAL_USERS ? $userid : $useraccount['userid']);
$qa_content['navigation']['sub'] = qa_user_sub_navigation($handle, 'jenis', $ismyuser);


return $qa_content;
