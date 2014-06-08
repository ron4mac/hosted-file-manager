<?php

class ImageCR {
	
	protected $imgW, $imgH, $imgType;
	protected $truImg, $newImg = null;

	public function copySample ($sx, $sy, $dw, $dh, $sw, $sh)
	{
		$this->newImg = ImageCreateTrueColor($dw, $dh);
		imagecopyresampled($this->newImg, $this->truImg, 0, 0, $sx, $sy, $dw, $dh, $sw, $sh);
	}

	public function copy ($x, $y, $w, $h)
	{
		$this->newImg = ImageCreateTrueColor($w, $h);
		imagecopy($this->newImg, $this->truImg, 0, 0, $x, $y, $w, $h);
	}

	public function rotate ($deg)
	{
		if (!$deg) return;

    $width_before = imagesx($this->truImg);
    $height_before = imagesy($this->truImg);
    $this->truImg = imagerotate($this->truImg, -$deg, 0);
    //but imagerotate scales, so we clip to the original size
    $img2 = @imagecreatetruecolor($width_before, $height_before);
    $new_width = imagesx($this->truImg); // these dimensions are
    $new_height = imagesy($this->truImg);// the scaled ones (by imagerotate)
    imagecopyresampled(
        $img2, $this->truImg,
        0, 0,
        ($new_width-$width_before)/2,
        ($new_height-$height_before)/2,
        $width_before,
        $height_before,
        $width_before,
        $height_before
    );
    $this->truImg = $img2;
    // now img1 is center rotated and maintains original size

	//	$rot = imagerotate($this->truImg, $deg, 0);
	//	imagedestroy($this->truImg);
	//	$this->truImg = $rot;
	}

	public function saveToFile ($fPath, $qual=90)
	{
		$img = $this->newImg ? $this->newImg : $this->truImg;
		switch ($this->imgType) {
		case IMAGETYPE_GIF:
			imagegif($img, $fPath);
			break;
		case IMAGETYPE_JPEG2000:
		case IMAGETYPE_JPEG:
			imagejpeg($img, $fPath, $qual);
			break;
		case IMAGETYPE_PNG:
			imagepng($img, $fPath);
			break;
		default:
			exit('Error: Unknown image object');
		}
	}


	public function __construct ($imgFile)
	{
		$isi = getimagesize($imgFile);
		$this->imgW = $isi[0];
		$this->imgH = $isi[1];
		$this->imgType = $isi[2];
		switch ($this->imgType) {
		case IMAGETYPE_GIF:
			$this->truImg = imagecreatefromgif($imgFile);
			break;
		case IMAGETYPE_JPEG2000:
		case IMAGETYPE_JPEG:
			$this->truImg = imagecreatefromjpeg($imgFile);
			break;
		case IMAGETYPE_PNG:
			$this->truImg = imagecreatefrompng($imgFile);
			break;
/*
		case IMAGETYPE_SWF:
			break;
		case IMAGETYPE_PSD:
			break;
		case IMAGETYPE_BMP:
			break;
		case IMAGETYPE_WBMP:
			break;
		case IMAGETYPE_XBM:
			break;
		case IMAGETYPE_TIFF_II:
			break;
		case IMAGETYPE_TIFF_MM:
			break;
		case IMAGETYPE_IFF:
			break;
		case IMAGETYPE_JB2:
			break;
		case IMAGETYPE_JPC:
			break;
		case IMAGETYPE_JP2:
			break;
		case IMAGETYPE_JPX:
			break;
		case IMAGETYPE_SWC:
			break;
		case IMAGETYPE_ICO:
			break;
*/
		default:
			exit('Error: Unknown image type');
		}
	}

	public function __destruct ()
	{
		@imagedestroy($this->truImg);
		@imagedestroy($this->newImg);
	}

}