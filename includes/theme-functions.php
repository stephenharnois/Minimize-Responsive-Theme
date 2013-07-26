<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;


/**
 * SDS Theme Options Functions
 *
 * Description: This file contains functions for utilizing options within themes (displaying site logo, tagline, etc...)
 *
 * @version 1.0
 */


// Globalize Theme options
$sds_theme_options = SDS_Theme_Options::get_sds_theme_options();

/**
 * This function displays either a logo, or the site title depending on options.
 *
 * @uses site_url()
 * @uses get_bloginfo()
 * @uses wp_get_attachment_image()
 */
if ( ! function_exists( 'sds_logo' ) ) {
	function sds_logo() {
		global $sds_theme_options;

		// Logo
		if ( ! empty( $sds_theme_options['logo_attachment_id'] ) ) :
	?>
		<h1 id="title" class="site-title site-title-logo has-logo">
			<a href="<?php echo site_url(); ?>" title="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
				<?php echo wp_get_attachment_image( $sds_theme_options['logo_attachment_id'], 'full' ); ?>
			</a>
		</h1>
	<?php
		else : // No logo
	?>
		<h1 id="title" class="site-title site-title-logo has-logo">
			<a href="<?php echo site_url(); ?>" title="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
				<?php bloginfo( 'name' ); ?>
			</a>
		</h1>
	<?php
		endif;
	}
}

/**
 * This function displays the site tagline, optionally with a CSS class to hide it depending on options.
 *
 * @uses bloginfo()
 */
if ( ! function_exists( 'sds_tagline' ) ) {
	function sds_tagline() {
		global $sds_theme_options;
	?>
		<h2 id="slogan" class="slogan <?php echo ( $sds_theme_options['hide_tagline'] ) ? 'hide hidden hide-tagline hide-slogan' : false; ?>">
			<?php bloginfo( 'description' ); ?>
		</h2>
	<?php
	}
}

/**
 * This function displays featured images based on options.
 *
 * @param $link_image, Boolean, link featured image to post.
 *
 * @uses the_permalink()
 * @uses has_post_thumbnail()
 * @uses the_post_thumbnail()
 */
if ( ! function_exists( 'sds_featured_image' ) ) {
	function sds_featured_image( $link_image = false ) {
		$featured_image_size = apply_filters( 'sds_theme_options_default_featured_image_size', '' );

		// Featured Image
		if ( has_post_thumbnail() && $link_image ) :
	?>
		<figure class="post-image <?php echo $featured_image_size . '-featured-image ' . $featured_image_size . '-post-image'; ?>">
			<a href="<?php the_permalink(); ?>">
				<?php the_post_thumbnail( $featured_image_size ); ?>
			</a>
		</figure>
	<?php
		else :
	?>
		<figure class="post-image <?php echo $featured_image_size . '-featured-image ' . $featured_image_size . '-post-image'; ?>">
			<?php the_post_thumbnail( $featured_image_size ); ?>
		</figure>
	<?php
		endif;
	}
}

/**
 * This function enqueues all necessary scripts/styles based on options.
 */
if ( ! function_exists( 'sds_wp_enqueue_scripts' ) ) {
	add_action( 'wp_enqueue_scripts', 'sds_wp_enqueue_scripts' );

	function sds_wp_enqueue_scripts() {
		global $sds_theme_options;

		// Color Schemes
		if ( function_exists( 'sds_color_schemes' ) && ! empty( $sds_theme_options['color_scheme'] ) ) {
			$color_schemes = sds_color_schemes();

			if ( ! empty( $sds_theme_options['color_scheme'] ) && isset( $color_schemes[$sds_theme_options['color_scheme']] ) ) {
				$selected_color_scheme = array_key_exists( $sds_theme_options['color_scheme'], $color_schemes ) ? $color_schemes[$sds_theme_options['color_scheme']] : false;

				// Make sure this is not the default color scheme
				if ( ! empty( $selected_color_scheme ) && ( ! isset( $selected_color_scheme['default'] ) || ! $selected_color_scheme['default'] ) )
					wp_enqueue_style( $selected_color_scheme['deps'] . '-' . $sds_theme_options['color_scheme'], get_template_directory_uri() . $selected_color_scheme['stylesheet'], array( $selected_color_scheme['deps'] ) );
			}
		}

		// Theme Option Fonts (Social Media)
		if ( ! empty( $sds_theme_options['social_media'] ) ) {
			$social_networks_active = false;

			foreach( $sds_theme_options['social_media'] as $network => $url )
				if ( ! empty( $url ) ) {
					$social_networks_active = true;
					break;
				}

			if ( $social_networks_active ) {
				wp_enqueue_style( 'font-awesome-css-min', get_template_directory_uri() . '/includes/css/font-awesome.min.css' );
				wp_enqueue_style( 'sds-theme-options-fonts', get_template_directory_uri() . '/includes/css/sds-theme-options-fonts.css', array( 'font-awesome-css-min' ) );
			}
		}

		// Comment Replies
		if ( is_singular() )
			wp_enqueue_script( 'comment-reply' );
	}
}

/**
 * This function outputs necessary scripts/styles in the head section based on options (web font, custom scripts/styles).
 */
if ( ! function_exists( 'sds_wp_head' ) ) {
	add_action( 'wp_head', 'sds_wp_head' );

	function sds_wp_head() {
		global $is_IE;

		// HTML5 Shiv (IE only, conditionally for less than IE9)
		if ( $is_IE )
			echo '<!--[if lt IE 9]><script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script><![endif]-->';
	}
}

/**
 * This function configures/sets up theme options/features.
 */
if ( ! function_exists( 'sds_after_setup_theme' ) ) {
	add_action( 'after_setup_theme', 'sds_after_setup_theme' );

	function sds_after_setup_theme() {
		// Enable Featured Images
		add_theme_support( 'post-thumbnails' );

		// Enable Automatic Feed Links
		add_theme_support( 'automatic-feed-links' );

		// Enable excerpts on Pages
		add_post_type_support( 'page', 'excerpt' );

		// Register WordPress Menus
		register_nav_menus( array(
			'top_nav' => 'Top Navigation',
			'primary_nav' => 'Primary Navigation',
			'footer_nav' => 'Footer Navigation'
		) );
	}
}

/**
 * This function configures sidebars for use throughout the theme
 */
if ( ! function_exists( 'sds_widgets_init' ) ) {
	add_action( 'widgets_init', 'sds_widgets_init' );

	function sds_widgets_init() {
		// Register SDS Social Media Widget (/includes/widget-social-media.php)
		register_widget( 'SDS_Social_Media_Widget' );

		// Primary sidebar
		register_sidebar( array(
			'name'          => 'Primary Sidebar',
			'id'            => 'primary-sidebar',
			'description'   => 'This widget area is the primary widget area.',
			'before_widget' => '<div id="primary-sidebar-%1$s" class="widget primary-sidebar %2$s">',
			'after_widget'  => '<div class="clear"></div></div>',
			'before_title'  => '<h3 class="widgettitle">',
			'after_title'   => '</h3>',
		) );

		// Front Page Slider
		register_sidebar( array(
			'name'          => 'Front Page Slider',
			'id'            => 'front-page-slider-sidebar',
			'description'   => '*This widget area is only displayed if a Front Page is selected via Settings > Reading in the Dashboard. Specifically formatted for Soliloquy or SlideDeck sliders.* This widget area is displayed above the content on the Front Page.',
			'before_widget' => '<section id="front-page-slider-%1$s" class="front-page-slider slider %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h3 class="widgettitle widget-title front-page-slider-title">',
			'after_title'   => '</h3>'
		) );

		// Header Call To Action
		register_sidebar( array(
			'name'          => 'Header Call To Action',
			'id'            => 'header-call-to-action-sidebar',
			'description'   => 'This widget area is used to display a call to action in the header',
			'before_widget' => '<div id="header-call-to-action-%1$s" class="widget header-call-to-action-widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<h3 class="widgettitle">',
			'after_title'   => '</h3>',
		) );

		// After Posts
		register_sidebar( array(
			'name'          => 'After Posts',
			'id'            => 'after-posts-sidebar',
			'description'   => 'This widget area is displayed below the content on single posts only.',
			'before_widget' => '<section id="after-posts-%1$s" class="after-posts after-posts-widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h3 class="widgettitle widget-title after-posts-title">',
			'after_title'   => '</h3>'
		) );

		// Footer
		register_sidebar( array(
			'name'          => 'Footer',
			'id'            => 'footer-sidebar',
			'description'   => 'Tis widget area is displayed in the footer of all pages.',
			'before_widget' => '<section id="footer-widget-%1$s" class="footer-widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h3 class="widgettitle widget-title footer-widget-title">',
			'after_title'   => '</h3>'
		) );

		// Copyright
		register_sidebar( array(
			'name'          => 'Copyright Area',
			'id'            => 'copyright-area-sidebar',
			'description'   => 'This widget area is designed for small text blurbs or disclaimers at the bottom of the website.',
			'before_widget' => '<div id="copyright-area-widget-%1$s" class="widget copyright-area-widget %2$s">',
			'after_widget'  => '<div class="clear"></div></div>',
			'before_title'  => '<h3 class="widgettitle">',
			'after_title'   => '</h3>',
		) );
	}
}

/**
 * This function outputs a fallback menu and is used when the Primary Menu is inactive.
 */
if ( ! function_exists( 'sds_primary_menu_fallback' ) ) {
	function sds_primary_menu_fallback() {
		wp_page_menu( array(
			'depth'       => 1,
			'sort_column' => 'menu_order, post_title',
			'menu_class'  => 'primary-nav menu',
			'include'     => '',
			'exclude'     => '',
			'echo'        => true,
			'show_home'   => true,
			'link_before' => '',
			'link_after'  => ''
		) );
	}
}

/**
 * This function outputs the Primary Sidebar.
 */
if ( ! function_exists( 'sds_primary_sidebar' ) ) {
	function sds_primary_sidebar() {
		if ( is_active_sidebar( 'primary-sidebar' ) )
			dynamic_sidebar( 'primary-sidebar' );
	}
}

/**
 * This function outputs the Front Page Slider Sidebar.
 */
if ( ! function_exists( 'sds_front_page_slider_sidebar' ) ) {
	function sds_front_page_slider_sidebar() {
		if ( is_active_sidebar( 'front-page-slider-sidebar' ) )
			dynamic_sidebar( 'front-page-slider-sidebar' );
	}
}

/**
 * This function outputs the Header Call to Action Sidebar.
 */
if ( ! function_exists( 'sds_header_call_to_action_sidebar' ) ) {
	function sds_header_call_to_action_sidebar() {
		if ( is_active_sidebar( 'header-call-to-action-sidebar' ) )
			dynamic_sidebar( 'header-call-to-action-sidebar' );
	}
}

/**
 * This function outputs the After Posts Sidebar.
 */
if ( ! function_exists( 'sds_after_posts_sidebar' ) ) {
	function sds_after_posts_sidebar() {
		if ( is_active_sidebar( 'after-posts-sidebar' ) )
			dynamic_sidebar( 'after-posts-sidebar' );
	}
}

/**
 * This function outputs the Footer Sidebar.
 */
if ( ! function_exists( 'sds_footer_sidebar' ) ) {
	function sds_footer_sidebar() {
		if ( is_active_sidebar( 'footer-sidebar' ) )
			dynamic_sidebar( 'footer-sidebar' );
	}
}

/**
 * This function outputs the Copyright Area Sidebar.
 */
if ( ! function_exists( 'sds_copyright_area_sidebar' ) ) {
	function sds_copyright_area_sidebar() {
		if ( is_active_sidebar( 'copyright-area-sidebar' ) )
			dynamic_sidebar( 'copyright-area-sidebar' );
	}
}


/**
 * This function outputs a sitemap (most typically found on a 404 template).
 */
if ( ! function_exists( 'sds_sitemap' ) ) {
	function sds_sitemap() {
	?>
	<section class="sds-sitemap sitemap">
		<section class="sitemap-pages page-list">
			<h2 title="Pages">Pages</h2>
			<ul>
				<?php wp_list_pages( array( 'title_li' => '' ) ); ?>
			</ul>
		</section>

		<section class="sitemap-archives sitemap-monthly-archives monthly-archives archive-list">
			<h2 title="Monthly Archives">Monthly Archives</h2>
			<ul>
				<?php wp_get_archives(); ?>
			</ul>
		</section>

		<section class="sitemap-categories category-list">
			<h2 title="Blog Categories">Blog Categories</h2>
			<ul>
				<?php wp_list_categories( array( 'title_li' => '' ) ); ?>
			</ul>
		</section>


		<?php
			// Output all public post types except attachments and pages (see above for pages)
			foreach( get_post_types( array( 'public' => true ) ) as $post_type ) {
				if ( ! in_array( $post_type, array( 'attachment', 'page' ) ) ) {
				$post_type_object = get_post_type_object( $post_type );

				$query = new WP_Query( array(
					'post_type' => $post_type,
					'posts_per_page' => wp_count_posts( $post_type )->publish
				) );

				if( $query->have_posts() ) :
				?>
					<section class="sitemap-post-type-list sitemap-<?php echo $post_type_object->name; ?>-list post-type-list">
						<h2 title="<?php echo esc_attr( $post_type_object->labels->name ); ?>">
							<?php echo $post_type_object->labels->name; ?>
						</h2>

						<ul>
							<?php while( $query->have_posts() ) : $query->the_post(); ?>
								<li>
									<a href="<?php echo get_permalink(); ?>"><?php the_title(); ?></a>
								</li>
							<?php endwhile; ?>
						</ul>
					</div><!-- end post-type-list -->
				<?php
				endif;
			}
		}
	}
}

/**
 * This function outputs a title for Archive page templates.
 */
if ( ! function_exists( 'sds_archive_title' ) ) {
	function sds_archive_title() {
		// Author
		if ( is_author() ) :
			$author = get_user_by( 'slug', get_query_var( 'author_name' ) );
			?>
				<h1 title="<?php echo $author_text; ?> Archive: <?php echo esc_attr( $author->display_name ); ?>" class="page-title">
					Author Archive: <?php echo $author->display_name; ?>
				</h1>
			<?php
		// Categories
		elseif ( is_category() ) :
		?>
			<h1 title="<?php single_cat_title( 'Category Archive: ' ); ?>" class="page-title">
				<?php single_cat_title( 'Category Archive: ' ); ?>
			</h1>
		<?php 
		// Tags
		elseif ( is_tag() ) :
		?>
			<h1 title="<?php single_tag_title( 'Tag Archive: ' ); ?>" class="page-title">
				<?php single_tag_title( 'Tag Archive: ' ); ?>
			</h1>
		<?php
		// Daily Archives
		elseif ( is_day() ) :
			$the_date = get_the_date();
		?>
			<h1 title="Daily Archives: <?php echo $the_date; ?>" class="page-title">
				Daily Archives: <?php echo $the_date; ?>
			</h1>
		<?php
		// Monthly Archives
		elseif ( is_month() ) :
			$the_date = get_the_date( 'F Y' );
		?>
			<h1 title="Monthly Archives <?php echo $the_date; ?>" class="page-title">
				Monthly Archives: <?php echo $the_date; ?>
			</h1>
		<?php
		// Yearly Archives
		elseif ( is_year() ) :
			$the_date = get_the_date( 'Y' );
		?>
			<h1 title="Yearly Archives <?php echo $the_date; ?>" class="page-title">
				Yearly Archives: <?php echo $the_date; ?>
			</h1>
		<?php
		endif;
	}
}

/**
 * This function outputs a "no posts" message when no posts are found in a The Loop.
 */
if ( ! function_exists( 'sds_no_posts' ) ) {
	function sds_no_posts() {
	?>
		<section class="no-results no-posts">
			<p>We were not able to find any posts. Please try again.</p>
		</section>
	<?php
	}
}

/**
 * This function outputs next/prev navigation on single posts.
 */
if ( ! function_exists( 'sds_single_post_navigation' ) ) {
	function sds_single_post_navigation() {
	?>
		<section class="single-post-navigation post-navigation">
			<section class="previous-posts">
				<?php next_post_link( '%link', '&laquo; %title' ); ?>
			</section>
			<section class="next-posts">
				<?php previous_post_link( '%link', '%title &raquo;' ); ?>
			</section>
		</section>
	<?php
	}
}

/**
 * This function outputs next/prev navigation on single image attachments.
 */
if ( ! function_exists( 'sds_single_image_navigation' ) ) {
	function sds_single_image_navigation() {
	?>
		<section class="single-post-navigation post-navigation">
			<section class="previous-posts">
				<?php previous_image_link( false, '&laquo; Previous Image' ); ?>
			</section>
			<section class="next-posts">
				<?php next_image_link( false, 'Next Image &raquo;' ); ?>
			</section>
		</section>
	<?php
	}
}

/**
 * This function outputs the site's copyright as well as the SDS copyright.
 */
if ( ! function_exists( 'sds_copyright' ) ) {
	function sds_copyright() {
	?>
		<span class="site-copyright">
			<?php echo apply_filters( 'sds_copyright', 'Copyright &copy; ' . date( 'Y' ) . ' <a href="' . home_url() . '" title="' . get_bloginfo( 'name' ) . '">' . get_bloginfo( 'name' ) . '</a>. All Rights Reserved.' ); ?>
		</span>
		<span class="slocum-credit">
			<?php echo apply_filters( 'sds_copyright_branding', '<a href="http://slocumstudio.com/?utm_source=' . home_url() . '&amp;utm_medium=footer-plugs&amp;utm_campaign=WordPressThemes" target="_blank">WordPress theme by Slocum Design Studio</a>' ); ?>
		</span>
	<?php
	}
}

/**
 * This function outputs a list of selected social networks based on options. Can be called throughout the theme and is used in the Social Media Widget.
 */
if ( ! function_exists( 'sds_social_media' ) ) {
	function sds_social_media() {
		global $sds_theme_options;

		if ( ! empty( $sds_theme_options['social_media'] ) ) {
			// Map the correct values for social icon display (JustVector webfont, i.e. 'r' = RSS icon)
			$social_font_map = array(
				'facebook_url' => 'icon-facebook-sign',
				'twitter_url' => 'icon-twitter-sign',
				'linkedin_url' => 'icon-linkedin-sign',
				'google_plus_url' => 'icon-google-plus-sign',
				'youtube_url' => 'icon-youtube-sign',
				'vimeo_url' => 'icon-play',
				'instagram_url' => 'icon-instagram-sign',
				'pinterest_url' => 'icon-pinterest-sign',
				//'yelp_url' => '',
				'foursquare_url' => 'icon-foursquare',
				'rss_url' => 'icon-rss'
			);

			$social_font_map = apply_filters( 'sds_social_icon_map', $social_font_map );
		?>
			<section class="social-media-icons">
			<?php
				foreach( $sds_theme_options['social_media'] as $key => $url ) :
					// RSS (use site RSS feed, $url is Boolean this case)
					if ( $key === 'rss_url_use_site_feed' && $url ) :
					?>
						<a href="<?php bloginfo( 'rss2_url' ); ?>" class="rss_url <?php echo $social_font_map['rss_url']; ?>" target="_blank"></a>
					<?php
					// RSS (use custom RSS feed)
					elseif ( $key === 'rss_url' && ! $sds_theme_options['social_media']['rss_url_use_site_feed'] && ! empty( $url ) ) :
					?>
						<a href="<?php echo esc_attr( $url ); ?>" class="rss_url <?php echo $social_font_map['rss_url']; ?>" target="_blank"></a>
					<?php
					// All other networks
					elseif ( $key !== 'rss_url_use_site_feed' && $key !== 'rss_url' && ! empty( $url ) ) :
					?>
						<a href="<?php echo esc_url( $url ); ?>" class="<?php echo $key; ?> <?php echo $social_font_map[$key]; ?>" target="_blank" rel="me"></a>
					<?php
					endif;
				endforeach;
			?>
			</section>
		<?php
		}
	}
}

/**
 * This function displays meta for the current post (including categories and tags).
 */
if ( ! function_exists( 'sds_post_meta' ) ) {
	function sds_post_meta() {
		$cats = get_the_category();
		$tags = get_the_tags();

		// Categories and no tags
		if ( $cats && ! $tags ) :
		?>
			<p>This entry was posted in <?php the_category( ', ', 'multiple' ); ?>.</p>
		<?php
		// Categories and tags
		else:
		?>
			<p>This entry was posted in <?php the_category( ', ', 'multiple' ); ?>
		<?php
		endif;

		// Tags and categories
		if ( $tags && $cats ) :
		?>
			and tagged in <?php the_tags( '', ', ' ); ?>.</p>
		<?php
		// Tags and no categories
		elseif ( $tags && ! $cats ) :
		?>
			<p>This entry was tagged in <?php the_tags( '', ', ' ); ?>.</p>
		<?php
		endif;
	}
}


/*
 * This function displays pagination links based on arguments
 * @uses paginate_links for output
 */
if ( ! function_exists( 'sds_post_navigation' ) ) {
	function sds_post_navigation( $return = false ) {
		global $wp_query, $post;

		$pagination_links = paginate_links( array(
			'base' => esc_url( get_pagenum_link() ) . '%_%', // %_% will be replaced with format below
			'format' => ( ! $wp_query->is_search ) ? '?paged=%#%' : '&paged=%#%', // %#% will be replaced with page number
			'current' => max( 1, get_query_var('paged') ), // Get whichever is the max out of 1 and the current page count
			'total' => $wp_query->max_num_pages, // Get total number of pages in current query
			'next_text' => 'Next &#8594;',
			'prev_text' => '&#8592; Previous',
			'type' => ( $return ) ? 'array' : 'list'  // Output this as an unordered list
		) );

		if( $return )
			return $pagination_links;
		else
			echo $pagination_links;
	}
}

/**
 * Template for comments and pingbacks.
 *
 * Used as a callback by wp_list_comments() for displaying the comments.
 *
 * @param object $comment Comment to display.
 * @param array $args Optional args.
 * @param int $depth Depth of comment.
 */
if ( ! function_exists( 'sds_comment' ) ) {
	function sds_comment( $comment, $args, $depth ) {
		$GLOBALS['comment'] = $comment;
		switch ( $comment->comment_type ) :
			case 'pingback' :
			case 'trackback' :
			// Display trackbacks differently than normal comments.
		?>
		<li id="comment-<?php comment_ID(); ?>" <?php comment_class(); ?>>
			<p>Pingback: <?php comment_author_link(); ?> <?php edit_comment_link( 'Edit', '<span class="ping-meta"><span class="edit-link">', '</span></span>' ); ?></p>
		<?php
				break;
			default :
			// Proceed with normal comments.
		?>
		<li id="li-comment-<?php comment_ID(); ?>">
			<article id="comment-<?php comment_ID(); ?>" <?php comment_class(); ?>>
				<section class="comment-author vcard">
					<section class="author-details">
						<?php echo get_avatar( $comment, 74 ); ?>
						<span class="author-link"><?php comment_author_link(); ?></span>
						<br />
						<header class="comment-meta">
							<cite class="fn">
								<?php
										printf( '<a href="%1$s"><time datetime="%2$s" itemprop="commentTime">%3$s</time></a>',
											esc_url( get_comment_link( $comment->comment_ID ) ),
											get_comment_time( 'c' ),
											sprintf( '%1$s at %2$s', get_comment_date(), get_comment_time() )
										);

								?>

								<?php edit_comment_link( 'Edit', '<span class="edit-link">', '<span>' ); ?>
							</cite>
						</header>
					</section>
				</section>

				<section class="comment-content-container">
					<?php if ( '0' == $comment->comment_approved ) : ?>
						<p class="comment-awaiting-moderation">Your comment is awaiting moderation.</p>
					<?php endif; ?>

					<section class="comment-content">
						<?php comment_text(); ?>
					</section>
				</section>

				<section class="clear">&nbsp;</section>

				<section class="reply">
					<?php comment_reply_link( array_merge( $args, array( 'reply_text' => 'Reply', 'depth' => $depth, 'max_depth' => $args['max_depth'] ) ) ); ?>
				</section>
			</article>
		<?php
			break;
		endswitch;
	}
}