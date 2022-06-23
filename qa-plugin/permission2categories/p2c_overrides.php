<?php

/**
 * This is an override of the core function 'qa_page_q_post_rules'.
 * Adds another permissions check to see if the user has the right permit level for the category the question is in. 
 * If not the question will be blocked.
 *
 * @see qa_page_q_post_rules() in core files
 */
function qa_page_q_post_rules($post, $parentpost=null, $siblingposts=null, $childposts=null)
{
	//setup vars and initiate p2c class
	$p2c = qa_load_module('process', 'Permissions2Categories');
	$categoryid = $post['categoryid'];
	
	// run the original function and get all the info
	$rules=qa_page_q_post_rules_base($post, $parentpost, $siblingposts, $childposts);
	
	//check to see if user has permission to view the category, if not, then hide the question
	if (!$p2c->has_permit($categoryid))
		$rules['viewable']=0;

	return $rules;
}

/**
 * This is an override of the core function 'qa_page_q_post_rules'.
 * Adds another permissions check to see if the user has the right permit level for the category the question is in. 
 * If not the question will be blocked.
 * 
 * @see qa_page_q_post_rules() in core files
 */
function qa_create_new_user($email, $password, $handle, $level = QA_USER_LEVEL_BASIC, $confirmed = false)
{
//	echo "AAAA";die();
	require_once QA_INCLUDE_DIR . 'db/users.php';
	require_once QA_INCLUDE_DIR . 'db/points.php';
	require_once QA_INCLUDE_DIR . 'app/options.php';
	require_once QA_INCLUDE_DIR . 'app/emails.php';
	require_once QA_INCLUDE_DIR . 'app/cookies.php';

	$userid = qa_db_user_create($email, $password, $handle, $level, qa_remote_ip_address());
	qa_db_points_update_ifuser($userid, null);
	qa_db_uapprovecount_update();

	if ($confirmed)
		qa_db_user_set_flag($userid, QA_USER_FLAGS_EMAIL_CONFIRMED, true);

	if (qa_opt('show_notice_welcome'))
		qa_db_user_set_flag($userid, QA_USER_FLAGS_WELCOME_NOTICE, true);

	$custom = qa_opt('show_custom_welcome') ? trim(qa_opt('custom_welcome')) : '';

	if (qa_opt('confirm_user_emails') && $level < QA_USER_LEVEL_EXPERT && !$confirmed) {
		$confirm = strtr(qa_lang('emails/welcome_confirm'), array(
			'^url' => qa_get_new_confirm_url($userid, $handle),
		));

		if (qa_opt('confirm_user_required'))
			qa_db_user_set_flag($userid, QA_USER_FLAGS_MUST_CONFIRM, true);

	} else
		$confirm = '';

	// we no longer use the 'approve_user_required' option to set QA_USER_FLAGS_MUST_APPROVE; this can be handled by the Permissions settings

	qa_send_notification($userid, $email, $handle, qa_lang('emails/welcome_subject'), qa_lang('emails/welcome_body'), array(
		'^password' => isset($password) ? qa_lang('main/hidden') : qa_lang('users/password_to_set'), // v 1.6.3: no longer email out passwords
		'^url' => qa_opt('site_url'),
		'^custom' => strlen($custom) ? ($custom . "\n\n") : '',
		'^confirm' => $confirm,
	));

	qa_report_event('u_register', $userid, $handle, qa_cookie_get(), array(
		'email' => $email,
		'level' => $level,
	));

	return $userid;
}

function qa_user_level_string($level,$jenis=false)
{
    if(!$jenis) {
        if ($level >= QA_USER_LEVEL_SUPER)
            $string = 'users/level_super';
        elseif ($level >= QA_USER_LEVEL_ADMIN)
            $string = 'users/level_admin';
        elseif ($level >= QA_USER_LEVEL_MODERATOR)
            $string = 'users/level_moderator';
        elseif ($level >= QA_USER_LEVEL_EDITOR)
            $string = 'users/level_editor';
        elseif ($level >= QA_USER_LEVEL_EXPERT)
            $string = 'users/level_expert';
        elseif ($level >= QA_USER_LEVEL_APPROVED)
            $string = 'users/approved_user';
        else
            $string = 'users/registered_user';
    } else {
        if ($level == QA_JENIS_USER_WP)
            $string = 'users/jenis_wp';
        elseif ($level == QA_JENIS_USER_DJP)
            $string = 'users/jenis_pegawai';
        elseif ($level == QA_JENIS_USER_PENGAWASAN)
            $string = 'users/jenis_ar';
    }

    return qa_lang($string);
}
