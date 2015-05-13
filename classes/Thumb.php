<?php

class Thumb {
    static function create($imgPath, $thumbsDir, $newWidth = 300) {
        $isThumb = false;
        $types = [1 => 'gif', 2 => 'jpeg', 3 => 'png'];
        $type = exif_imagetype($imgPath);

        if ($type && $t = $types[$type]) {
            $isThumb = true;
            $name = basename($imgPath);
            list($width, $height) = getimagesize($imgPath);

            if ($width <= $newWidth) {
                $newWidth = $width;
                $newHeight = $height;

            } else {
                $ratio = $width / $newWidth;
                $newHeight = $height / $ratio;
            }

            $thumb = imagecreatetruecolor($newWidth, $newHeight);
            $source = call_user_func("imagecreatefrom$t", $imgPath);

            imagecopyresized($thumb, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

            $thumbsPath = "$thumbsDir$name";

            call_user_func_array("image$t", [$thumb, $thumbsPath]);
            chmod($thumbsPath, 0777);
        }
        return $isThumb;
    }
}