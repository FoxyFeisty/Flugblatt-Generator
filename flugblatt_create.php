<?php 

session_start();

require("fpdf181/fpdf.php");

// define('FPDF_FONTPATH', 'resources/php/fpdf/font/'); 

class PDF extends FPDF {
	// TITEL Formatierung
	function Header() {
		global $title;
		$this->SetFont("Helvetica", 'B', 40);
		$titleWidth = $this->GetStringWidth($title); 
		$this->SetX((210-$titleWidth)/2);
		$this->Cell($titleWidth,40,$title,0,0,'C');
		$this->Ln(20);
	}
	// BILD Formatierung
	const DPI = 64;
	const MM_IN_INCH = 25.4;
	const A4_WIDTH = 297;
	const A4_HEIGHT = 210;

    function pixelsToMM($val) {
        return $val * self::MM_IN_INCH / self::DPI;
    }
    function resizeToFit($imgFilename) {
        list($width, $height) = getimagesize($imgFilename);
        // Unterschiedliche Angaben für Hoch- und Querformat
        if ($width > $height) {
		$MAX_HEIGHT = 600;
		$MAX_WIDTH = 400;
		} else {
		$MAX_HEIGHT = 300;
		$MAX_WIDTH = 200;
		}
        $widthScale = $MAX_WIDTH / $width;
        $heightScale = $MAX_HEIGHT / $height;
        $scale = min($widthScale, $heightScale);
        return array(
            round($this->pixelsToMM($scale * $width)),
            round($this->pixelsToMM($scale * $height))
        );
    }
    function centreImage($img) {
        list($width, $height) = $this->resizeToFit($img);
        $this->Image(
            $img, (self::A4_HEIGHT - $width) / 2,
            50, // nicht zentriert in der Höhe
            $width,
            $height);
    }
}
// Titel
$title = $_SESSION["title"];
// Bild
if (isset($_SESSION["bild"])) {
	$image = $_SESSION["bild"];
} else {
	$image = "standard_image.jpg";
}
// Beschreibung
$text = $_SESSION["text"];
// Kontaktangaben
if (isset($_SESSION["tel"])) {
	$tel = $_SESSION["tel"];
}
if (isset($_SESSION["mail"])) {
	$mail = $_SESSION["mail"];
}
// Neues PDF erstellen
$flugblatt = new PDF();
// Neue Seite hinzufügen
$flugblatt->AddPage();
// Titel über Klasse erstellen
$flugblatt->SetTitle($title);
// Userbild bzw. Standardbild einfügen
$flugblatt->centreImage($image);
// Beschreibung einfügen
$flugblatt->SetFont("Helvetica", '', 14);
$flugblatt->Ln(150);
$flugblatt->Write(9,$text);
$flugblatt->Ln(15);
// Kontaktangaben einfügen
$flugblatt->SetFontSize(25);
$flugblatt->SetTextColor(230, 0, 172); 

if ((isset($mail)) && (isset($tel))) {
	$flugblatt->Cell(0,10,$mail,0,1,'L');
	$flugblatt->Cell(0,10,$tel,0,1,'');
}
if ((isset($mail)) && (!isset($tel))) {
	$flugblatt->Cell(10,10,$mail,0,0,'C');
}
if (!(isset($mail)) && (isset($tel))) {
	$flugblatt->Cell(10,10,$tel,0,0,'C');
}
// Bild vom Server löschen
if ($image != "standard_image.jpg") {
	unlink($image);
}
// PDF im Browser darstellen 
$flugblatt->Output("flugblatt_test.pdf", "I");


