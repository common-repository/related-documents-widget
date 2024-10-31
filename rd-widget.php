<?php
/******************************************************************************************
* Plugin Name: Related documents widget
* Plugin URI: http://drzdigital.com/wordpress-plugins/related-documents-widget/
* Description: This widget automatically displays a list of uploaded media associated to the current post or page in the sidebar. It has several configuration options including the ability to omit display on specific pages as well as the ability to exclude images from the list.
* Version: 2.1.2
* Author: Dan Zaniewski
* Author URI: http://www.drzdigital.com/
*
* Copyright (C) 2012 Dan Zaniewski
*
* This program is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with this program. If not, see <http://www.gnu.org/licenses/>.
*
* @link http://drzdigital.com/wordpress-plugins/related-documents-widget/
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
***********************************************************************************************/

///////////////////////////////////////////////////////////////////


class related_docs_widget extends WP_Widget{

	// declaration of the class
	function related_docs_widget(){
		$widget_ops = array( 'classname' => 'related_docs_widget', 'description' => __( "Display related documents!" ));
		$this->WP_Widget( 'related_docs', __( 'Related Docs' ), $widget_ops);
	}
	
	
	// generates sidebar output
	function widget( $args, $instance ){
		extract($args, EXTR_SKIP);
		
		global $wp_query;
		$thePostID = $wp_query->post->ID;
		$excludeImages = array("");
		
		if ($instance['newWindow']){
			$newWindow = 'target="_new"';
		}else{
			$newWindow = '';
		}
		
		//Get options
		$title = empty($instance['title']) ? ' ' : apply_filters('widget_title', $instance['title']);		
		$exclude_list = empty($instance['exclude']) ? ' ' : apply_filters('widget_title', $instance['exclude']);
		if ($instance['excludeImages']){
			$excludeImages = array('image/jpeg','image/gif','image/png','image/vnd.wap.wbmp','image/bmp','image/vnd.microsoft.icon');
		}
		

		
		$excluded = explode(',', $exclude_list); //convert list of excluded pages to array 
		if ( in_array($thePostID,$excluded) || is_home() ) return false;  //don't show widget if page is excluded
		

        $args = array(
          'post_type' => 'attachment',
          'numberposts' => -1,
          'post_status' => null,
          'post_parent' => $thePostID,
          'orderby' => 'menu_order',
          'order' => 'desc'
          );
		
		
		  
        $attachments = get_posts($args);
		$count = 0;
        if ($attachments) {
			foreach ($attachments as $attloop) {
				if(!in_array($attloop->post_mime_type, $excludeImages)) {
					$count++;
				}
			}
			if($count > 0){
				echo $before_widget;
				if ( !empty( $title ) ) { echo $before_title . $title . $after_title; };
				echo '<ul>';			
				foreach ($attachments as $attachment) {
					if(!in_array($attachment->post_mime_type, $excludeImages)) {
						echo '<li><a href="'.wp_get_attachment_url($attachment->ID).'" '.$newWindow.' >';
						echo $attachment->post_title;
						echo '</a></li>';
					}
				}
				echo '</ul>'; 
				echo $after_widget;
			}
        }


	}
	
	// saves widgets settings.
	function update( $new_instance, $old_instance ){
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['exclude'] = strip_tags($new_instance['exclude']);
		$instance['newWindow'] = ( $new_instance['newWindow'] ) ? true : false;
		$instance['excludeImages'] = ( $new_instance['excludeImages'] ) ? true : false;
		return $instance;
	}

	// creates widget edit form
	function form( $instance ){
		global $wpdb;
		$instance = wp_parse_args( (array) $instance, array( 'title' => '', 'exclude' => '', 'newWindow' => false, 'excludeImages' => true ) );
		
		$title = strip_tags($instance['title']);
		$exclude = strip_tags($instance['exclude']);
		$excludeImages = strip_tags($instance['excludeImages']);
		$newWindow = strip_tags($instance['newWindow']);

		
		?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>">Title: <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo attribute_escape($title); ?>" /></label></p>
		<p><label for="<?php echo $this->get_field_id('exclude'); ?>">Exclude pages/posts: <input class="widefat" id="<?php echo $this->get_field_id('exclude'); ?>" name="<?php echo $this->get_field_name('exclude'); ?>" type="text" value="<?php echo attribute_escape($exclude); ?>" /></label><br/><small>Page IDs, separated by commas.</small></p>
		<p><label for="<?php echo $this->get_field_id('excludeImages'); ?>"><input class="checkbox" id="<?php echo $this->get_field_id('newWindow'); ?>" name="<?php echo $this->get_field_name('excludeImages'); ?>" type="checkbox" <?php checked($instance['excludeImages']); ?> /> Exclude images</label></p>
		<p><label for="<?php echo $this->get_field_id('newWindow'); ?>"><input class="checkbox" id="<?php echo $this->get_field_id('newWindow'); ?>" name="<?php echo $this->get_field_name('newWindow'); ?>" type="checkbox" <?php checked($instance['newWindow']); ?> /> Open in a new window</label></p>
		
		<?php
		
	}

}


if (class_exists("related_docs_widget")) {
        $rd_plugin = new related_docs_widget();
}



// register grouped links widget
function related_docs_widget_init() {
	register_widget( 'related_docs_widget' );
}

function rd_widget_add_styles(){
		wp_register_style( 'rd-style', plugins_url('/css/rd-widget.css', __FILE__) );
        wp_enqueue_style( 'rd-style' );
}


if (isset($rd_plugin)) {
    //Actions
		add_action( 'widgets_init', 'related_docs_widget_init' );
		add_action( 'wp_enqueue_scripts', 'rd_widget_add_styles' );
		
    //Filters
}

?>