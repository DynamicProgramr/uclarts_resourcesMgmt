<?php
/**
 * Functions
 *
 * @package      QueryLoop
 * @subpackage   Brio
 * @author       QueryLoop <hello@queryloop.com>
 * @copyright    Copyright (c) QueryLoop
 * @license      http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since        1.0
*/

/**
 * File version control:
 * Version Control begins on 02-19-2019
 * 
 * Version: 0.0.1 file as it was on 02-18-2019
 * Version: 0.0.2 adding custom carat display code to programs section - specifically 'function rt_ourPrograms_detail_func($atts)'
 */

/*
|--------------------------------------------------------------------------
| Theme defines
|--------------------------------------------------------------------------
*/

define( 'QUERYLOOP_THEME_VERSION', wp_get_theme()->display('Version'));
define( 'QUERYLOOP_THEME_DIR', get_template_directory() );
define( 'QUERYLOOP_THEME_URI', get_template_directory_uri() );
define( 'QUERYLOOP_DIR', QUERYLOOP_THEME_DIR . '/queryloop/' );
define( 'QUERYLOOP_URI', QUERYLOOP_THEME_URI . '/queryloop/' );

/*
|--------------------------------------------------------------------------
| QueryLoop Framework
|--------------------------------------------------------------------------
*/

if ( ! isset( $content_width ) ) {
    $content_width = 1020;
}

// Require the QueryLoop Framework
require_once QUERYLOOP_DIR . 'ql-core.php';

/*
|--------------------------------------------------------------------------
| Functions files that are specific for this theme
|--------------------------------------------------------------------------
*/

// Theme filterable functions
$filterable_includes = array(
    'includes/options.php',
    'includes/functions.php',
    'includes/comments.php',
    'includes/js.php',
    'includes/css.php',
    'includes/sidebars.php',
    'includes/banners.php',
    'includes/highlights.php',
    'includes/forms.php',
    'includes/sliders.php',
    'includes/testimonials.php',
    'includes/widgets.php',
    'includes/hooks.php'
);

// Allow child themes and plugins to filter the includes
$includes = apply_filters( 'ql_theme_includes', $filterable_includes );
        
// Include the theme functions files		
foreach ( $includes as $include ) { 
    locate_template( $include, true ); 
}

/*
|--------------------------------------------------------------------------
| Added for child theme
|--------------------------------------------------------------------------
*/

/* getting WP to use the ucla custom scripts */
function rt_ucla_custom_scripts()
{
    // register the script
    wp_register_script("ucla-custom-script", get_stylesheet_directory_uri()."/js/ucla_custom.js", array('jquery'), "04132015", false);
    
    // enqueue the script
    wp_enqueue_script("ucla-custom-script");
} // end rt_ucla_custom_scripts function

add_action("wp_enqueue_scripts", "rt_ucla_custom_scripts");

function rt_ucla_custom_scripts_footer()
{
    // for code that has to run last
    // register the script
    wp_register_script("ucla-custom-script-footer", get_stylesheet_directory_uri()."/js/ucla_custom_footer.js", array('jquery'), "10122017", true);
    
    // enqueue the script
    wp_enqueue_script("ucla-custom-script-footer");
} // end rt_ucla_custom_scripts function

add_action("wp_enqueue_scripts", "rt_ucla_custom_scripts_footer");

/* these lines turn off the auto update for plugins and the theme */
add_filter("auto_update_plugin", "__return_false");
add_filter("auto_update_theme", "__return_false");

function get_displayCategoryList ()
{
    $args = array(	"type"			=>	"post",
                "child_of"		=>	0,
                "parent"			=>	"",
                "orderby"			=>	"name",
                "order"			=>	"ASC",
                "hide_empty"		=>	1,
                "hierarchical"		=>	1,
                "exclude"			=>	"",
                "include"			=>	"",
                "number"			=>	"",
                "taxonomy"		=>	"category",
                "pad_counts"		=>	false);
    
    $categories = get_categories($args);
    $printThis = "";
    
    if ($categories)
    {
        foreach($categories AS $category)
        {
            $printThis .= "ID: ".$category->term_id.", Name: ".$category->name.", Cat Name: ".$category->cat_name."<br />";
        }
    }
    else
    {
        $printThis = "Error: No Category Returned";
    }
    
    print $printThis;
} // end get_displayCategoryList

function rt_replaceNewLine($passedString)
{
    // this is not actually removing a new line (\n, \r, \r\n, or \n\r)
    // this converts an actual slash + an n ( or two slashes + an n) when they were saved to the database as literal charactors
    // this function puts in a <br />.  below function puts in a space.
    $returnString = str_replace("\\n", "<br />", $passedString);
    $returnString = str_replace("\n", "<br />", $returnString);
    
    return $returnString;
} //end rt_replaceNewLine function

function rt_removeNewLine($passedString)
{
    // this is not actually removing a new line (\n, \r, \r\n, or \n\r)
    // this converts an actual slash + an n ( or two slashes + an n) when they were saved to the database as literal charactors
    // this function puts in a space, above function puts in a <br />.
    // for testing ->$returnString = "Working... ".$passedString;
    $returnString = str_replace("\\n", " ", $passedString);
    $returnString = str_replace("\n", " ", $returnString);
    $returnString = str_replace(".nn", ". ", $returnString); // this is because of a weird import thing that happened with the mass import of resources.
    $returnString = str_replace(". nn", ". ", $returnString); // another weird import thing... ugh.
    
    return $returnString;
} //end rt_removeNewLine function

// Display 24 products per page. Goes in functions.php
add_filter( 'loop_shop_per_page', create_function( '$cols', 'return 24;' ), 20 );

function getHomePagePosts ($catId, $howMany = 5, $linkCopy = "details")
{
    /* this function takes the post catagory number and the number of posts that the client wants returned.
     * it uses that info to get the title, excerpt and permalink from the db, formats it and prints it for slider display on the home page.
     * also used is basic-jquery-slider.  the code for this is included in the styles.css and the ql-theme-scripts.js files for this child. */
    
    $args = array(	"posts_per_page"		=>	$howMany,
                "category"			=>	$catId,
                "orderby"				=>	"post_date",
                "order"				=>	"DESC");
    
    $thePosts = get_posts($args);
    
    foreach($thePosts AS $post)
    {
        setup_postdata($post);
        
        if(strpos(the_title(), "-") !== FALSE)
        {
            $titlesArray = explode("-", the_title());
            
            $titleStuff = "	<span class=\"cat-".$catId."-month\">".trim($titlesArray[0])."</span><br />
                            <br />
                            <span class=\"cat-".$catId."-title\">".trim($titlesArray[1])."</span><br />";
        }
        else
        {
            $titleStuff = "	<span class=\"cat-".$catId."-title\">".the_title()."</span><br />";
        }
        
        $printThis .= "	<li>
                            ".$titleStuff."
                            <br />
                            ".the_excerpt()."<br />
                            <span class=\"cat-".$catId."-link\"><a href=\"".the_permalink()."\">".$linkCopy."</a></span>
                        </li>";
    } // end foreach loop
    
    print $printThis;
} // end getHomePagePosts function

// wasn't sure if I should put this with WooCommerce stuff or with other shortcode functions. ended up here with other shortcodes
// this is for external product detail pages.  UCLAandH staff will add shortcode to the products detail manually to display a green
// 'buy now' button or a gray 'out of stock' button.

function rt_get_external_buy_btn()
{
    global $product;
    $printThis = "";  // what gets returned
    
    $printThis = apply_filters("woocommerce_loop_add_to_cart_link",
            sprintf("<a href=\"%s\" rel=\"nofollow\" target=\"_blank\" data-product_id=\"%s\" data-product_sku=\"%s\" data-quantity=\"%s\" class=\"%s product_type_%s\"></a>",
                esc_url($product->add_to_cart_url()),
                esc_url($product->id),
                esc_url($product->get_sku()),
                esc_url(isset($quantity) ? $quantity : 1),
                $product->is_purchasable() && $product->is_in_stock() ? 'button_rt_16 add_to_cart_button' : 'button_rt_16',
                esc_attr($product->product_type),
                esc_html($product->add_to_cart_text())
            ),
        $product);
    
    print $printThis;
}

function rt_get_external_out_btn()
{
    global $product;
    $printThis = "";  // what gets returned
    
    $printThis = apply_filters("woocommerce_loop_add_to_cart_link",
            sprintf("<a href=\"%s\" rel=\"nofollow\" target=\"_blank\" data-product_id=\"%s\" data-product_sku=\"%s\" data-quantity=\"%s\" class=\"%s product_type_%s\"></a>",
                esc_url($product->add_to_cart_url()),
                esc_url($product->id),
                esc_url($product->get_sku()),
                esc_url(isset($quantity) ? $quantity : 1),
                $product->is_purchasable() && $product->is_in_stock() ? 'button_rt_14 add_to_cart_button' : 'button_rt_14',
                esc_attr($product->product_type),
                esc_html($product->add_to_cart_text())
            ),
        $product);
    
    print $printThis;
}

function rt_external_product_detail($atts)
{
    global $product;
    $printThis = "";  // what gets returned
    
    $passedBtn = shortcode_atts(array("the_button" => "buy"), $atts);
    
    switch($passedBtn['the_button'])
    {
        case "buy":
        default:
            add_action("rt_external_button", "rt_get_external_buy_btn", 10);
        break;
        
        case "out":
            add_action("rt_external_button", "rt_get_external_out_btn", 10);
        break;
    } // end switch
    
    return;
} // end rt_external_product_detail function

add_shortcode("rt_externalProductBtn", "rt_external_product_detail");

// this is so that we can use shortcodes in sidebar widgets
add_filter ("widget_text", "do_shortcode");

function rt_faq_page()
{
    $printThis = ""; // this is what gets returned
    $printThisFirst = ""; // this will hold the stuff with a cat name of 'general'
    // get the categories that have "faq-" in the front
    $args = array(	"type"			=>	"post",
                "child_of"		=>	0,
                "parent"			=>	"",
                "orderby"			=>	"name",
                "order"			=>	"ASC",
                "hide_empty"		=>	1,
                "hierarchical"		=>	1,
                "exclude"			=>	"",
                "include"			=>	"",
                "number"			=>	"",
                "taxonomy"		=>	"category",
                "pad_counts"		=>	false);
    
    $categories = get_categories($args);
    
    $findString = "faq-";
    $catArray = [];
    $indexCount = 0;
    
    if ($categories)
    {
        foreach($categories AS $category)
        {
            // for testing -> $printThis .= "cat slug: ".$category->slug."<br />";
            
            // get the $category->term_id and $category->name if they meet criteria
            $testName = $category->slug;
            if(strpos($testName, $findString) !== FALSE)
            {
                $catArray[$indexCount]["id"] = $category->term_id;
                $catArray[$indexCount]["name"] = $category->name;
                
                $indexCount++;
            }
        }
        
        // for testing ->	$printThis .= "Number of categories: ".$indexCount."<br /><br />";
    }
    else
    {
        $printThis .= "Not catetories returned.";
    }
    
    for($x = 0; $x < count($catArray); $x++)
    {
        // remove the 'FAQ-' from the front of the category name
        $printCatName = trim(substr($catArray[$x]["name"], 6));
        /*$printThis .= "<div class=\"ql-sc-toggle close|open\">
                        <div class=\"ql-sc-toggle-preview\">
                            <h2 class=\"faq_section_title\"><a class=\"ql-sc-toggle-link\" href=\"#\">".$printCatName."</a></h2>
                        </div>
                        <div class=\"ql-sc-toggle-content\">";*/
        
        // client wants 'general' cat to always be first
        if (strtolower($printCatName) == "general")
        {
            $printThisFirst .= "<h2 class=\"faq_section_title faq_section_carrot_open\"> ".$printCatName."</h2>
                            <div class=\"faq_section_slide\">";
            
            // splitting up the display for now to show 2 possible layouts for review
            if ($printCatName != "dont even use this")
            {
                // this one uses the title as a link to open and close
                
                // get all the posts with type = "faq-pages"
                $args = array(	"post_type"		=> "faq_pages",
                            "category"		=> $catArray[$x]["id"],
                            "post_status"		=> "publish",
                            "posts_per_page"	=> -1,
                            "order" 			=> "DESC");
                
                $getPosts = get_posts($args);
                
                if (!empty($getPosts))
                {
                    foreach($getPosts AS $post)
                    {
                        if ($post->ID != "")
                        {
                            $printThisFirst .= "	<div class=\"faq_holder\">
                                                <p class=\"faq_title2\"><span class=\"act_like_link\">".$post->post_title."</span></p>
                                                <section class=\"faq_slide\">".$post->post_content."<br /><span class=\"faq_close\">close</span></section>
                                            </div>";
                        }
                    }
                } // end if that checks for post return
                else
                {
                    $printThisFirst .= "No posts returned.";
                }
            }
            else
            {
                // this one displays the 'open' 'close' button
                
                // get all the posts with type = "faq-pages"
                $args = array(	"post_type"		=> "faq_pages",
                            "category"		=> $catArray[$x]["id"],
                            "post_status"		=> "publish",
                            "posts_per_page"	=> -1,
                            "order" 			=> "DESC");
                
                $getPosts = get_posts($args);
                
                if (!empty($getPosts))
                {
                    foreach($getPosts AS $post)
                    {
                        if ($post->ID != "")
                        {
                            $printThisFirst .= "	<div class=\"faq_holder\">
                                                <h4 class=\"faq_title\">".$post->post_title."</h4>
                                                <section class=\"faq_slide\">".$post->post_content."</section>
                                                <button type=\"button\" class=\"faq_slideBtn\">open</button>
                                            </div>";
                        }
                    }
                } // end if that checks for post return
                else
                {
                    $printThisFirst .= "No posts returned.";
                }
            }
            
            $printThisFirst .= "</div>";
        } // end if cat name = 'general'
        else
        {
            $printThis .= "<h2 class=\"faq_section_title faq_section_carrot_open\"> ".$printCatName."</h2>
                            <div class=\"faq_section_slide\">";
            
            // splitting up the display for now to show 2 possible layouts for review
            if ($printCatName != "dont even use this")
            {
                // this one uses the title as a link to open and close
                
                // get all the posts with type = "faq-pages"
                $args = array(	"post_type"		=> "faq_pages",
                            "category"		=> $catArray[$x]["id"],
                            "post_status"		=> "publish",
                            "posts_per_page"	=> -1,
                            "order" 			=> "DESC");
                
                $getPosts = get_posts($args);
                
                if (!empty($getPosts))
                {
                    foreach($getPosts AS $post)
                    {
                        if ($post->ID != "")
                        {
                            $printThis .= "	<div class=\"faq_holder\">
                                                <p class=\"faq_title2\"><span class=\"act_like_link\">".$post->post_title."</span></p>
                                                <section class=\"faq_slide\">".$post->post_content."<br /><span class=\"faq_close\">close</span></section>
                                            </div>";
                        }
                    }
                } // end if that checks for post return
                else
                {
                    $printThis .= "No posts returned.";
                }
            }
            else
            {
                // this one displays the 'open' 'close' button
                
                // get all the posts with type = "faq-pages"
                $args = array(	"post_type"		=> "faq_pages",
                            "category"		=> $catArray[$x]["id"],
                            "post_status"		=> "publish",
                            "posts_per_page"	=> -1,
                            "order" 			=> "DESC");
                
                $getPosts = get_posts($args);
                
                if (!empty($getPosts))
                {
                    foreach($getPosts AS $post)
                    {
                        if ($post->ID != "")
                        {
                            $printThis .= "	<div class=\"faq_holder\">
                                                <h4 class=\"faq_title\">".$post->post_title."</h4>
                                                <section class=\"faq_slide\">".$post->post_content."</section>
                                                <button type=\"button\" class=\"faq_slideBtn\">open</button>
                                            </div>";
                        }
                    }
                } // end if that checks for post return
                else
                {
                    $printThis .= "No posts returned.";
                }
            }
            
            $printThis .= "</div>";
        } // end if/else for cat 'general'
        
        // this gets commented out if the built in accordion does not work for this -> 
        //$printThis .= "</div>";
        // for now, no line -> $printThis .= "<hr />";
    }
    
    // javascript for slider
    $printThis .= "	<script>
                        jQuery(document).ready(function($)
                        {
                            $(\".faq_slideBtn\").click(function()
                            {
                                $(this).prev().toggle(\"slow\", function()
                                {
                                    if ($(this).is(\":visible\"))
                                    {
                                        $(this).next().html(\"close\");
                                    }
                                    else
                                    {
                                        $(this).next().html(\"open\");
                                    }
                                });
                            });
                            
                            $(\".faq_title2\").click(function()
                            {
                                $(this).next().toggle(\"slow\");
                            });
                            
                            $(\".faq_close\").click(function()
                            {
                                $(this).parent().toggle(\"slow\");
                            });
                            
                            $(\".faq_section_title\").click(function()
                            {
                                $(this).toggleClass(\"faq_section_carrot_close\");
                                $(this).next().toggle(\"slow\");
                            });
                        });
                    </script>";
    
    
    return $printThisFirst.$printThis;
}

add_shortcode("rt_display_faqs", "rt_faq_page");

// this shortcode is a way to correct enteries by Kraupp, Inc that are incorrect.
// it is to be placed in the page 'russ_fixes' when there is something to fix.
// the sql code here is should be changed for all the different changes, this one shortcode/function should be used for everything.
function rt_sql_fix()
{
    global $wpdb;
    
    // this requires admin functionality
    require_once(ABSPATH."wp-admin/includes/user.php");
    
    $success = 0;
    $fail = 0;
    $resultMessage = "";
    $printThis = "";
    $formIdArray = array(2, 4, 7, 8, 9, 10);
    $row = 1;
    
    if (($handle = fopen(get_stylesheet_directory()."/uclarts_delete_users_10192017_01.csv", "r")) !== FALSE)
    {
        while (($dataArray = fgetcsv($handle, 0, ',', '"', '/')) !== FALSE)
        {
            if ($row == 1) // this is the row that will be used as meta_key values
            {
                $keysArray = $dataArray; // this will never change
            }
            else // this is the data that actually get imported as the meta_value values
            {
                if (is_array($keysArray) && count($keysArray) > 0) // check, just to make sure
                {
                    $deleteArray = array_combine($keysArray, $dataArray); // this gives new array like $deleteArray['meta_key'] = meta_value
                    
                    // use the user login to get the user ID and then delete
                    $user = get_userdatabylogin($deleteArray['UserName']);
                    
                    if ($user)
                    {
                        if (wp_delete_user($user->ID))
                        {
                            // true, success
                            $resultMessage .= "ID: ".$user->ID." - ".$deleteArray['UserName']." deleted successfully.<br />";
                            $success++;
                        }
                        else
                        {
                            // delete failed
                            $resultMessage .= "<span style=\"color: #ff0000;\">ID: ".$user->ID." - ".$deleteArray['UserName']." was NOT deleted.</span><br />";
                            $fail++;
                        }
                    }
                }
            } // end if that checks the row number
            $row++;
        } // end while that loops through file
        
        fclose($handle);
    } // end if that opens the file
    else
    {
        $resultMessage .= "<br /><strong>The file was not opened.</strong><br />";
    }
    
    $printThis = $resultMessage."<br />Success: ".$success."<br />Fail: ".$fail;
    
    return $printThis;
} // end rt_sql_fix

add_shortcode("rt_fix_database", "rt_sql_fix");

// this function (and shortcode) is to import the user profile data from the old site's database to the new site's wp_usermeta table.
/* process:
   (1) open the cvs file.
   (2) get the first line - this line is what will be put in the meta_key fields.
   (3) get the next line (this will increment automatically with PHP) and convert to array (with fgetcsv()) - this holds the meta_value.
   (4) use the email address in the array from (3) and check to see if there is a record using it in wp_users.
   (5) if (4) == true, get the ID associated with the email address in wp_users.
   (6) use the ID from (5) as user_id, array from (2) as meta_key values, and array from (3) as meta_value values; insert into wp_usermeta.
*/
function rt_sql_import()
{
    global $wpdb;
    
    $gotIdSuccess = 0;
    $success = 0;
    $fail = 0;
    $metaKeyCount = 0;
    $resultMessage = "";
    $printThis = "";
    
    $row = 1;
    
    if (($handle = fopen(get_stylesheet_directory()."/userImport_test_01.csv", "r")) !== FALSE)
    {
        while (($dataArray = fgetcsv($handle, 0, ',', '"', '/')) !== FALSE)
        {
            if ($row == 1) // this is the row that will be used as meta_key values
            {
                $keysArray = $dataArray; // this will never change
            }
            else // this is the data that actually get imported as the meta_value values
            {
                if (is_array($keysArray) && count($keysArray) > 0) // check, just to make sure
                {
                    $insertArray = array_combine($keysArray, $dataArray); // this gives new array like $insertArray['meta_key'] = meta_value
                    
                    // use email to find the ID
                    $emailValue = $insertArray['billing_email'];
                    $emailStatement = "SELECT ID FROM wp_users WHERE user_email = '$emailValue'";
                    $getEmail = $wpdb->get_results($emailStatement);
                    
                    if ($getEmail != FALSE)
                    {
                        $gotIdSuccess++;
                        $metaKeyCount = 0;
                        
                        foreach($getEmail AS $theEmail)
                        {
                            $resultMessage .= "ID: ".$theEmail->ID." and email: ".$emailValue."<br />";
                            
                            foreach($insertArray AS $insertKey => $insertValue)
                            {
                                $insertStatement = "INSERT INTO wp_usermeta (umeta_id, user_id, meta_key, meta_value) VALUES ('', ".$theEmail->ID.", '".$insertKey."', '".$insertValue."')";
                                $doInsert = $wpdb->query($insertStatement);
                                
                                if ($doInsert != FALSE)
                                {
                                    // print results
                                    $resultMessage .= "&nbsp;&nbsp;&nbsp;<span style=\"color: #00ff00;\">".$insertKey." -> ".$insertValue."</span><br />";
                                    $success++;
                                }
                                else
                                {
                                    $resultMessage .= "&nbsp;&nbsp;&nbsp;<span style=\"color: #ff0000;\">ERROR ON INSERT ".$insertKey." -> ".$insertValue."</span><br />";
                                    $fail++;
                                }
                                
                                $metaKeyCount++;
                            } // end foreach that loops through the insertArray and does the insert
                            
                            $resultMessage .= "<em>meta_key count: ".$metaKeyCount."</em><br /><br />";
                            
                        } // end foreach to insert
                    } // end if $getEmail
                } // end the if to make sure $keysArray exists and is not empty
            }
            $row++;
        } // end while loop that gets one line at a time
        
        fclose($handle);
    } // end if that opens file for read
    else
    {
        $resultMessage .= "<br /><strong>The file was not opened.</strong><br />";
    }
    
    $printThis .= "<hr />successes: ".$success."<br />fails: ".$fail."<br /><br />".$resultMessage;
    
    return $printThis;
} // end rt_sql_fix

//add_shortcode("rt_import_users", "rt_sql_import");

// this shortcode is see how the gravity form fields are saved in the database - it displays the JSON array data.
// it is to be placed in the page 'russ_fixes' when there is something to fix.
function rt_gravityFormsData($atts)
{
    global $wpdb;
    $print_this = "";
    $passed = shortcode_atts(array("the_form" => null), $atts);
    
    if ($passed['the_form'] === null)
    {
        $printThis = "Shortcode must specify the form id, example: \"[gravity_form_fields the_form=7]\"";
    }
    else
    {
        $sqlStatement = "SELECT display_meta FROM wp_rg_form_meta WHERE form_id = ".$passed['the_form'];
        
        $getFields = $wpdb->get_results($sqlStatement);
        
        if ($getFields != FALSE)
        {
            foreach ($getFields AS $theFields)
            {
                $json_data = json_decode($theFields->display_meta, true);
                
                $printThis .= "The Form ID: ".$passed['the_form']."<br /><br />";
                
                // works, but not pretty -> 
                $printThis .= print_r($json_data, true);
                
                /*
                foreach ($json_data AS $key => $value)
                {
                    $printThis .= $key." : ".$value."<br />";
                }
                */
            }
        }
        else
        {
            $printThis = "The Form ID: ".$passed['the_form']."<br />SQL: ".$sqlStatement."<br />Nothing returned from query.";
        }
    }
    
    return $printThis;
} // end rt_sql_fix

add_shortcode("gravity_form_fields", "rt_gravityFormsData");

// this shortcode is to take the imported resources and 'assemble' the input into content and then insert the info into the wp_posts table
// use this in the page 'russ_fixes'
function rt_resources2posts()
{
    // the work is all done in the function 'rt_do_resources2posts' in rt_functions.php
    // this is to keep down on the code that has to be in this file
    include('rt_functions.php');
    $returnThis = rt_do_resources2posts();
    
    return $returnThis;
} // end rt_resources_to_posts function

add_shortcode("rt_resources_to_posts", "rt_resources2posts");

// this short code is to delete a number of posts or pages (or even attachments) in one move
// use this in the page 'russ_fixes'
function rt_delete()
{
    global $wpdb;
    $printThis = "";
    $successCount = 0;
    $failCount = 0;
    /*
    // for this use, the titles of the posts to delete will come from a spreadsheet file as a comma seperated string
    $theTitlesString = "Loyola Marymount University's  Clinical Art Therapy Program;California Institute of Integral Studies (CIIS) Drama Therapy Masters Degree Program;California Institute of Integral Studies (CIIS) Expressive Arts Therapy Program;Center for Movement Education and Research's (CMER) Alternate Route Training in Dance/Movement Therapy;American Art Therapy Association (AATA) Approved Graduate Degree Programs;American Dance Therapy Association (ADTA) Approved Graduate Degree Programs ;American Dance Therapy Association (ADTA) Approved Undergraduate Programs ;American Music Therapy Association (AMTA) Approved Graduate Degree Programs;North American Drama Therapy Association (NADT) Approved  Graduate Degree Programs;National Federation for Biblio/Poetry Therapy (NFBPT) Approved Graduate Degree Programs;\"American Board of Examiners in Psychodrama, Sociometry, and Group Psychotherapy Approved Training Programs\";International Expressive Arts Therapy Association (IEATA) Doctorate Degree Program Listings;International Expressive Arts Therapy Association (IEATA) Graduate Degree Program Listings;International Expressive Arts Therapy Association (IEATA) Undergraduate Degree Program Listings;International Expressive Arts Therapy Association (IEATA) Certificate Program Listings";
    // explode into an array
    $theTitlesArray = explode(";", $theTitlesString);
    // will get the IDs using the titles in the array above and store in
    $storedIdArray = array();
    // for testing
    $htmlTitlesArray = array();
    
    foreach ($theTitlesArray AS $key => $value)
    {
        $value = preg_replace('/\s+/', ' ', $value); // this removes the multiple whitespace from between words
        $post = get_page_by_title(trim(htmlentities($value)), OBJECT, "post");
        
        // for testing ->
        $htmlTitlesArray[$key] = htmlentities($value);
        // for testing ->		$printThis .= "<span style=\"font-weight: bold;\">".html_entity_decode($value)."</span><br />";
        
        if (!is_null($post))
        {
            $storedIdArray[$key] = $post->ID;
        }
    }
    
    // if IDs were returned, delete from db
    if (count($storedIdArray) > 0)
    {
        foreach ($storedIdArray AS $key => $value)
        {
            $printThis .= "ID: ".$storedIdArray[$key]." ".$theTitlesArray[$key].":";
            
            $delete = wp_delete_post($value, true);
            
            if ($delete != FALSE)
            {
                $printThis .= " delete <span style=\"color: #009900;\">SUCCESSFUL</span><br />";
                
                $successCount++;
            }
            else
            {
                $printThis .= " delete <span style=\"color: #bb0000;\">FAIL</span><br />";
                
                $failCount++;
            }
        }
        
        $printThis .= "<br />
                    <span style=\"color: #009900;\">Success ".$successCount."</span> / <span style=\"color: #bb0000;\">Fail ".$failCount."</span><br />
                    The number of items in the title array: ".count($theTitlesArray)."<br />";
    }
    else
    {
        $printThis .= "	no IDs returned<br />
                        The number of items in the title array: ".count($theTitlesArray)."<hr />";
                    
        foreach ($theTitlesArray AS $key => $value)
        {
            $printThis .= "	<span style=\"color: #0000dd;\">".$theTitlesArray[$key]."</span><br />
                            <span style=\"color: #ff9933;\">".$htmlTitlesArray[$key]."</span><br />";
        }
    }
    */
    
    // for deleting post and there meta from db
    $sqlDeleteStatement1 = "DELETE FROM wp_postmeta WHERE post_id >= 5241";
    $printThis = $wpdb->query($sqlDeleteStatement1);
    $printThis .= "<br />";
    $sqlDeleteStatement2 = "DELETE FROM wp_posts WHERE ID >= 5241";
    $printThis .= $wpdb->query($sqlDeleteStatement2);
    
    
    /* special case, need to set the 'post_id' field to NULL if its value is >= 5241
    $allIds = $wpdb->get_results("SELECT * FROM wp_rg_lead WHERE post_id >= 5241", ARRAY_A);
    
    if (!empty($allIds))
    {
        $idCount = 0;
        
        foreach ($allIds AS $theId)
        {
            $queryString = "UPDATE wp_rg_lead SET post_id = NULL WHERE id = ".$theId[id];
            $doSetNull = $wpdb->query($queryString);
            
            if ($doSetNull > 0) $idCount++;
        }
        
        $printThis = $idCount." record had post_id set to NULL.<br />";
    }
    else
    {
        $printThis = "no ids returned";
    } */
    
    /* this returns the number of entries for each form in the db... actually does not delete anything
    $formIdArray = array(	2, // organizations
                            4, //literature
                            7, // conferences
                            8, // degree programs
                            9, // listserv
                            10); // ind. practitioners
    
    foreach ($formIdArray AS $value)
    {
        // get the resources entry id first
        $formId = $value;		
        $printThis .= "<span style=\"font-weight: bold; text-decoration: underline;\">Form Number: ".$formId."</span><br />";
        $sqlStatement0 = "SELECT id FROM wp_rg_lead WHERE post_id IS NULL AND created_by = 3 AND form_id = ".$formId;
        $formEntryNum = $wpdb->get_results($sqlStatement0, ARRAY_A); // or die(mysql_error());
        
        $printThis .= "Number of Leads Returned for Form ".$value." is: ".count($formEntryNum)."<br />";
        
        // ********* have to do a while or foreach here to do ALL entries with the form id.
        if ($formEntryNum)
        {
            $printThis .= "Entries for form ".$formId." is: ".count($formEntryNum)."<br />";
        }
        else
        {
            $printThis .= "<span style=\"color: #cc0000;\">No entries returned for form ".$formId."</span><br />";
        }
    }
    */
    
    /*
    // for deleting resource entries from "wp_rg_lead" and the two lead detail tables
    $detailLongSuccessCount = 0;
    $detailLongFailCount = 0;
    $detailSuccessCount = 0;
    $detailFailCount = 0;
    $leadSuccessCount = 0;
    $leadFailCount = 0;
    $detailEntries = 0;
    $printThis = "";
    
    // first get the id from the "wp_rg_lead_detail" table and use to delete entries data in "wp_rg_lead_detail_long"
    $getDetailIds = $wpdb->get_results("SELECT id FROM wp_rg_lead_detail WHERE form_id = 7 AND (field_number BETWEEN 8 AND 9)");
    
    if($getDetailIds > 0)
    {
        foreach ($getDetailIds AS $theDetailId)
        {
            $queryString = "DELETE FROM wp_rg_lead_detail_long WHERE lead_detail_id = ".$theDetailId->id;
            $result = $wpdb->query($queryString);
            
            ($result > 0) ? $detailLongSuccessCount++ : $detailLongFailCount++ ;
            
            $detailEntries++;
        }
        
        $printThis .= "<i>Detail Long</i><br />detail entries in foreach: ".$detailEntries." returned from db: ".$getDetailIds."<br />success: ".$detailLongSuccessCount."<br />fail: ".$detailLongFailCount."<br /><br />";
        
        // step 2 delete entries from "wp_rg_lead_detail
        // delete all records with id's between 466 and 498, inclusive
        $deleteDetailAttemptCount = 0;
        
        for ($x = 466; $x <= 498; $x++)
        {
            $detailQueryString = "DELETE FROM wp_rg_lead_detail WHERE lead_id = ".$x;
            $detailResult = $wpdb->query($detailQueryString);
            
            ($detailResult > 0) ? $detailSuccessCount++ : $detailFailCount++ ;
            
            $deleteDetailAttemptCount++;
        }
            
        $printThis .= "<i>Detail</i><br />success: ".$detailSuccessCount."<br />fail: ".$detailFailCount."<br /><br />";
        
        if ($detailSuccessCount >= 33)
        {
            // step 3 if all the detail entries are removed, delete the lead entries
            $leadQuery = "DELETE FROM wp_rg_lead WHERE form_id = 7 AND created_by = 3";
            $leadResults = $wpdb->query($leadQuery);
            
            $printThis .= "<i>Lead</i><br />deleted from lead table: ".$leadResults."<br /><br />";
        }
        else
        {
            $printThis .= "<span style=\"color: #cc0000; font-weight: bold;\">NOT ALL DETAIL ENTRIES WERE DELETED - PROCESS HALTED</span>";
            return $printThis;
        }
    }
    else
    {
        $printThis .= "no results from detail id select (step 1)<br />";
    }
    
    
    // step 2 delete entries from "wp_rg_lead_detail
    // delete all records with id's between 466 and 498, inclusive
    $deleteDetailAttemptCount = 0;
    
    for ($x = 466; $x <= 498; $x++)
    {
        $detailQueryString = "DELETE FROM wp_rg_lead_detail WHERE lead_id = ".$x;
        $detailResult = $wpdb->query($detailQueryString);
        
        ($detailResult > 0) ? $detailSuccessCount++ : $detailFailCount++ ;
        
        $deleteDetailAttemptCount++;
    }
        
    $printThis .= "<i>Detail</i><br />delete attempts: ".$deleteDetailAttemptCount."<br />success: ".$detailSuccessCount."<br />fail: ".$detailFailCount."<br /><br />";
    
    if ($detailSuccessCount >= 33)
    {
        // step 3 if all the detail entries are removed, delete the lead entries
        $leadQuery = "DELETE FROM wp_rg_lead WHERE form_id = 7 AND created_by = 3";
        $leadResults = $wpdb->query($leadQuery);
        
        $printThis .= "<i>Lead</i><br />deleted from lead table: ".$leadResults."<br /><br />";
    }
    else
    {
        $printThis .= "<span style=\"color: #cc0000; font-weight: bold;\">NOT ALL DETAIL ENTRIES WERE DELETED - PROCESS HALTED</span>";
        return $printThis;
    }
    */
    
    return $printThis;
} // end rt_delete function

add_shortcode("rt_delete_from_db", "rt_delete");

// this shortcode function adds the content to the 'our videos' main video landing page
function rt_video_main_landing()
{
    $vid_display = "coming_soon";
    
    if ($vid_display == "coming_soon") :
        ?>
        <br />
        <center>
            <h1>Coming Soon</h1>
            Many of our videos can be viewed on our <a href="https://vimeo.com/uclartsandhealing/" target="_blank">Vimeo site</a><br />
            or on our <a href="https://www.youtube.com/channel/UC693MeEqdfQzx1-V_aPKjWw/" target="_blank">YouTube channel</a>.
        </center>
        <?php
    else :
        $imageAttrsArray = array(	"height"	=> "200px",
                                "width"	=> "149px");
        
        // test changes for mockups - code here.  different versions of what they may want in file 'codeHolder_video_listPg.php'
        // current option = 6
        ?>
            <fieldset style="border: 1.25px solid #eda233; padding: 5px;">
                <legend><span style="color: #eda233; font-size: 1.25em; font-weight: 600;"> Feature Videos </span></legend>
                <table style="border: none;">
                    <tr>
        <?php
        // get first featured video
        $argsFeature1 = array(	"posts_per_page"	=> -1,
                            "category"		=> "205",
                            "orderby"			=> "post_title",
                            "order"			=> "ASC",
                            "meta_key"		=> "featured_video",
                            "meta_value"		=> "one",
                            "post_status"		=> "publish");
        
        $videoPosts = get_posts($argsFeature1);
        
        foreach ($videoPosts AS $post) : setup_postdata($post); ?>
            <td style="width: 50%; padding: 5px; border: none;">
                <p>
                    <?php if (has_post_thumbnail($post->ID)) : ?>
                        <center>
                            <a href="<?php echo get_site_url()."/".$post->post_name; ?>" title="<?php echo esc_attr($post->post_title); ?>">
                            <?php echo get_the_post_thumbnail($post->ID, "post-thumbnail", $imageAttrsArray); ?>
                            </a>
                        </center>
                        <br />
                    <?php
                    endif; ?>
                    <span class="featureVideo_yellow_label"><a href="<?php echo get_site_url()."/".$post->post_name; ?>"><?php echo esc_attr($post->post_title); ?></a></span><br />
                    <span style="font-size: 0.8em;"><?php rt_excerpt($post->post_name, $post->ID); ?></span>
                </p>
            </td>
        <?php
        endforeach;
        
        // get second featured video
        $argsFeature2 = array(	"posts_per_page"	=> -1,
                            "category"		=> "205",
                            "orderby"			=> "post_title",
                            "order"			=> "ASC",
                            "meta_key"		=> "featured_video",
                            "meta_value"		=> "two",
                            "post_status"		=> "publish");
        
        $videoPosts = get_posts($argsFeature2);
        
        foreach ($videoPosts AS $post) : setup_postdata($post); ?>
            <td style="width: 50%; padding: 5px; border: none;">
                <p>
                    <?php if (has_post_thumbnail($post->ID)) : ?>
                        <center>
                            <a href="<?php echo get_site_url()."/".$post->post_name; ?>" title="<?php echo esc_attr($post->post_title); ?>">
                            <?php echo get_the_post_thumbnail($post->ID, "post-thumbnail", $imageAttrsArray); ?>
                            </a>
                        </center>
                        <br />
                    <?php
                    endif; ?>
                    <span class="featureVideo_yellow_label"><a href="<?php echo get_site_url()."/".$post->post_name; ?>"><?php echo esc_attr($post->post_title); ?></a></span><br />
                    <span style="font-size: 0.8em;"><?php rt_excerpt($post->post_name, $post->ID); ?></span>
                </p>
            </td>
        <?php
        endforeach;
        ?>
                    </tr>
                </table>
            </fieldset>
        <?php
        // end test changes code area
        
        // get videos that are in the 'conferences' cat, id=121
        $argsConferences = array(	"posts_per_page"		=> -1,
                                "category__and"		=> array("205", "121"),
                                "orderby"				=> "post_title",
                                "order"				=> "ASC",
                                "post_status"			=> "publish");
        
        $videoPosts = get_posts($argsConferences);
        ?>
        <div class="ql-sc-toggle open|close">
            <div class="ql-sc-toggle-preview">
                <a class="ql-sc-toggle-link" href="#">conferences</a>
            </div>
            <div class="ql-sc-toggle-content">
        <?php
        foreach ($videoPosts AS $post) : setup_postdata($post); ?>
            <h3 class="yellow"><a href="<?php echo get_site_url()."/".$post->post_name; ?>"><?php echo esc_attr($post->post_title); ?></a></h3>
            <p>
            <?php if (has_post_thumbnail($post->ID)) : ?>
                <a href="<?php echo get_site_url()."/".$post->post_name; ?>" title="<?php echo esc_attr($post->post_title); ?>">
                <?php echo get_the_post_thumbnail($post->ID, "post-thumbnail", $imageAttrsArray); ?>
                </a> 
            <?php
            endif;
            rt_excerpt($post->post_name, $post->ID); ?>
            </p>
            <br />
        <?php
        endforeach;
        ?>
            </div>
        </div>
        <?php
        
        // get videos that are in the 'our programs' cat, id=232
        $argsOurPrograms = array(	"posts_per_page"		=> -1,
                                "category__and"		=> array("205", "232"),
                                "orderby"				=> "post_title",
                                "order"				=> "ASC",
                                "post_status"			=> "publish");
        
        $videoPosts = get_posts($argsOurPrograms);
        ?>
        <div class="ql-sc-toggle open|close">
            <div class="ql-sc-toggle-preview">
                <a class="ql-sc-toggle-link" href="#">our programs</a>
            </div>
            <div class="ql-sc-toggle-content">
        <?php
        foreach ($videoPosts AS $post) : setup_postdata($post); ?>
            <h3 class="yellow"><a href="<?php echo get_site_url()."/".$post->post_name; ?>"><?php echo esc_attr($post->post_title); ?></a></h3>
            <p>
            <?php if (has_post_thumbnail($post->ID)) : ?>
                <a href="<?php echo get_site_url()."/".$post->post_name; ?>" title="<?php echo esc_attr($post->post_title); ?>">
                <?php echo get_the_post_thumbnail($post->ID, "post-thumbnail", $imageAttrsArray); ?>
                </a> 
            <?php
            endif;
            rt_excerpt($post->post_name, $post->ID); ?>
            </p>
            <br />
        <?php
        endforeach;
        ?>
            </div>
        </div>
        <?php
        
        // get videos that are in the 'social emotional arts in action' cat, id=233
        $argsSocialEmotional = array(	"posts_per_page"		=> -1,
                                "category__and"		=> array("205", "233"),
                                "orderby"				=> "post_title",
                                "order"				=> "ASC",
                                "post_status"			=> "publish");
        
        $videoPosts = get_posts($argsSocialEmotional);
        ?>
        <div class="ql-sc-toggle open|close">
            <div class="ql-sc-toggle-preview">
                <a class="ql-sc-toggle-link" href="#">social emotional arts in action</a>
            </div>
            <div class="ql-sc-toggle-content">
        <?php
        foreach ($videoPosts AS $post) : setup_postdata($post); ?>
            <h3 class="yellow"><a href="<?php echo get_site_url()."/".$post->post_name; ?>"><?php echo esc_attr($post->post_title); ?></a></h3>
            <p>
            <?php if (has_post_thumbnail($post->ID)) : ?>
                <a href="<?php echo get_site_url()."/".$post->post_name; ?>" title="<?php echo esc_attr($post->post_title); ?>">
                <?php echo get_the_post_thumbnail($post->ID, "post-thumbnail", $imageAttrsArray); ?>
                </a> 
            <?php
            endif;
            rt_excerpt($post->post_name, $post->ID); ?>
            </p>
            <br />
        <?php
        endforeach;
        ?>
            </div>
        </div>
        <?php
    endif;
} // end rt_video-main-landing

add_shortcode("rt_video-main-landing", "rt_video_main_landing");

// shortcode function for truncating any body of text on the site
// use by surronding the text in [fits_truncate][/fits_truncate]
function do_fits_truncate ($atts, $content = null)
{
    $printThis = "	<div id=\"truncateDiv\" class=\"ui-widget-content fits-truncate\">
                    ".$content."
                    <div id=\"toggleTruncateBtn\">
                        . . . Learn More
                    </div>
                </div>
                <script>
                    $(function()
                    {
                        var state = true;
                        $(\"#toggleTruncateBtn\").click(function()
                        {
                            if (state)
                            {
                                $(\"#truncateDiv\").animate({height: auto}, 1000);
                            }
                            else
                            {
                                $(\"#truncateDiv\").animate({height: 200px}, 1000);
                            }
                            
                            state = !state;
                        }
                    });
                </script>
                ";
    
    return $printThis;
} // end do_fits_truncate function

add_shortcode('fits_truncate', 'do_fits_truncate');

function rt_news_only_func()
{
    $args = array(	"posts_per_page" => -1,
                "category" => "18",
                "orderby" => "post_date",
                "order" => "DESC",
                "post_status" => "publish");
    
    $newsPosts = get_posts($args);
    
    foreach ($newsPosts AS $post) : setup_postdata($post); ?>
        <h1><a class="rt_news_title" href="<?php echo get_site_url()."/".$post->post_name; ?>"><?php echo $post->post_title; ?></a></h1>
        <?php rt_excerpt($post->post_name, $post->ID); ?>
        <p><?php echo get_the_date("F j, Y", $post->ID); ?></p>
    <?php endforeach;
} // end rt_news_only_func function

add_shortcode('news_only', 'rt_news_only_func');

// shortcode to list the resources submitted on their search page (currently under the search form)
function rt_list_resource_submissions($atts)
{
    global $wpdb;
    $postDataArray = array();
    $alphabetArray = array();
    $displayThis = "";
    $returnThis = "";
    
    $passed = shortcode_atts(array("the_form" => "all", "display_all" => "false"), $atts);
    
    // for testing -> echo $passed['the_form']."<br />";
    
    if ($passed['the_form'] == "all")
    {
        // return all resources that have been submitted... all that have "_gform-form-id" in meta_key
        $getSubmissions = $wpdb->get_results("SELECT DISTINCT(post_id) AS post_id FROM wp_postmeta WHERE meta_key = '_gform-form-id'");
    }
    else
    {
        $getSubmissions = $wpdb->get_results("SELECT post_id FROM wp_postmeta WHERE meta_key = '_gform-form-id' AND meta_value = $passed[the_form]", ARRAY_A);
    }
        
    if (!empty($getSubmissions))
    {
        $loopCount = 0;
        
        foreach ($getSubmissions AS $theSubmission)
        {
            $getPosts = $wpdb->get_results("SELECT * FROM wp_posts WHERE post_status = 'publish' AND ID = $theSubmission[post_id]", ARRAY_A);
            
            foreach ($getPosts AS $post)
            {
                // for testing -> print "Title: ".$post[post_title]." (".$loopCount.")<br />";
                // for testing -> print "Name: ".$post[post_name]." (".$loopCount.")<br />";
                
                $postDataArray[$loopCount]['title'] = $post[post_title];
                $postDataArray[$loopCount]['name'] = $post[post_name];
                
                // this entry will be used for sorting - the HTML and quotes are interferring, so strip them out
                $stripTitle = strip_tags($post[post_title]);
                $stripTitle = preg_replace("/[^a-zA-Z0-9]+/", "", html_entity_decode($stripTitle, ENT_QUOTES));
                
                $postDataArray[$loopCount]['forSort'] = $stripTitle;
                
                // check to see if there is a post_excerpt.  if so, use it.
                /*if (!empty($post[post_excerpt]))
                {
                    $passIn = array(	0 => rt_removeNewLine($post[post_excerpt]),
                                    1 => $theSubmission[post_id]);
                }
                else
                {
                    // pass the content to 'rt_trim_exerpt'
                    $passIn = array(	0 => rt_removeNewLine($post[post_content]),
                                    1 => $theSubmission[post_id]);
                }*/
                
                // pass the content to 'rt_trim_exerpt'
                    $passIn = array(	0 => rt_removeNewLine($post[post_content]),
                                    1 => $theSubmission[post_id]);
                
                $returnedExcerpt = rt_trim_excerpt($passIn);
                // $returnedExcerpt = rt_excerpt($post[post_name], $theSubmission[post_id]);
                // for testing -> print "Excerpt: ".$returnedExcerpt." (".$loopCount.")<br /><br />";
                $postDataArray[$loopCount]['excerpt'] = $returnedExcerpt;
                
                // set up an array with the first letter of each title, make unique
                // this array will be used for pagination links
                $letter = substr($stripTitle, 0, 1);
                
                // this one is used for pagination
                $postDataArray[$loopCount]['pagination'] = $letter;
                
                if (!in_array($letter, $alphabetArray))
                {
                    $alphabetArray[] = $letter;
                    sort($alphabetArray);
                }
            }
            
            $loopCount++;
        }
        
        array_unique($alphabetArray); // just to make sure there are only one of each letter
        
        // now sort the array of posts
        usort($postDataArray, function ($a, $b)
        {
            return strcasecmp($a['forSort'], $b['forSort']);
        });
        
        $pageNum = 1;
        $alphaIndex = 0;
        
        $displayThis .= "<div id=\"pagination\">"; // this is the outer div that for the pagination lists
        
        while ($alphaIndex < count($alphabetArray))
        {
            $displayThis .= "<div id=\"page-".$pageNum."\">";
            
            foreach ($postDataArray AS $key1 => $value1)
            {
                /* for testing ->
                print "Title: ".$value1['title']."<br />";
                print "Name: ".$value1['name']."<br />";
                print "Excerpt; ".$value1['excerpt']."<br />";
                print "<hr />";
                */
                
                if ($value1['pagination'] == $alphabetArray[$alphaIndex])
                {
                    $displayThis .= "	<h3 class=\"yellow\"><a href=\"".get_site_url()."/".$value1['name']."\">".$value1['title']."</a></h3>
                                    <p>".$value1['excerpt']."</p>";
                    
                    // the client wants an "all" at the end of the pagination.
                    // this holds that info and adds it as the last sliding div below
                    $allListings .= "	<h3 class=\"yellow\"><a href=\"".get_site_url()."/".$value1['name']."\">".$value1['title']."</a></h3>
                                    <p>".$value1['excerpt']."</p>";
                } // end if comparison
            
            } // end $postDataArray foreach
            
            $displayThis .= "</div><!-- end page div -->";
            
            // make the letter matrix links that switch the pages
            if ($alphaIndex < (count($alphabetArray) - 1))
            {
                $paginationLink .= "<span id=\"pagination-".$pageNum."\" onclick=\"resources_slide(".$pageNum.");\" class=\"paginationLink\">".strtolower($alphabetArray[$alphaIndex])."</span> | ";
            }
            else
            {
                $paginationLink .= "<span id=\"pagination-".$pageNum."\" onclick=\"resources_slide(".$pageNum.");\" class=\"paginationLink\">".strtolower($alphabetArray[$alphaIndex])."</span>";
            }
            
            $pageNum++;
            $alphaIndex++;
        } // end while loop
        
        // add "all" to pagination list
        $paginationLink .= " | <span id=\"pagination-all\" onclick=\"resources_slide('all');\" class=\"paginationLink\">all</span>";
        
        // add the "all" div
        $displayThis .= "	<div id=\"page-all\">
                            ".$allListings."
                        </div><!-- end all page div -->";
        
        $displayThis .= "</div><!-- end pagination div -->";
    }
    else // remove this else after testing is complete
    {
        print "There were no submissions returned.<br /><br />";
    }
    
    $theJavaScript = "	<script type=\"text/javascript\" charset=\"UTF-8\">
                        /* <![CDATA[ */
                        
                        // hold the current page - which will always start out to be 1
                        var currentPageNum = 1;
                        
                        function resources_slide(pageNum)
                        {
                            // for testing -> alert (\"pageNum is: \" + pageNum);
                            /* move current page over 710px */
                            jQuery(\"#page-\" + currentPageNum).animate({left: \"710px\"}, 500);
                            
                            /* move the selected letter page in */
                            // for testing -> alert (\"move in.\");
                            jQuery(\"#page-\" + pageNum).animate({left: \"0px\"}, 500);
                            // for testing -> alert (\"complete.\");
                            
                            /* set new current page */
                            currentPageNum = pageNum;
                        }";
    
    // client wants functionality set up so that if 'display_all = true' the pagination slides to the last div (the all div)
    if ($passed['display_all'] == "true")
    {
        $theJavaScript .= "window.onload = resources_slide('all');";
    }
    
    $theJavaScript .= "
                        /* ]]> */
                    </script>";
    
    /* client wanted this functionality changed... leaving this code 'cuz they have a tendency to change their minds a lot
    if ($passed['display_all'] == "true")
    {
        $returnThis = "	<center>| <span id=\"pagination-all\" class=\"paginationLink\">all</span> |</center><br />
                        <div id=\"pagination\">
                            <div id=\"page-all-only\">
                                ".$allListings."
                            </div><!-- end all page div -->
                        </div><!-- end pagination div -->";
    }
    else
    {
        $returnThis = "<center>".$paginationLink."</center><br />".$displayThis.$theJavaScript;
    }
    */
    
    $returnThis = "<center>".$paginationLink."</center><br />".$displayThis.$theJavaScript;
    
    return $returnThis;
}

add_shortcode("fits_resource_list", "rt_list_resource_submissions");

// shortcode to display video subcategories in the sidebar on video pages
function rt_display_video_subcats()
{
    $printThis = ""; // used to return the list of categories
    
    $args = array(	"parent"		=> 205,
                "orderby"		=> "name",
                "order"		=> "ASC",
                "hide_empty"	=> FALSE,
                "taxonomy"	=> "category");
    
    $categories = get_categories($args);
    
    if (!empty($categories))
    {
        foreach ($categories AS $category)
        {
            $printThis .= "<p><a href=\"".get_category_link($category->term_id)."\" title=\"".sprintf(__('view all posts in %s'), $category->name)."\">".$category->name."</a></p>";
        }
    }
    else
    {
        $printThis .= "no cats returned";
    }
    
    return $printThis;
}  // end rt_display_video_subcats function

add_shortcode("rt_video_sidebar", "rt_display_video_subcats");

function rt_get_posts_func($atts)
{
    $passed = shortcode_atts(array('thesecats' => 'all'), $atts);
    
    $args = array(	"posts_per_page" => -1,
                "category_name" => $passed['thesecats'],
                "orderby" => "post_date",
                "order" => "DESC",
                "post_status" => "publish");
    
    $newsPosts = get_posts($args);
    
    foreach ($newsPosts AS $post) : setup_postdata($post);
        $doDisplay = "yes";
        $theCatArray = get_the_category($post->ID);
        
        foreach ($theCatArray AS $theCat)
        {
            if ($theCat->cat_name == "News" || $theCat->cat_name == "news" || $theCat->term_id == "18")
            {
                $doDisplay = "no";
            }
            
            //$displayCat .= $theCat." ";
        } 
        
        if ($doDisplay == "yes")
        { ?>
            <h1><a class="rt_blog_title" href="<?php echo get_site_url()."/".$post->post_name; ?>"><?php echo $post->post_title; ?></a></h1>
            <?php rt_excerpt($post->post_name, $post->ID); ?>
            <p><?php echo get_the_date("F j, Y", $post->ID); ?></p>
    <?php }
    endforeach;
} // end rt_news_only_func function

add_shortcode('rt_posts', 'rt_get_posts_func');

// shortcode to get post of custom type 'program_pages'
// passed in as an argument is the program_type - either 'our' or 'other'
// passed in as an argument is one of the vaules 'current,' 'oneyear' or 'archive'
// 09-09-2015, client would like the listings in 'most current' order (whatever that really means).  Use meta_key orderBy_date for this.
function rt_programs_list_func($atts)
{
    $passed = shortcode_atts(array('programtype' => 'all', 'listperiod' => 'all'), $atts);
    $todayUnix = date("U");
    
    if ($passed['listperiod'] == "current")
    {
        $args = array(	"post_type" => "program_pages",
                    "post_status" => "publish",
                    "orderby" => "meta_value_num",
                    "order" => "ASC",
                    "meta_key" => "orderBy_date",
                    "posts_per_page" => -1);
    }
    else
    {
        $args = array(	"post_type" => "program_pages",
                "post_status" => "publish",
                "orderby" => "meta_value_num",
                "meta_key" => "orderBy_date",
                "posts_per_page" => -1);
    }
    
    $programPosts = new WP_Query($args);
    
    if($programPosts->have_posts())
    {
        while ($programPosts->have_posts()) : $programPosts->the_post(); 
            foreach($programPosts AS $post)
            {
                if ($post->ID != "")
                {
                    //echo $post->ID."<br />";
                    // create the date line - make sure dates exist too
                    if ($post->display_start_month != "" && $post->display_start_day != "" && $post->display_start_year != "")
                    {
                        $theStartDateUnix = @mktime(0,0,0,$post->display_start_month,$post->display_start_day,$post->display_start_year);
                        $theStartDate = date("l F d, Y", $theStartDateUnix);
                    }
                    else
                    {
                        $theStartDate = "false";
                    }
                    
                    if($post->display_end_month != "" && $post->display_end_day != "" && $post->display_end_year != "")
                    {
                        $theEndDateUnix = @mktime(0,0,0,$post->display_end_month,$post->display_end_day,$post->display_end_year);
                        $theEndDatePlusYearUnix = @mktime(0,0,0,$post->display_end_month,$post->display_end_day,($post->display_end_year)+1);
                        $theEndDate = date("l F d, Y", $theEndDateUnix);
                    }
                    else
                    {
                        $theEndDate = "false";
                    }
                    
                    // get the dates you want the program to display as 'current'
                    if ($post->current_start_month != "" && $post->current_start_day != "" && $post->current_start_year != "")
                    {
                        $theCurrentStartDateUnix = @mktime(0,0,0,$post->current_start_month,$post->current_start_day,$post->current_start_year);
                        // $theStartDate = date("l F d, Y", $theStartDateUnix);
                    }
                    
                    if($post->current_end_month != "" && $post->current_end_day != "" && $post->current_end_year != "")
                    {
                        $theCurrentEndDateUnix = @mktime(0,0,0,$post->current_end_month,$post->current_end_day,$post->current_end_year);
                        $theCurrentEndDatePlusYearUnix = @mktime(0,0,0,$post->current_end_month,$post->current_end_day,($post->current_end_year)+1);
                        // $theEndDate = date("l F d, Y", $theEndDateUnix);
                    }
                    
                    if ($theStartDateUnix == $theEndDateUnix)
                    {
                        $displayDateLine = "<h4>".$theStartDate."</h4>";
                    }
                    elseif($theStartDate != "false" && $theEndDate != "false")
                    {
                        $displayDateLine = "<h4>".$theStartDate." - ".$theEndDate."</h4>";
                    }
                    elseif ($theStartDate != "false" && $theEndDate == "false")
                    {
                        $displayDateLine = "<h4>".$theStartDate."</h4>";
                    }
                    elseif ($theStartDate == "false" && $theEndDate != "false")
                    {
                        $displayDateLine = "<h4>".$theEndDate."</h4>";
                    }
                    else
                    {
                        $displayDateLine = "";
                    }
                    
                    switch ($passed['programtype'])
                    {
                        case "all":
                        default:
                            switch ($passed['listperiod'])
                            {
                                case "all":
                                default:
                                    echo $displayDateLine;
                                    ?>
                                    <p><a class="programs_titles" href="<?php get_site_url(); ?>/our-programs-detail?theProgram=<?php echo $post->ID; ?>"><?php the_title(); ?></a></p>
                                    <?php
                                    //echo $post->display_start_month."/";
                                    //echo $post->display_start_day."/";
                                    //echo $post->display_start_year."<br />";
                                    //echo $post->post_name."<hr />";
                                    //the_meta();
                                    //echo $post->ID;
                                    //echo $post->post_name;
                                    //echo $post->main_description;
                                    rt_excerpt("useID", $post->ID);
                                    ?>
                                    <hr />
                                    <?php
                                break;
                                
                                case "current":
                                    // use this is client does NOT want programs to display if they have not started yet. ->if($todayUnix >= $theStartDateUnix && $todayUnix <= $theEndDateUnix)
                                    if($todayUnix >= $theCurrentStartDateUnix && $todayUnix <= $theCurrentEndDateUnix) // this is for current AND future programs
                                    {
                                        echo $displayDateLine;
                                        ?>
                                        <p><a class="programs_titles" href="<?php get_site_url(); ?>/our-programs-detail?theProgram=<?php  echo $post->ID; ?>"><?php the_title(); ?></a></p>
                                        <?php
                                        //echo $post->display_start_month."/";
                                        //echo $post->display_start_day."/";
                                        //echo $post->display_start_year."<br />";
                                        //echo $post->post_name."<hr />";
                                        //the_meta();
                                        //echo $post->ID;
                                        //echo $post->post_name;
                                        //echo $post->main_description;
                                        rt_excerpt("useID", $post->ID);
                                        ?>
                                        <hr />
                                        <?php
                                    }
                                break;
                                
                                case "past":
                                    if($todayUnix > $theCurrentEndDateUnix && $todayUnix <= $theCurrentEndDatePlusYearUnix)
                                    {
                                        echo $displayDateLine;
                                        ?>
                                        <p><a class="programs_titles" href="<?php get_site_url(); ?>/our-programs-detail?theProgram=<?php echo $post->ID; ?>"><?php the_title(); ?></a></p>
                                        <?php
                                        //echo $post->display_start_month."/";
                                        //echo $post->display_start_day."/";
                                        //echo $post->display_start_year."<br />";
                                        //echo $post->post_name."<hr />";
                                        //the_meta();
                                        //echo $post->ID;
                                        //echo $post->post_name;
                                        //echo $post->main_description;
                                        rt_excerpt("useID", $post->ID);
                                        ?>
                                        <hr />
                                        <?php
                                    }
                                break;
                                
                                case "archive":
                                    if($todayUnix > $theCurrentEndDatePlusYearUnix)
                                    {
                                        echo $displayDateLine;
                                        ?>
                                        <p><a class="programs_titles" href="<?php get_site_url(); ?>/our-programs-detail?theProgram=<?php echo $post->ID; ?>"><?php the_title(); ?></a></p>
                                        <?php
                                        //echo $post->display_start_month."/";
                                        //echo $post->display_start_day."/";
                                        //echo $post->display_start_year."<br />";
                                        //echo $post->post_name."<hr />";
                                        //the_meta();
                                        //echo $post->ID;
                                        //echo $post->post_name;
                                        //echo $post->main_description;
                                        rt_excerpt("useID", $post->ID);
                                        ?>
                                        <hr />
                                        <?php
                                    }
                                break;
                            } // end listperiod switch - inside 'all' case for programtype
                        break;
                        
                        case "our":
                            if ($post->program_type == "our")
                            {
                                switch ($passed['listperiod'])
                                {
                                    case "all":
                                    default:
                                        echo $displayDateLine;
                                        ?>
                                        <p><a class="programs_titles" href="<?php get_site_url(); ?>/our-programs-detail?theProgram=<?php echo $post->ID; ?>"><?php the_title(); ?></a></p>
                                        <?php
                                        //echo $post->display_start_month."/";
                                        //echo $post->display_start_day."/";
                                        //echo $post->display_start_year."<br />";
                                        //echo $post->post_name."<hr />";
                                        //the_meta();
                                        //echo $post->ID;
                                        //echo $post->post_name;
                                        //echo $post->main_description;
                                        rt_excerpt("useID", $post->ID);
                                        ?>
                                        <hr />
                                        <?php
                                    break;
                                    
                                    case "current":
                                        // use this is client does NOT want programs to display if they have not started yet. ->if($todayUnix >= $theStartDateUnix && $todayUnix <= $theEndDateUnix)
                                        if($todayUnix >= $theCurrentStartDateUnix && $todayUnix <= $theCurrentEndDateUnix) // this is for current AND future programs
                                        {
                                            echo $displayDateLine;
                                            ?>
                                            <p><a class="programs_titles" href="<?php get_site_url(); ?>/our-programs-detail?theProgram=<?php echo $post->ID; ?>"><?php the_title(); ?></a></p>
                                            <?php
                                            //echo $post->display_start_month."/";
                                            //echo $post->display_start_day."/";
                                            //echo $post->display_start_year."<br />";
                                            //echo $post->post_name."<hr />";
                                            //the_meta();
                                            //echo $post->ID;
                                            //echo $post->post_name;
                                            //echo $post->main_description;
                                            rt_excerpt("useID", $post->ID);
                                            ?>
                                            <hr />
                                            <?php
                                        }
                                    break;
                                    
                                    case "past":
                                        if($todayUnix > $theCurrentEndDateUnix && $todayUnix <= $theCurrentEndDatePlusYearUnix)
                                        {
                                            echo $displayDateLine;
                                            ?>
                                            <p><a class="programs_titles" href="<?php get_site_url(); ?>/our-programs-detail?theProgram=<?php echo $post->ID; ?>"><?php the_title(); ?></a></p>
                                            <?php
                                            //echo $post->display_start_month."/";
                                            //echo $post->display_start_day."/";
                                            //echo $post->display_start_year."<br />";
                                            //echo $post->post_name."<hr />";
                                            //the_meta();
                                            //echo $post->ID;
                                            //echo $post->post_name;
                                            //echo $post->main_description;
                                            rt_excerpt("useID", $post->ID);
                                            ?>
                                            <hr />
                                            <?php
                                        }
                                    break;
                                    
                                    case "archive":
                                        if($todayUnix > $theCurrentEndDatePlusYearUnix)
                                        {
                                            echo $displayDateLine;
                                            ?>
                                            <p><a class="programs_titles" href="<?php get_site_url(); ?>/our-programs-detail?theProgram=<?php echo $post->ID; ?>"><?php the_title(); ?></a></p>
                                            <?php
                                            //echo $post->display_start_month."/";
                                            //echo $post->display_start_day."/";
                                            //echo $post->display_start_year."<br />";
                                            //echo $post->post_name."<hr />";
                                            //the_meta();
                                            //echo $post->ID;
                                            //echo $post->post_name;
                                            //echo $post->main_description;
                                            rt_excerpt("useID", $post->ID);
                                            ?>
                                            <hr />
                                            <?php
                                        }
                                    break;
                                } // end listperiod switch - inside 'our' programtype
                            }
                        break;
                        
                        case "other":
                            if ($post->program_type == "other")
                            {
                                switch ($passed['listperiod'])
                                {
                                    case "all":
                                    default:
                                        echo $displayDateLine;
                                        ?>
                                        <p><a class="programs_titles" href="<?php get_site_url(); ?>/other-programs-detail?theProgram=<?php echo $post->ID; ?>"><?php the_title(); ?></a></p>
                                        <?php
                                        //echo $post->display_start_month."/";
                                        //echo $post->display_start_day."/";
                                        //echo $post->display_start_year."<br />";
                                        //echo $post->post_name."<hr />";
                                        //the_meta();
                                        //echo $post->ID;
                                        //echo $post->post_name;
                                        //echo $post->main_description;
                                        rt_excerpt("useID", $post->ID);
                                        ?>
                                        <hr />
                                        <?php
                                    break;
                                    
                                    case "current":
                                        // use this is client does NOT want programs to display if they have not started yet. ->if($todayUnix >= $theStartDateUnix && $todayUnix <= $theEndDateUnix)
                                        if($todayUnix >= $theCurrentStartDateUnix && $todayUnix <= $theCurrentEndDateUnix) // this is for current AND future programs
                                        {
                                            echo $displayDateLine;
                                            ?>
                                            <p><a class="programs_titles" href="<?php get_site_url(); ?>/other-programs-detail?theProgram=<?php echo $post->ID; ?>"><?php the_title(); ?></a></p>
                                            <?php
                                            //echo $post->display_start_month."/";
                                            //echo $post->display_start_day."/";
                                            //echo $post->display_start_year."<br />";
                                            //echo $post->post_name."<hr />";
                                            //the_meta();
                                            //echo $post->ID;
                                            //echo $post->post_name;
                                            //echo $post->main_description;
                                            rt_excerpt("useID", $post->ID);
                                            ?>
                                            <hr />
                                            <?php
                                        }
                                    break;
                                    
                                    case "past":
                                        if($todayUnix > $theCurrentEndDateUnix && $todayUnix <= $theCurrentEndDatePlusYearUnix)
                                        {
                                            echo $displayDateLine;
                                            ?>
                                            <p><a class="programs_titles" href="<?php get_site_url(); ?>/other-programs-detail?theProgram=<?php echo $post->ID; ?>"><?php the_title(); ?></a></p>
                                            <?php
                                            //echo $post->display_start_month."/";
                                            //echo $post->display_start_day."/";
                                            //echo $post->display_start_year."<br />";
                                            //echo $post->post_name."<hr />";
                                            //the_meta();
                                            //echo $post->ID;
                                            //echo $post->post_name;
                                            //echo $post->main_description;
                                            rt_excerpt("useID", $post->ID);
                                            ?>
                                            <hr />
                                            <?php
                                        }
                                    break;
                                    
                                    case "archive":
                                        if($todayUnix > $theCurrentEndDatePlusYearUnix)
                                        {
                                            echo $displayDateLine;
                                            ?>
                                            <p><a class="programs_titles" href="<?php get_site_url(); ?>/other-programs-detail?theProgram=<?php echo $post->ID; ?>"><?php the_title(); ?></a></p>
                                            <?php
                                            //echo $post->display_start_month."/";
                                            //echo $post->display_start_day."/";
                                            //echo $post->display_start_year."<br />";
                                            //echo $post->post_name."<hr />";
                                            //the_meta();
                                            //echo $post->ID;
                                            //echo $post->post_name;
                                            //echo $post->main_description;
                                            rt_excerpt("useID", $post->ID);
                                            ?>
                                            <hr />
                                            <?php
                                        }
                                    break;
                                } // end listperiod switch - inside 'other' programtype
                            }
                        break;
                    } // end programtype switch
                    //break;
                }
            }
        endwhile;
    }
    
    wp_reset_query();
} // end rt_programs_list_func

add_shortcode('rt_programs', 'rt_programs_list_func');

function rt_checkInCart($passedID, $programID)
{
    // this function is to check to see if a product is already in the woocommerce cart... if so, a 'grayed' button is displayed
    // green button -> include class 'button_rt'
    // gray button -> include class 'button_rt_14
    
    // $passedID is the program product id <- this was named long before we needed the program post id
    // $programID is the program post id
    
    // to add coupon discount stuff, must have $wpdb
    global $wpdb;
    
    $alt_passedID = intval(trim($passedID));
    // for testing -> echo "<center>".$alt_passedID."</center><br />";
    
    // 10-02-2015 - add code to check stock
    $stock = get_post_meta($alt_passedID, '_stock', true);
    
    /* for testing
    if (is_array($stock))
    {
        $stockAnArray = "stock is array";
    }
    elseif (!$stock)
    {
        $stockAnArray = "returning false";
    }
    else
    {
        $stockAnArray = $stock;
    }
    */
    
    // default is green, unless all the seats are full.  then the button is grey with note below.
    if($stock <= 0 || is_null($stock))
    {
        $returnThis = "	<form class=\"cart\" enctype=\"multipart/form-data\" method=\"post\">
                            <button type=\"button\" class=\"single_add_to_cart_button button_rt_14 alt\"></button>
                            <br />
                            <div id=\"notice_div\">
                                This program is currently full. Click <a href=\"".site_url('/program-waiting-list/?programID='.$passedID)."\">here</a> to add your name to the waiting list.<br />
                            </div>
                        </form>";
    }
    else
    {
        $discountsExist = FALSE;
        // must check to see if there is a coupon for material/manuals for this program
        $discountQuery = "SELECT meta_value FROM $wpdb->postmeta WHERE post_id = $programID AND meta_key = 'include_discount'";
        $getDiscountInfo = $wpdb->get_results($discountQuery);
        
        // for testing ->	echo "post id: ".$programID."<br />";
        
        foreach ($getDiscountInfo AS $theDiscountInfo)
        {
            $discountsExist = trim($theDiscountInfo->meta_value) == "yes" ? TRUE : FALSE;
            
            // for testing ->	echo "discount exists: ".$discountsExit."<br />";
        }
        
        if ($discountsExist)
        {
            $couponQuery = "SELECT meta_value FROM $wpdb->postmeta WHERE post_id = $programID AND meta_key = 'material_product_ids'";
            $possible_coupons = $wpdb->get_results($couponQuery);
            
            // must check to see if ID from above is in the string returned below
            foreach ($possible_coupons AS $possible_coupon)
            {
                $productArray = explode(",", $possible_coupon->meta_value);
                
                foreach ($productArray AS $key=>$value)
                {
                    if ($value != "")
                    {
                        $associatedProducts .= "<a href=\"".esc_url(get_permalink($value))."\">".get_the_title($value)."</a><br />";
                    }
                }
            }
            
            $theButton = "<a id=\"rt_start_dialog\" class=\"button_rt add_to_cart_button product_type_simple\" data-russ=\"has_coupon\" data-quantity=\"1\" data-product_id=\"".$passedID."\" rel=\"nofollow\" href=\"/shop/?add-to-cart=".$passedID."\"></a>"; // add an id to the button so jQuery can track for dialog
            
            $dialogMarkup = "	<div id=\"coupon_dialog_message\" title=\"special trainee offer\">
                                Purchase the following materials for this program now, and automatically receive 30% off the regular price:<br />
                                <br />
                                ".$associatedProducts."<br />
                            </div>";
        }
        else
        {
            $theButton = "<a id=\"rt_check_ceu\" class=\"button_rt add_to_cart_button product_type_simple\" data-russ=\"no_coupon\" data-quantity=\"1\" data-product_id=\"".$passedID."\" rel=\"nofollow\" href=\"/shop/?add-to-cart=".$passedID."\"></a>";
            
            $dialogMarkup = "";
        }
        
        
        $returnThis = "<p>".$theButton."</p>".$dialogMarkup;
    }
    
    foreach(WC()->cart->get_cart() AS $cart_item_key => $values)
    {
        $_cartItems = $values['data'];
        
        if ($_cartItems->id == $passedID)
        {
            $returnThis = "	<form class=\"cart\" enctype=\"multipart/form-data\" method=\"post\">
                                <button type=\"button\" class=\"single_add_to_cart_button button_rt_17 alt\"></button>
                            </form>";
        }
    }
    
    // for testing	$returnThis .= "<br /><b>Passed ID: ".$passedID."<br />Altered, Passed ID: ".$alt_passedID."<br />Stock Amount: ".$stockAnArray."</b><br />";
    
    return $returnThis;
}

// this function is used to display the program detail on the public facing side of the site
// 02-19-2019 - adding code to display the custom carats
function rt_ourPrograms_detail_func($atts)
{
    global $post, $wp_query, $wpdb;
    $passed = shortcode_atts(array('programtype' => 'all', 'listperiod' => 'all'), $atts);
    $printThis = ""; // will hold what is to be echoed to the screen
    
    // add the javascript for the truncation effect to the footer
    wp_register_script("do_fits_truncate", get_stylesheet_directory_uri()."/js/fits_detail_truncate.js", array("jquery", "jquery-ui-core"), "0.1", true);
    wp_enqueue_script("do_fits_truncate");
    
    // add javascript for the coupon popup dialog. if there is no 'coupon_dialog_message' div, this should have no effect on anything
    wp_register_script("do_fits_dialog", get_stylesheet_directory_uri()."/js/fits_popup_dialog.js", array("jquery", "jquery-ui-dialog"), "0.1", true);
    wp_enqueue_script("do_fits_dialog");
    wp_enqueue_style("wp-jquery-ui-dialog");
    
    // add javascript for the general scripts for program pages
    wp_register_script("do_fits_program_management", get_stylesheet_directory_uri()."/js/fits_program_management.js", array("jquery"), "0.1", true);
    wp_localize_script("do_fits_program_management", "fits_program_obj", array("ajax_url" => admin_url('admin-ajax.php'), "ceu_product_id" => 8928));
    wp_enqueue_script("do_fits_program_management");
    
    if(isset($wp_query->query_vars["theProgram"]))
    {
        // for testing -> echo "Program ID: ".$wp_query->query_vars["theProgram"]."<br />";
        $theProgramID = $wp_query->query_vars["theProgram"];
        $args = array(	"p" => $theProgramID,
                    "post_type" => "program_pages");
        
        $programPosts = new WP_Query($args);
        
        if($programPosts->have_posts())
        {
            while ($programPosts->have_posts()) : $programPosts->the_post(); 
                foreach($programPosts AS $post)
                {
                    // for testing -> echo "Post ID: ".$post->ID."<br />";
                    if ($post->ID != "")
                    {
                        // to determine whether or not to display the 'add to cart' button, find out if todays date is before the end date
                        $todaysDate = date("U");
                        $theEndDate = date("U", mktime(23,59,59,$post->display_end_month,$post->display_end_day,$post->display_end_year));
                        
                        if($todaysDate <= $theEndDate)
                        {
                            $cartButton = rt_checkInCart($post->shopping_product_id, $post->ID);
                        }
                        else
                        {
                            $cartButton = "";
                        }
                        
                        if (isset($post->program_sponsor) && $post->program_sponsor !== "")
                        {
                            $sponsorLine = "	<div id=\"program_sponsor\" style=\"width: 100%;\">
                                                <h4>".$post->program_sponsor."</h4>
                                            </div>";
                        }
                        else
                        {
                            $sponsorLine = "";
                        }
                        
                        $printThis =  	$sponsorLine."
                                    <div id=\"program_title\" style=\"width: 100%;\">
                                        ".$post->program_title."
                                    </div>
                                    <div id=\"main_description\" style=\"width: 100%;\">
                                        <p class=\"post_description\">".nl2br(str_replace(']]>', ']]&gt', $post->main_description))."</p>
                                        <div id=\"main_description_toggle\">
                                            <a>more &#9660;</a>
                                        </div>
                                    </div>
                                    
                                    <div id=\"sub_section\" style=\"width: 100%; margin-top: 7px;\">
                                        <table id=\"sub_section_table\" style=\"font-size: inherit; width: 100%; border-collapse: collapse; border: 0;\">
                                            <tr>
                                                <td style=\"width: 50%; padding: 5px 10px 5px 5px; border: 0; font-size: inherit; color: #333333;\">
                                                    <h3>date:</h3>
                                                    <p>".nl2br(str_replace(']]>', ']]&gt', $post->left_date))."</p>
                                                    <h3>time:</h3>
                                                    <p>".nl2br(str_replace(']]>', ']]&gt', $post->left_time))."</p>
                                                    <h3>fee:</h3>
                                                    <p>".nl2br(str_replace(']]>', ']]&gt', $post->left_fee))."</p>";
                        
                        if ($post->use_ceu == "yes")
                        {
                            $printThis .= "			<input type=\"checkbox\" id=\"product_ceu\" name=\"product_ceu\" value=\"add_ceu\" /> Add CEs to cart.<br />
                                                    <input type=\"hidden\" id=\"ceu_amount\" name=\"ceu_amount\" value=\"".$post->ceu_variant."\" />";
                        }
                                                    
                        $printThis .=					$cartButton."
                                                </td>
                                                <td style=\"width: 50%; padding: 5px 5px 5px 10px; border: 0; font-size: inherit; color: #333333;\">
                                                    <h3>instructor(s):</h3>
                                                    <p>".nl2br(str_replace(']]>', ']]&gt', $post->right_instructor))."</p>
                                                    <h3>location:</h3>
                                                    <p>".nl2br(str_replace(']]>', ']]&gt', $post->right_location))."</p>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                    <div id=\"caret_section\">
                                        <h3>additional information:</h3>";
                        
                        // caret sections that that have no data in the db will not show.  This will not be changed again without extra payment to do so! russ - 05-25-2017
                        // 02-19-2019 - new carat section display code, after the addition of the custom carates. they did pay for this change.
                        // remember old (default) carats are misspelled with "caret_" the new (custom) carats are correct with "carat_"

                        // ** default carats **
                        // first, get the carat info from wp_fits_progmng_carats
                        // 05-06-2019 now there are no 'default' and 'custom' carats, they are all listed together
                            // old -> $sql_getDefaultCaratInfo = "SELECT * FROM wp_fits_progmng_carats WHERE carat_role = 'default' ORDER BY carat_order ASC";
                        $sql_getDefaultCaratInfo = "SELECT * FROM wp_fits_progmng_carats ORDER BY carat_order ASC";
                        $getDefaultCaratInfo = $wpdb->get_results($sql_getDefaultCaratInfo);

                        // for testing -> $printThis .= "<br />Query Results: ".$wpdb->num_rows."</br />";

                        if($getDefaultCaratInfo)
                        {
                            $fallBackOrder = 1;
                            foreach ($getDefaultCaratInfo AS $defaultCaratInfo)
                            {
                                // get order set in program save for this carat item
                                $positionName = $defaultCaratInfo->carat_textarea_name."Position";
                                $desiredOrderPosition = ($post->$positionName) ? $post->$positionName : $fallBackOrder;

                                // check to see if desired position is different than default
                                if ($defaultCaratInfo->carat_order == $desiredOrderPosition)
                                {
                                    $defaultCaratOrder[$defaultCaratInfo->carat_order]['textarea'] = $defaultCaratInfo->carat_textarea_name;
                                    $defaultCaratOrder[$defaultCaratInfo->carat_order]['title'] = $defaultCaratInfo->carat_title;
                                }
                                else
                                {
                                    $defaultCaratOrder[$desiredOrderPosition]['textarea'] = $defaultCaratInfo->carat_textarea_name;
                                    $defaultCaratOrder[$desiredOrderPosition]['title'] = $defaultCaratInfo->carat_title;
                                }
                                
                                $fallBackOrder++;
                            }

                            ksort($defaultCaratOrder);
                            $defaultCaratCount = count($defaultCaratOrder);
                        }
                        else
                        {
                            // this should never be seen. but for troubleshooting, it's here.
                            $printThis .= "<br /><span style=\"font-weight: 600; color: #ff0000;\">Error no default carat order found!</span><br />";
                        }

                        // next, cycle through the array and print those carats that have information
                        for ($x = 0; $x < count($defaultCaratOrder); $x++)
                        {
                            $y = $x + 1;
                            if (isset($post->$defaultCaratOrder[$y]['textarea']) && trim($post->$defaultCaratOrder[$y]['textarea']) != FALSE)
                            {
                                $printThis .= "	<div class=\"ql-sc-toggle close|open\">
                                                    <div class=\"ql-sc-toggle-preview\">
                                                        <a href=\"#\" class=\"ql-sc-toggle-link\">".$defaultCaratOrder[$y]['title']."</a>
                                                    </div>
                                                    <div class=\"ql-sc-toggle-content\">
                                                        ".nl2br(str_replace(']]>', ']]&gt', $post->$defaultCaratOrder[$y]['textarea']))."
                                                    </div>
                                                </div>";
                            }
                        }

                        // ** custom carats **
                        // first, get the carat info from wp_fits_progmng_carats
                        // ******* 05-06-2019 all carats now handeled above, in the 'default' section *******
                        // $sql_getCustomCaratInfo = "SELECT * FROM wp_fits_progmng_carats WHERE carat_role = 'custom' ORDER BY carat_role ASC";
                        // $getCustomCaratInfo = $wpdb->get_results($sql_getCustomCaratInfo);

                        // // for testing -> $printThis .= "<br />Custom Carat Query Results: ".$wpdb->num_rows."</br />";

                        // if($getCustomCaratInfo)
                        // {
                        //     foreach ($getCustomCaratInfo AS $customCaratInfo)
                        //     {
                        //         // get order set in program save for this carat item
                        //         $positionName = $customCaratInfo->carat_textarea_name."Position";
                        //         $desiredOrderPosition = $post->$positionName;
                        //         //$desiredOrderPosition += 10;

                        //         // check to see if desired position is different than default
                        //         if ($customCaratInfo->carat_order == $desiredOrderPosition)
                        //         {
                        //             $customCaratOrder[$customCaratInfo->carat_order]['textarea'] = $customCaratInfo->carat_textarea_name;
                        //             $customCaratOrder[$customCaratInfo->carat_order]['title'] = $customCaratInfo->carat_title;
                        //         }
                        //         else
                        //         {
                        //             $customCaratOrder[$desiredOrderPosition]['textarea'] = $customCaratInfo->carat_textarea_name;
                        //             $customCaratOrder[$desiredOrderPosition]['title'] = $customCaratInfo->carat_title;
                        //         }
                                
                        //     }

                        //     ksort($customCaratOrder);
                        //     // for testing -> $printThis .= "<br />Custom Carat Array Count: ".count($customCaratOrder)."<br />";
                        // }
                        // else
                        // {
                        //     // this should never be seen. but for troubleshooting, it's here.
                        //     $printThis .= "<br /><span style=\"font-weight: 600; color: #ff0000;\">Error no custom carat order found!</span><br />";
                        // }

                        // // next, cycle through the array and print those carats that have information
                        // for ($x = 0; $x < count($customCaratOrder); $x++)
                        // {
                        //     $y = $x + $defaultCaratCount;

                        //     // for testing -> $printThis .= "x = ".$x."<br />y = ".$y."<br />";

                        //     if (isset($post->$customCaratOrder[$y]['textarea']) && trim($post->$customCaratOrder[$y]['textarea']) != FALSE)
                        //     {
                        //         $printThis .= "	<div class=\"ql-sc-toggle close|open\">
                        //                             <div class=\"ql-sc-toggle-preview\">
                        //                                 <a href=\"#\" class=\"ql-sc-toggle-link\">".$customCaratOrder[$y]['title']."</a>
                        //                             </div>
                        //                             <div class=\"ql-sc-toggle-content\">
                        //                                 ".nl2br(str_replace(']]>', ']]&gt', $post->$customCaratOrder[$y]['textarea']))."
                        //                             </div>
                        //                         </div>";
                        //     }
                        // }
                        
                        // social media shares
                        if(function_exists("synved_social_share_markup"))
                        {
                            $printThis .= "	<br />".synved_social_share_markup();
                        }
                        else
                        {
                            $printThis .= "	error - social sharing not available";
                        }
                        
                        $printThis .= "</div>";
                        break;
                    }
                    else
                    {
                        $printThis = "Program information was not found. (Error 02)";
                    }
                }
            endwhile;
        }
        else
        {
            $printThis = "Passed Program ID: ".$theProgramID." No data returned for this program. (Error 03).";
        }
    }
    else
    {
        $printThis = "An error has occured.  No data has been returned. (Error 01)";
    }
    
    print $printThis;
} // end rt_programs_detail_func

add_shortcode('rt_ourPrograms_detail', 'rt_ourPrograms_detail_func');

/**
 * This code checks the cart for program products (virtual products) and if they are discounted, resets the full price.
 */
function fits_fix_discount_amount($cart_object)
{
    global $wpdb, $woocommerce;
    $returnThis = 0;
    
    // for testing ->	echo "discount fix running<br />";
    
    // get cart contents
    foreach ($cart_object->get_cart() AS $item_values)
    {
        $item_id = $item_values['data']->id; // product ID
        $item_quantity = $item_values['quantity'];
        $item_price = $item_values['data']->price; // the product's original price
        
        // for testing ->	echo "outer loop, product id: ".$item_id."<br />";
        
        // the product id in the cart is not the id we need to get the discount info.
        // get program id using the product id and the meta_key, 'shopping_product_id'
        $getProgramQuery = "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = 'shopping_product_id' AND meta_value = '$item_id'";
        $getProgramId = $wpdb->get_results($getProgramQuery);
        
        if ($getProgramId != FALSE)
        {
            foreach ($getProgramId AS $theProgramId)
            {
                $item_program = $theProgramId->post_id;
            }
            
            $discount_on = get_post_meta($item_program, "include_discount", true);
            
            // for testing ->	echo "item: ".$item_program." has discount: ".$discount_on."<br />";
            
            if (isset($discount_on) && $discount_on == "yes")
            {
                // for testing ->	echo "&nbsp;&nbsp;&nbsp;begin inner proccess<br />";
                
                $discount_amount = get_post_meta($item_program, "discount_percent", true);
                $discount_product_ids = get_post_meta($item_program, "material_product_ids", true);
                $discountProductIdsArray = explode(",", $discount_product_ids);
                
                // for testing ->	echo "discounted products: ".$discount_product_ids."<br />";
                
                // now loop through the cart again to see if products in discount array are in cart
                foreach($cart_object->get_cart() AS $inner_item_values)
                {
                    $inner_item_id = $inner_item_values['data']->id;
                    $inner_item_quantity = $inner_item_values['quantity'];
                    $inner_item_price = $inner_item_values['data']->price;
                    
                    if (in_array($inner_item_id, $discountProductIdsArray))
                    {
                        // do calculation
                        $returnThis += $inner_item_price * ($discount_amount / 100) * $inner_item_quantity;
                    }
                }
            }
        } // end if that looks for product's program's id
        
        // for testing ->	echo "cart price: ".$item_price." current fee value: ".$returnThis."<br />";
    } // end foreach that loops through cart products
        
    
    
    if($returnThis > 0)
    {
        // this adds a line (with no description) that adds the fee to the cart
        $cart_object->add_fee(__("Coupon: special trainee offer", "woocommerce"), -($returnThis), true);
        
        //print "	<script> jQuery(window).load(function(){jQuery(\".cart-discount .amount\").html(\"$".$new_discount_amount."\");});</script>";
        
        // for testing ->	echo "old discount: ".$current_discount_amount."<br />new discount: ".$new_discount_amount."<br />difference should be: ".$returnThis."<br />";
    }
} // end fits_fix_discount_amount function

add_action("woocommerce_cart_calculate_fees", "fits_fix_discount_amount", 10);
//add_action("woocommerce_before_calculate_totals", "fits_fix_discount_amount", 10);

/**
 * The code to handle the ajax (admin-ajax) stuff when the CEU checkbox is checked.
 * See the file 'fits_program_management.js' for the ajax call.
 */
function fits_add_ceu_data_callback()
{
    global $woocommerce; //, $product, $post;
    
    // the data sent by ajax
    $variantId = $_POST['id']; // this is the product's variant id. the product id for ceus is always 8928.
    $theQuantity = $_POST['quantity']; // this should always be 1... but just in case, get what's sent.
    
    // the variantId should never be empty, but check just in case
    if(!empty($variantId))
    {
        //$variations = fits_get_ceu_variation_data($variantId);
        $variations = fits_get_ceu_variation_data($variantId);
        
        $variant_args["attribute_program-hours"] = $variations;
        
        // when adding a variant to cart args are (product_id, quantity, variant_id, variant_attribute_array, cart_item_data)
        $woocommerce->cart->add_to_cart(8928, $theQuantity, $variantId, $variant_args, null);
        //fits_refresh_mini_cart();
        
        print "true";
    }
    else
    {
        // for testing -> $variations = "data problem - error 33";
        print "false";
    }
} // end fits_add_ceu_data_callback function

add_action("wp_ajax_fits_add_ceu_data", "fits_add_ceu_data_callback");
add_action("wp_ajax_nopriv_fits_add_ceu_data", "fits_add_ceu_data_callback");

function fits_get_ceu_variation_data($passed_variantId)
{
    $variation_details = get_post_meta($passed_variantId, "attribute_program-hours", true);
    
    return $variation_details;
} // end fits_get_ceu_variation_data function

/**
 * Refresh mini-cart
 * This should actually refresh it any time the cart is updated.
 */
add_filter("woocommerce_add_to_cart_fragments", function($fragments)
    {
        ob_start();
        ?>
            <div class="widget_shopping_cart_content">
                <?php woocommerce_mini_cart(); ?>
            </div>
        <?php
        
        $fragments['div.widget_shopping_cart_content'] = ob_get_clean();
        
        return $fragments;
    });
/* end update mini-cart */

/* *** end get ceu data for program *** */

function rt_otherPrograms_detail_func($atts)
{
    global $post, $wp_query;
    $passed = shortcode_atts(array('programtype' => 'all', 'listperiod' => 'all'), $atts);
    $printThis = ""; // will hold what is to be echoed to the screen
    
    // add the javascript for the truncation effect to the footer
    wp_register_script("do_fits_truncate", get_stylesheet_directory_uri()."/js/fits_detail_truncate.js", array("jquery", "jquery-ui-core"), "0.1", true);
    wp_enqueue_script("do_fits_truncate");
    
    if(isset($wp_query->query_vars["theProgram"]))
    {
        // for testing ->echo "Program ID: ".$wp_query->query_vars["theProgram"];
        $theProgramID = $wp_query->query_vars["theProgram"];
        $args = array(	"p" => $theProgramID,
                    "post_type" => "program_pages");
        
        $programPosts = new WP_Query($args);
        
        if($programPosts->have_posts())
        {
            while ($programPosts->have_posts()) : $programPosts->the_post(); 
                foreach($programPosts AS $post)
                {
                    if ($post->ID != "")
                    {
                        // to determine whether or not to display the 'add to cart' button, find out if todays date is before the end date
                        $todaysDate = date("U");
                        $theEndDate = date("U", mktime(23,59,59,$post->display_end_month,$post->display_end_day,$post->display_end_year));
                        
                        if (isset($post->program_sponsor) && $post->program_sponsor !== "")
                        {
                            $sponsorLine = "	<div id=\"program_sponsor\" style=\"width: 100%;\">
                                                <h4>".$post->program_sponsor."</h4>
                                            </div>";
                        }
                        else
                        {
                            $sponsorLine = "";
                        }
                        
                        $printThis = 	$sponsorLine."
                                    <div id=\"program_title\" style=\"width: 100%;\">
                                        ".$post->program_title."
                                    </div>
                                    <div id=\"main_description\" style=\"width: 100%;\">
                                        <p>".nl2br(str_replace(']]>', ']]&gt', $post->main_description))."</p>
                                        <div id=\"main_description_toggle\">
                                            <a>more &#9660;</a>
                                        </div>
                                    </div>
                                    <div id=\"sub_section\" style=\"width: 100%; margin-top: 7px;\">
                                        <table id=\"sub_section_table\" style=\"font-size: inherit; width: 100%; border-collapse: collapse; border: 0;\">
                                            <tr>
                                                <td  class=\"programs_table\" style=\"width: 50%; padding: 5px 10px 5px 5px; border: 0; font-size: inherit; color: #333333;\">
                                                    <h3>date:</h3>
                                                    <p>".nl2br(str_replace(']]>', ']]&gt', $post->left_date))."</p>
                                                    <h3>time:</h3>
                                                    <p>".nl2br(str_replace(']]>', ']]&gt', $post->left_time))."</p>
                                                    <h3>fee:</h3>
                                                    <p>".nl2br(str_replace(']]>', ']]&gt', $post->left_fee))."</p>
                                                </td>
                                                <td  class=\"programs_table\" style=\"width: 50%; padding: 5px 5px 5px 10px; border: 0; font-size: inherit; color: #333333;\">
                                                    <h3>instructor(s):</h3>
                                                    <p>".nl2br(str_replace(']]>', ']]&gt', $post->right_instructor))."</p>
                                                    <h3>location:</h3>
                                                    <p>".nl2br(str_replace(']]>', ']]&gt', $post->right_location))."</p>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                    <div id=\"caret_section\">
                                        <h3>additional information:</h3>";
                                        
                        if (isset($post->caret_instructorBio) && trim($post->caret_instructorBio) != FALSE)
                        {
                            $printThis .= "	<p>
                                            <div class=\"ql-sc-toggle close|open\">
                                                <div class=\"ql-sc-toggle-preview\">
                                                    <a href=\"#\" class=\"ql-sc-toggle-link\">instructor bios</a>
                                                </div>
                                                <div class=\"ql-sc-toggle-content\">
                                                    ".nl2br(str_replace(']]>', ']]&gt', $post->caret_instructorBio))."
                                                </div>
                                            </div>
                                            </p>";
                        }
                        
                        if (isset($post->caret_testimonials) && trim($post->caret_testimonials) != FALSE)
                        {
                            $printThis .= "	<p>
                                            <div class=\"ql-sc-toggle close|open\">
                                                <div class=\"ql-sc-toggle-preview\">
                                                    <a href=\"#\" class=\"ql-sc-toggle-link\">testimonials</a>
                                                </div>
                                                <div class=\"ql-sc-toggle-content\">
                                                    ".nl2br(str_replace(']]>', ']]&gt', $post->caret_testimonials))."
                                                </div>
                                            </div>
                                            </p>";
                        }
                        
                        if (isset($post->caret_continuingEducation) && trim($post->caret_continuingEducation) != FALSE)
                        {
                            $printThis .= "	<p>
                                            <div class=\"ql-sc-toggle close|open\">
                                                <div class=\"ql-sc-toggle-preview\">
                                                    <a href=\"#\" class=\"ql-sc-toggle-link\">continuing education</a>
                                                </div>
                                                <div class=\"ql-sc-toggle-content\">
                                                    ".nl2br(str_replace(']]>', ']]&gt', $post->caret_continuingEducation))."
                                                </div>
                                            </div>
                                            </p>";
                        }
                        
                        if (isset($post->caret_directionsParking) && trim($post->caret_directionsParking) != FALSE)
                        {
                            $printThis .= "	<p>
                                            <div class=\"ql-sc-toggle close|open\">
                                                <div class=\"ql-sc-toggle-preview\">
                                                    <a href=\"#\" class=\"ql-sc-toggle-link\">directions and parking</a>
                                                </div>
                                                <div class=\"ql-sc-toggle-content\">
                                                    ".nl2br(str_replace(']]>', ']]&gt', $post->caret_directionsParking))."
                                                </div>
                                            </div>
                                            </p>";
                        }
                        
                        if (isset($post->caret_forMoreInfo) && trim($post->caret_forMoreInfo) != FALSE)
                        {
                            $printThis .= "	<p>
                                            <div class=\"ql-sc-toggle close|open\">
                                                <div class=\"ql-sc-toggle-preview\">
                                                    <a href=\"#\" class=\"ql-sc-toggle-link\">for more information</a>
                                                </div>
                                                <div class=\"ql-sc-toggle-content\">
                                                    ".nl2br(str_replace(']]>', ']]&gt', $post->caret_forMoreInfo))."
                                                </div>
                                            </div>
                                            </p>";
                        }
                        
                        if (isset($post->caret_syllabus) && trim($post->caret_syllabus) != FALSE)
                        {
                            $printThis .= "	<p>
                                            <div class=\"ql-sc-toggle close|open\">
                                                <div class=\"ql-sc-toggle-preview\">
                                                    <a href=\"#\" class=\"ql-sc-toggle-link\">syllabus</a>
                                                </div>
                                                <div class=\"ql-sc-toggle-content\">
                                                    ".nl2br(str_replace(']]>', ']]&gt', $post->caret_syllabus))."
                                                </div>
                                            </div>
                                            </p>";
                        }
                        
                        if (isset($post->caret_programSchedule) && trim($post->caret_programSchedule) != FALSE)
                        {
                            $printThis .= "	<p>
                                            <div class=\"ql-sc-toggle close|open\">
                                                <div class=\"ql-sc-toggle-preview\">
                                                    <a href=\"#\" class=\"ql-sc-toggle-link\">program schedule</a>
                                                </div>
                                                <div class=\"ql-sc-toggle-content\">
                                                    ".nl2br(str_replace(']]>', ']]&gt', $post->caret_programSchedule))."
                                                </div>
                                            </div>
                                            </p>";
                        }
                        
                        if (isset($post->caret_financialAid) && trim($post->caret_financialAid) != FALSE)
                        {
                            $printThis .= "	<p>
                                            <div class=\"ql-sc-toggle close|open\">
                                                <div class=\"ql-sc-toggle-preview\">
                                                    <a href=\"#\" class=\"ql-sc-toggle-link\">financial aid</a>
                                                </div>
                                                <div class=\"ql-sc-toggle-content\">
                                                    ".nl2br(str_replace(']]>', ']]&gt', $post->caret_financialAid))."
                                                </div>
                                            </div>
                                            </p>";
                        }
                        
                        if (isset($post->caret_whatToBring) && trim($post->caret_whatToBring) != FALSE)
                        {
                            $printThis .= "	<p>
                                            <div class=\"ql-sc-toggle close|open\">
                                                <div class=\"ql-sc-toggle-preview\">
                                                    <a href=\"#\" class=\"ql-sc-toggle-link\">what to bring</a>
                                                </div>
                                                <div class=\"ql-sc-toggle-content\">
                                                    ".nl2br(str_replace(']]>', ']]&gt', $post->caret_whatToBring))."
                                                </div>
                                            </div>
                                            </p>";
                        }
                        
                        if (isset($post->caret_preRegistrationInfo) && trim($post->caret_preRegistrationInfo) != FALSE)
                        {
                            $printThis .= "	<p>
                                            <div class=\"ql-sc-toggle close|open\">
                                                <div class=\"ql-sc-toggle-preview\">
                                                    <a href=\"#\" class=\"ql-sc-toggle-link\">pre-registration information</a>
                                                </div>
                                                <div class=\"ql-sc-toggle-content\">
                                                    ".nl2br(str_replace(']]>', ']]&gt', $post->caret_preRegistrationInfo))."
                                                </div>
                                            </div>
                                            </p>";
                        }
                        
                        // social media shares
                        if(function_exists("synved_social_share_markup"))
                        {
                            $printThis .= "	<br />".synved_social_share_markup();
                        }
                        else
                        {
                            $printThis .= "	error - social sharing not available";
                        }
                        
                        $printThis .= "</div>";
                        break;
                    }
                    else
                    {
                        $printThis = "Program information was not found. (Error 02)";
                    }
                }
            endwhile;
        }
        else
        {
            $printThis = "Passed Program ID: ".$theProgramID." No data returned for this program. (Error 03).";
        }
    }
    else
    {
        $printThis = "An error has occured.  No data has been returned. (Error 01)";
    }
    
    print $printThis;
} // end rt_programs_detail_func

add_shortcode('rt_otherPrograms_detail', 'rt_otherPrograms_detail_func');

// this is to get wordpress to accept url variables added to the end of permalinks/name links
function parameter_queryVars($urlVars)
{
    $urlVars[] = "theProgram";
    return $urlVars;
} // end parameter_queryVars function

add_filter("query_vars", "parameter_queryVars");

function include_programs_template($template_path)
{
    if(get_post_type() == "program_pages")
    {
        if(is_single())
        {
            // first check to see if it's in the them dir, if not, check plugin dir
            if($theme_file = locate_template(array('single-program_pages.php')))
            {
                $template_path = $theme_file;
            }
            else
            {
                $template_path = plugin_dir_path(__FILE__)."/single-program_pages.php";
            }
        }
    }
    
    return $template_path;
} // end include_programs_template function

add_filter('template_include', 'include_programs_template', 1);

function include_resources_template($template_path)
{
    global $wp_query, $post;
    
    if(get_post_type() == "resources_pages")
    {
        if(is_single())
        {
            // first check to see if it's in the theme dir, if not, check plugin dir
            if($theme_file = locate_template(array('single-resources_pages.php')))
            {
                $template_path = $theme_file;
            }
            else
            {
                $template_path = plugin_dir_path(__FILE__)."/single-resources_pages.php";
            }
        }
        
        /*new
        foreach((array)get_the_category($post->ID) as $cat) :
            
            if ($cat->term_id == 79 || $cat->term_id == 121 || $cat->term_id == 100 || $cat->term_id == 136 || $cat->term_id == 151 || $cat->term_id == 172)
            {
                switch($cat->term_id)
                {
                    case 121:
                        return SINGLE_PATH."/conferences_single.php";
                    break;
                    
                    case 79:
                        return SINGLE_PATH."/organizations_single.php";
                    break;
                    
                    case 100:
                        return SINGLE_PATH."/literature_single.php";
                    break;
                    
                    case 136:
                        return SINGLE_PATH."/degreePrograms_single.php";
                    break;
                    
                    case 151:
                        return SINGLE_PATH."/listserv_single.php";
                    break;
                    
                    case 172:
                        return SINGLE_PATH."/individualPractitioners_single.php";
                    break;
                }
            }
            
        endforeach;
        
        // if we get this far, check for single post file or default
        if (file_exists(SINGLE_PATH."/single.php"))
        {
            return SINGLE_PATH."/single.php";
            // for testing ->	return SINGLE_PATH."/conferences_single.php";
        }
        elseif (file_exists(SINGLE_PATH."/default.php"))
        {
            return SINGLE_PATH."/default.php";
        }
        
        return $single;
        // end new */
    }
    
    return $template_path;
} // end include_resources_template function

//add_filter('template_include', 'include_resources_template', 1);

// shortcode for the social media icons - mostly used in the sidebars
function rt_socialMedia_links()
{
    return "	<a href=\"https://www.facebook.com/uclartsandhealing\" target=\"_blank\"><img src=\"".get_stylesheet_directory_uri()."/facebook_icon_36x36.png\" border=\"0\" /></a>
            <a href=\"https://www.instagram.com/uclartsandhealing/\" target=\"_blank\"><img src=\"".get_stylesheet_directory_uri()."/instagram_icon_36x36.png\" border=\"0\" /></a>
            <a href=\"https://twitter.com/uclartshealing\" target=\"_blank\"><img src=\"".get_stylesheet_directory_uri()."/twitter_icon_36x36.png\" border=\"0\" /></a>
            <a href=\"https://www.youtube.com/user/UCLArtsandHealing/videos\" target=\"_blank\"><img src=\"".get_stylesheet_directory_uri()."/youTube_icon_36x36.png\" border=\"0\" /></a>
            <a href=\"https://vimeo.com/uclartsandhealing\" target=\"_blank\"><img src=\"".get_stylesheet_directory_uri()."/vimeo_icon_36x36.png\" border=\"0\" /></a>";
} // end rt_socialMedia_links function

add_shortcode('russ_socialMedia', 'rt_socialMedia_links');

/* add login link to woocommerece customer confirmation emails */
function fits_addLoginLink_woo_customer_emails()
{
    echo "<p><b>You can login to your account <a href=\"".site_url("/my-account/")."\" target=\"blank\">here</a> to view your order details.</b></p>";
}

add_action ("woocommerce_email_before_order_table", "fits_addLoginLink_woo_customer_emails", 10, 1);

function wc_extra_register_fields() {
    ?>

    <p class="form-row form-row-first">
    <label for="reg_billing_first_name"><?php _e('First name', 'woocommerce'); ?> <span class="required">*</span></label>
    <input type="text" class="input-text" name="billing_first_name" id="reg_billing_first_name" value="<?php if ( ! empty( $_POST['billing_first_name'] ) ) esc_attr_e( $_POST['billing_first_name'] ); ?>" />
    </p>

    <p class="form-row form-row-last">
    <label for="reg_billing_last_name"><?php _e('Last name', 'woocommerce'); ?> <span class="required">*</span></label>
    <input type="text" class="input-text" name="billing_last_name" id="reg_billing_last_name" value="<?php if ( ! empty( $_POST['billing_last_name'] ) ) esc_attr_e( $_POST['billing_last_name'] ); ?>" />
    </p>
    
    <p class="form-row form-row-wide">
    <label for="reg_billing_address_1"><?php _e('Address 1', 'woocommerce'); ?></label>
    <input type="text" class="input-text" name="billing_address_1" id="reg_billing_address_1" value="<?php if (!empty($_POST['billing_address_1'])) esc_attr_e($_POST['billing_address_1']); ?>" />
    </p>
    
    <p class="form-row form-row-wide">
    <label for="reg_billing_address_2"><?php _e('Address 2', 'woocommerce'); ?></label>
    <input type="text" class="input-text" name="billing_address_2" id="reg_billing_address_2" value="<?php if (!empty($_POST['billing_address_2'])) esc_attr_e($_POST['billing_address_2']); ?>" />
    </p>

    <p class="form-row form-row-first">
    <label for="reg_billing_city"><?php _e('City', 'woocommerce'); ?></label>
    <input type="text" class="input-text" name="billing_city" id="reg_billing_city" value="<?php if (!empty($_POST['billing_city'])) esc_attr_e($_POST['billing_city']); ?>" />
    </p>
    
    <p class="form-row form-row-last">
    <label for="reg_billing_state"><?php _e('State', 'woocommerce'); ?></label>
    <input type="text" class="input-text" name="billing_state" id="reg_billing_state" value="<?php if (!empty($_POST['billing_state'])) esc_attr_e($_POST['billing_state']); ?>" />
    </p>
    
    <p class="form-row form-row-first">
    <label for="reg_billing_postcode"><?php _e('Zip', 'woocommerce'); ?></label>
    <input type="text" class="input-text" name="billing_postcode" id="reg_billing_postcode" value="<?php if (!empty($_POST['billing_postcode'])) esc_attr_e($_POST['billing_postcode']); ?>" />
    </p>
    
    <p class="form-row form-row-last">
    <label for="reg_billing_country"><?php _e('Country', 'woocommerce'); ?></label>
    <input type="text" class="input-text" name="billing_country" id="reg_billing_country" value="<?php if (!empty($_POST['billing_country'])) esc_attr_e($_POST['billing_country']); ?>" />
    </p>
    
    <p class="form-row form-row-first">
    <label for="reg_billing_phone"><?php _e('Phone', 'woocommerce'); ?></label>
    <input type="text" class="input-text" name="billing_phone" id="reg_billing_phone" value="<?php if (!empty($_POST['billing_phone'])) esc_attr_e($_POST['billing_phone']); ?>" />
    </p>
    
    <p class="form-row form-row-last">
    <label for="reg_billing_ext"><?php _e('Ext', 'woocommerce'); ?></label>
    <input type="text" class="input-text" name="billing_ext" id="reg_billing_ext" value="<?php if (!empty($_POST['billing_ext'])) esc_attr_e($_POST['billing_ext']); ?>" />
    </p>
    
    <div class="clear"></div>
    
    <p class="form-row form-row-wide">
    <lable for="reg_billing_profession"><?php _e('Profession', 'woocommerce'); ?></lable>
    <input type="text" class="input-text" name="billing_profession" id="reg_billing_profession" value="<?php if (!empty($_POST['billing_profession'])) esc_attr_e($_POST['billing_profession']); ?>" />
    </p>
    
    <p class="form-row form-row-wide">
    <lable for="reg_billing_jobTitle"><?php _e('Job Title', 'woocommerce'); ?></lable>
    <input type="text" class="input-text" name="billing_jobTitle" id="reg_billing_jobTitle" value="<?php if (!empty($_POST['billing_jobTitle'])) esc_attr_e($_POST['billing_jobTitle']); ?>" />
    </p>
    
    <p class="form-row form-row-wide">
    <lable for="reg_billing_employer"><?php _e('Employer', 'woocommerce'); ?></lable>
    <input type="text" class="input-text" name="billing_employer" id="reg_billing_employer" value="<?php if (!empty($_POST['billing_employer'])) esc_attr_e($_POST['billing_employer']); ?>" />
    </p>
    
    <p class="form-row form-row-wide">
    <lable for="reg_billing_hearAboutUs"><?php _e('How did you hear about us?', 'woocommerce'); ?></lable>
    <input type="text" class="input-text" name="billing_hearAboutUs" id="reg_billing_hearAboutUs" value="<?php if (!empty($_POST['billing_hearAboutUs'])) esc_attr_e($_POST['billing_hearAboutUs']); ?>" />
    </p>
    <?php
}

add_action( 'woocommerce_register_form_start', 'wc_extra_register_fields' );

/**
 * Validate the name fields added above.
 *
 * @param string $username			current username
 * @param string $email				current email
 * @param object $validation_errors	WP_Error object
 *
 * @return void
 */
function wc_validate_extra_register_fields($username, $email, $validation_errors)
{
    if (isset($_POST['billing_first_name']) && empty($_POST['billing_first_name']))
    {
        $validation_errors->add('billing_first_name_error', __('<strong>Error</strong>: First name is required.', 'woocommerce'));
    }
    
    if (isset($_POST['billing_last_name']) && empty($_POST['billing_last_name']))
    {
        $validation_errors->add('billing_last_name_error', __('<strong>Error</strong>: Last name is required.', 'woocommerce'));
    }
} // end wc_validate_extra_register_fields function

add_action('woocommerce_register_post', 'wc_validate_extra_register_fields', 10, 3);

/**
 *
 * Save the extra fields added above.
 *
 * @param int $customer_id		current customer id
 *
 * @return $data				array of form values to pass into WP / WC functions
 */
function wc_save_extra_register_fields($customer_id)
{
    if (isset($_POST['billing_first_name']))
    {
        // first, the WP default first name field
        update_user_meta($customer_id, 'first_name', sanitize_text_field($_POST['billing_first_name']));
        
        // now the extra field first name
        update_user_meta($customer_id, 'billing_first_name', sanitize_text_field($_POST['billing_first_name']));
        
        // save shipping info
        update_user_meta($customer_id, 'shipping_first_name', sanitize_text_field($_POST['shipping_first_name']));
    }
    
    if (isset($_POST['billing_last_name']))
    {
        // first, the WP default last name field
        update_user_meta($customer_id, 'last_name', sanitize_text_field($_POST['billing_last_name']));
        
        // now the extra field last name
        update_user_meta($customer_id, 'billing_last_name', sanitize_text_field($_POST['billing_last_name']));
        
        // save shipping info
        update_user_meta($customer_id, 'shipping_last_name', sanitize_text_field($_POST['shipping_last_name']));
    }
    
    if (isset($_POST['billing_address_1']))
    {
        // save billing info
        update_user_meta($customer_id, 'billing_address_1', sanitize_text_field($_POST['billing_address_1']));
        
        // save shipping info
        update_user_meta($customer_id, 'shipping_address_1', sanitize_text_field($_POST['shipping_address_1']));
    }
    
    if (isset($_POST['billing_address_2']))
    {
        // save billing info
        update_user_meta($customer_id, 'billing_address_2', sanitize_text_field($_POST['billing_address_2']));
        
        // save shipping info
        update_user_meta($customer_id, 'shipping_address_2', sanitize_text_field($_POST['shipping_address_2']));
    }
    
    if (isset($_POST['billing_city']))
    {
        // save billing info
        update_user_meta($customer_id, 'billing_city', sanitize_text_field($_POST['billing_city']));
        
        // save shipping info
        update_user_meta($customer_id, 'shipping_city', sanitize_text_field($_POST['shipping_city']));
    }
    
    if (isset($_POST['billing_state']))
    {
        // save billing info
        update_user_meta($customer_id, 'billing_state', sanitize_text_field($_POST['billing_state']));
        
        // save shipping info
        update_user_meta($customer_id, 'shipping_state', sanitize_text_field($_POST['shipping_state']));
    }
    
    if (isset($_POST['billing_postcode']))
    {
        // save billing info
        update_user_meta($customer_id, 'billing_postcode', sanitize_text_field($_POST['billing_postcode']));
        
        // save shipping info
        update_user_meta($customer_id, 'shipping_postcode', sanitize_text_field($_POST['shipping_postcode']));
    }
    
    if (isset($_POST['billing_country']))
    {
        // save billing info
        update_user_meta($customer_id, 'billing_country', sanitize_text_field($_POST['billing_country']));
        
        // save shipping info
        update_user_meta($customer_id, 'shipping_country', sanitize_text_field($_POST['shipping_country']));
    }
    
    if (isset($_POST['billing_phone']))
    {
        update_user_meta($customer_id, 'billing_phone', sanitize_text_field($_POST['billing_phone']));
    }
    
    if (isset($_POST['billing_ext']))
    {
        update_user_meta($customer_id, 'billing_ext', sanitize_text_field($_POST['billing_ext']));
    }
    
    if (isset($_POST['billing_profession']))
    {
        update_user_meta($customer_id, 'billing_profession', sanitize_text_field($_POST['billing_profession']));
    }
    
    if (isset($_POST['billing_jobTitle']))
    {
        update_user_meta($customer_id, 'billing_jobTitle', sanitize_text_field($_POST['billing_jobTitle']));
    }
    
    if (isset($_POST['billing_employer']))
    {
        update_user_meta($customer_id, 'billing_employer', sanitize_text_field($_POST['billing_employer']));
    }
    
    if (isset($_POST['billing_hearAboutUs']))
    {
        update_user_meta($customer_id, 'billing_hearAboutUs', sanitize_text_field($_POST['billing_hearAboutUs']));
    }
} // end wc_save_extra_register_fields function

add_action('woocommerce_created_customer', 'wc_save_extra_register_fields');

/* add fields to the checkout form - only when user is a guest */

/**
 * Add field(s) to the woocommerce checkout form
 */
function wc_custom_checkout_field($checkout)
{
    echo "<div id=\"wc_custom_checkout_field\">";
    
    if(is_user_logged_in() === false)
    {
        // add the profession? and employer? fields
        woocommerce_form_field("checkout_profession", array(
            "type"		=> "text",
            "class"		=> array("wc_custom_field_class form-row-wide"),
            "label"		=> __("Profession"),
            "placeholder"	=> __("")
            ),
            $checkout->get_value("checkout_profession"));
        
        woocommerce_form_field("checkout_employer", array(
            "type"		=> "text",
            "class"		=> array("wc_custom_field_class form-fow-wide"),
            "label"		=> __("Employer"),
            "placeholder"	=> __("")
            ),
            $checkout->get_value("checkout_employer"));
    }
    
    woocommerce_form_field("checkout_hearAboutUs", array(
        "type"		=> "text",
        "class"		=> array("wc_custom_field_class form-row-wide"),
        "label"		=> __("How did you hear about us?"),
        "placeholder"	=> __("")
        ),
        $checkout->get_value("checkout_hearAboutUs"));
    
    echo "</div>";
} // end wc_custom_checkout_field function

add_action('woocommerce_after_order_notes', 'wc_custom_checkout_field');

/**
 * Save the new field(s) to db
 */
function wc_custom_checkout_save($order_id)
{
    if(isset($_POST['checkout_hearAboutUs']) && !empty($_POST['checkout_hearAboutUs']))
    {
        update_post_meta($order_id, 'checkout_hearAboutUs', sanitize_text_field($_POST['checkout_hearAboutUs']));
    }
    
    if(isset($_POST['checkout_profession']) && !empty($_POST['checkout_profession']))
    {
        update_post_meta($order_id, 'checkout_profession', sanitize_text_field($_POST['checkout_profession']));
    }
    
    if(isset($_POST['checkout_employer']) && !empty($_POST['checkout_employer']))
    {
        update_post_meta($order_id, 'checkout_employer', sanitize_text_field($_POST['checkout_employer']));
    }
} // end wc_custom_checkout_save function

add_action('woocommerce_checkout_update_order_meta', 'wc_custom_checkout_save');

/**
 * This will display the custom field in the admin order edit page
 */
function wc_custom_checkout_admin_display($order)
{
    echo "<p><strong>".__("How they heard about us?")."&nbsp;</strong> ".get_post_meta($order->id, "checkout_hearAboutUs", true)."</p>";
    echo "<p><strong>".__("Profession")."&nbsp;</strong>".get_post_meta($order->id, "checkout_profession", true)."</p>";
    echo "<p><strong>".__("Employer")."&nbsp;</strong>".get_post_meta($order->id, "checkout_employer", true)."</p>";
} // end wc_custom_checkout_admin_display function

add_action('woocommerce_admin_order_data_after_billing_address', 'wc_custom_checkout_admin_display', 10, 1);

/**
 * This function converts phone numbers to the format (xxx) xxx-xxxx.
 * It was originally developed for the emails sent by woocommerce.  But can be used anywhere.
 */
function rt_phone_format($theNum)
{
    $strLength = strlen($theNum);
    
    if($strLength == 7)
    {
        // american number without area code
        $exchange = substr($theNum, 0, 3);
        $lineNumber = substr($theNum, -4);
        $returnString = $exchange."-".$lineNumber;
    }
    elseif($strLength == 10)
    {
        // american number with area code
        $areaCode = substr($theNum, 0, 3);
        $exchange = substr($theNum, 3, 3);
        $lineNumber = substr($theNum, -4);
        $returnString = "(".$areaCode.") ".$exchange."-".$lineNumber;
    }
    else
    {
        // the number does not appear to be an american number, just return what was sent in
        $returnString = $theNum;
    }
    
    return $returnString;
} // end rt_phone_format function

// functions/hooks/actions added to do custom woocommerce product page

if (!function_exists('rt_woocommerce_template_single_price'))
{

    /**
     * Output the product price.
     * Custom template of content-single-product.php in child theme dir
     *
     * @access public
     * @subpackage	Product
     * @return void
     */
    function rt_woocommerce_template_single_price()
    {
        wc_get_template('single-product/price.php');
    }
}

add_action( 'rt_woocommerce_template_single_price', 'woocommerce_template_single_price', 10 );

if (!function_exists('rt_woocommerce_simple_add_to_cart'))
{

    /**
     * Output the simple product add to cart area.
     * Custom template of content-single-product.php in child theme dir
     *
     * @access public
     * @subpackage	Product
     * @return void
     */
    function woocommerce_simple_add_to_cart()
    {
        wc_get_template('single-product/add-to-cart/simple.php');
    }
}

add_action('rt_woocommerce_simple_add_to_cart', 'woocommerce_simple_add_to_cart', 30);

if (!function_exists('rt_woocommerce_tabs'))
{
    /**
     * Output the product tabs.
     *
     * @access public
     * @subpackage	Product/Tabs
     * @return void
     */
    function woocommerce_output_product_data_tabs()
    {
        wc_get_template('single-product/tabs/tabs.php');
    }
}

add_action('rt_woocommerce_tabs', 'woocommerce_output_product_data_tabs', 20);

if (!function_exists('rt_woocommerce_related_products'))
{
    /**
     * Output the related products.
     *
     * @access public
     * @subpackage	Product
     * @return void
     */
    function woocommerce_output_related_products()
    {
        $args = array(
                    'posts_per_page' => 3,
                    'columns' => 3,
                    'orderby' => 'rand');
        
        woocommerce_related_products(apply_filters('woocommerce_output_related_products_args', $args));
    }
}

add_action('rt_woocommerce_related_products', 'woocommerce_output_related_products', 40);

function rt_woocommerce_product_script()
{
    /**
     * Enqueue the script that is used on the single product pages
     */
    
    // register the script
    wp_register_script("rt-singleProduct-custom-script", get_stylesheet_directory_uri()."/js/fits_singleProduct.js", array('jquery'), "11232016", true);
    
    // enqueue the script
    wp_enqueue_script("rt-singleProduct-custom-script");
}

add_action("wp_enqueue_scripts", "rt_woocommerce_product_script");

// this area of code is for setting up the 'sale' and 'image' hooks so that I can put them wherever I want.
// woocommerce lumps them together in the hook 'woocommerce_before_single_project_summary'
remove_action("woocommerce_before_single_product_summary", "woocommerce_show_product_sale_flash", 10);
remove_action("woocommerce_before_single_product_summary", "woocommerce_show_product_images", 20);

add_action("rt_woocommerce_sale", "woocommerce_show_product_sale_flash", 10);
add_action("rt_woocommerce_images", "woocommerce_show_product_images", 20);
// now in CHILD/woocommerce/content-single-product.php, call these with do_action("rt_woocommerce_sale"); and do_action("rt_woocommerce_images");

// redirect the user after they reset their password. this is here because woocommerce controls all account stuff after installed
function fits_after_password_reset_redirect($user)
{
    if (wp_redirect(get_permalink(9032)))
    {
        exit;
    }
    else
    {
        wp_redirect(site_url());
    }
} // end fits_password_reset_redirect function

add_action("woocommerce_customer_reset_password", "fits_after_password_reset_redirect");

/**
 * This checkes cookies to see if the user has made a donation
 * and then adds that donation to the cart.
 *
 * @since child 0.2
 */
function rt_add_product_to_cart()
{
    if (!is_admin())
    {
        $productId = 4927;
        $found = false;
        
        // check if cookie exists and has a value other than zero
        if (isset($_COOKIE['donation_amount']) && $_COOKIE['donation_amount'] > 0)
        {
            // cookie exists and there is a donation amount
            $donationAmount = $_COOKIE['donation_amount'];
            
            /* for testing
            print "	<div id=\"cookie_results\" style=\"background: #ffffff; z-index: 1500;\">
                        <center>
                            <br />
                            Inside the cookie if. And amount: ".$donationAmount."
                            <br />
                        </center>
                    </div>";
            */
                
            // now check if product is already in the cart
            if (sizeof(WC()->cart->get_cart()) > 0)
            {
                foreach(WC()->cart->get_cart() AS $cart_item_key => $values)
                {
                    $_product = $values['data'];
                    if ($_product->id == $productId)
                    {
                        if($_product->price == 1 || $_product->price == 1.00)
                        {
                            // this far means the user made a donation, the donation product is in the cart AND it's still set to $1.00
                            // must give it the amount they entered in cookie
                            $_product->price = $donationAmount;
                            $_product->quantity = 1;
                            
                            // then to make sure the amount isn't added twice, clear the cookie
                            setcookie("donation_amount", 0, time()+3600*24*100, COOKIEPATH, COOKIE_DOMAIN, false);
                        }
                    }
                }
            }
        }
        /* shouldn't need this anymore
        else
        {
            // check to see if the cookie exists and is equal to zero. if so, delete the item from the cart
            if (isset($_COOKIE['donation_amount']) && $_COOKIE['donation_amount'] == 0)
            {
                if (sizeof(WC()->cart->get_cart()) > 0)
                {
                    foreach(WC()->cart->get_cart() AS $cart_item_key => $values)
                    {
                        $_product = $values['data'];
                        if ($_product->id == $productId)
                        {
                            // items are referenced in the cart by a unique id, generate it here
                            $unique_id = WC()->cart->generate_cart_id($productId);
                            // remove from cart
                            unset(WC()->cart->cart_contents[$unique_id]);
                        }
                    }
                }
            }
        }*/
    } // end if (!is_admin()
} // end function rt_add_product_to_cart

//add_action("template_redirect", "rt_add_product_to_cart");

/**
 * Display the post excerpt.
 *
 * @since child 0.2
 */
function rt_excerpt($postName, $postId)
{

    /**
     * Filter the displayed post excerpt.  This is similar to 'the_excerpt,' but accepts perams
     * that are passed on so that a 'more' link can be added after the excerpt that replaces
     * the rediculous [...].
     *
     * @since child 0.2
     *
     * @see rt_get_the_excerpt()
     *
     * @param string $postName - the post name to be used in the link.
     * @param number $postId - the post ID, used to grab or generate the post excerpt.
     */
    
    if (!empty($postName) && !empty($postId))
    {
        echo apply_filters( 'rt_excerpt', rt_get_the_excerpt($postName, $postId) );
    }
}

/**
 * Retrieve the post excerpt.
 *
 * @since 0.71
 *
 * @param mixed $deprecated Not used.
 * @return string
 */
function rt_get_the_excerpt($postName, $postId )
{
    $post = get_post($postId);
    if (empty($post))
    {
        return '';
    }

    if (post_password_required())
    {
        return __( 'There is no excerpt because this is a protected post.' );
    }

    if (!empty($postId))
    {
        $args = array($post->post_excerpt, $postId);
        $returnThis = rt_trim_excerpt($args); //return apply_filters_ref_array("get_the_excerpt", $args );
        $returnThis = rt_removeNewLine($returnThis);
        return $returnThis;
    }
    else
    {
        return apply_filters( 'get_the_excerpt', $post->post_excerpt );
    }
} // end rt_get_the_excerpt function

/* this function is to remove the [...] from the excerpts and add client's preference */
function rt_excerpt_more($more, $theID = "")
{
    if ($more == "[&hellip;]")
    {
        return "&nbsp;".$more."&nbsp;";
        // return "&nbsp;<a class=\"rt_more_link\" href=\"".get_permalink(get_the_ID())."\">.&nbsp;.&nbsp;.&nbsp;Learn More</a>";
    }
    elseif ($more == "useID")
    {
        $post = get_post($more);
        return "&nbsp;.&nbsp;.&nbsp;.&nbsp;<a class=\"rt_more_link\" href=\"".get_site_url()."/?p=".$post->ID."\">Learn More</a>";
    }
    else
    {
        $post = get_post($more);
        $permalink = get_permalink($more);
        $thePostType = get_post_type($more);
        
        if ($thePostType == "program_pages")
        {
            return "&nbsp;.&nbsp;.&nbsp;.&nbsp;<a class=\"rt_more_link\" href=\"".get_site_url()."/our-programs-detail?theProgram=".$post->ID."\">Learn More</a>";
        }
        else
        {
            return "&nbsp;.&nbsp;.&nbsp;.&nbsp;<a class=\"rt_more_link\" href=\"".get_site_url()."/".$post->post_name."\">Learn More</a>";
        }
        
        // for testing -> return "&nbsp;<a class=\"rt_more_link\" href=\"".get_site_url()."/".$post->post_name."\">".$more."</a>";
    }
}

add_filter("excerpt_more", "rt_excerpt_more");

/* the purpose of this function is to add the ". . . Learn More" that the client wants on the blog listing page */
/* other than the lines indicated, this is just like the 'wp_trim_excerpt' function in 'wp-includes/formatting.php' */
function rt_trim_excerpt($passedArray)
{
    $text = $passedArray[0];
    $theID = $passedArray[1];
    
    $raw_excerpt = $text;
    if ( '' == $text )
    {
        $text = get_the_content('');
        
        // $theID = $post->ID; //get_the_ID(); // this line has been added
        
        $text = strip_shortcodes( $text );
        
        /** This filter is documented in wp-includes/post-template.php */
        $text = apply_filters( 'the_content', $text );
        $text = str_replace(']]>', ']]&gt;', $text);
        
        /**
         * Filter the number of words in an excerpt.
         *
         * @since 2.7.0
         *
         * @param int $number The number of words. Default 55.
         */
        
        $excerpt_length = apply_filters( 'excerpt_length', 55 );
        
        /**
         * Filter the string in the "more" link displayed after a trimmed excerpt.
         *
         * @since 2.9.0
         *
         * @param string $more_string The string shown within the more link.
         */
        
        if (!empty($theID))
        {
            $excerpt_more = apply_filters("excerpt_more", $theID );
        }
        else
        {
            $excerpt_more = apply_filters( 'excerpt_more', ' ' . 'no text - no id' ); //$excerpt_more = apply_filters( 'excerpt_more', ' ' . '[&hellip;]' );
        }
        
        $text = wp_trim_words( $text, $excerpt_length, $excerpt_more );
    }
    else
    {
        // $text = get_the_content('');
        
        // $theID = $post->ID; //get_the_ID(); // this line has been added
        
        $text = strip_shortcodes( $text );
        
        /** This filter is documented in wp-includes/post-template.php */
        $text = apply_filters( 'the_content', $text );
        $text = str_replace(']]>', ']]&gt;', $text);
        
        /**
         * Filter the number of words in an excerpt.
         *
         * @since 2.7.0
         *
         * @param int $number The number of words. Default 55.
         */
        
        $excerpt_length = apply_filters( 'excerpt_length', 55 );
        
        /**
         * Filter the string in the "more" link displayed after a trimmed excerpt.
         *
         * @since 2.9.0
         *
         * @param string $more_string The string shown within the more link.
         */
        
        if (!empty($theID))
        {
            $excerpt_more = apply_filters("excerpt_more", $theID );
        }
        else
        {
            $excerpt_more = apply_filters( 'excerpt_more', ' ' . 'text - no id' ); //$excerpt_more = apply_filters( 'excerpt_more', ' ' . '[&hellip;]' );
        }
        
        $text = wp_trim_words( $text, $excerpt_length, $excerpt_more );
    }
    
    /**
     * Filter the trimmed excerpt string.
     *
     * @since 2.8.0
     *
     * @param string $text        The trimmed text.
     * @param string $raw_excerpt The text prior to trimming.
     */
    
    return apply_filters( 'wp_trim_excerpt', $text, $raw_excerpt );
}

remove_filter("get_the_excerpt", "wp_trim_excerpt");
add_filter("get_the_excerpt", "rt_trim_excerpt");

// this funtion sets up a cookie to hold donation amount, which is entered by the user in an input field on the /product/donation/ page (post id 4927)
// second funtion sets cookie note, which will be used to hold the info for where the donator wants their donation to go.
function rt_donation_cookie()
{
    if (!isset($_COOKIE['donation_amount']))
    {
        setcookie("donation_amount", 0, time()+3600*24*100, COOKIEPATH, COOKIE_DOMAIN, false);
    }
}

add_action("init", "rt_donation_cookie");

function rt_donation_note()
{
    if (!isset($_COOKIE['donation_note']))
    {
        setcookie("donation_note", 0, time()+3600*24*100, COOKIEPATH, COOKIE_DOMAIN, false);
    }
}

add_action("init", "rt_donation_note");

/*
|--------------------------------------------------------------------------
| End of Functions.php
|--------------------------------------------------------------------------
*/
add_action( 'pre_get_posts', 'custom_pre_get_posts_query' );

function custom_pre_get_posts_query( $q ) {

    if ( ! $q->is_main_query() ) return;
    if ( ! $q->is_post_type_archive() ) return;
    
    if ( ! is_admin() && is_shop() ) {

        $q->set( 'tax_query', array(array(
            'taxonomy' => 'product_cat',
            'field' => 'slug',
            'terms' => array( 'programs', 'membership' ), // Don't display products in the knives category on the shop page
            'operator' => 'NOT IN'
        )));
    
    }

    remove_action( 'pre_get_posts', 'custom_pre_get_posts_query' );

}

/*
|----------------------------------------------------------------------
| this is for the mulitselect for 'ultimate wp query search form'
|----------------------------------------------------------------------
*/

// add option to backend
function add_multiselect_admin($fields)
{
    $fields['multiselect'] = "Multi select";
    return $fields;
}

add_filter("uwpqsftaxo_field", "add_multiselect_admin");

// add the field to frontend
function  multiselect_front($type, $exc, $hide, $taxname, $taxlabel, $taxall, $opt, $c, $defaultclass, $formid, $divclass)
{
    $eid		= explode(",", $exc);
    $args	= array(	"hide_empty"	=> $hide,
                    "exclude"		=> $eid);
    $taxoargs	= apply_filters("uwpqsf_taxonomy_arg", $args, $taxname, $formid);
    $terms	= get_terms($taxname, $taxoargs);
    $count	= count($terms);
    $html	= "<div class=\"".$defaultclass." ".$divclass."\" id=\"tax-select-".$c."\"><span class=\"taxolabel-".$c."\">".$taxlabel."</span>";
    $html	.= "<input type=\"hidden\" name=\"taxo[".$c."][name]\" value=\"".$taxname."\">";
    $html	.= "<input type=\"hidden\" name=\"taxo[".$c."][opt]\" value=\"".$opt."\">";
    $html	.= "<select multiple id=\"tdp-".$c."\" class=\"tdp-class-".$c."\" name=\"taxo[".$c."][term][]\">";
        
    if (!empty($taxall))
    {
        $html	.= "<option selected value=\"uwpqsftaxoall\">".$taxall."</option>";
    }
    
    if ($count > 0)
    {
        foreach ($terms AS $term)
        {
            $selected		= (isset($_GET['taxo'][$c]['term']) && $_GET['taxo'][$c]['term'] == $term->slug) ? 'selected="selected"' : '';
            $html		.= "<option value=\"".$term->slug."\" ".$selected.">".$term->name."</option>";
        }
    }
    
    $html	.= "</select>";
    $html	.= "</div>";
    return $html;
}

add_filter("uwpqsf_addtax_field_multiselect", "multiselect_front", "", 11);

/*
|----------------------------------------------------------------------
| end code for the mulitselect for 'ultimate wp query search form'
|----------------------------------------------------------------------
*/

/*
 |---------------------------------------------------------------------------
 | code to add a 'change post type' select to the publish box on the 
 | edit post page.  this is specifically for publishing the resources that
 | were submitted before the resource management plugin took care of it.
 |---------------------------------------------------------------------------
*/
/* 01-09-2019 ** this code is causing problems when creating new or cloning (built-in or custom) posts.
function fits_addPostTypeSelect()
{
    // this is the list of posts I want displayed
    $postDisplayArray = array(	"post",
                            "page",
                            "product",
                            "faq_pages",
                            "program_pages",
                            "resources_pages");
    
    $currentPostType = get_post_type();
    $printThis = "	<label for=\"postTypeSelect\">Change post type:</label>
                <select id=\"postTypeSelect\" name=\"postTypeSelect\" class=\"\" style=\"margin-bottom: 5px;\">
                    <option value=\"".$currentPostType."\">".$currentPostType."</option>";

    foreach (get_post_types("", "names") AS $post_type)
    {
        if (in_array($post_type, $postDisplayArray))
        {
            $printThis .= "	<option value=\"".$post_type."\">".$post_type."</option>";
        }
    }

    $printThis .= "</select>";

    print $printThis;
}

add_action("post_submitbox_misc_actions", "fits_addPostTypeSelect");

function fits_savePostType($postid)
{
    global $wpdb; 
    
    if (defined("DOING_AUTOSAVE") && DOING_AUTOSAVE) return false; // if wordpress is doing an autosave, don't do this
    if (!current_user_can("edit_page", $postid)) return false; // does the logged in user have permission
    
    $selectStatement = "UPDATE wp_posts SET post_type = '".$_POST['postTypeSelect']."' WHERE ID = ".$postid;
    $queryResult = $wpdb->query($selectStatement);
}

add_action("save_post", "fits_savePostType");
** end problem code */
/*
 |----------------------------------------------------------------------------
 | end code that adds 'change post type' select
 |----------------------------------------------------------------------------
*/

/*
 |----------------------------------------------------------------------------
 | code for custom post types - these are to handle the resource detail posts.
 | these posts are from form submissions on the main resource landing pages.
 |----------------------------------------------------------------------------
*/
function create_post_type_resource_organizations()
{
    register_post_type("rt_resource-organizations",
                    array(	"labels" => array(	"name" => __("Resource-Organizations"),
                                            "singular_name" => __("Resource-Organization")
                                            ),
                            "public" => true,
                            "has_archive" => true,
                            )
                    );
}

add_action ("init", "create_post_type_resource_organizations");

// the client wants a special font family and style for the mce editor
function rt_theme_add_editor_styles()
{
    add_editor_style("rt-editor-style.css");
}

add_action("init", "rt_theme_add_editor_styles");

// test to check ancestory of a page
// descendent is the id of the page to check
// $ancestor is the possible parent, grandparent, etc.
function rt_is_ancestor($ancestor, $descendent)
{
    $parents = get_post_ancestors($descendent);
    foreach($parents AS $test_id)
    {
        if($test_id == $ancestor)
        {
            return TRUE;
        }
    }
    
    return FALSE;
}
/*
 |-----------------------------------------------------------------------------
 | end custom post types for resource submissions
 |-----------------------------------------------------------------------------
*/

/*
 |-----------------------------------------------------------------------------------------------
 | code to route wordpress to different *-single.php files depending on the category of the post
 |-----------------------------------------------------------------------------------------------
*/
    // define constant page for templates
    define(SINGLE_PATH, STYLESHEETPATH);
    
    function rt_single_template($single)
    {
        global $wp_query, $post;
        
        // check for single template by ID... just in case
        if(file_exists(SINGLE_PATH."/single-".$post->ID.".php"))
        {
            return SINGLE_PATH."/single-".$post->ID.".php";
        }
        
        foreach((array)get_the_category($post->ID) as $cat) :
            
            if ($cat->term_id == 79 || $cat->term_id == 121 || $cat->term_id == 100 || $cat->term_id == 136 || $cat->term_id == 151 || $cat->term_id == 172)
            {
                switch($cat->term_id)
                {
                    case 121:
                        return SINGLE_PATH."/conferences_single.php";
                    break;
                    
                    case 79:
                        return SINGLE_PATH."/organizations_single.php";
                    break;
                    
                    case 100:
                        return SINGLE_PATH."/literature_single.php";
                    break;
                    
                    case 136:
                        return SINGLE_PATH."/degreePrograms_single.php";
                    break;
                    
                    case 151:
                        return SINGLE_PATH."/listserv_single.php";
                    break;
                    
                    case 172:
                        return SINGLE_PATH."/individualPractitioners_single.php";
                    break;
                }
            }
            
        endforeach;
        
        // if we get this far, check for single post file or default
        if (file_exists(SINGLE_PATH."/single.php"))
        {
            return SINGLE_PATH."/single.php";
            // for testing ->	return SINGLE_PATH."/conferences_single.php";
        }
        elseif (file_exists(SINGLE_PATH."/default.php"))
        {
            return SINGLE_PATH."/default.php";
        }
        
        return $single;
        
    }
    
    // add filter for single _tempalate with the custom function
    add_filter("single_template", "rt_single_template");
    
    // try this for the resource post-type template
    function rt_resources_pages_redirect()
    {
        global $wpdb, $post;
        
        $thePostType = get_post_type($post->ID);
        // for testing -> error_log(date("m-d-Y H:i:s")." the post id = ".$post->ID." the post type = ".$thePostType, 0);
        
        if ($thePostType == "resources_pages")
        {
            foreach((array)get_the_category($post->ID) as $cat) :
                
                if ($cat->term_id == 79 || $cat->term_id == 121 || $cat->term_id == 100 || $cat->term_id == 136 || $cat->term_id == 151 || $cat->term_id == 172)
                {
                    switch($cat->term_id)
                    {
                        case 121:
                        return SINGLE_PATH."/conferences_single.php";
                    break;
                    
                    case 79:
                        return SINGLE_PATH."/organizations_single.php";
                    break;
                    
                    case 100:
                        return SINGLE_PATH."/literature_single.php";
                    break;
                    
                    case 136:
                        return SINGLE_PATH."/degreePrograms_single.php";
                    break;
                    
                    case 151:
                        return SINGLE_PATH."/listserv_single.php";
                    break;
                    
                    case 172:
                        return SINGLE_PATH."/individualPractitioners_single.php";
                    break;
                    }
                }
                
            endforeach;
        }
    }
    
    //add_action("wp", "rt_resources_pages_redirect");
    
    // this function changes the error message when a user tries to login with an incorrect id or password
    function rt_login_error_message($error)
    {
        $error = "Your username or password is incorrect.  Please try again.";
        return $error;
    }
    
    add_filter("login_errors", "rt_login_error_message");
    
    // remove WooCommerce Updater notification
    remove_action("admin_notices", "woothemes_updater_notice");
    
/*
 |-----------------------------------------------------------------------------------------------
 | end code for handling post display based on category - redirecting to *-single.php
 |-----------------------------------------------------------------------------------------------
*/

/**
 * Gravity Wiz // Gravity Forms // Map Submitted Field Values to Another Field
 *
 * Preview your Gravity forms on the frontend of your website. Adds a "Live Preview" link to the Gravity Forms toolbar.
 *
 * Usage
 *
 * 1 - Enable "Allow field to be populated dynamically" option on field which should be populated.
 * 2 - In the "Parameter Name" input, enter the merge tag (or merge tags) of the field whose value whould be populated into this field.
 *
 * Basic Fields
 *
 * To map a single input field (and most other non-multi-choice fields) enter: {Field Label:1}. It is useful to note that
 * you do not actually need the field label portion of any merge tag. {:1} would work as well. Change the "1" to the ID of your field.
 *
 * Multi-input Fields (i.e. Name, Address, etc)
 *
 * To map the first and last name of a Name field to a single field, follow the steps above and enter {First Name:1.3} {Last Name:1.6}.
 * In this example it is assumed that the name field ID is "1". The input IDs for the first and last name of this field will always be "3" and "6".
 *
 * # Uses
 *
 *  - use merge tags as post tags
 *  - aggregate list of checked checkboxes
 *  - map multiple conditional fields to a single field so you can refer to the single field for the selected value
 *
 * @version	  1.1
 * @author    David Smith <david@gravitywiz.com>
 * @license   GPL-2.0+
 * @link      http://gravitywiz.com/...
 * @copyright 2014 Gravity Wiz
 */
class GWMapFieldToField {
    public $lead = null;
    function __construct( ) {
        add_filter( 'gform_pre_validation', array( $this, 'map_field_to_field' ), 11 );
    }
    function map_field_to_field( $form ) {
        foreach( $form['fields'] as $field ) {
            if( is_array( $field['inputs'] ) ) {
                $inputs = $field['inputs'];
            } else {
                $inputs = array(
                    array(
                    'id' => $field['id'],
                    'name' => $field['inputName']
                    )
                );
            }
            foreach( $inputs as $input ) {
                $value = rgar( $input, 'name' );
                if( ! $value )
                    continue;
                $post_key = 'input_' . str_replace( '.', '_', $input['id'] );
                $current_value = rgpost( $post_key );
                preg_match_all( '/{[^{]*?:(\d+(\.\d+)?)(:(.*?))?}/mi', $input['name'], $matches, PREG_SET_ORDER );
                // if there is no merge tag in inputName - OR - if there is already a value populated for this field, don't overwrite
                if( empty( $matches ) )
                    continue;
                $entry = $this->get_lead( $form );
                foreach( $matches as $match ) {
                    list( $tag, $field_id, $input_id, $filters, $filter ) = array_pad( $match, 5, 0 );
                    $force = $filter === 'force';
                    $tag_field = RGFormsModel::get_field( $form, $field_id );
                    // only process replacement if there is no value OR if force filter is provided
                    $process_replacement = ! $current_value || $force;
                    if( $process_replacement && ! RGFormsModel::is_field_hidden( $form, $tag_field, array() ) ) {
                        $field_value = GFCommon::replace_variables( $tag, $form, $entry );
                        if( is_array( $field_value ) ) {
                            $field_value = implode( ',', array_filter( $field_value ) );
                        }
                    } else {
                        $field_value = '';
                    }
                    $value = trim( str_replace( $match[0], $field_value, $value ) );
                }
                if( $value ) {
                    $_POST[$post_key] = $value;
                }
            }
        }
        return $form;
    }
    function get_lead( $form ) {
        if( ! $this->lead )
            $this->lead = GFFormsModel::create_lead( $form );
        return $this->lead;
    }
}
new GWMapFieldToField();


// Activate WordPress Maintenance Mode
function fits_maintenance_mode(){
    if(!current_user_can("edit_themes") || !is_user_logged_in()){
        wp_die("<h1 style=\"color:red\">Website Under Maintenance</h1><br />We are performing scheduled maintenance. We will be back on-line shortly!");
        //wp_die("<h1 style=\"color:red\">Production Website is Available</h1><br />The production website is now live.  Please change bookmarks and links for Webber International University - Integrated Marketing Communications to \"webber-imc.com\".<br /><br /><a href=\"http://webber-imc.com\">Use this link to go there now.</a>");
    }
}
//add_action("get_header", "fits_maintenance_mode");
?>
