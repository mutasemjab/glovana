<?php

function uploadImage($folder, $image)
{
    $extension = strtolower($image->getClientOriginalExtension());
    
    // Generate unique filename with timestamp and random number
    $filename = time() . '_' . rand(100000, 999999) . '.' . $extension;
    
    // Move the image to the folder
    $image->move($folder, $filename);
    
    return $filename;
}


function uploadFile($file, $folder)
{
    $path = $file->store($folder);
    return $path;
}



