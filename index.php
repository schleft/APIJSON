<!DOCTYPE html>
<html>
<head>
	<title>Croisement API en utilisant JSON</title>
	<meta charset="utf-8" />

	<meta name="viewport" content="width=device-width, initial-scale=1.0">

	<link rel="stylesheet" href="http://cdn.leafletjs.com/leaflet/v0.7.7/leaflet.css" />
</head>
<body>
	<script src="http://cdn.leafletjs.com/leaflet/v0.7.7/leaflet.js"></script>
	<form>
		Location: <input type="text" name="adresse"> '*' affiche tous les évènements.<br>
	</form>
	<div id="map" style="width: 600px; height: 400px"></div>
</body>
<?php
$lienCircu = "http://data.loire-atlantique.fr/api/publication/24440040400129_NM_NM_00177/Alertes_infotrafic_nm_STBL/content";

$fileCircuJson = file_get_contents($lienCircu);
$fileCircu = json_decode($fileCircuJson,true);
//var_dump($fileCircu["data"][0]);

if(isset($_GET["adresse"])){
	$affichage= [];
	if($_GET["adresse"] == ""){
		$adresse="nantes";
	}else{
		$motsCles=explode(" ",$_GET["adresse"]);
		$adresse=$motsCles[0];
		foreach($motsCles as $value){
			if($value == "*" || $value==""){
				$adresse="nantes";
			}
			foreach ($fileCircu['data'] as $info){
				if(strtolower($value) == "travaux" || strtolower($value) == "embouteillage" || strtolower($value) == "bouchon" || strtolower($value) == "circulation" || $value == "*"){
					if(strpos($info['Secteur'], $value)){
						if(strpos(strtolower($info['Nom']), strtolower($value)) !== FALSE || strpos(strtolower($info['Detail']), strtolower($value)) !== FALSE || $value=="*"){
							if(!in_array($info,$affichage)){
								array_push($affichage,$info);
							}
						}
					}else{
						foreach ($fileCircu['data'] as $info) {
							if(strpos(strtolower($info['Nom']), strtolower($value)) !== FALSE || strpos(strtolower($info['Detail']), strtolower($value)) !== FALSE || $value=="*"){
								if(!in_array($info,$affichage)){
									array_push($affichage,$info);
								}
							}
						}
					}
				}else{
					// echo "inf: ".$info['Secteur']."<br />";
					// echo "val: ".$value."<br /><br />";
					if(strpos(strtolower($info['Secteur']), strtolower($value))){
						if(!in_array($info,$affichage)){
							array_push($affichage,$info);
						}
					}
				}
			}
		}
		foreach ($affichage as $infoA) {
			$date=explode(" ",$infoA['Date_notification']);
			echo "Secteur: ".$infoA['Secteur']."<br />";
			echo "Rue: ".$infoA['Nom']."<br />";
			echo "Détail: ".$infoA['Detail']."<br />";
			echo "Date: ".$date[0]." à ".$date[1];
			echo "<br /><br />";
		}

	}

	$lienAdresse = str_replace(" ", "%20", "https://maps.googleapis.com/maps/api/geocode/json?address=".$adresse."&key=AIzaSyDeHMq1KiQk6pR_GhXAmsz6OhfKBnmiiWY");

}else{
	$lienAdresse = str_replace(" ", "%20", "https://maps.googleapis.com/maps/api/geocode/json?address=nantes&key=AIzaSyDeHMq1KiQk6pR_GhXAmsz6OhfKBnmiiWY");
}

$fileNantesJSON = file_get_contents($lienAdresse);
$fileNantes = json_decode($fileNantesJSON, true);
if( $fileNantes["status"] == "OK")
{
	echo("
	<script>
	var lat =". $fileNantes["results"][0]["geometry"]["location"]["lat"] ."
	var lon =". $fileNantes["results"][0]["geometry"]["location"]["lng"] ."
	map = L.map('map').setView([lat, lon], 13);

	L.tileLayer('https://api.tiles.mapbox.com/v4/{id}/{z}/{x}/{y}.png?access_token=pk.eyJ1IjoibWFwYm94IiwiYSI6ImNpandmbXliNDBjZWd2M2x6bDk3c2ZtOTkifQ._QA7i5Mpkd_m30IGElHziw', {
		maxZoom: 18,
		attribution: 'Map data &copy; <a href=\"http://openstreetmap.org\">OpenStreetMap</a> contributors, ' +
		'<a href=\"http://creativecommons.org/licenses/by-sa/2.0/\">CC-BY-SA</a>, ' +
		'Imagery © <a href=\"http://mapbox.com\">Mapbox</a>',
		id: 'mapbox.streets'
	}).addTo(map);
	</script>");
	// var_dump("<pre>",$fileCircu,"</pre>");
	foreach ($fileCircu["data"] as $key => $value){
		foreach($value as $cle => $valeur){
			if($cle == "Nom"){
				$lienAdresse = str_replace(" ", "%20", "https://maps.googleapis.com/maps/api/geocode/json?address=". $valeur ."&key=AIzaSyDeHMq1KiQk6pR_GhXAmsz6OhfKBnmiiWY");
				$fileNantesJSON = file_get_contents($lienAdresse);
				$fileNantes = json_decode($fileNantesJSON, true);
			}

			if($cle == "Detail" && $fileNantes["status"] == "OK"){
				echo('
				<script>
				L.marker(['. $fileNantes["results"][0]["geometry"]["location"]["lat"] .','. $fileNantes["results"][0]["geometry"]["location"]["lng"] .']).addTo(map)
				.bindPopup("'. $valeur .'");

				</script>');
			}
		}
	}
}else{
	echo("Adresse inexistante");
}
?>
