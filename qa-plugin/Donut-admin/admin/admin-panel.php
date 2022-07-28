<?php

    if ( !defined( 'QA_VERSION' ) ) { // don't allow this page to be requested directly from browser
        exit;
    }

    require_once QA_INCLUDE_DIR . '/app/admin.php';
require_once QA_INCLUDE_DIR . '/app/upload.php';

    $adminsection = strtolower( qa_request_part( 2 ) );

    //	Get list of categories and all options

    $categories = qa_db_select_with_pending( qa_db_category_nav_selectspec( null, true ) );

//	See if we need to redirect

    if ( empty( $adminsection ) ) {
        $subnav = qa_admin_sub_navigation();

        if ( isset( $subnav[@$_COOKIE['qa_admin_last']] ) )
            qa_redirect( $_COOKIE['qa_admin_last'] );
        elseif ( count( $subnav ) ) {
            reset( $subnav );
            qa_redirect( key( $subnav ) );
        }
    }

//	Check admin privileges (do late to allow one DB query)
    if ( !qa_admin_check_privileges( $qa_content ) )
        return $qa_content;


//	For non-text options, lists of option types, minima and maxima

    $optiontype = array(
        'donut_activate_prod_mode'           => 'checkbox',
        'donut_use_local_font'               => 'checkbox',
        'donut_enable_top_bar'               => 'checkbox',
        'donut_show_top_social_icons'        => 'checkbox',
        'donut_enable_sticky_header'         => 'checkbox',
        'donut_enable_back_to_top_btn'       => 'checkbox',
        'donut_show_home_page_banner'        => 'checkbox',
        'donut_banner_closable'              => 'checkbox',
        'donut_banner_show_ask_box'          => 'checkbox',
        'donut_show_breadcrumbs'             => 'checkbox',
        'donut_show_site_stats_above_footer' => 'checkbox',
        'donut_show_social_links_at_footer'  => 'checkbox',
        'donut_show_copyright_at_footer'     => 'checkbox',
        'donut_show_custom_404_page'         => 'checkbox',
        'donut_copyright_text'               => 'text',
        'donut_banner_head_text'             => 'text',
        'donut_banner_div1_text'             => 'text',
        'donut_banner_div1_link'             => 'text',
        'donut_banner_div1_img'              => 'file',
        'donut_banner_div2_text'             => 'text',
        'donut_banner_div2_link'             => 'text',
        'donut_banner_div2_img'              => 'file',
        'donut_banner_div3_text'             => 'text',
        'donut_banner_div3_link'             => 'text',
        'donut_banner_div3_img'              => 'file',
        'donut_top_bar_left_text'            => 'text',
        'donut_top_bar_right_text'           => 'text',
        'donut_facebook_url'                 => 'text',
        'donut_twitter_url'                  => 'text',
        'donut_pinterest_url'                => 'text',
        'donut_google-plus_url'              => 'text',
        'donut_vk_url'                       => 'text',
        'donut_email_address'                => 'text',
        'donut_custom_404_text'              => 'text',
        'donut_general_settings_notice'      => 'custom',
        'donut_homepage_settings_notice'     => 'custom',
        'donut_footer_settings_notice'       => 'custom',
        'donut_social_settings_notice'       => 'custom',
    );

    $optionmaximum = array();

    $optionminimum = array();


//	Define the options to show (and some other visual stuff) based on request

    $formstyle = 'tall';
    $checkboxtodisplay = null;

    switch ( $adminsection ) {

        case 'general-settings':
            $subtitle = 'general';
            $showoptions = array( 'donut_general_settings_notice', 'donut_activate_prod_mode', 'donut_use_local_font','donut_enable_top_bar', 'donut_top_bar_left_text', 'donut_top_bar_right_text', 'donut_show_top_social_icons', 'donut_enable_sticky_header', 'donut_enable_back_to_top_btn' );
            array_push( $showoptions, 'donut_show_custom_404_page', 'donut_custom_404_text' );

            array_push( $showoptions, 'donut_homepage_settings_notice', 'donut_show_home_page_banner', 'donut_banner_head_text', 'donut_banner_div1_text', 'donut_banner_div1_link', 'donut_banner_div1_img', 'donut_banner_div2_text', 'donut_banner_div2_link', 'donut_banner_div2_img',  'donut_banner_div3_text', 'donut_banner_div3_link', 'donut_banner_div3_img', 'donut_banner_show_ask_box', 'donut_banner_closable' );

            if ( class_exists( 'Ami_Breadcrumb' ) ) {
                array_push( $showoptions, '', 'donut_show_breadcrumbs' );
            }

            array_push( $showoptions, 'donut_footer_settings_notice', 'donut_show_site_stats_above_footer', 'donut_show_social_links_at_footer', 'donut_show_copyright_at_footer', 'donut_copyright_text' );

            array_push( $showoptions, 'donut_social_settings_notice', 'donut_facebook_url', 'donut_twitter_url', 'donut_pinterest_url', 'donut_google-plus_url', 'donut_vk_url', 'donut_email_address' );

            $formstyle = 'wide';

            $checkboxtodisplay = array(
                'donut_top_bar_left_text'     => 'option_donut_enable_top_bar',
                'donut_top_bar_right_text'    => 'option_donut_enable_top_bar',
                'donut_show_top_social_icons' => 'option_donut_enable_top_bar',
                'donut_banner_head_text'      => 'option_donut_show_home_page_banner',
                'donut_banner_div1_text'      => 'option_donut_show_home_page_banner',
                'donut_banner_div1_link'      => 'option_donut_show_home_page_banner',
                'donut_banner_div1_img'      => 'option_donut_show_home_page_banner',
                'donut_banner_div2_text'      => 'option_donut_show_home_page_banner',
                'donut_banner_div2_link'      => 'option_donut_show_home_page_banner',
                'donut_banner_div2_img'      => 'option_donut_show_home_page_banner',
                'donut_banner_div3_text'      => 'option_donut_show_home_page_banner',
                'donut_banner_div3_link'      => 'option_donut_show_home_page_banner',
                'donut_banner_div3_img'      => 'option_donut_show_home_page_banner',
                'donut_banner_show_ask_box'   => 'option_donut_show_home_page_banner',
                'donut_banner_closable'       => 'option_donut_show_home_page_banner',
                'donut_copyright_text'        => 'option_donut_show_copyright_at_footer',
                'donut_custom_404_text'       => 'option_donut_show_custom_404_page',
            );
            break;

        default:
            $pagemodules = qa_load_modules_with( 'page', 'match_request' );
            $request = qa_request();

            foreach ( $pagemodules as $pagemodule )
                if ( $pagemodule->match_request( $request ) )
                    return $pagemodule->process_request( $request );

            return include QA_INCLUDE_DIR . 'qa-page-not-found.php';
            break;
    }


//	Filter out blanks to get list of valid options

    $getoptions = array();
    foreach ( $showoptions as $optionname )
        if ( strlen( $optionname ) && ( strpos( $optionname, '/' ) === false ) ) // empties represent spacers in forms
            $getoptions[] = $optionname;


//	Process user actions

    $errors = array();
    $securityexpired = false;

    $formokhtml = null;

    if ( qa_clicked( 'doresetoptions' ) ) {
        if ( !qa_check_form_security_code( 'admin/' . $adminsection, qa_post_text( 'code' ) ) )
            $securityexpired = true;

        else {
            donut_reset_options( $getoptions );
            $formokhtml = donut_lang_html( 'options_reset' );
        }
    } elseif ( qa_clicked( 'dosaveoptions' ) ) {  //TODO : Jika klik save, belum ada perubahan backend
        if ( !qa_check_form_security_code( 'admin/' . $adminsection, qa_post_text( 'code' ) ) )
            $securityexpired = true;

        else {
            foreach ( $getoptions as $optionname ) {
                $optionvalue = qa_post_text( 'option_' . $optionname );

                if (
                    ( @$optiontype[$optionname] == 'number' ) ||
                    ( @$optiontype[$optionname] == 'checkbox' ) ||
                    ( ( @$optiontype[$optionname] == 'number-blank' ) && strlen( $optionvalue ) )
                )
                    $optionvalue = (int) $optionvalue;

                if ( isset( $optionmaximum[$optionname] ) )
                    $optionvalue = min( $optionmaximum[$optionname], $optionvalue );

                if ( isset( $optionminimum[$optionname] ) )
                    $optionvalue = max( $optionminimum[$optionname], $optionvalue );

                if (@$optiontype[$optionname] == 'file' && !empty($_FILES['option_'.$optionname]['tmp_name'])) {
                    // M<ulai upload dan buat blob
                    $test = qa_upload_file($_FILES['option_'.$optionname]['tmp_name'], $_FILES['option_'.$optionname]['name']);
                    $optionvalue = $test['bloburl'];
                }elseif (@$optiontype[$optionname] == 'file' && empty($_FILES['option_'.$optionname]['tmp_name'])) {
                    // M<ulai upload dan buat blob
                    $optionvalue = qa_opt( $optionname ) ?? donut_default_option( $optionname );
                };

                qa_set_option( $optionname, $optionvalue );
            }

            $formokhtml = donut_lang_html( 'options_saved' );
        }
    }

    //	Get the actual options

    $options = qa_get_options( $getoptions );


    //	Prepare content for theme

    $qa_content = qa_content_prepare();

    $qa_content['title'] = donut_lang( 'donut_theme_settings' ) . ' - ' . donut_lang( $subtitle );
    $qa_content['error'] = $securityexpired ? qa_lang_html( 'admin/form_security_expired' ) : qa_admin_page_error();

    $qa_content['script_rel'][] = 'qa-content/qa-admin.js?' . QA_VERSION;

    $qa_content['form'] = array(
        'ok'      => $formokhtml,

        'tags'    => 'enctype="multipart/form-data" method="post" action="' . qa_self_html() . '" name="admin_form" onsubmit="document.forms.admin_form.has_js.value=1; return true;"',

        'style'   => $formstyle,

        'fields'  => array(),

        'buttons' => array(
            'save'  => array(
                'tags'  => 'id="dosaveoptions"',
                'label' => qa_lang_html( 'admin/save_options_button' ),
            ),

            'reset' => array(
                'tags'  => 'name="doresetoptions"',
                'label' => qa_lang_html( 'admin/reset_options_button' ),
            ),
        ),

        'hidden'  => array(
            'dosaveoptions' => '1', // for IE
            'has_js'        => '0',
            'code'          => qa_get_form_security_code( 'admin/' . $adminsection ),
        ),
    );

    function qa_optionfield_make_select( &$optionfield, $options, $value, $default )
    {
        $optionfield['type'] = 'select';
        $optionfield['options'] = $options;
        $optionfield['value'] = isset( $options[qa_html( $value )] ) ? $options[qa_html( $value )] : @$options[$default];
    }

    $indented = false;
$x = 1;
    foreach ( $showoptions as $optionname )
        // TODO : Jika jenis file (Slider 1 2 3)
        if ( @$optiontype[$optionname] == 'file' ) {
            // ini preview gambarnya
            if(!empty(qa_opt( $optionname ))) {
                // cek ukuran gambar
                $image_info = getimagesize(qa_opt( $optionname ));
                $image_width = $image_info[0];
                $image_height = $image_info[1];

                $rasio = number_format($image_height/$image_width*10,0);

                $qa_content['form']['fields']['gambar_'.$x] = array(
                    'type'  => 'image',
                    'label' => ( $indented ? '&ndash; ' : '' ) . donut_options_lang( $optionname ),
                    'html' => "<img src='".qa_opt( $optionname )."' width='150'/>",
                    'note' => '<br>'.$image_width.' x '.$image_height . ' ( 10 : '.
                        $rasio.' ) <br><small> Size : '.
                            (strlen( file_get_contents(qa_opt( $optionname )) ) > 1024*1024 ?
                                round((strlen( file_get_contents(qa_opt( $optionname )) ) / 1024 / 1024),2) ." MB":
                                round((strlen(file_get_contents(qa_opt( $optionname )) ) / 1024),2)." KB") . "</small>"
                );

                if($rasio != 3)
                    $qa_content['form']['fields']['gambar_'.$x]['error'] = "<span style='color:red;font-weight: 300'>".$image_width.' x '.$image_height . ' ( 10 : '.
                        $rasio.' ) <br><small> Size : '.
                        (strlen( file_get_contents(qa_opt( $optionname )) ) > 1024*1024 ?
                            round((strlen( file_get_contents(qa_opt( $optionname )) ) / 1024 / 1024),2) ." MB":
                            round((strlen(file_get_contents(qa_opt( $optionname )) ) / 1024),2)." KB")
                            ."</span></small>"
                        ;

                $x++;
            } else {
                // jika belum ada gambar ditambahkan
                $qa_content['form']['fields'][] = array(
                    'type'  => 'static',
                    'label' => 'gambar belum ada silakan tambahkan',
                );
            }

            $type = @$optiontype[$optionname];
            if ( $type == 'number-blank' )
                $type = 'number';

            $value = $options[$optionname];

            $optionfield = array(
                'id'    => $optionname,
                'label' => '',
                'tags'  => 'name="option_' . $optionname . '" id="option_' . $optionname . '"',
                'value' => qa_html( $value ),
                'type'  => $type,
//                'error' => "ERROR",
                'note' => "
                        <ul>
                            <li><small>Upload gambar dengan rasio 10:3 agar gambar tidak terpotong</small></li> 
                            <li>Contoh : 1366 x 405 , 1920 x 569 ,</li> 
                        </ul>
                        "
            );

            $qa_content['form']['fields'][$optionname] = $optionfield;
        }elseif ( empty( $optionname ) ) {
            $indented = false;

            $qa_content['form']['fields'][] = array(
                'type' => 'blank',
            );

        } elseif ( strpos( $optionname, '/' ) !== false ) {
            $qa_content['form']['fields'][] = array(
                'type'  => 'static',
                'label' => qa_lang_html( $optionname ),
            );

            $indented = true;

        } else {
            $type = @$optiontype[$optionname];
            if ( $type == 'number-blank' )
                $type = 'number';

            $value = $options[$optionname];

            $optionfield = array(
                'id'    => $optionname,
                'label' => ( $indented ? '&ndash; ' : '' ) . donut_options_lang( $optionname ),
                'tags'  => 'name="option_' . $optionname . '" id="option_' . $optionname . '"',
                'value' => qa_html( $value ),
                'type'  => $type,
                'error' => qa_html( @$errors[$optionname] ),
            );

            if ( isset( $optionmaximum[$optionname] ) )
                $optionfield['note'] = qa_lang_html_sub( 'admin/maximum_x', $optionmaximum[$optionname] );

            $feedrequest = null;
            $feedisexample = false;

            switch ( $optionname ) { // special treatment for certain options

                case 'special_opt': //not using for now
                    $optionfield['note'] = donut_options_lang_html( $optionname . '_note' );
                    break;

            }

            switch ( $optionname ) {
                case 'donut_activate_prod_mode':
                case 'donut_use_local_font':
                case 'donut_top_bar_left_text':
                case 'donut_top_bar_right_text':
                case 'donut_enable_top_bar':
                case 'donut_enable_sticky_header':
                case 'donut_enable_back_to_top_btn':
                case 'donut_show_home_page_banner':
                case 'donut_show_breadcrumbs':
                case 'donut_show_site_stats_above_footer':
                case 'donut_show_social_links_at_footer':
                case 'donut_show_copyright_at_footer':
                case 'donut_show_custom_404_page':
                    $optionfield['style'] = 'tall';
                    break;
            }

            $qa_content['form']['fields'][$optionname] = $optionfield;
        }


    if ( isset( $checkboxtodisplay ) )
        qa_set_display_rules( $qa_content, $checkboxtodisplay );

    $qa_content['navigation']['sub'] = qa_admin_sub_navigation();


    return $qa_content;


    /*
        Omit PHP closing tag to help avoid accidental output
    */
