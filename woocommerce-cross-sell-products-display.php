<?php
/*
Plugin Name: Woocommerce Cross Sell Products Display
Plugin URI: http://logicfire.in
Description: Allow to display cross sell products on single product page. Allow to use shotrcode [wcsp_cross_sell] with various attributes on single product page or enable to display below the single product summary.
Author: Logicfire
Version: 1.0

*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/*** Check if WooCommerce is active ***/
if ( !in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
    exit("Please Install and Activate Woocommerce Plugin.");
}

register_activation_hook( __FILE__, 'wcsp_set_defaults' );

/* Shortcode [wcsp_cross_sell] */
add_shortcode('wcsp_cross_sell','wcsp_cross_sell_products');

/* get cross sell products and display */
function wcsp_cross_sell_products($atts){
global $woocommerce, $woocommerce_loop, $post;	
//$wcsp_title = get_option('wcsp_title');
$atts = shortcode_atts( array(
 	      'product_num' => '10',
		  'orderby' => 'title',
		  'order' => 'ASC',
		  'display_columns' => 0,
		  'title' => 'Cross Sells...'
      ), $atts );
	  
extract($atts);	  
$product_id = $post->ID;

$crosssells = get_post_meta( $product_id, '_crosssell_ids',true);
if ( sizeof($crosssells ) == 0  || $crosssells =='') return;

$meta_query = WC()->query->get_meta_query();
$args = array(
	'post_type'           => 'product',
	'ignore_sticky_posts' => 1,
	'no_found_rows'       => 1,
	'posts_per_page'      => $product_num,
	'orderby'             		 => $orderby,
	'order' 						 =>  $order,
	'post__in'            		 => $crosssells,
	'meta_query'          	 => $meta_query
);

$products = new WP_Query( $args );
	if($display_columns == 0){	
		$woocommerce_loop['columns'] = apply_filters( 'woocommerce_cross_sells_columns', $columns );
	}else{
		$woocommerce_loop['columns'] =  $display_columns ;
	}
if( $products->have_posts() ) : ?>
	<?php 
	  ob_start();
	echo '<div class="cross-sells"><h2>'.$title.'</h2>';
		woocommerce_product_loop_start();
			while ( $products->have_posts() ) : $products->the_post();
				  wc_get_template_part( 'content', 'product' ); 
		 endwhile; // end of the loop. 
		 woocommerce_product_loop_end();
	echo '</div>';
endif;
wp_reset_query();
  $cross_sell_content = ob_get_contents();
  ob_end_clean();
  return $cross_sell_content;
}

/* show cross sell products on single product page on 'woocommerce_after_single_product_summary' action */
function wcsp_show_products(){
	$wcsp_enable_on_single_product = get_option('wcsp_enable_on_single_product');
	$wcsp_title = get_option('wcsp_title');
	$wcsp_number_of_products = get_option('wcsp_number_of_products');
	$wcsp_orderby = get_option('wcsp_orderby');
	$wcsp_order = get_option('wcsp_order');
	$wcsp_display_columns = get_option('wcsp_display_columns');
	
	if($wcsp_enable_on_single_product == 'yes'){
	echo do_shortcode('[wcsp_cross_sell orderby="'.$orderby.'" order="'.$wcsp_order.'" product_num="'.$wcsp_number_of_products.'" display_columns="'.$wcsp_display_columns.'" title="'.$wcsp_title.'" ]');
		}
	}
$wcsp_priority = get_option('wcsp_priority');		
add_action('woocommerce_after_single_product_summary', 'wcsp_show_products' ,$wcsp_priority);

/* add menu tab */
add_action('admin_menu', 'wcsp_admin_menu');
function wcsp_admin_menu() {
	add_options_page('Woocommerce Cross Sell Products Display', 'Cross Sell Products Settings', 'manage_options', 'wcsp-settings', 'wcsp_basic_options_page');
	}

/* set defaults on plugin activation */
function wcsp_set_defaults(){

	if(!get_option('wcsp_enable_on_single_product'))
	add_option('wcsp_enable_on_single_product','no');
	
	if(!get_option('wcsp_priority'))
	add_option('wcsp_priority', 99);
	
	if(!get_option('wcsp_title'))
	add_option('wcsp_title','Cross Sells...');
	
	if(!get_option('wcsp_number_of_products'))
	add_option('wcsp_number_of_products',10);
	
	if(!get_option('wcsp_orderby'))
	add_option('wcsp_orderby','none');
	
	if(!get_option('wcsp_order'))
	add_option('wcsp_order','ASC');
	
	if(!get_option('wcsp_display_columns'))
	add_option('wcsp_display_columns',0);
	}


function wcsp_HtmlPrintBoxHeader($id, $title) {
		?>
			<div id="<?php echo $id; ?>" class="postbox">
				<h3 class="hndle"><span><?php echo $title ?></span></h3>
				<div class="inside">
		<?php
	}

function wcsp_HtmlPrintBoxFooter() {
			?>
				</div>
			</div>
		<?php
	}

/* Basic admin setting options */
function wcsp_basic_options_page() {
?>
<div class="wrap" id="wcsp_div">
			<form method="post" action="<?php echo $_SERVER['PHP_SELF'].'?page=wcsp-settings'; ?>">
<h2> <?php _e('Woocommerce Cross Sell Products', 'crosssell'); ?> </h2>
<div id="poststuff" class="metabox-holder has-right-sidebar">
							<div class="inner-sidebar">
								<div id="side-sortables" class="meta-box-sortabless ui-sortable" style="position:relative;">
                                <?php wcsp_HtmlPrintBoxHeader('wcsp_about_plugin',__('About this Plugin:','crosssell'),true); ?>
                                <a class="wcsp_button"    href="http://logicfire.in/woocommerce-cross-sell-products-display/"><?php _e('Plugin Homepage','crosssell'); ?></a><br />
								<a class="wcsp_button"    href="http://logicfire.in/contact-us/"><?php _e('Suggest a Feature','crosssell'); ?></a>
								<?php wcsp_HtmlPrintBoxFooter(true); ?>
                                </div>
                              </div>
      <div class="has-sidebar wcsp-padded" >
				<div id="post-body-content" class="has-sidebar-content">
    					<div class="meta-box-sortabless">  
                               <?php wcsp_HtmlPrintBoxHeader('wcsp_basic_options',__('Basic Options:','crosssell'),true); ?>
                               <ul>
                               		<li>
                                    	<label for="wcsp_enable_on_single_product">
									<strong>Enable cross sell products on single product page: </strong><input type="checkbox" id="wcsp_enable_on_single_product" name="wcsp_enable_on_single_product" <?php if(get_option('wcsp_enable_on_single_product')=='yes')echo 'checked="checked"'; ?>></label>
                                    </li>
                                    <li>
                                    	<label for="wcsp_title">
										<strong>Cross Sell Products section title:</strong><br /><input name="wcsp_title" type="text" id="wcsp_title" value="<?php echo get_option('wcsp_title'); ?>" /></label>
                                    </li>
                                    <li>
                                    	<label for="wcsp_number_of_products"> <strong>Number of products to show:</strong><br />
										<input name="wcsp_number_of_products" type="text" size="3" id="wcsp_number_of_products" value="<?php echo get_option('wcsp_number_of_products'); ?>" /></label>
                                    </li>
                                    <li>
                                    	<label for="wcsp_priority">
										<strong>Products display priority on single product page: </strong><br />
                                        <input name="wcsp_priority" type="text" size="3" id="wcsp_priority" value="<?php echo get_option('wcsp_priority'); ?>" />
                                        </label>
                                    </li>
                                    <li>
                                    	<label for="wcsp_display_columns">
                                        <strong>Products display columns:</strong><br />
										<input name="wcsp_display_columns" size="3" type="text" id="wcsp_display_columns" value="<?php echo get_option('wcsp_display_columns'); ?>" /></label>
                                    </li>
                                    <li>
                                    	<label for="wcsp_orderby"><strong>Products order by:</strong><br />
										<select name="wcsp_orderby" id="wcsp_orderby">                                        
                                        	<option value="none" <?php if(get_option('wcsp_orderby')=='none')echo 'selected="selected"'; ?>>None</option>
                                        	<option value="rand" <?php if(get_option('wcsp_orderby')=='rand')echo 'selected="selected"'; ?>>Random</option>
                                            <option value="title" <?php if(get_option('wcsp_orderby')=='title')echo 'selected="selected"'; ?>>Title</option>
                                            <option value="date" <?php if(get_option('wcsp_orderby')=='date')echo 'selected="selected"'; ?>>Date</option>
                                        </select>
                                        </label>
                                    </li>
                                    <li>
                                    	<label for="wcsp_order">
										<strong>Products Order (Asc/Desc):</strong><br />
                                        <select name="wcsp_order" id="wcsp_order">                                        
                                        	<option value="ASC" <?php if(get_option('wcsp_order')=='ASC')echo 'selected="selected"'; ?>>Ascending</option>
                                        	<option value="DESC" <?php if(get_option('wcsp_order')=='DESC')echo 'selected="selected"'; ?>>Descending</option>
                                        </select>
                                        </label>
                                    </li>
                               </ul>
                               
                              
                                <input type="hidden" name="action" value="wcsp-update" />
                                <?php wcsp_HtmlPrintBoxFooter(true); ?>
                               <input type="submit" class="button-primary" value="Save Changes" />
                          </div>      
                  </div>   
       </div>                
<?php wp_nonce_field('wcsp-update-options-check','wcsp-update-options'); ?>
</form>
</div>
<?php
}

/* save settings */
add_action('admin_init', 'wcsp_save_settings');
function wcsp_save_settings(){
	if($_POST['action'] == 'wcsp-update' && wp_verify_nonce( $_POST['wcsp-update-options'], 'wcsp-update-options-check' )){
		if(isset($_POST['wcsp_enable_on_single_product'])){
			update_option('wcsp_enable_on_single_product','yes');
		}else{
			update_option('wcsp_enable_on_single_product','no');
		}
		
		update_option('wcsp_priority',$_POST['wcsp_priority']);
		update_option('wcsp_title',$_POST['wcsp_title']);
		update_option('wcsp_number_of_products',$_POST['wcsp_number_of_products']);
		update_option('wcsp_orderby',$_POST['wcsp_orderby']);
		update_option('wcsp_order',$_POST['wcsp_order']);
		update_option('wcsp_display_columns',$_POST['wcsp_display_columns']);
		}
	}
?>