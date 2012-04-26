<?php
/*
Plugin Name: Wordpress Page Contact
Plugin URI: https://github.com/sideshowcoder/WPageContact
Description: Plugin to manage asignment of spefic contacts to pages and post and display via widget
Author: Philipp Fehre
Version: 1.0
Author URI: http://sideshowcoder.com/
License: All rights reserved
Text Domain: wpc
*/

/**
 * Javascript and CSS loading
 */
add_action('init', 'register_wpc_script');
add_action('init', 'register_wpc_style');
add_action('admin_print_scripts', 'enqueue_wpc_scripts');
add_action('admin_print_styles', 'enqueue_wpc_styles');
 
function register_wpc_script() 
{
	wp_register_script(
	  'wpc-admin-script', 
	  plugins_url('wpagecontact/js/wpc-admin.js'), 
	  array('jquery'), '0.6', true);
}
 
function enqueue_wpc_scripts() 
{
	wp_enqueue_script(
	  'wpc-admin-script', 
	  plugins_url('wpagecontact/js/wpc-admin.js'), 
	  array('jquery'));
}

function register_wpc_style()
{
  wp_register_style(
    'wpc-admin-style', 
    plugins_url('wpagecontact/css/wpc-admin.css'));
}

function enqueue_wpc_styles()
{
  wp_enqueue_style('wpc-admin-style');
}

/** 
 * Internationalization
 */
function wpc_internationalize() 
{
  load_plugin_textdomain('wpc', false, 'wpagecontact/languages');
}
add_action('init', 'wpc_internationalize');

/* 

Handle install and Update

*/
global $wpc_db_version;
$wpc_db_version = "1.0";

function wpc_install() 
{
  global $wpc_db_version;
  global $wpdb;
  $table_name = $wpdb->prefix.'wpagecontact';

  // create contacts table
  $sql = "CREATE TABLE " . $table_name . " (
    id MEDIUMINT(9) NOT NULL AUTO_INCREMENT,
    firstname VARCHAR(128),
    lastname VARCHAR(128),
    email VARCHAR(128),
    phone VARCHAR(128),
    fax VARCHAR(128),
    company VARCHAR(128),
    division VARCHAR(128),
    isdefault TINYINT(1),
    imageurl VARCHAR(2048),
    misc TEXT,
    UNIQUE KEY id (id)
  );";

  require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
  dbDelta($sql);
  // Add initial Data
  $rows_affected = $wpdb->insert($table_name, array());
  // save the current version for update check
  add_option("wpc_db_version", $wpc_db_version);
};
register_activation_hook(__FILE__,'wpc_install');

function wpc_update_db_check() 
{
    global $wpc_db_version;
    // check if we need to update
    if (get_site_option('wpc_db_version') != $wpc_db_version) 
    {
        wpc_install();
    }
}
add_action('plugins_loaded', 'wpc_update_db_check');

/*

Add needed menu to add contacts

*/
function wpc_add_pages()
{
  add_object_page(__('Contacts Manager', 'wpc'), 
                  __('Contacts Manager', 'wpc'), 
                  'edit_published_posts', 
                  'wpagecontact-plugin', 
                  'wpc_contact_manage');
}

// define field names for form
define('HIDDEN_FIELD', 'wpc_hidden_field');
define('ID_FIELD', 'wpc_id_field');
define('METHOD_FIELD', 'wpc_method_field');
define('FIRST_NAME_FIELD', 'wpc_first_field');
define('LAST_NAME_FIELD', 'wpc_last_field');
define('EMAIL_FIELD', 'wpc_email_field');
define('PHONE_FIELD', 'wpc_phone_field');
define('FAX_FIELD', 'wpc_fax_field');
define('COMPANY_FIELD', 'wpc_company_field');
define('DIVISION_FIELD', 'wpc_division_field');
define('IMAGE_URL_FIELD', 'wpc_image_field');
define('DEFAULT_FIELD', 'wpc_default_field');
define('MISC_FIELD', 'wpc_misc_field');

function wpc_show_contact_input_form($editcontact = null)
{
  ?>
  <h2><?php _e("Contacts", 'wpc'); ?> <a href="#" class="button add-new-h2" id="wpcshowformbtn"><?php _e("Show Contact Form", 'wpc'); ?> </a> </h2>
  <form name="wpccontactform" method="post" action="" id="wpccontactform">
  <?php
  // Edit an existing contact so prefill
  if($editcontact)
  {
    ?>
    <input type="hidden" name="<?php echo HIDDEN_FIELD; ?>" value="Y">
    <input type="hidden" name="<?php echo METHOD_FIELD; ?>" value="Edit" id="wpcedit">
    <input type="hidden" name="<?php echo ID_FIELD; ?>" value="<?php echo $editcontact->id; ?>">
    <p><?php _e("Firstname", 'wpc'); ?> 
      <input type="text" name="<?php echo FIRST_NAME_FIELD; ?>" value="<?php echo $editcontact->firstname; ?>" size="40">
    </p>
    <p><?php _e("Lastname", 'wpc'); ?> 
      <input type="text" name="<?php echo LAST_NAME_FIELD; ?>" value="<?php echo $editcontact->lastname; ?>" size="40">
    </p>
    <p><?php _e("EMail", 'wpc'); ?> 
      <input type="text" name="<?php echo EMAIL_FIELD; ?>" value="<?php echo $editcontact->email; ?>" size="40">
    </p>
    <p><?php _e("Phone", 'wpc'); ?> 
      <input type="text" name="<?php echo PHONE_FIELD; ?>" value="<?php echo $editcontact->phone; ?>" size="40">
    </p>
    <p><?php _e("Fax", 'wpc'); ?> 
      <input type="text" name="<?php echo FAX_FIELD; ?>" value="<?php echo $editcontact->fax; ?>" size="40">
    </p>
    <p><?php _e("Company", 'wpc'); ?> 
      <input type="text" name="<?php echo COMPANY_FIELD; ?>" value="<?php echo $editcontact->company; ?>" size="40">
    </p>
    <p><?php _e("Division", 'wpc'); ?> 
      <input type="text" name="<?php echo DIVISION_FIELD; ?>" value="<?php echo $editcontact->division; ?>" size="40">
    </p>
    <p><?php _e("Image URL", 'wpc'); ?> 
      <input type="text" name="<?php echo IMAGE_URL_FIELD; ?>" value="<?php echo $editcontact->imageurl; ?>" size="40">
    </p>
    <p><?php _e("Miscellaneous", 'wpc'); ?> 
      <input type="text" name="<?php echo MISC_FIELD; ?>" value="<?php echo $editcontact->misc; ?>" size="40">
    </p>
    <p><?php _e("Is the default contact", 'wpc'); ?> 
    <?php
    if($editcontact->isdefault == 1)
    {
      echo '<input type="checkbox" name="' . DEFAULT_FIELD . '" value="1" checked="checked" >';
    }
    else 
    {
      echo '<input type="checkbox" name="' . DEFAULT_FIELD . '" value="1" >';
    }
    ?>
    </p>  
    <?php
  }
  // Create an empty form to create a new Contact
  else
  {
    ?>
    <input type="hidden" name="<?php echo HIDDEN_FIELD; ?>" value="Y">
    <input type="hidden" name="<?php echo METHOD_FIELD; ?>" value="Create">
    <p><?php _e("Firstname", 'wpc'); ?> 
      <input type="text" name="<?php echo FIRST_NAME_FIELD; ?>" value="" size="40">
    </p>
    <p><?php _e("Lastname", 'wpc'); ?> 
      <input type="text" name="<?php echo LAST_NAME_FIELD; ?>" value="" size="40">
    </p>
    <p><?php _e("EMail", 'wpc'); ?> 
      <input type="text" name="<?php echo EMAIL_FIELD; ?>" value="" size="40">
    </p>
    <p><?php _e("Phone", 'wpc'); ?> 
      <input type="text" name="<?php echo PHONE_FIELD; ?>" value="" size="40">
    </p>
    <p><?php _e("Fax", 'wpc'); ?> 
      <input type="text" name="<?php echo FAX_FIELD; ?>" value="" size="40">
    </p>
    <p><?php _e("Company", 'wpc'); ?> 
      <input type="text" name="<?php echo COMPANY_FIELD; ?>" value="" size="40">
    </p>
    <p><?php _e("Division", 'wpc'); ?> 
      <input type="text" name="<?php echo DIVISION_FIELD; ?>" value="" size="40">
    </p>
    <p><?php _e("Image URL", 'wpc'); ?> 
      <input type="text" name="<?php echo IMAGE_URL_FIELD; ?>" value="" size="40">
    </p>
    <p><?php _e("Miscellaneous", 'wpc'); ?> 
      <input type="text" name="<?php echo MISC_FIELD; ?>" value="" size="40">
    </p>
    <p><?php _e("Is the default contact", 'wpc'); ?> 
      <input type="checkbox" name="<?php echo DEFAULT_FIELD; ?>" value="1">
    </p>    
    <?php
  }
  ?>
  <p class="submit">
    <input type="submit" class="button" value="<?php esc_attr_e('Create', 'wpc') ?>" />
  </p>
  </form>
  <?php
}

function wpc_contact_manage() 
{
  // check user rights
	if (!current_user_can('edit_published_posts'))  
	{
		wp_die( __('You do not have sufficient permissions to access this page.', 'wpc') );
	}

	// table
	global $wpdb;
  $table_name = $wpdb->prefix.'wpagecontact';
	  	
  // Create or Edit a contact
  if( isset($_POST[ HIDDEN_FIELD ]) 
      && $_POST[ HIDDEN_FIELD ] == 'Y' 
      && ($_POST[ METHOD_FIELD ] == 'Create' 
          || $_POST[ METHOD_FIELD ] == 'Edit')) 
  {
    $firstname = $_POST[ FIRST_NAME_FIELD ];
    $lastname = $_POST[ LAST_NAME_FIELD ];
    $email = $_POST[ EMAIL_FIELD ];
    $phone = $_POST[ PHONE_FIELD ];
    $fax = $_POST[ FAX_FIELD ];
    $company = $_POST[ COMPANY_FIELD ];
    $division = $_POST[ DIVISION_FIELD ];
    $imageurl = $_POST[ IMAGE_URL_FIELD ];
    $default = $_POST[ DEFAULT_FIELD ];
    $misc = $_POST[ MISC_FIELD ];
        
    // Save to database 
    if(isset($_POST[ ID_FIELD ]))
    {
      // update an existing contact
      $wpdb->update( $table_name, array( 
        'firstname' => $firstname,
        'lastname' => $lastname,
        'email' => $email,
        'phone' => $phone,
        'fax' => $fax,
        'company' => $company,
        'isdefault' => $default,
        'imageurl' => $imageurl,
        'division' => $division,
        'misc' => $misc
       ), array(
        'id' => $_POST[ ID_FIELD ]
       ));    
      
    } 
    else
    {      
      // create a new contact
      $wpdb->insert( $table_name, array( 
        'firstname' => $firstname,
        'lastname' => $lastname,
        'email' => $email,
        'phone' => $phone,
        'fax' => $fax,
        'company' => $company,
        'isdefault' => $default,
        'imageurl' => $imageurl,
        'division' => $division,
        'misc' => $misc
       ));    
    }
  }
  
  elseif( isset($_POST[ HIDDEN_FIELD ]) && $_POST[ METHOD_FIELD ] == 'Edit') 
  {
    // Contact edit
    $editcontact = $wpdb->get_row("SELECT * FROM $table_name WHERE id = " .  $_POST[ HIDDEN_FIELD ]);
    wpc_show_contact_input_form($editcontact);
  }
  elseif( isset($_POST[ HIDDEN_FIELD ]) && $_POST[ METHOD_FIELD ] == 'Delete')
  {
    // Contact delete
    $wpdb->query("DELETE FROM $table_name WHERE id = " .  $_POST[ HIDDEN_FIELD ]);
  }
  // Show the Contact form!
  wpc_show_contact_input_form();

  // Show existing contacts
  ?>
  <table class="wp-list-table widefat fixed" cellspacing="0" id="wpccontactstable">
	<thead>
    <tr>
      <th class="manage-column desc" style="" scope="col"><?php _e("Firstname", 'wpc'); ?></th>
      <th class="manage-column desc" style="" scope="col"><?php _e("Lastname", 'wpc'); ?></th>
      <th class="manage-column desc" style="" scope="col"><?php _e("EMail", 'wpc'); ?></th>
      <th class="manage-column desc" style="" scope="col"><?php _e("Phone", 'wpc'); ?></th>
      <th class="manage-column desc" style="" scope="col"><?php _e("Fax", 'wpc'); ?></th>
      <th class="manage-column desc" style="" scope="col"><?php _e("Company", 'wpc'); ?></th>
      <th class="manage-column desc" style="" scope="col"><?php _e("Division", 'wpc'); ?></th>
      <th class="manage-column desc" style="" scope="col"><?php _e("Image", 'wpc'); ?></th>
      <th class="manage-column desc" style="" scope="col"><?php _e("Miscellaneous", 'wpc'); ?></th>
      <th class="manage-column desc" style="" scope="col"><?php _e("Default Contact", 'wpc'); ?></th>
      <th class="manage-column desc" style="" scope="col"><?php _e("Actions", 'wpc'); ?></th>
    </tr>
	</thead>
	<tbody id="the-list">
  <?php
  
  $contacts = $wpdb->get_results("SELECT * FROM $table_name");
  foreach ($contacts as $contact)
  { 
    echo '<tr class="alternate">';
  	echo '<td>' . $contact->firstname . '</td>';
	  echo '<td>' . $contact->lastname . '</td>';
	  echo '<td>' . $contact->email . '</td>';
	  echo '<td>' . $contact->phone . '</td>';
	  echo '<td>' . $contact->fax . '</td>';
	  echo '<td>' . __($contact->company) . '</td>';
	  echo '<td>' . __($contact->division) . '</td>';
	  echo '<td><img src="' . $contact->imageurl . '" alt="' . $contact->imageurl . '" width="54" height="69" /></td>';
	  echo '<td>' . __($contact->misc) . '</td>';
	  if($contact->isdefault == 0) 
	  {
	    echo '<td>' . __('No', 'wpc') . '</td>';
	  }
	  else 
	  {
	    echo '<td>' . __('Yes', 'wpc') . '</td>';
	  } 
	  echo '<td><form name="wpccontactedit" method="post" action="">';
    echo '<input type="hidden" name="' . HIDDEN_FIELD . '" value="' . $contact->id . '">';
    echo '<input type="hidden" name="' . METHOD_FIELD . '" value="Edit">';
	  echo '<input type="submit" class="button wpceditbtn" value="' . __('Edit', 'wpc') .'" />';
    echo '</form>';
    echo '<form name="wpccontactedit" method="post" action="">';
    echo '<input type="hidden" name="' . HIDDEN_FIELD . '" value="' . $contact->id . '">';
    echo '<input type="hidden" name="' . METHOD_FIELD . '" value="Delete">';
	  echo '<input type="submit" class="button" value="' . __('Delete', 'wpc') . '" />';
    echo '</form></td>';
	  
    echo '</tr>';
  }
  ?>
  </tbody>
  </table>
  <?php
  
};
add_action('admin_menu', 'wpc_add_pages');

/* 

Add custom box to post editor to associate a contact with a post

*/

function wpc_add_custom_box() 
{
  // post creation
  add_meta_box( 
    'wpc_sectionid',
    __( 'Contact Relations for Post', 'wpc'),
    'wpc_inner_custom_box',
    'post' 
  );
  // page creation
  add_meta_box(
    'wpc_sectionid',
    __( 'Contact Relations for Page', 'wpc'), 
    'wpc_inner_custom_box',
    'page'
  );
}

function wpc_inner_custom_box( $post ) 
{
  global $wpdb;
  $table_name = $wpdb->prefix.'wpagecontact';  
  wp_nonce_field( plugin_basename( __FILE__ ), 'wpc_noncename' );

  $set_contact = get_post_meta($post->ID, 'wpc_contacts', true);
  // The actual fields for data entry
  echo '<label for="wpc_contact_field">';
       _e("Associated Contact", 'wpc');
  echo '</label> ';
  echo '<select id="wpc_contact_field" name="wpc_contact_field">';
  // Add the none contact
  echo '<option selected="selected" value="-1">' . __('None', 'wpc') . '</option>';
  
  $contacts = $wpdb->get_results("SELECT * FROM $table_name");
  // Dropdown to associate a Contact with a post or page
  foreach($contacts as $contact)
  {
    if($contact->id == $set_contact)
    {
      echo '<option selected="selected" value="' . $contact->id . '">' . $contact->firstname . ' ' . $contact->lastname . '</option>';
      
    } 
    else 
    {
      echo '<option value="' . $contact->id . '">' . $contact->firstname . ' ' . $contact->lastname . '</option>';        
    }
  }
  echo '</select>';
  
}
add_action( 'add_meta_boxes', 'wpc_add_custom_box' );

/*

Save the associated contact

*/

function wpc_save_postdata( $post_id ) 
{
  // don't save on autosave
  if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
      return;

  // Only save if user selected
  if ( !wp_verify_nonce( $_POST['wpc_noncename'], plugin_basename( __FILE__ ) ) )
      return;

  // Check rights
  if ( 'page' == $_POST['post_type'] ) 
  {
    if ( !current_user_can( 'edit_page', $post_id ) )
        return;
  }
  else
  {
    if ( !current_user_can( 'edit_post', $post_id ) )
        return;
  }

  // Write to postmeta
  $contact = $_POST['wpc_contact_field'];
  add_post_meta($post_id, 'wpc_contacts', $contact, true) or 
    update_post_meta($post_id, 'wpc_contacts', $contact);
}
add_action( 'save_post', 'wpc_save_postdata' );


/*

Widget to display associated contacts

*/ 

class Conrel_Widget extends WP_Widget
{
  function Conrel_Widget()
  {
    $widget_ops = array('classname' => 'Conrel_Widget', 'description' => 'Display contact related to post or page');
    $this->WP_Widget('Conrel_Widget', 'Contact Relation Widget', $widget_ops);
  }
 
  function form($instance)
  {
    // Allow for setting a title in the sidebar
    $instance = wp_parse_args((array) $instance, array( 'title' => '' ));
    $title = $instance['title'];
    ?>
    <p><label for="<?php echo $this->get_field_id('title'); ?>">Title: <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></label></p>
    <?php
  }
 
  function update($new_instance, $old_instance)
  {
    $instance = $old_instance;
    $instance['title'] = $new_instance['title'];
    return $instance;
  }
 
  function widget($args, $instance)
  {
    extract($args, EXTR_SKIP);
 
    $title = empty($instance['title']) ? '' : apply_filters('widget_title', $instance['title']);
  
    global $post;
    global $wpdb;
    $table_name = $wpdb->prefix . 'wpagecontact';
    // try to get the associated contact
    $set_contact = get_post_meta($post->ID, 'wpc_contacts', true);
    // None is selected so we dont display the widget
    if($set_contact != -1)
    {
      if(empty($set_contact))
      {
        $contact = $wpdb->get_row("SELECT * FROM $table_name WHERE isdefault = 1");        
      }
      else
      {
        $contact = $wpdb->get_row("SELECT * FROM $table_name WHERE id = $set_contact");
      }
      // If no default default to none
      if($contact)
      {
        echo $before_widget;
      
        if (!empty($title))
          echo $before_title . $title . $after_title;;
 
        ?>
        <div class="info-area">
          <?php if($contact->imageurl): ?>
            <div class="wpc-image-section">
    			    <img src="<?php echo $contact->imageurl ?>" width="54" height="69" />
    			  </div>
				  <?php endif;?>    				  
    			<div class="info">
            <?php if($contact->firstname): ?>
              <div class="wpc-name-section">
    				    <span class="name"><b><?php _e($contact->firstname . ' ' . $contact->lastname) ?></b></span>
    				  </div>
    				<?php endif;?>    				  
            <?php if($contact->division): ?>
              <div class="wpc-division-section">
    				    <span class="division"><?php _e($contact->division) ?></span>
    				  </div>
    				<?php endif;?>
    				<?php if($contact->phone): ?>
    				  <div class="wpc-fon-section">
    				    <span class="fondesc">Fon:</span><span class="fon"><?php echo $contact->phone ?></span>
    				  </div>
    				<?php endif;?>
    				<?php if($contact->fax): ?>
    				  <div class="wpc-fax-section">
    				    <span class="faxdesc">Fax:</span><span class="fax"><?php echo $contact->fax ?></span>
    				  </div>
  					<?php endif;?>
    				<?php if($contact->email):?>
    				  <div class="wpc-email-section">
    				    <span class="emaildesc">Mail:</span><span class="email"><a href="mailto:<?php echo $contact->email; ?>"><?php echo $contact->email ?></a></span>
              </div>
    				<?php endif;?>
    				<?php if($contact->misc):?>
    				  <div class="wpc-misc-section">
    				    <?php _e($contact->misc) ?>
    				  </div>
    				<?php endif;?>
    			</div>
    		</div>
    
        <?php
        echo $after_widget;
      }
    }
  }
}
add_action( 'widgets_init', create_function('', 'return register_widget("Conrel_Widget");') );


?>
