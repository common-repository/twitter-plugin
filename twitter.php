<?php
/**
Plugin Name: BestWebSoft's Twitter
Plugin URI: https://bestwebsoft.com/products/wordpress/plugins/twitter/
Description: Add Twitter Follow, Tweet, Hashtag, and Mention buttons to WordPress posts, pages and widgets.
Author: BestWebSoft
Text Domain: twitter-plugin
Domain Path: /languages
Version: 2.66
Author URI: https://bestwebsoft.com/
License: GPLv2 or later
 */

/**
  @ Copyright 2021 BestWebSoft ( https://support.bestwebsoft.com )

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License, version 2, as
	published by the Free Software Foundation.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
 */

if ( ! function_exists( 'twttr_add_admin_menu' ) ) {
	/**
	 * Add BWS menu
	 */
	function twttr_add_admin_menu() {
		global $submenu, $wp_version, $twttr_plugin_info;

		$settings = add_menu_page(
			__( 'Twitter Settings', 'twitter-plugin' ),
			'Twitter',
			'manage_options',
			'twitter.php',
			'twttr_settings_page',
			'none'
		);
		add_submenu_page(
			'twitter.php',
			__( 'Twitter Settings', 'twitter-plugin' ),
			__( 'Settings', 'twitter-plugin' ),
			'manage_options',
			'twitter.php',
			'twttr_settings_page'
		);

		add_submenu_page(
			'twitter.php',
			'BWS Panel',
			'BWS Panel',
			'manage_options',
			'twttr-bws-panel',
			'bws_add_menu_render'
		);
		if ( isset( $submenu['twitter.php'] ) ) {
			$submenu['twitter.php'][] = array(
				'<span style="color:#d86463"> ' . __( 'Upgrade to Pro', 'twitter-plugin' ) . '</span>',
				'manage_options',
				'https://bestwebsoft.com/products/wordpress/plugins/twitter/?k=a8417eabe3c9fb0c2c5bed79e76de43c&pn=76&v=' . $twttr_plugin_info['Version'] . '&wp_v=' . $wp_version,
			);
		}
		add_action( 'load-' . $settings, 'twttr_add_tabs' );
	}
}

if ( ! function_exists( 'twttr_plugins_loaded' ) ) {
	/**
	 * Internationalization
	 */
	function twttr_plugins_loaded() {
		load_plugin_textdomain( 'twitter-plugin', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}
}

if ( ! function_exists( 'twttr_init' ) ) {
	/**
	 * Init
	 */
	function twttr_init() {
		global $twttr_plugin_info;

		if ( empty( $twttr_plugin_info ) ) {
			if ( ! function_exists( 'get_plugin_data' ) ) {
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
			}
			$twttr_plugin_info = get_plugin_data( __FILE__ );
		}

		/*## add general functions */
		require_once dirname( __FILE__ ) . '/bws_menu/bws_include.php';
		bws_include_init( plugin_basename( __FILE__ ) );

		bws_wp_min_version_check( plugin_basename( __FILE__ ), $twttr_plugin_info, '4.5' ); /* check compatible with current WP version ##*/

		/* Get/Register and check settings for plugin */
		if ( ! is_admin() || ( isset( $_GET['page'] ) && ( 'twitter.php' == $_GET['page'] || 'social-buttons.php' == $_GET['page'] ) ) ) {
			twttr_settings();
		}
	}
}

if ( ! function_exists( 'twttr_admin_init' ) ) {
	/**
	 * Admin init
	 */
	function twttr_admin_init() {
		/* Add variable for bws_menu */
		global $bws_plugin_info, $twttr_plugin_info, $bws_shortcode_list, $pagenow, $twttr_options;

		/* Function for bws menu */
		if ( empty( $bws_plugin_info ) ) {
			$bws_plugin_info = array(
				'id'      => '76',
				'version' => $twttr_plugin_info['Version'],
			);
		}

		if ( 'plugins.php' == $pagenow ) {
			/* Install the option defaults */
			if ( function_exists( 'bws_plugin_banner_go_pro' ) ) {
				twttr_settings();
				bws_plugin_banner_go_pro( $twttr_options, $twttr_plugin_info, 'twttr', 'twitter', '137342f0aa4b561cf7f93c190d95c890', '76', 'twitter-plugin' );
			}
		}

		/* Add Twitter to global $bws_shortcode_list */
		$bws_shortcode_list['twttr'] = array(
			'name'        => 'Twitter',
			'js_function' => 'twttr_shortcode_init',
		);
	}
}

if ( ! function_exists( 'twttr_settings' ) ) {
	/**
	 * This is settings functions
	 */
	function twttr_settings() {
		global $twttr_options, $twttr_plugin_info;
		/* Get options from the database */
		if ( ! get_option( 'twttr_options' ) ) {
			$options_default = twttr_get_options_default();
			add_option( 'twttr_options', $options_default );
		}

		$twttr_options = get_option( 'twttr_options' );

		if ( ! isset( $twttr_options['plugin_option_version'] ) || $twttr_options['plugin_option_version'] != $twttr_plugin_info['Version'] ) {
			$options_default = twttr_get_options_default();
			twttr_plugin_activate();
			$twttr_options = array_merge( $options_default, $twttr_options );
			$twttr_options['plugin_option_version'] = $twttr_plugin_info['Version'];
			/* show pro features */
			$twttr_options['hide_premium_options'] = array();
			update_option( 'twttr_options', $twttr_options );
		}
	}
}

if ( ! function_exists( 'twttr_get_options_default' ) ) {
	/**
	 * Get default options for plugin
	 */
	function twttr_get_options_default() {
		global $twttr_plugin_info;

		$options_default = array(
			'plugin_option_version'     => $twttr_plugin_info['Version'],
			'display_settings_notice'   => 1,
			'suggest_feature_banner'    => 1,
			'url_twitter'               => 'twitter',
			'display_option'            => 'standart',
			'count_icon'                => 1,
			'img_link'                  => plugins_url( 'images/twitter-follow.png', __FILE__ ),
			'position'                  => array( 'before' ),
			'tweet_display'             => 0,
			'size'                      => 'default',
			'lang_default'              => 1,
			'lang'                      => 'en',
			'tailoring'                 => 0,
			'url_of_twitter'            => 'page_url',
			'text_option_twitter'       => 'page_title',
			'text_twitter'              => '',
			'via_twitter'               => '',
			'related_twitter'           => '',
			'hashtag_twitter'           => '',
			'followme_display'          => 0,
			'username_display'          => 0,
			'followers_count_followme'  => 0,
			'hashtag_display'           => 0,
			'hashtag'                   => '',
			'text_option_hashtag'       => 'page_title',
			'text_hashtag'              => '',
			'url_option_hashtag'        => 'page_url',
			'url_hashtag'               => '',
			'related_hashtag'           => '',
			'mention_display'           => 0,
			'tweet_to_mention'          => '',
			'text_option_mention'       => 'page_title',
			'text_mention'              => '',
			'related_mention'           => '',
		);
		return $options_default;
	}
}

if ( ! function_exists( 'twttr_plugin_activate' ) ) {
	/**
	 * Function for activation
	 */
	function twttr_plugin_activate() {
		/* registering uninstall hook */
		if ( is_multisite() ) {
			switch_to_blog( 1 );
			register_uninstall_hook( __FILE__, 'twttr_delete_options' );
			restore_current_blog();
		} else {
			register_uninstall_hook( __FILE__, 'twttr_delete_options' );
		}
	}
}

if ( ! function_exists( 'twttr_settings_page' ) ) {
	/**
	 * Settings page
	 */
	function twttr_settings_page() {
		if ( ! class_exists( 'Bws_Settings_Tabs' ) ) {
			require_once( dirname( __FILE__ ) . '/bws_menu/class-bws-settings.php' );
		}
		require_once( dirname( __FILE__ ) . '/includes/class-twttr-settings.php' );
		$page = new Twttr_Settings_Tabs( plugin_basename( __FILE__ ) );
		if ( method_exists( $page, 'add_request_feature' ) ) {
			$page->add_request_feature();
		} ?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Twitter Settings', 'twitter-plugin' ); ?></h1>
			<noscript><div class="error below-h2"><p><strong><?php esc_html_e( 'Please enable JavaScript in Your browser.', 'twitter-plugin' ); ?></strong></p></div></noscript>
			<?php $page->display_content(); ?>
		</div>
		<?php
	}
}

if ( ! function_exists( 'twttr_twitter_buttons' ) ) {
	/**
	 * Function to creates shortcode [twitter_buttons]
	 *
	 * @param array $atts Attributes.
	 */
	function twttr_twitter_buttons( $atts = array( 'display' => 'follow' ) ) {
		$atts    = shortcode_atts( array( 'display' => 'follow' ), $atts, 'twitter_buttons' );
		$tweet   = ( stripos( $atts['display'], 'tweet' ) === false ) ? 0 : 1;
		$follow  = ( stripos( $atts['display'], 'follow' ) === false ) ? 0 : 1;
		$hashtag = ( stripos( $atts['display'], 'hashtag' ) === false ) ? 0 : 1;
		$mention = ( stripos( $atts['display'], 'mention' ) === false ) ? 0 : 1;
		if ( 1 == $tweet || 1 == $follow || 1 == $hashtag || 1 == $mention ) {
			return twttr_show_button( $tweet, $follow, $hashtag, $mention );
		}
	}
}

if ( ! function_exists( 'twttr_twit' ) ) {
	/**
	 * Positioning in the page
	 *
	 * @param string $content Content.
	 */
	function twttr_twit( $content ) {
		global $post, $twttr_options, $wp_current_filter;

		if ( ! empty( $wp_current_filter ) && in_array( 'get_the_excerpt', $wp_current_filter ) ) {
			return $content;
		}
		if ( ! empty( $twttr_options['position'] ) &&
			( 1 == $twttr_options['tweet_display'] || 1 == $twttr_options['followme_display'] || 1 == $twttr_options['hashtag_display'] || 1 == $twttr_options['mention_display'] ) ) {

			$buttons = twttr_show_button( $twttr_options['tweet_display'], $twttr_options['followme_display'], $twttr_options['hashtag_display'], $twttr_options['mention_display'] );
			$buttons = apply_filters( 'twttr_button_in_the_content', $buttons );

			if ( in_array( 'before', $twttr_options['position'] ) ) {
				$content = $buttons . $content;
			}
			if ( in_array( 'after', $twttr_options['position'] ) ) {
				$content = $content . $buttons;
			}
		}
		return $content;
	}
}


if ( ! function_exists( 'twttr_show_button' ) ) {
	/**
	 * Function for showing buttons
	 *
	 * @param int $tweet   Flag for display.
	 * @param int $follow  Flag for display.
	 * @param int $hashtag Flag for display.
	 * @param int $mention Flag for display.
	 */
	function twttr_show_button( $tweet, $follow, $hashtag, $mention ) {
		global $post, $twttr_options, $twttr_add_api_script;

		if ( is_feed() ) {

			return;
		}
		if ( 1 == $tweet || 1 == $follow || 1 == $hashtag || 1 == $mention ) {

			$twttr_add_api_script = true;

			$lang = ( 1 == $twttr_options['lang_default'] ) ? '' : 'data-lang="' . $twttr_options['lang'] . '"';
			$tailoring = ! empty( $twttr_options['tailoring'] ) ? 'data-dnt="true"' : '';

			if ( 1 == $tweet ) {
				/* Option for tweet button */
				$permalink_post = ( 'page_url' == $twttr_options['url_of_twitter'] ) ? get_permalink( $post->ID ) : get_home_url();
				$title_post = ( 'page_title' == $twttr_options['text_option_twitter'] ) ? htmlspecialchars( urlencode( $post->post_title ) ) : $twttr_options['text_twitter'];
				/* Show tweet button */
				$tweet = '<div class="twttr_twitter">
					<a href="http://twitter.com/share?text=' . $title_post . '" class="twitter-share-button" data-via="' . $twttr_options['via_twitter'] . '" data-hashtags="' . $twttr_options['hashtag_twitter'] . '" ' . $lang . ' data-size="' . $twttr_options['size'] . '" data-url="' . $permalink_post . '" ' . $tailoring . ' data-related="' . $twttr_options['related_twitter'] . '" target="_blank">' . __( 'Tweet', 'twitter-plugin' ) . '</a>
				</div>';
			} else {
				$tweet = '';
			}
			if ( 1 == $follow ) {
				/* Option for follow me button */
				if ( '' === $twttr_options['url_twitter'] ) {
					$twttr_options['url_twitter'] = 'twitter';
				}

				/* Show follow me button */
				$upload_dir = wp_upload_dir();
				if ( 'standart' == $twttr_options['display_option'] || ! is_writable( $upload_dir['basedir'] ) ) {
					$show_name = ( $twttr_options['username_display'] ) ? 'data-show-screen-name="true"' : 'data-show-screen-name="false"';

					$follow = '<div class="twttr_followme">
						<a href="https://twitter.com/' . $twttr_options['url_twitter'] . '" class="twitter-follow-button" data-size="' . $twttr_options['size'] . '" ' . $lang . ' ' . $show_name . ' ' . $tailoring . ' target="_blank">' . __( 'Follow me', 'twitter-plugin' ) . '</a>
					</div>';
				} else {
					$follow = '<div class="twttr_followme">
						<a href="http://twitter.com/' . $twttr_options['url_twitter'] . '" target="_blank" title="Follow me"><img src="' . $twttr_options['img_link'] . '" alt="' . __( 'Follow me', 'twitter-plugin' ) . '" /></a>
					</div>';
				}
			} else {
				$follow = '';
			}
			if ( 1 == $hashtag ) {
				/* Option for hashtag button */
				if ( '' == $twttr_options['hashtag'] ) {
					$main_hashtag             = __( 'TwitterStories', 'twitter-plugin' );
					$twttr_options['hashtag'] = $main_hashtag;
				} else {
					$hashtags = explode( '%2C', urlencode( $twttr_options['hashtag'] ), 2 );
					$main_hashtag = $hashtags[0];
				}

				$secondary_hastags = ( ! empty( $hashtags[1] ) ) ? '&hashtags=' . $hashtags[1] : '';

				$text_hashtag = ( 'page_title' == $twttr_options['text_option_hashtag'] ) ? htmlspecialchars( urlencode( $post->post_title ) ) : urlencode( $twttr_options['text_hashtag'] );

				if ( 'no_url' == $twttr_options['url_option_hashtag'] ) {
					$url_hashtag = '';
				} elseif ( 'home_url' == $twttr_options['url_option_hashtag'] ) {
					$url_hashtag = get_home_url();
				} else {
					$url_hashtag = get_permalink( $post->ID );
				}
				/* Show hashtag button */
				if ( '' === $text_hashtag ) {
					$hashtag = '<div class="twttr_hashtag">
						<a href="https://twitter.com/intent/tweet?button_hashtag=' . $main_hashtag . $secondary_hastags . '" class="twitter-hashtag-button" data-size="' . $twttr_options['size'] . '" ' . $lang . ' data-related="' . $twttr_options['related_hashtag'] . '" data-url="' . $url_hashtag . '" ' . $tailoring . ' target="_blank">' . __( 'Tweet', 'twitter-plugin' ) . ' #' . $twttr_options['hashtag'] . '</a>
					</div>';
				} else {
					$hashtag = '<div class="twttr_hashtag">
						<a href="https://twitter.com/intent/tweet?button_hashtag=' . $main_hashtag . $secondary_hastags . '&text=' . $text_hashtag . '" class="twitter-hashtag-button" data-size="' . $twttr_options['size'] . '" ' . $lang . ' data-related="' . $twttr_options['related_hashtag'] . '" data-url="' . $url_hashtag . '" ' . $tailoring . ' target="_blank">' . __( 'Tweet', 'twitter-plugin' ) . ' #' . $twttr_options['hashtag'] . '</a>
					</div>';
				}
			} else {
				$hashtag = '';
			}
			if ( 1 == $mention ) {
				/* Option for mention button */
				if ( empty( $twttr_options['tweet_to_mention'] ) ) {
					$twttr_options['tweet_to_mention'] = 'support';
				}
				if ( 'page_title' == $twttr_options['text_option_mention'] ) {
					$text_mention = '';
				}
				$text_mention = ( 'page_title' == $twttr_options['text_option_mention'] ) ? htmlspecialchars( urlencode( $post->post_title ) ) : urlencode( $twttr_options['text_mention'] );
				/* Show mention button */
				$mention = '<div class="twttr_mention">
					<a href="https://twitter.com/intent/tweet?screen_name=' . $twttr_options['tweet_to_mention'] . '&text=' . $text_mention . '" class="twitter-mention-button" data-size="' . $twttr_options['size'] . '" ' . $lang . ' data-related="' . $twttr_options['related_mention'] . '" ' . $tailoring . ' target="_blank">' . __( 'Tweet to', 'twitter-plugin' ) . ' @' . $twttr_options['tweet_to_mention'] . '</a>
				</div>';
			} else {
				$mention = '';
			}
			return '<div class="twttr_buttons">' . $tweet . $follow . $hashtag . $mention . '</div>';
		}
	}
}

if ( ! function_exists( 'twttr_wp_head' ) ) {
	/**
	 * Connecting CSS styles
	 */
	function twttr_wp_head() {
		wp_enqueue_style( 'twttr_stylesheet', plugins_url( 'css/style.css', __FILE__ ), array(), '2.66' );
	}
}

if ( ! function_exists( 'twttr_admin_enqueue_scripts' ) ) {
	/**
	 * Connecting JS and CSS styles to dashboard
	 */
	function twttr_admin_enqueue_scripts() {
		global $twttr_plugin_info;
		wp_enqueue_style( 'twttr_icon', plugins_url( 'css/icon.css', __FILE__ ), array(), $twttr_plugin_info['Version'] );
		if ( isset( $_GET['page'] ) && ( 'twitter.php' == $_GET['page'] || 'social-buttons.php' == $_GET['page'] ) ) {
			wp_enqueue_style( 'twttr_stylesheet', plugins_url( 'css/style.css', __FILE__ ), array(), $twttr_plugin_info['Version'] );
			wp_enqueue_script( 'twttr_script', plugins_url( 'js/script.js', __FILE__ ), array( 'jquery' ), $twttr_plugin_info['Version'], true );

			bws_enqueue_settings_scripts();
			bws_plugins_include_codemirror();
		}
	}
}

if ( ! function_exists( 'twttr_api_scripts' ) ) {
	/**
	 * Connecting JS
	 */
	function twttr_api_scripts() {
		global $twttr_add_api_script, $twttr_plugin_info;
		if ( true == $twttr_add_api_script || defined( 'BWS_ENQUEUE_ALL_SCRIPTS' ) ) {
			$script_api = '!function(d,s,id) {
					var js,fjs=d.getElementsByTagName(s)[0];
					if (!d.getElementById(id)) {
						js=d.createElement(s);
						js.id=id;
						js.src="https://platform.twitter.com/widgets.js";
						fjs.parentNode.insertBefore(js,fjs);
					}
				}(document,"script","twitter-wjs")';
			wp_register_script( 'twttr_api_scripts_init', '', array(), $twttr_plugin_info['Version'], true );
			wp_enqueue_script( 'twttr_api_scripts_init' );
			wp_add_inline_script( 'twttr_api_scripts_init', sprintf( $script_api ) );
			$twttr_add_api_script = false;
		}
	}
}

if ( ! function_exists( 'twttr_pagination_callback' ) ) {
	/**
	 * Pagination_callback function
	 *
	 * @param string $content Content.
	 */
	function twttr_pagination_callback( $content ) {
		$content .= "if (typeof twttr !== 'undefined') { twttr.widgets.load(); }";
		return $content;
	}
}

if ( ! function_exists( 'twttr_shortcode_button_content' ) ) {
	/**
	 * Shortcode content
	 *
	 * @param string $content Content.
	 */
	function twttr_shortcode_button_content( $content ) {
		global $post, $twttr_plugin_info;
		?>
		<div id="twttr" style="display:none;">
			<fieldset>
				<?php esc_html_e( 'Please select twitter buttons which will be displayed', 'twitter-plugin' ); ?><br />
				<label><input type="checkbox" value="1" id="twttr_tweet" name="twttr_tweet"><?php esc_html_e( 'Tweet', 'twitter-plugin' ); ?></label><br />
				<label><input type="checkbox" value="1" id="twttr_followme" name="twttr_followme" checked="checked"><?php esc_html_e( 'Follow', 'twitter-plugin' ); ?></label><br />
				<label><input type="checkbox" value="1" id="twttr_hashtag" name="twttr_hashtag"><?php esc_html_e( 'Hashtag', 'twitter-plugin' ); ?></label><br />
				<label><input type="checkbox" value="1" id="twttr_mention" name="twttr_mention"><?php esc_html_e( 'Mention', 'twitter-plugin' ); ?></label><br />
			</fieldset>
			<input class="bws_default_shortcode" type="hidden" name="default" value="[twitter_buttons]" />
			<?php
			$script = "function twttr_shortcode_init() {
					( function( $ ) {
						var current_object = '.mce-reset';
						$( current_object + ' #twttr_tweet,' + current_object + ' #twttr_followme,' + current_object + ' #twttr_hashtag,' + current_object + ' #twttr_mention' ).on( 'change', function() {
							var tweet = ( $( current_object + ' #twttr_tweet' ).is( ':checked' ) ) ? 'tweet,' : '';
							var follow_me = ( $( current_object + ' #twttr_followme' ).is( ':checked' ) ) ? 'follow,' : '';
							var hashtag = ( $( current_object + ' #twttr_hashtag' ).is( ':checked' ) ) ? 'hashtag,' : '';
							var mention = ( $( current_object + ' #twttr_mention' ).is( ':checked' ) ) ? 'mention' : '';
							if ( tweet != '' || follow_me != '' || hashtag != '' || mention != '' ) {
								var shortcode = '[twitter_buttons display=' + tweet + follow_me + hashtag + mention + ']';
							} else {
								var shortcode = '';
							}
							$( current_object + ' #bws_shortcode_display' ).text( shortcode );
						} );
					} ) ( jQuery );
				}";
			wp_register_script( 'twttr_bws_shortcode_button', '', array(), $twttr_plugin_info['Version'], true );
			wp_enqueue_script( 'twttr_bws_shortcode_button' );
			wp_add_inline_script( 'twttr_bws_shortcode_button', sprintf( $script ) );
			?>
			<div class="clear"></div>
		</div>
		<?php
	}
}

if ( ! function_exists( 'twttr_action_links' ) ) {
	/**
	 * This links under plugin name
	 *
	 * @param array  $links All plugins links array.
	 * @param string $file  File name.
	 */
	function twttr_action_links( $links, $file ) {
		if ( ! is_network_admin() ) {
			/* Static so we don't call plugin_basename on every plugin row */
			static $this_plugin;
			if ( ! $this_plugin ) {
				$this_plugin = plugin_basename( __FILE__ );
			}
			if ( $file == $this_plugin ) {
				$settings_link = '<a href="admin.php?page=twitter.php">' . __( 'Settings', 'twitter-plugin' ) . '</a>';
				array_unshift( $links, $settings_link );
			}
		}
		return $links;
	}
}

if ( ! function_exists( 'twttr_links' ) ) {
	/**
	 * Added Settings, FAQ and Support links
	 *
	 * @param array  $links All plugins links array.
	 * @param string $file  File name.
	 */
	function twttr_links( $links, $file ) {
		$base = plugin_basename( __FILE__ );
		if ( $file == $base ) {
			if ( ! is_network_admin() ) {
				$links[] = '<a href="admin.php?page=twitter.php">' . __( 'Settings', 'twitter-plugin' ) . '</a>';
			}
			$links[]    = '<a href="https://support.bestwebsoft.com/hc/en-us/sections/200538889" target="_blank">' . __( 'FAQ', 'twitter-plugin' ) . '</a>';
			$links[]    = '<a href="https://support.bestwebsoft.com">' . __( 'Support', 'twitter-plugin' ) . '</a>';
		}
		return $links;
	}
}

if ( ! function_exists( 'twttr_plugin_banner' ) ) {
	/**
	 * Add plugin banners
	 */
	function twttr_plugin_banner() {
		global $hook_suffix, $twttr_plugin_info, $twttr_options;
		if ( 'plugins.php' == $hook_suffix ) {

			/* show banner go settings pls*/
			if ( ! is_network_admin() ) {
				bws_plugin_banner_to_settings( $twttr_plugin_info, 'twttr_options', 'twitter-plugin', 'admin.php?page=twitter.php' );
			}
		}

		if ( isset( $_GET['page'] ) && 'twitter.php' == $_GET['page'] ) {
			bws_plugin_suggest_feature_banner( $twttr_plugin_info, 'twttr_options', 'twitter-plugin' );
		}
	}
}

if ( ! function_exists( 'twttr_add_tabs' ) ) {
	/**
	 * Add help tab
	 */
	function twttr_add_tabs() {
		$screen = get_current_screen();
		$args = array(
			'id'            => 'twttr',
			'section'       => '200538889',
		);
		bws_help_tab( $screen, $args );
	}
}

if ( ! function_exists( 'twttr_delete_options' ) ) {
	/**
	 * Uninstall plugin
	 */
	function twttr_delete_options() {
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}
		$all_plugins = get_plugins();

		if ( ! array_key_exists( 'twitter-pro/twitter-pro.php', $all_plugins ) &&
			! array_key_exists( 'twitter-plus/twitter-plus.php', $all_plugins ) &&
			! array_key_exists( 'bws-social-buttons/bws-social-buttons.php', $all_plugins ) &&
			! array_key_exists( 'bws-social-buttons-pro/bws-social-buttons-pro.php', $all_plugins ) ) {

			global $wpdb;
			/* delete custom images if no PRO version */
			$upload_dir = wp_upload_dir();
			$twttr_cstm_mg_folder = $upload_dir['basedir'] . '/twitter-logo/';
			if ( is_dir( $twttr_cstm_mg_folder ) ) {
				$twttr_cstm_mg_files = scandir( $twttr_cstm_mg_folder );
				foreach ( $twttr_cstm_mg_files as $value ) {
					@unlink( $twttr_cstm_mg_folder . $value );
				}
				@rmdir( $twttr_cstm_mg_folder );
			}

			if ( function_exists( 'is_multisite' ) && is_multisite() ) {
				$old_blog = $wpdb->blogid;
				/* Get all blog ids */
				$blogids = $wpdb->get_col( "SELECT `blog_id` FROM $wpdb->blogs" );
				foreach ( $blogids as $blog_id ) {
					switch_to_blog( $blog_id );
					delete_option( 'twttr_options' );
				}
				switch_to_blog( $old_blog );
			} else {
				delete_option( 'twttr_options' );
			}
		}

		require_once( dirname( __FILE__ ) . '/bws_menu/bws_include.php' );
		bws_include_init( plugin_basename( __FILE__ ) );
		bws_delete_plugin( plugin_basename( __FILE__ ) );
	}
}
/* Plugin uninstall function */
register_activation_hook( __FILE__, 'twttr_plugin_activate' );
add_action( 'admin_menu', 'twttr_add_admin_menu' );
/* Initialization */
add_action( 'plugins_loaded', 'twttr_plugins_loaded' );
add_action( 'init', 'twttr_init' );
/*admin_init */
add_action( 'admin_init', 'twttr_admin_init' );
/* Adding stylesheets */
add_action( 'wp_enqueue_scripts', 'twttr_wp_head' );
add_action( 'wp_footer', 'twttr_api_scripts' );
add_filter( 'pgntn_callback', 'twttr_pagination_callback' );
add_action( 'admin_enqueue_scripts', 'twttr_admin_enqueue_scripts' );
/* Adding plugin buttons */
add_shortcode( 'follow_me', 'twttr_twitter_buttons' );
add_shortcode( 'twitter_buttons', 'twttr_twitter_buttons' );
add_filter( 'widget_text', 'do_shortcode' );
add_filter( 'the_content', 'twttr_twit' );
/* custom filter for bws button in tinyMCE */
add_filter( 'bws_shortcode_button_content', 'twttr_shortcode_button_content' );
/*## Additional links on the plugin page */
add_filter( 'plugin_action_links', 'twttr_action_links', 10, 2 );
add_filter( 'plugin_row_meta', 'twttr_links', 10, 2 );
/* Adding banner */
add_action( 'admin_notices', 'twttr_plugin_banner' );
