<?php

//delete medium_large thumbnail size
//isnt added via usual methods so needs this function to remove it.
add_filter('intermediate_image_sizes', function($sizes) {
  return array_diff($sizes, ['medium_large']);  // Medium Large (768 x 0)
});

//remove all image sizes, including default wordpress ones
function ahi_remove_extra_image_sizes() {
    foreach ( get_intermediate_image_sizes() as $size ) {
        //add a size to the array (after size) to exclude it from removal
        if ( !in_array( $size, array('') ) ) {
          //remove the image size
          remove_image_size( $size );
        }
    }
}
add_action('init', 'ahi_remove_extra_image_sizes');

function ahi_find_images() {
if (is_admin()) {
  return;
}

require_once( ABSPATH . 'wp-admin/includes/file.php' );//must be included to use the below function
$upload_dir = wp_upload_dir();

echo '<pre>Uploads Folder Locations <br>';
print_r ($upload_dir);
echo '</pre>';

$x = 1;

$folder = $upload_dir['basedir'] . '/installation-images/mr-and-mrs-parkers-bathroom-wollaton/';
$files = list_files( $folder, 2 );
  foreach ( $files as $file ) {
    if ( is_file( $file ) ) {
      //echo '<pre>image type<br>';
      //print_r($file);
     // echo '</pre>';
      $filename = basename( $file ); 
      
      $file_name = $upload_dir['url'] . '/installation-images/mr-and-mrs-parkers-bathroom-wollaton/' . $filename;

      echo 'checking : ' . $filename . '<br>';

      $arrContextOptions=array(
          "ssl"=>array(
              "verify_peer"=>false,
              "verify_peer_name"=>false,
          ),
      );  

      //$attachment_id = attachment_url_to_postid($file_name );
      /*$im = image_type_to_mime_type(exif_imagetype($file_name));
      $exif = exif_read_data($file_name, 0, true);
      echo $file_name . '<br>';
      foreach ($exif as $key => $section) {
          foreach ($section as $name => $val) {
              echo "$key.$name: $val<br />\n";
          }
      }
      echo '<pre>Image Type <br>';
      print_r ($exif);
      echo '</pre>';
      */
      //$data = file_get_contents($file_name);
      //$image = imagecreatefromstring( $data );
       
      $arrContextOptions=array(
          "ssl"=>array(
              "verify_peer"=>false,
              "verify_peer_name"=>false,
          ),
      );  

      //$response = file_get_contents($file_name, false, stream_context_create($arrContextOptions));

     // echo $response; 
     // header('Content-Type: image/jpeg');
      //$file = readfile($file_name,'' , stream_context_create($arrContextOptions));
      $image - imagejpeg($filename);

      //$image = imagecreatefromstring( $file );
      echo '<pre>Image Type <br>';
      print_r ($image);
      echo '</pre>';

      // Free up memory
      imagedestroy($image);
      
      return;
    }
     /* if ($attachment_id != '0') {
        echo 'File no: ' . $x . '-' . $filename . ' - attached with id: ' . $attachment_id .'<br>';
      } 
      else {
        echo 'File no: ' . $x . ' Not attached - deleting :' . $file . '<br>';
        //$remove = unlink ($file);
        if ($remove == true) {
          echo 'Deleted ' . $file . '<br>';
        }
        else {
          echo '<pre>Not Removed <br>';
          print_r($remove);
          echo '</pre>';
        }
      }//end else*/
   // }//end if is file
    $x++;
    
  }//end for each
}
//add_action ('the_content', 'ahi_find_images');

function ahi_attach_images() {
  global $post;
  require_once( ABSPATH . 'wp-admin/includes/image.php' );//must be included to use the below function
  //bail if inside admin
  if (is_admin()) {
    return;
  }

  //set the arguments and fetch the installations
  $args = array(
      'post_type' => 'installations',
      'post_status' => 'publish',
      'posts_per_page' => 1,
      'p' => 1066, 
     // 'p' => 1066, 
  );
                                                    
  $installation_query = null;                                                          
  $installation_query = new WP_Query($args);  
  
  $removed_image_ids = array();
  
  //if there are installations, lets run the functions
  if( $installation_query->have_posts() ) : 
    while ($installation_query->have_posts()) : $installation_query->the_post();
      //check that theres a thumbnail attached - proving that its a complete installation and published
      if (has_post_thumbnail()) {  
        $unnatached = array();
        $image_id = array();
        
        //get all posts attached to this post
        $attachments = get_posts(
                            array(
                                'posts_per_page' => -1,
                                'post_mime_type' => 'image',
                                'post_type' => 'attachment',
                                'fields' => 'ids',
                                'post_parent' => $post->ID,
                            )
                        );

        foreach ($attachments as $ims) {
          $image_meta = array(
          'ID'            => $ims, //id of the image
          'post_parent'   => ''
          );
         wp_update_post( $image_meta );
        }
        /*
        //GET THE IMAGES
        //featured image
        $image_id[]   = get_post_thumbnail_id();
        //images from before and after galleries
        $images_before  = get_post_meta( $post->ID, 'wpcf-gallery-images' );
        $images_after   = get_post_meta( $post->ID, 'wpcf-gallery-images-after' );

        //make sure that the galleries all show unique fields and theres no duplicates
        $new_before = array_unique($images_before);
        $new_after = array_unique($images_after);
       // delete_post_meta ($post->ID, 'wpcf-gallery-images'); 
        //delete_post_meta ($post->ID, 'wpcf-gallery-images-after'); 
        foreach ($new_before as $before) {
          add_post_meta ($post->ID,'wpcf-gallery-images', $before);
        }

        foreach ($new_after as $after) {
          add_post_meta($post->ID,'wpcf-gallery-images-after', $after);
        }

        $images_before  = get_post_meta( $post->ID, 'wpcf-gallery-images' );
        $images_after   = get_post_meta( $post->ID, 'wpcf-gallery-images-after' );

        echo '<pre>before images<br>';
          print_r ($images_before);
          echo '</pre>';

          echo '<pre>after images<br>';
          print_r ($images_after);
          echo '</pre>';

        //now make sure that the image set in the gallery is actually attached in wordpress - if not delete it
        /*foreach ($images_before as $image_before) {
          
          
            /*$id = attachment_url_to_postid($image_before);
            if ($id == 0) {
              echo 'error with image ' . $image_before . '<br>';
              $removed_image_ids[] = $image_before . ' - Customer : ' . $post->post_name;
              //delete_post_meta ($post->ID, 'wpcf-gallery-images', $image_before); 
            }
            else {
              echo 'image ' . $image_before . '<br>';
              $image_id[] = $id;
            }
        }

        foreach ($images_after as $image_after) {
            $id = attachment_url_to_postid($image_after);
            if ($id == 0) {
              echo 'error with image ' . $image_after . '<br>';
              $removed_image_ids[] = $image_after . ' - Customer : ' . $post->post_name;
              //delete_post_meta ($post->ID, 'wpcf-gallery-images-after', $image_after); 
            }
            else {
              $image_id[] = $id;
           }
        }*/
        //now ensure that each image that is in a gallery is attached to this post
        /*foreach ($image_id as $ims) {
          $image_meta = array(
          'ID'            => $ims, //id of the image
          'post_parent'   => $post->ID
          );
         wp_update_post( $image_meta );
        }

        //unnatach images not used in the galleries or featured image
        $difference = array_diff($attachments, $image_id);
        foreach ($difference as $diff) {
          $image_meta = array(
          'ID'            => $diff, //id of the image
          'post_parent'   => ''
          );
          wp_update_post( $image_meta );
          $unnatached[] = $diff;
        }
        if ($unnatached) {
          echo '<pre>Unnatached Images<br>';
          print_r ($unnatached);
          echo '</pre>';
        }*/
        
        /*LETS DO THIS - MOVE AND CHANGE ALL IMAGES WITHIN THE POST*/

        //START WITHE THE FEATURED IMAGE//

        //get the featured imaage ID
        //$image_id   = get_post_thumbnail_id();
        //get the featured image url
        //$image_url  = get_the_post_thumbnail_url();

        //perform a check to ensure the image hasnt already been moved to he staff folder
        //if the image url has installation-images in there, its been moved already
        /*
        if(strpos($image_url, 'installation-images') !== false){
          continue;
        } 
        //image isnt moved so continue
        else{*/
          /*
          //get the image attachement for meta data
          $image          = get_attached_file( $image_id );
          
          //get all the inage details
          $image_details  = pathinfo( $image_url );
          
          //get the current post title as a slug
          $post_title = $post->post_name;
          
          //setup the image folders
          $installations_folder = trailingslashit( wp_upload_dir()['basedir'] ) . 'installation-images';
          $current_installation_directory = trailingslashit( wp_upload_dir()['basedir'] ) . 'installation-images/' . $post_title;

          //if the installations folder doesnt exist, create it
          if(!is_dir($installations_folder)) {
            wp_mkdir_p($installations_folder);
          }

          //if the current installation folder doesnt exist - create it
          if (!is_dir($current_installation_directory)) {
            wp_mkdir_p($current_installation_directory);
          }
          
          //rename the image
          $image_new_name = $post_title . '-featured-image' . '.jpg'; 
          
          //change its location
          $new_location = $current_installation_directory . '/' . $image_new_name;
          echo 'moving featured image: ' . $image_new_name;

          //move the image - copy only as were going to rename it later
          copy( $image, $new_location);

          // Update the attachment name
          update_attached_file($image_id, $new_location);

          //generate all required thumbnails / image sizes for the new image
          $image_updated_meta = wp_generate_attachment_metadata( $image_id, $new_location );
          $update_meta = wp_update_attachment_metadata( $image_id, $image_updated_meta );

          $content = 'Featured Image: ' . $post->post_title . ' - ' . 'Previous Installation From Aquarius Home Improvements LTD';

          $image_meta = array(
          'ID'            => $image_id, //id of the image
          'post_title'    => 'Featured Installation Image: ' . $post->post_title, //title
          'post_excerpt'  => $post->post_title . ' - Previous Installation From Aquarius Home Improvements LTD',  // caption
          'post_content'  => $content,  // description
          'post_parent'   => $post->ID //fallback to ensure its attached
          );
          wp_update_post( $image_meta );

          // update alt text for post
          update_post_meta($image_id, '_wp_attachment_image_alt', $content );
          
          /*organise other images*/

          //GET THE GALLERIES
          
         /* $images_before  = get_post_meta( $post->ID, 'wpcf-gallery-images' );
          $images_after   = get_post_meta( $post->ID, 'wpcf-gallery-images-after' );
          delete_post_meta ($post->ID, 'wpcf-gallery-images'); 
          delete_post_meta ($post->ID, 'wpcf-gallery-images-after'); 
       
          //create an iterator for the image
          $x = 1;

          //foreach($images as $k => $v):
          foreach ($images_before as $image_before) {
            $image_id = attachment_url_to_postid($image_before );
            $image    = get_attached_file( $image_id );
        
            //get all the inage details
            $image_details  = pathinfo( $image_before);
            
            //rename the image
            $image_new_name = $post_title . '-before-' . $x . '.jpg'; 
            echo 'Moving Before Image: ' . $image_new_name;
            //change its location
            $new_location = $current_installation_directory . '/' . $image_new_name;
            //move the image
            rename( $image, $new_location);
            
            // Update the attachment name in wordpress
            update_attached_file($image_id, $new_location);

            //generate all required thumbnails / image sizes for the new image
            $image_updated_meta = wp_generate_attachment_metadata( $image_id, $new_location );
            $update_meta = wp_update_attachment_metadata( $image_id, $image_updated_meta );

            $content = 'Before Image ' . $x . ' Of ' . $post->post_title . ' - Installation Completed By Aquarius Home Improvements LTD.';

            $image_meta = array(
            'ID'            => $image_id, //id of the image
            'post_title'    => 'Previous Installation Image ' . $x . ' - ' . $post->post_title . '.', //title
            'post_excerpt'  => $post->post_title . ' Image ' . $x . ' - Before We Undertook The Installation.',  // caption
            'post_content'  => $content,  // description
            'post_parent'   => $post->ID
            );
            wp_update_post( $image_meta );

            // update alt text for post
            update_post_meta($image_id, '_wp_attachment_image_alt', $content );
            
            $location = wp_upload_dir()['url'] . '/installation-images/' . $post_title . '/' . $image_new_name;
            //update toolset gallery field with new locations
            add_post_meta ($post->ID,'wpcf-gallery-images', $location);
       
            $x++;
           
          }//end for each  

          $x = 1;

          foreach ($images_after as $image_after) {
            $image_id = attachment_url_to_postid($image_after);
            $image    = get_attached_file( $image_id );
        
            //get all the inage details
            $image_details  = pathinfo( $image_after);
            
            //rename the image
            $image_new_name = $post_title . '-after-' . $x . '.jpg'; 
             echo 'Moving After Image: ' . $image_new_name;
            //change its location
            $new_location = $current_installation_directory . '/' . $image_new_name;
            //move the image
            rename( $image, $new_location);
            
            // Update the attachment name in wordpress
            update_attached_file($image_id, $new_location);

            //generate all required thumbnails / image sizes for the new image
            $image_updated_meta = wp_generate_attachment_metadata( $image_id, $new_location );
            $update_meta = wp_update_attachment_metadata( $image_id, $image_updated_meta );

            $content = 'After Image ' . $x . ' Of ' . $post->post_title . ' - Installation Completed By Aquarius Home Improvements LTD.';

            $image_meta = array(
            'ID'            => $image_id, //id of the image
            'post_title'    => 'Previous Installation Image ' . $x . ' - ' . $post->post_title . '.', //title
            'post_excerpt'  => $post->post_title . ' Image ' . $x . ' - After We Undertook The Installation.',  // caption
            'post_content'  => $content,  // description
            );
            wp_update_post( $image_meta );

            // update alt text for post
            update_post_meta($image_id, '_wp_attachment_image_alt', $content );
            
            $location = wp_upload_dir()['url'] . '/installation-images/' . $post_title . '/' . $image_new_name;
            //update toolset gallery field with new locations
            add_post_meta($post->ID,'wpcf-gallery-images-after', $location);
            $x++;
            
          }//end for each  
      */
       // }
      }
    endwhile;
  endif;
  //clear the query                                                              
  wp_reset_query();                                   
  wp_reset_postdata();
}
//add_action ('the_content', 'ahi_attach_images');

//move installation images into installation folder
function ahi_move_installation_images() {
  global $post;
  require_once( ABSPATH . 'wp-admin/includes/image.php' );//must be included to use the below function
  //bail if inside admin
  if (is_admin()) {
    return;
  }

  //set the arguments and fetch the installations
  $args = array(
      'post_type' => 'installations',
      'post_status' => 'publish',
      'posts_per_page' => 1,
      //'post__not_in' => array(154410),
      //'offset' => 5
      //'paged' => 6
      'p' => 1066, 
  );
                                                    
  $installation_query = null;                                                          
  $installation_query = new WP_Query($args);  
  $qty = 1;
  //if there are installations, lets run the functions
  if( $installation_query->have_posts() ) : 
    while ($installation_query->have_posts()) : $installation_query->the_post();
      //check that theres a thumbnail attached - proving that its a complete installation and published
      if (has_post_thumbnail()) {  
        
        //START WITHE THE FEATURED IMAGE//

        //get the featured imaage ID
        $image_id   = get_post_thumbnail_id();
        //get the featured image url
        $image_url  = get_the_post_thumbnail_url();

        /*if(strpos($image_url, 'installation-images') !== false){
          continue;
        } 
        //image isnt moved so continue
        else{*/
        //get the image attachement for meta data
        $image          = get_attached_file( $image_id );
        
        //get all the inage details
        $image_details  = pathinfo( $image_url );

        //get the current post title as a slug
        $post_title = $post->post_name;
        $date = $post->post_date;
        
        //setup the image folders
        $installations_folder = trailingslashit( wp_upload_dir()['basedir'] ) . 'installation-images';
        $current_installation_directory = trailingslashit( wp_upload_dir()['basedir'] ) . 'installation-images/' . $post_title;

        //if the installations folder doesnt exist, create it
        if(!is_dir($installations_folder)) {
          wp_mkdir_p($installations_folder);
        }

        //if the current installation folder doesnt exist - create it
        if (!is_dir($current_installation_directory)) {
          wp_mkdir_p($current_installation_directory);
        }
        
        //rename the image
        $image_new_name = $post_title . '-featured-image' . '.jpg'; 
        
        //change its location
        $new_location = $current_installation_directory . '/' . $image_new_name;
        echo 'moving featured image: ' . $image_new_name . '<br>';
        
        //move the image - copy only as were going to rename it later
        rename( $image, $new_location);

        // Update the attachment name
        update_attached_file($image_id, $new_location);

        //delete old featured image from galleries
        delete_post_meta ($post->ID, 'wpcf-gallery-images', $image_url); 
        delete_post_meta ($post->ID, 'wpcf-gallery-images-after', $image_url); 
     
        //generate all required thumbnails / image sizes for the new image
        $image_updated_meta = wp_generate_attachment_metadata( $image_id, $new_location );
        $update_meta = wp_update_attachment_metadata( $image_id, $image_updated_meta );

        $content = 'Featured Image: ' . $post->post_title . ' - ' . 'Previous Installation From Aquarius Home Improvements LTD';

        $image_meta = array(
        'ID'            => $image_id, //id of the image
        'post_title'    => 'Featured Installation Image: ' . $post->post_title, //title
        'post_excerpt'  => $post->post_title . ' - Previous Installation From Aquarius Home Improvements LTD',  // caption
        'post_content'  => $content,  // description
        'post_parent'   => $post->ID, //fallback to ensure its attached
        'post_date'     => $date
        );
        wp_update_post( $image_meta );

        // update alt text for post
        update_post_meta($image_id, '_wp_attachment_image_alt', $content );
        
        /*organise other images*/

        //GET THE GALLERIES
        
        $images_before  = get_post_meta( $post->ID, 'wpcf-gallery-images' );
        $images_after   = get_post_meta( $post->ID, 'wpcf-gallery-images-after' );
      
        //create an iterator for the image
        $x = 1;
        if ($images_before) {
        foreach ($images_before as $image_before) {
          $image_id = attachment_url_to_postid($image_before );
          $image    = get_attached_file( $image_id );
      
          //get all the inage details
          $image_details  = pathinfo( $image_before);

          //rename the image
          $image_new_name = $post_title . '-before-' . $x . '.jpg'; 
          //echo 'Moving Before Image: ' . $image_new_name . '<br>';;
          //change its location
          $new_location = $current_installation_directory . '/' . $image_new_name;
          //echo 'New Location ' . $new_location . '<br>';
          //move the image
          echo 'moving before image: ' . $image_before . '<br>';
          rename( $image, $new_location);
          
          // Update the attachment name in wordpress
          update_attached_file($image_id, $new_location);

          //generate all required thumbnails / image sizes for the new image
          $image_updated_meta = wp_generate_attachment_metadata( $image_id, $new_location );
          $update_meta = wp_update_attachment_metadata( $image_id, $image_updated_meta );

          $content = 'Before Image ' . $x . ' Of ' . $post->post_title . ' - Installation Completed By Aquarius Home Improvements LTD.';

          $image_meta = array(
          'ID'            => $image_id, //id of the image
          'post_title'    => 'Previous Installation Image ' . $x . ' - ' . $post->post_title . '.', //title
          'post_excerpt'  => $post->post_title . ' Image ' . $x . ' - Before We Undertook The Installation.',  // caption
          'post_content'  => $content,  // description
          'post_parent'   => $post->ID,
          'post_date'     => $date
          );
          wp_update_post( $image_meta );

          // update alt text for post
          update_post_meta($image_id, '_wp_attachment_image_alt', $content );
          
          $location = wp_upload_dir()['url'] . '/installation-images/' . $post_title . '/' . $image_new_name;
          //update toolset gallery field with new locations
          update_post_meta ($post->ID,'wpcf-gallery-images', $location, $image_before);
  
          $x++;
         
        }//end for each  
      }

        $x = 1;
        if ($images_after) {
        foreach ($images_after as $image_after) {
          $image_id = attachment_url_to_postid($image_after);
          $image    = get_attached_file( $image_id );
      
          //get all the inage details
          $image_details  = pathinfo( $image_after);
          
          //rename the image
          $image_new_name = $post_title . '-after-' . $x . '.jpg'; 
    
          //change its location
          $new_location = $current_installation_directory . '/' . $image_new_name;
         // echo 'New Location ' . $new_location . '<br>';
          //move the image
          echo 'moving after image: ' . $image_after . '<br>';
          rename( $image, $new_location);
          
          // Update the attachment name in wordpress
          update_attached_file($image_id, $new_location);

          //generate all required thumbnails / image sizes for the new image
          $image_updated_meta = wp_generate_attachment_metadata( $image_id, $new_location );
          $update_meta = wp_update_attachment_metadata( $image_id, $image_updated_meta );

          $content = 'After Image ' . $x . ' Of ' . $post->post_title . ' - Installation Completed By Aquarius Home Improvements LTD.';

          $image_meta = array(
          'ID'            => $image_id, //id of the image
          'post_title'    => 'Previous Installation Image ' . $x . ' - ' . $post->post_title . '.', //title
          'post_excerpt'  => $post->post_title . ' Image ' . $x . ' - After We Undertook The Installation.',  // caption
          'post_content'  => $content,  // description
          'post_parent'   => $post->ID,
          'post_date'     => $date
          );
          wp_update_post( $image_meta );

          // update alt text for post
          update_post_meta($image_id, '_wp_attachment_image_alt', $content );
          
          $location = wp_upload_dir()['url'] . '/installation-images/' . $post_title . '/' . $image_new_name;
          //update toolset gallery field with new locations
          update_post_meta($post->ID,'wpcf-gallery-images-after', $location, $image_after);
          
          $x++;
          
        }//end for each
      }
     $qty++;
  // }//end else
      }
    endwhile;
  endif;
  echo 'moved : ' . $qty . ' folders';
  //clear the query                                                              
  wp_reset_query();                                   
  wp_reset_postdata();
}
//add_action ('the_content', 'ahi_move_installation_images');

function ahi_move_service_images() {
  if (is_admin()) {
  return;
}
  global $post;
  require_once( ABSPATH . 'wp-admin/includes/image.php' );//must be included to use the below function
  
  //set the arguments and fetch the staff members
  $args = array(
      'post_type' => array('page'),//'fitted-bathrooms','fitted-kitchens','fitted-bedrooms','smaller-works','packages','post'),
      'post_status' => 'publish',
      'posts_per_page' => -1,
      //'p'  => 3722,
  );
                                                            
  $the_query = null;                                                          
  $the_query = new WP_Query($args);  
  
  //if there are installations, lets run the functions
  if( $the_query->have_posts() ) : 
    while ($the_query->have_posts()) : $the_query->the_post();
        
        $post_type = $post->post_type;
        switch ($post_type) {
          case 'post':
            $post_type_folder = 'posts';
            break;
          case 'page':
            $post_type_folder = 'pages';
            break;
          case 'fitted-bathrooms':
            $post_type_folder = 'services/fitted-bathrooms';
            break;
          case 'fitted-kitchens':
            $post_type_folder = 'services/fitted-kitchens';
            break;
          case 'fitted-bedrooms':
            $post_type_folder = 'services/fitted-bedrooms';
            break;
          case 'smaller-works':
            $post_type_folder = 'services/smaller-works';
            break;
          case 'packages':
            $post_type_folder = 'packages';
            break;
        }

        //get the name of the post (slug to create the folder)
        $name = $post->post_name;
        $date = $post->post_date;

        //setup the image folders
        $image_folder = trailingslashit( wp_upload_dir()['basedir'] ) . $post_type_folder;
        $current_directory = trailingslashit( wp_upload_dir()['basedir'] ) . $post_type_folder .'/' . $name;

        //if the folder doesnt exist, create it
        if(!is_dir($image_folder)) {
         wp_mkdir_p($image_folder);
        }

        //if the current folder doesnt exist - create it
        if (!is_dir($current_directory)) {
          wp_mkdir_p($current_directory);
        }

        //lets get all attached images
        //get all attached images to this post
        /*($attachments = get_posts(
                            array(
                                'posts_per_page' => -1,
                                'post_mime_type' => 'image',
                                'post_type' => 'attachment',
                                'fields' => 'ids',
                                'post_parent' => $post->ID,
                            )
                        );*/
        //which post are we working with?
        echo 'Working with : ' . $name . '<br>';
        /*echo '<pre>';
        print_r ($attachments);
        echo '</pre>';*/

        //get the featured imaage ID
        $image_id   = get_post_thumbnail_id();
        //get the featured image url
        $image_url  = get_the_post_thumbnail_url();

        //get the image attachement for meta data
        $image      = get_attached_file( $image_id );
        
        //get all the inage details
        $image_details  = pathinfo( $image_url );

        //rename the image tp match the title of the post
        $image_new_name = $name . '-featured-image' . '.jpg'; 
        
        //change its location
        $new_location = $current_directory . '/' . $image_new_name;
        echo 'moving featured image: ' . $image_new_name . '<br>';
        
        //move the image - copy only as were going to rename it later
        rename( $image, $new_location);

        // Update the attachment name
        update_attached_file($image_id, $new_location);

        //generate all required thumbnails / image sizes for the new image
        $image_updated_meta = wp_generate_attachment_metadata( $image_id, $new_location );
        $update_meta = wp_update_attachment_metadata( $image_id, $image_updated_meta );

        $content = $post->post_title . ' - ' . 'Featured Image';

        $image_meta = array(
        'ID'            => $image_id, //id of the image
        'post_title'    => $post->post_title, //title
        'post_excerpt'  => $post->post_title,  // caption
        'post_content'  => $content,  // description
        'post_parent'   => $post->ID, //fallback to ensure its attached
        'post_date'     => $date
        );
        wp_update_post( $image_meta );

        // update alt text for post
        update_post_meta($image_id, '_wp_attachment_image_alt', $content );
        
        //lets look inside the post content and get all the embedded images
        /*$htmlString = $post->post_content;

        //Create a new DOMDocument object.
        $htmlDom = new DOMDocument;

        //Load the HTML string into our DOMDocument object.
        @$htmlDom->loadHTML($htmlString);

        //Extract all images elements / tags from the HTML.
        $imageTags = $htmlDom->getElementsByTagName('img');
        
        //Create an array to add extracted images to.
        $extractedImages = array();

        //Loop through the images tags that DOMDocument found.
        foreach($imageTags as $imgTag){

            //Get the src attribute of the img tag.
            $imgSrc = $imgTag->getAttribute('src');
            $image_id = attachment_url_to_postid($imgSrc);
            $image_meta = array(
              'ID'            => $image_id, //id of the image
              'post_parent'   => $post->ID,
              'post_date'     => $date
              );
            wp_update_post( $image_meta );
            echo 'attached : ' . $image_id . '<br>';
            //Add the image details to $extractedImages array.
            $extractedImages[] = $image_id; 
        }

        //now get the featured image
        $extractedImages[]   = get_post_thumbnail_id();

        echo "<pre>Images in content";
        print_r($extractedImages);
        echo '</pre>';

        echo "<pre>Attached Images <br>";
        print_r($attachments);
        echo '</pre>';

        $difference = array_diff($attachments, $extractedImages);
        foreach ($difference as $diff) {
          $image_meta = array(
          'ID'            => $diff, //id of the image
          'post_parent'   => ''
          );
          wp_update_post( $image_meta );
          echo 'Unnatached ' . $diff . '<br>';
        }*/
        
    endwhile;
  endif;
  //clear the query                                                              
  wp_reset_query();                                   
  wp_reset_postdata();
}
//add_action ('the_content', 'ahi_move_service_images');
