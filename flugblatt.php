<?php

session_start();
// maximale Größe für Bildupload
$max_size = 3000 * 1024;
// Formulardetails in Variablen speichern
$title = isset($_POST["title"]) ? trim($_POST["title"]) : null;
// $image = isset($_POST["image"]) ? $_POST["image"] : null;
$text = isset($_POST["description"]) ? trim(substr($_POST["description"],0,500)) : null;
$mail = isset($_POST["mail"]) ? $_POST["mail"] : null;
$tel = isset($_POST["tel"]) ? $_POST["tel"] : null;
$mailTo = isset($_POST["mailTo"]) ? $_POST["mailTo"] : null;

if (isset($_POST["btnClick"])) {
	// Fehlermeldungen für Pflichtfelder
	if (!$title) {
		$meldung[] = "Bitte gib einen Titel ein.";
	}
	if (!$text) {
		$meldung[] = "Bitte beschreibe dein Anliegen.";
	}
	if (!$mail && !$tel) {
		$meldung[] = "Bitte gib Kontaktdaten an.";
 	}
 	//E-Mail-Überprüfung
 	if (!filter_var($mail, FILTER_VALIDATE_EMAIL)) {
 		$meldung[] = "Bitte gib eine gültige E-Mail-Adresse an.";
 	}
 	// $_FILES-Array aus Fileupload
 	$bild = $_FILES["image"];
 	// Bildendung über Konstante PATHINFO_EXTENSION generieren
 	$ending = pathinfo($bild['name'],PATHINFO_EXTENSION);
 	$allowed_endings = array("jpg", "jpeg", "gif", "png");
 	// Überprüfen, ob richtige Endung und Bild hochgeladen
 	if (!in_array($ending, $allowed_endings) && ($bild['error'] === 0)) {
 		$meldung[] = "Ups, das war wohl keine Bilddatei.";
 	}
 	// wenn Fehler durch Browserprüfung
 	if ($bild["error"] == 2) {
 		$meldung[] = "Das Bild ist leider zu groß.";
 	}
 	if ($bild["size"] > $max_size) {
 		$meldung[] = "Das Bild ist leider zu groß.";
 	}
 	// Formulardetails in Session speichern
	if (!isset($meldung)) {
		$_SESSION["title"] = $title;
		$_SESSION["text"] = $text;
		$_SESSION["tel"] = ($tel!=null) ? "Tel.: " . $tel : "";
		$_SESSION["mail"] = ($mail!=null) ? "E-Mail: " . $mail : "";
		// eventuelle vorige Auswahl löschen
		unset ($_SESSION["bild"]);

		// wurde Bild hochgeladen, dann Bildname generieren
		if($bild['error'] == 0){
			if ($ending="jpeg") {
				$bildname = "bild_" . session_id() . ".jpeg";
			} else if ($ending="jpg") {
				$bildname = "bild_" . session_id() . ".jpg";
			} else if ($ending="gif") {
				$bildname = "bild_" . session_id() . ".gif";
			} else {
				$bildname = "bild_" . session_id() . ".png";
			}
			// Bild auf Server speichern
	 		move_uploaded_file($bild['tmp_name'], $bildname);
			$_SESSION["bild"] = $bildname;
		}
		// PDF erstellen
		header('location: flugblatt_create.php');
	}
}

?>

<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>Flugblattgenerator</title>
		<link rel="stylesheet" type="text/css" href="flugblatt.css">
		<link href="https://fonts.googleapis.com/css?family=Gloria+Hallelujah" rel="stylesheet"> 
	</head>
	<body>
		<h1>Flugblatt-Generator</h1>
		<div class="intro">
		<span>Wohnung gesucht? Katze entlaufen? Schlüssel verloren?</span>
		<p>Bring dein Anliegen auf die Straße! Einfach Formular ausfüllen, PDF speichern, ausdrucken und aufhängen.</p>
		</div>
		<div id="generator">
			<h2>Jetzt Flugblatt erstellen:</h2>
			<div class="formDiv" name="meldung" id="meldung" style="color:red">
			<?php 
				if (isset($meldung)) {
				echo "<p style='color:#00b300'>" . implode('<br>', $meldung) . "</p>";
				}
			?>
			</div>
			<form enctype="multipart/form-data" action="<?php echo $_SERVER['SCRIPT_NAME'] ?>" method="post">	
				<div class="formDiv">
					<label for="title">Titel:* </label>
						<input type="text" id="title" name="title" value="<?php echo $title; ?>" maxlength="30">
				</div>
				<div class="formDiv">
				<label for="bild">Bild: </label>
					<input type="hidden" name="MAX_FILE_SIZE" value="<?php echo $max_size?>">
					<input type="file" id="image" name="image" accept="image/*">
				</div> 
				<div class="formDiv">
				<p>Beschreibe dein Anliegen (max. 500 Zeichen):* </p>
						<textarea id="description" name="description" rows="10" cols="30"><?php echo $text; ?></textarea>
				</div>
				<p>Deine Kontaktdaten für das Flugblatt:* </p>
				<div class="formDiv">
					<label for="contact">E-Mail: </label>
						<input type="mail" id="mail" name="mail" value="<?php echo $mail; ?>">	
				</div>
				<div class="formDiv">UND/ODER</div>
				<div class="formDiv">
					<label for="tel">Tel.: </label>
						<input type="number" id="tel" name="tel" value="<?php echo $tel; ?>">
				</div>
				<div class="formDiv">
					<input type="submit" value="Fertig!" name="btnClick"" class="btn">
				</div>
			</form>
		</div>
	</body>
</html>
