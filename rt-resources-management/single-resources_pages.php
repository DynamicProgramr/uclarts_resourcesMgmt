<?php
/**
 * Single resources_pages template.
 * for custom post type: resources_pages
 * this template should really never get used. the function 'rt_single_template' should redirect.
 * it uses lots of meta data stuff
 *
 *  Template Author: Russ Thompson, Freelance I.T. Solutions
 *
 * @package      QueryLoop
 * @subpackage   Brio
 * @author       Russ Thompson, Freelance I.T. Solutions
 * @copyright    Copyright (c) Freelance I.T. Solutions  freelanceitsolutions.com
 * @license      http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since        1.0 
*/

$ql_post_type = get_post_type();

// first make sure this is the post type I want here (should be check in functions.php, but just in case)
if($ql_post_type == "resources_pages")
{
	$theHeader = "<header class=\"page-header russ_resources_pages\">";
	
	get_header( 'archive' ); ?>
	
	<?php echo $theHeader; ?>
			
				<?php queryloop_page_header_start(); // Action Hook ?>
	
				
	
				<?php queryloop_page_header_end(); // Action Hook ?>
	</header><!-- .entry-header -->
	
	<?php queryloop_content_before(); // Action Hook ?>
	
	<div id="content" class="site-main resourcesPages" role="main">
	
		<?php if ( is_page() ): ?>
	
			<?php if ( have_posts() ) : ?>
	
				<?php while ( have_posts() ) : the_post(); ?>
	
					<div class="entry-content">
						<span style="font-weight: bold;">This is the single template resources page.</span><br />
						<?php the_content(); ?>
	
					</div>
	
				<?php endwhile; ?>
	
			<?php endif; ?>
	
		<?php endif ?>
	
		<?php queryloop_content_end(); // Action Hook ?>
	
	</div><!-- #content -->
	
	<?php get_sidebar(); ?>
				
	<?php get_footer( 'page' ); ?>
<?php
}
else
{
	?>
	<h1>This is not a resources page.</h1>
	<?php
}
?>