<?php
/**
 * @namespace
 */
namespace Engine\Tools;

/**
 * Class Image
 *
 * @category   Engine
 * @package    Tools
 */
class Image
{
    static function resize (
        $src,
        $dest,
        $width = 85,
        $height = 55,
        $exact = true,
        $watermark = false,
        $mode = 1,
        $watermark_position = [4]
    ) {

        //$exact  true: generate an image with the exact size WxH, with lateral or horizontal image completion(with bgColor)
        $quality = 100;

        if (! file_exists($src)) {
            return false;
        }

        $size = getimagesize($src);
        if ($size === false) {
            return false;
        }

        if (!is_array($watermark_position)) {
            $watermark_position = [$watermark_position];
        }

        $format = strtolower(substr($size ['mime'], strpos($size ['mime'], '/')+ 1));

        switch($size['mime']) {
            case 'image/jpeg' :
                $isrc = imagecreatefromjpeg($src);
                break;
            case 'image/gif' :
                $isrc = imagecreatefromgif ($src);
                break;
            case 'image/png' :
                $isrc = imagecreatefrompng($src);
                break;
            case 'image/bmp' :
                $isrc = imagecreatefrombmp($src);
                break;
            default :
                return false;
                break;
        }
        $ow = $size [0]; //original width
        $oh = $size [1]; //original height

        if ($width === false) $width = $size [0];
        if ($height === false) $height = $size [1];

        $zero = false;
        $w = $width;
        $h = $height;
        if ($w <= 0) {
            $zero = true;
            $w = $ow;
        }
        if ($h <= 0) {
            $zero = true;
            $h = $oh;
        }
        $rw = $ow / $w;
        $rh = $oh / $h;
        if ($mode == 0)
            $r = $rw < $rh ? $rw : $rh;
        else
            $r = $rw > $rh ? $rw : $rh;

        //если исходное изображение меньше по размера нового, то новые размеры приравниваем к исходным
        if ($r < 1) $r = 1;
        $res_w = $ow / $r;
        $res_h = $oh / $r;
        if ($r == 1) {
            $w = $ow;
            $h = $oh;
        }
        if ($exact && ! $zero) {
            $idest = imagecreatetruecolor($w, $h);
            $bgcolor = imagecolorallocate($idest, 255, 255, 255);
            imagefill($idest, 0, 0, $bgcolor);
            imagecopyresampled($idest, $isrc,($w - $res_w) / 2,($h - $res_h) / 2, 0, 0, $res_w, $res_h, $ow, $oh);
        } else {
            $idest = imagecreatetruecolor($res_w, $res_h);
            imagecopyresampled($idest, $isrc, 0, 0, 0, 0, $res_w, $res_h, $ow, $oh);
        }
        if ($watermark) {
            if (file_exists($watermark)) {
                $wm_image = imagecreatefrompng($watermark);
                $wsize = getimagesize($watermark);
                $wrw = $width /  $wsize[0];
                $wrh = $height /  $wsize[1];

                $ww = $res_w / $wrw;
                $wh = $res_h / $wrh;

                if ($exact && ! $zero) {
                    /*	dstX, dstY - Точка на изображении назначения, которая определяет левый верхний угол прямоугольника в который будет вставляться копируемая область.
                        dstW, dstH - ширина и высота прямоугольника в который будет вписана копируемая область.
                        srcX, srcY - Точка на изображении-источнике, которая определяет левый верхний угол прямоугольника, содержащего копируемую.
                        srcW, srcH - ширина и высота копируемой области на изображении-источнике.
                    */
                    if (in_array(1,$watermark_position)) { //left,top
                        $dstX = 0; $dstY = 0; $srcX = 0; $srcY = 0;
                        $dstW = $ww; $dstH = $wh; $srcW = $wsize[0]; $srcH = $wsize[1];
                        imagecopyresampled($idest, $wm_image, $dstX, $dstY, $srcX, $srcY, $dstW, $dstH, $srcW, $srcH);
                    }
                    if (in_array(2,$watermark_position)) { //right,top
                        $dstX = $w - $ww -(($w - $res_w) / 2); $dstY = 0; $srcX = 0; $srcY = 0;
                        $dstW = $ww; $dstH = $wh; $srcW = $wsize[0]; $srcH = $wsize[1];
                        imagecopyresampled($idest, $wm_image, $dstX, $dstY, $srcX, $srcY, $dstW, $dstH, $srcW, $srcH);
                    }
                    if (in_array(3,$watermark_position)) { //left,bottom
                        $dstX = 0; $dstY = $h - $wh -(($h - $res_h) / 2); $srcX = 0; $srcY = 0;
                        $dstW = $ww; $dstH = $wh; $srcW = $wsize[0]; $srcH = $wsize[1];
                        imagecopyresampled($idest, $wm_image, $dstX, $dstY, $srcX, $srcY, $dstW, $dstH, $srcW, $srcH);
                    }
                    if (in_array(4,$watermark_position)) { //right,bottom
                        $dstX = $w - $ww -(($w - $res_w) / 2); $dstY = $h - $wh -(($h - $res_h) / 2); $srcX = 0; $srcY = 0;
                        $dstW = $ww; $dstH = $wh; $srcW = $wsize[0]; $srcH = $wsize[1];
                        imagecopyresampled($idest, $wm_image, $dstX, $dstY, $srcX, $srcY, $dstW, $dstH, $srcW, $srcH);
                    }

                } else {
                    if (in_array(1,$watermark_position)) { //left,top
                        $dstX = 0; $dstY = 0; $srcX = 0; $srcY = 0;
                        $dstW = $wsize[0]; $dstH = $wsize[1]; $srcW = $wsize[0]; $srcH = $wsize[1];
                        imagecopyresampled($idest, $wm_image, $dstX, $dstY, $srcX, $srcY, $dstW, $dstH, $srcW, $srcH);
                    }
                    if (in_array(2,$watermark_position)) { //right,top
                        $dstX = $res_w - $wsize[0]; $dstY = 0; $srcX = 0; $srcY = 0;
                        $dstW = $wsize[0]; $dstH = $wsize[1]; $srcW = $wsize[0]; $srcH = $wsize[1];
                        imagecopyresampled($idest, $wm_image, $dstX, $dstY, $srcX, $srcY, $dstW, $dstH, $srcW, $srcH);
                    }
                    if (in_array(3,$watermark_position)) { //left,bottom
                        $dstX = 0; $dstY = $res_h - $wsize[1]; $srcX = 0; $srcY = 0;
                        $dstW = $wsize[0]; $dstH = $wsize[1]; $srcW = $wsize[0]; $srcH = $wsize[1];
                        imagecopyresampled($idest, $wm_image, $dstX, $dstY, $srcX, $srcY, $dstW, $dstH, $srcW, $srcH);
                    }
                    if (in_array(4,$watermark_position)) { //right,bottom
                        $dstX = $res_w - $wsize[0]; $dstY = $res_h - $wsize[1]; $srcX = 0; $srcY = 0;
                        $dstW = $wsize[0]; $dstH = $wsize[1]; $srcW = $wsize[0]; $srcH = $wsize[1];
                        imagecopyresampled($idest, $wm_image, $dstX, $dstY, $srcX, $srcY, $dstW, $dstH, $srcW, $srcH);
                    }

                }
            }else{
                $wm_image = imagecreatefrompng($watermark);
                var_dump(__FILE__);
            }
        }
        imagejpeg($idest, $dest, $quality);
        /*switch($size ['mime'])
         {
         case 'image/jpeg':
         imagejpeg( $idest, $dest, $quality); //100 is the quality settings, values range from 0-100.
         break;
         case 'image/gif':
         imagegif ($idest, $dest, $quality); //100 is the quality settings, values range from 0-100.
         break;
         case 'image/png':
         imagepng( $idest, $dest, $quality); //100 is the quality settings, values range from 0-100.
         break;
         case 'image/bmp':
         imagebmp( $idest, $dest, $quality); //100 is the quality settings, values range from 0-100.
         break;
         }*/
        imagedestroy($isrc);
        imagedestroy($idest);

        return true;
    }

    /**
     * @param $src
     * @param $mask
     * @param int $quality
     * @return bool
     */
    static function layer($src, $mask, $quality = 100)
    {
        if (file_exists($src)) {
            $size_img = getimagesize($src);

            if ($size_img [2] == 2) {
                $img = imagecreatefromjpeg($src);
            } elseif ($size_img [2] == 1) {
                $img = imagecreatefromgif ($src);
            } elseif ($size_img [2] == 3) {
                $img = imagecreatefrompng($src);
            }
            $size_mask = getimagesize($mask);
            if ($size_mask [2] != 3) {
                return false;
            }
            $blank = imagecreatefrompng($mask);
            $create_mask = imagecreatefrompng($mask);

            imagealphablending($blank, true);

            ImageCopy($blank, $img, 0, 0, 0, 0, $size_img [0], $size_img [1]);
            ImageCopy($blank, $create_mask, 0, 0, 0, 0, $size_img [0], $size_img [1]);

            if ($size_img [2] == 2) {
                imagejpeg($blank, $src, $quality);
            } elseif ($size_img [2] == 1) {
                $img = imagecreatefromgif ($blank, $src, $quality);
            } elseif ($size_img [2] == 3) {
                $img = imagecreatefrompng($blank, $src, $quality);
            }
            imagedestroy($blank);
            imagedestroy($img);
        } else {
            return false;
        }
    }

}

function imagebmp(&$img, $filename = "")
{
    $widthOrig = imagesx($img);
    // width = 16*x
    $widthFloor =((floor($widthOrig / 16)) * 16);
    $widthCeil =((ceil($widthOrig / 16)) * 16);
    $height = imagesy($img);

    $size =($widthCeil * $height * 3) + 54;

    // Bitmap File Header
    $result = 'BM'; // header(2b)
    $result .= int_to_dword($size); // size of file(4b)
    $result .= int_to_dword(0); // reserved(4b)
    $result .= int_to_dword(54); // byte location in the file which is first byte of IMAGE(4b)
    // Bitmap Info Header
    $result .= int_to_dword(40); // Size of BITMAPINFOHEADER(4b)
    $result .= int_to_dword($widthCeil); // width of bitmap(4b)
    $result .= int_to_dword($height); // height of bitmap(4b)
    $result .= int_to_word(1); // biPlanes = 1(2b)
    $result .= int_to_word(24); // biBitCount = {1(mono) or 4(16 clr)or 8(256 clr) or 24(16 Mil)}(2b)
    $result .= int_to_dword(0); // RLE COMPRESSION(4b)
    $result .= int_to_dword(0); // width x height(4b)
    $result .= int_to_dword(0); // biXPelsPerMeter(4b)
    $result .= int_to_dword(0); // biYPelsPerMeter(4b)
    $result .= int_to_dword(0); // Number of palettes used(4b)
    $result .= int_to_dword(0); // Number of important colour(4b)


    // is faster than chr()
    $arrChr = [];
    for($i = 0; $i < 256; $i ++) {
        $arrChr [$i] = chr($i);
    }

    // creates image data
    $bgfillcolor = array("red" => 0, "green" => 0, "blue" => 0);

    // bottom to top - left to right - attention blue green red !!!
    $y = $height - 1;
    for($y2 = 0; $y2 < $height; $y2 ++) {
        for($x = 0; $x < $widthFloor;) {
            $rgb = imagecolorsforindex($img, imagecolorat($img, $x ++, $y));
            $result .= $arrChr [$rgb ["blue"]] . $arrChr [$rgb ["green"]] . $arrChr [$rgb ["red"]];
            $rgb = imagecolorsforindex($img, imagecolorat($img, $x ++, $y));
            $result .= $arrChr [$rgb ["blue"]] . $arrChr [$rgb ["green"]] . $arrChr [$rgb ["red"]];
            $rgb = imagecolorsforindex($img, imagecolorat($img, $x ++, $y));
            $result .= $arrChr [$rgb ["blue"]] . $arrChr [$rgb ["green"]] . $arrChr [$rgb ["red"]];
            $rgb = imagecolorsforindex($img, imagecolorat($img, $x ++, $y));
            $result .= $arrChr [$rgb ["blue"]] . $arrChr [$rgb ["green"]] . $arrChr [$rgb ["red"]];
            $rgb = imagecolorsforindex($img, imagecolorat($img, $x ++, $y));
            $result .= $arrChr [$rgb ["blue"]] . $arrChr [$rgb ["green"]] . $arrChr [$rgb ["red"]];
            $rgb = imagecolorsforindex($img, imagecolorat($img, $x ++, $y));
            $result .= $arrChr [$rgb ["blue"]] . $arrChr [$rgb ["green"]] . $arrChr [$rgb ["red"]];
            $rgb = imagecolorsforindex($img, imagecolorat($img, $x ++, $y));
            $result .= $arrChr [$rgb ["blue"]] . $arrChr [$rgb ["green"]] . $arrChr [$rgb ["red"]];
            $rgb = imagecolorsforindex($img, imagecolorat($img, $x ++, $y));
            $result .= $arrChr [$rgb ["blue"]] . $arrChr [$rgb ["green"]] . $arrChr [$rgb ["red"]];
        }
        for($x = $widthFloor; $x < $widthCeil; $x ++) {
            $rgb =($x < $widthOrig) ? imagecolorsforindex($img, imagecolorat($img, $x, $y)) : $bgfillcolor;
            $result .= $arrChr [$rgb ["blue"]] . $arrChr [$rgb ["green"]] . $arrChr [$rgb ["red"]];
        }
        $y --;
    }

    // see imagegif
    if ($filename == "") {
        echo $result;
    } else {
        $file = fopen($filename, "wb");
        fwrite($file, $result);
        fclose($file);
    }
}

/*
 *------------------------------------------------------------
 *                    ImageCreateFromBmp
 *------------------------------------------------------------
 *            - Reads image from a BMP file
 *
 *         Parameters:  $file - Target file to load
 *
 *            Returns: Image ID
 */

function imagecreatefrombmp($file)
{
    global $CurrentBit, $echoMode;

    $Data = '';
    $f = fopen($file, "r");
    $Header = fread($f, 2);

    if ($Header == "BM") {
        $Size = freaddword($f);
        $Reserved1 = freadword($f);
        $Reserved2 = freadword($f);
        $FirstByteOfImage = freaddword($f);

        $SizeBITMAPINFOHEADER = freaddword($f);
        $Width = freaddword($f);
        $Height = freaddword($f);
        $biPlanes = freadword($f);
        $biBitCount = freadword($f);
        $RLECompression = freaddword($f);
        $WidthxHeight = freaddword($f);
        $biXPelsPerMeter = freaddword($f);
        $biYPelsPerMeter = freaddword($f);
        $NumberOfPalettesUsed = freaddword($f);
        $NumberOfImportantColors = freaddword($f);

        if ($biBitCount < 24) {
            $img = imagecreate($Width, $Height);
            $Colors = pow(2, $biBitCount);
            for($p = 0; $p < $Colors; $p ++) {
                $B = freadbyte($f);
                $G = freadbyte($f);
                $R = freadbyte($f);
                $Reserved = freadbyte($f);
                $Palette [] = imagecolorallocate($img, $R, $G, $B);
            }

            if ($RLECompression == 0) {
                $Zbytek =(4 - ceil(($Width /(8 / $biBitCount)))% 4) % 4;

                for($y = $Height - 1; $y >= 0; $y --) {
                    $CurrentBit = 0;
                    for($x = 0; $x < $Width; $x ++) {
                        $C = freadbits($f, $biBitCount);
                        imagesetpixel($img, $x, $y, $Palette [$C]);
                    }
                    if ($CurrentBit != 0) {
                        freadbyte($f);
                    }
                    for($g = 0; $g < $Zbytek; $g ++) {
                        freadbyte($f);
                    }
                }

            }
        }

        if ($RLECompression == 1) //$BI_RLE8
        {
            $y = $Height;
            $pocetb = 0;

            while(true) {
                $y--;
                $prefix = freadbyte($f);
                $suffix = freadbyte($f);
                $pocetb += 2;

                $echoit = false;

                if ($echoit) {
                    echo "Prefix: $prefix Suffix: $suffix<BR>";
                }
                if (($prefix == 0) and($suffix == 1)) {
                    break;
                }
                if (feof($f)) {
                    break;
                }

                while(!(($prefix == 0) and($suffix == 0))) {
                    if ($prefix == 0) {
                        $pocet = $suffix;
                        $Data .= fread($f, $pocet);
                        $pocetb += $pocet;
                        if ($pocetb % 2 == 1) {
                            freadbyte($f);
                            $pocetb ++;
                        }
                    }

                    if ($prefix > 0) {
                        $pocet = $prefix;
                        for($r = 0; $r < $pocet; $r ++) {
                            $Data .= chr($suffix);
                        }
                    }
                    $prefix = freadbyte($f);
                    $suffix = freadbyte($f);
                    $pocetb += 2;
                    if ($echoit) {
                        echo "Prefix: $prefix Suffix: $suffix<BR>";
                    }
                }

                for($x = 0; $x < strlen($Data); $x ++) {
                    imagesetpixel($img, $x, $y, $Palette [ord($Data [$x])]);
                }
                $Data = "";

            }

        }

        if ($RLECompression == 2) //$BI_RLE4
        {
            $y = $Height;
            $pocetb = 0;

            /*while(!feof($f))
             echo freadbyte($f)."_".freadbyte($f)."<BR>";*/
            while(true) {
                //break;
                $y --;
                $prefix = freadbyte($f);
                $suffix = freadbyte($f);
                $pocetb += 2;

                $echoit = false;

                if ($echoit) {
                    echo "Prefix: $prefix Suffix: $suffix<BR>";
                }
                if (($prefix == 0) and($suffix == 1)) {
                    break;
                }
                if (feof($f)) {
                    break;
                }

                while(!(($prefix == 0) and($suffix == 0))) {
                    if ($prefix == 0) {
                        $pocet = $suffix;

                        $CurrentBit = 0;
                        for($h = 0; $h < $pocet; $h ++) {
                            $Data .= chr(freadbits($f, 4));
                        }
                        if ($CurrentBit != 0) {
                            freadbits($f, 4);
                        }
                        $pocetb += ceil(($pocet / 2));
                        if ($pocetb % 2 == 1) {
                            freadbyte($f);
                            $pocetb ++;
                        }
                    }

                    if ($prefix > 0) {
                        $pocet = $prefix;
                        $i = 0;
                        for($r = 0; $r < $pocet; $r ++) {
                            if ($i % 2 == 0) {
                                $Data .= chr($suffix % 16);
                            } else {
                                $Data .= chr(floor($suffix / 16));
                            }
                            $i ++;
                        }
                    }
                    $prefix = freadbyte($f);
                    $suffix = freadbyte($f);
                    $pocetb += 2;
                    if ($echoit) {
                        echo "Prefix: $prefix Suffix: $suffix<BR>";
                    }
                }

                for($x = 0; $x < strlen($Data); $x ++) {
                    imagesetpixel($img, $x, $y, $Palette [ord($Data [$x])]);
                }
                $Data = "";

            }

        }

        if ($biBitCount == 24) {
            $img = imagecreatetruecolor($Width, $Height);
            $Zbytek = $Width % 4;

            for($y = $Height - 1; $y >= 0; $y --) {
                for($x = 0; $x < $Width; $x ++) {
                    $B = freadbyte($f);
                    $G = freadbyte($f);
                    $R = freadbyte($f);
                    $color = imagecolorexact($img, $R, $G, $B);
                    if ($color == - 1)
                        $color = imagecolorallocate($img, $R, $G, $B);
                    imagesetpixel($img, $x, $y, $color);
                }
                for($z = 0; $z < $Zbytek; $z ++) {
                    freadbyte($f);
                }
            }

        }

        return $img;

    }

    fclose($f);

}
;

/*
 * Helping functions:
 *-------------------------
 *
 * freadbyte($file) - reads 1 byte from $file
 * freadword($file) - reads 2 bytes(1 word) from $file
 * freaddword($file) - reads 4 bytes(1 dword) from $file
 * freadlngint($file) - same as freaddword($file)
 * decbin8($d) - returns binary string of d zero filled to 8
 * RetBits($byte,$start,$len) - returns bits $start->$start+$len from $byte
 * freadbits($file,$count) - reads next $count bits from $file
 * RGBToHex($R,$G,$B) - convert $R, $G, $B to hex
 * int_to_dword($n) - returns 4 byte representation of $n
 * int_to_word($n) - returns 2 byte representation of $n
 */

function freadbyte($f) {
    return ord(fread($f, 1));
}

function freadword($f) {
    $b1 = freadbyte($f);
    $b2 = freadbyte($f);
    return $b2 * 256 + $b1;
}

function freadlngint($f) {
    return freaddword($f);
}

function freaddword($f) {
    $b1 = freadword($f);
    $b2 = freadword($f);
    return $b2 * 65536 + $b1;
}

function RetBits($byte, $start, $len) {
    $bin = decbin8($byte);
    $r = bindec(substr($bin, $start, $len));
    return $r;

}

$CurrentBit = 0;
function freadbits($f, $count) {
    global $CurrentBit, $SMode;
    $Byte = freadbyte($f);
    $LastCBit = $CurrentBit;
    $CurrentBit += $count;
    if ($CurrentBit == 8) {
        $CurrentBit = 0;
    } else {
        fseek($f, ftell($f)- 1);
    }
    return RetBits($Byte, $LastCBit, $count);
}

function RGBToHex($Red, $Green, $Blue) {
    $hRed = dechex($Red);
    if (strlen($hRed)== 1)
        $hRed = "0$hRed";
    $hGreen = dechex($Green);
    if (strlen($hGreen)== 1)
        $hGreen = "0$hGreen";
    $hBlue = dechex($Blue);
    if (strlen($hBlue)== 1)
        $hBlue = "0$hBlue";
    return($hRed . $hGreen . $hBlue);
}

function int_to_dword($n) {
    return chr($n & 255). chr(($n >> 8) & 255). chr(($n >> 16) & 255). chr(($n >> 24) & 255);
}

function int_to_word($n) {
    return chr($n & 255). chr(($n >> 8) & 255);
}

function decbin8($d) {
    return decbinx($d, 8);
}

function decbinx($d, $n) {
    $bin = decbin($d);
    $sbin = strlen($bin);
    for($j = 0; $j < $n - $sbin; $j ++)
        $bin = "0$bin";
    return $bin;
}

function inttobyte($n) {
    return chr($n);
}
;