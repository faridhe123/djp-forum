<ul class="nav navbar-nav navbar-right login-nav">
    <li class="dropdown login-dropdown login active">
        <a href="#" data-toggle="dropdown" class="navbar-login-button">
            <span class="fa fa-sign-in text-muted"></span>
        </a>
        <ul class="dropdown-menu" role="menu" id="login-dropdown-menu">
            <?php
                if ( !empty( $this->content['navigation']['user'] ) ) {
                    $this->output( '<li class="open-login-buttons">' );
                    // echo "<pre>",print_r($this->content['navigation']['user']);die();
                    foreach ( $this->content['navigation']['user'] as $k => $custom ) {
                        if ( ( $k != 'login' ) && ( $k != 'register' ) ) {

                            if ( $k == 'facebook-login' ) {
                                //for the default facebook login plugin
                                $this->output( '<div class="text-center ">' );
                                $this->output( $custom['label'] );
                                $this->output( '</div>' );
                                continue;
                            }

                            //support for open login plugin
                            $icon = '';
                            // preg_match( '/class="([^"]+)"/', @$custom['label'], $class );

                            // if ( $k == 'facebook' )
                            //     $icon = 'class="' . @$class[1] . ' fa fa-facebook"';
                            // elseif ( $k == 'github' )
                            //     $icon = 'class="' . @$class[1] . ' fa fa-github"';
                            // elseif ( $k == 'foursquare' )
                            //     $icon = 'class="' . @$class[1] . ' fa fa-foursquare"';
                            // elseif ( $k == 'google' )
                            //     $icon = 'class="' . @$class[1] . ' fa fa-google"';
                            // elseif ( $k == 'googleplus' )
                            //     $icon = 'class="' . @$class[1] . ' fa fa-google-plus"';
                            // elseif ( $k == 'live' )
                            //     $icon = 'class="' . @$class[1] . ' fa fa-windows"';
                            // elseif ( $k == 'tumblr' )
                            //     $icon = 'class="' . @$class[1] . ' fa fa-tumblr"';
                            // elseif ( $k == 'yahoo' )
                            //     $icon = 'class="' . @$class[1] . ' fa fa-yahoo"';
                            // elseif ( $k == 'twitter' )
                            //     $icon = 'class="' . @$class[1] . ' fa fa-twitter"';
                            // elseif ( $k == 'linkedin' )
                            //     $icon = 'class="' . @$class[1] . ' fa fa-linkedin"';
                            // elseif ( $k == 'vk' )
                            //     $icon = 'class="' . @$class[1] . ' fa fa-vk"';

                            $pattern = "/_(?=[^>]*<)/";

                            $custom['label'] = preg_replace( $pattern, $icon, $custom['label'] );
                            $this->output( str_replace( @$class[0], @$icon, @$custom['label'] ) );
                        }
                    }
                    $this->output( '</li>' );
                }
            ?>
            <?php if ( isset( $this->content['navigation']['user'] ) && count( $this->content['navigation']['user'] ) > 2 ): ?>
                <li>
                    <div class="login-or">
                        <!-- <hr class="hr-or colorgraph"> -->
                       <!--  <span class="span-or">atau</span>-->
                    </div>
                </li>
            <?php endif ?>
            <?php if (!defined( 'QA_WORDPRESS_INTEGRATE_PATH' )): ?>
				
            <?php endif ?>
			

            <?php if (defined( 'QA_WORDPRESS_INTEGRATE_PATH' )): ?>
                <!--For Wordpress integrated website-->
                <li class="dropdown-header">Login or Register</li>
                <li>
                    <a href="<?php echo $this->content['navigation']['user']['login']['url']; ?>">
                        <span class="fa fa-sign-in"></span>
                        <?php echo $this->content['navigation']['user']['login']['label']; ?>
                    </a>
                </li>
                <li>
                    <a href="<?php echo $this->content['navigation']['user']['register']['url']; ?>">
                        <span class="fa fa-user"></span>
                        <?php echo $this->content['navigation']['user']['register']['label']; ?>
                    </a>
                </li>
            <?php endif ?>
        </ul>
    </li>
</ul>

<?php
    unset( $this->content['navigation']['user']['login'] );
    unset( $this->content['navigation']['user']['register'] );
