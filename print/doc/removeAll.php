<?php
include('../../php/connection.php');

$files = glob('pickupsheet/pus_pre/*'); // get all file names
foreach($files as $file){ // iterate files
  if(is_file($file)) {
    unlink($file); // delete file
  }
}

$files = glob('pickupsheet/merge_pus/*'); // get all file names
foreach($files as $file){ // iterate files
  if(is_file($file)) {
    unlink($file); // delete file
  }
}

$files = glob('merge_doc/*'); // get all file names
foreach($files as $file){ // iterate files
  if(is_file($file)) {
    unlink($file); // delete file
  }
}

$files = glob('truckfrom/*'); // get all file names
foreach($files as $file){ // iterate files
  if(is_file($file)) {
    unlink($file); // delete file
  }
}

