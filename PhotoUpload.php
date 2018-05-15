<?php
namespace PhotoUpload {
  //require 'vendor/autoload.php';
  require 'lib/rb.php';
  require 'vendor/cloudinary/cloudinary_php/src/Cloudinary.php';
  require 'vendor/cloudinary/cloudinary_php/src/Uploader.php';
  require 'vendor/cloudinary/cloudinary_php/src/Api.php'; // Only required for creating upload presets on the fly
  error_reporting(E_ALL | E_STRICT);

  // Sets up Cloudinary's parameters and RB's DB
  include 'settings.php';

  // Global settings
  /*if (array_key_exists('REQUEST_SCHEME', $_SERVER)) {
    $cors_location = $_SERVER["REQUEST_SCHEME"] . "://" . $_SERVER["SERVER_NAME"] .
        dirname($_SERVER["SCRIPT_NAME"]) . "/lib/cloudinary_cors.html";
  } else {
    $cors_location = "http://" . $_SERVER["HTTP_HOST"] . "/lib/cloudinary_cors.html";
  }*/

  $thumbs_params = array("format" => "jpg", "height" => 150, "width" => 150,
    "class" => "thumbnail inline");

  // Helper functions
  function ret_var_dump($var) {
    ob_start();
    var_dump($var);
    return ob_get_clean();
  }

  function status_dump($array)
  {
    $saved_error_reporting = error_reporting(0);
    foreach ($array as $key => $value) {
      if($key != 'class') {
        if($key = 'url' || $key == 'secure_url') {
          echo $key . ': ' . $value . PHP_EOL;
        } else {
          echo $key . ': ' . json_encode($value) . PHP_EOL;
        }
      }
    }
    echo "END" . PHP_EOL;
    error_reporting($saved_error_reporting);
  }

  function array_to_table($array) {
    $saved_error_reporting = error_reporting(0);
    echo "<table class='info'>";
    foreach ($array as $key => $value) {
      if ($key != 'class') {
        if ($key == 'url' || $key == 'secure_url') {
          $display_value = '"' . $value . '"';
        } else {
          $display_value = json_encode($value);
        }
        echo "<tr><td>" . $key . ":</td><td>" . $display_value . "</td></tr>";
      }
    }
    echo "</table>";
    error_reporting($saved_error_reporting);
  }

  function check_photo_uploaded($name)
  {
    $uploadedPhotos = \R::findOne('photo','public_id = ?', Array($name)); 
    return $uploadedPhotos;
  }


  function create_photo_model($options = array()) {
    $photo = \R::dispense('photo');

    foreach ( $options as $key => $value ) {
      if ($key != 'tags') {
        $photo->{$key} = $value;
      }
    }

    # Add metadata we want to keep:
    $photo->moderated = false;
    $photo->created_at = (array_key_exists('created_at', $photo) ?
      DateTime::createFromFormat(DateTime::ISO8601, $photo->created_at) :
      \R::isoDateTime());

    $id = \R::store($photo);
  }
}
