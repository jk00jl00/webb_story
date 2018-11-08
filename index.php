<?php 
session_start();
function addToTrail($a,$b){
	$a = preg_replace("/\s/", "|", $a);
	return ($a . $b . " ");
}

function getPrevLocation($a){
	preg_match("/[0-9]*\|[0-9]+[\s]/",$a, $b);

	preg_match("/[0-9]+/", $b[0], $str);
	return (isset($str[0])) ? $str[0]: " ";
}

function removeLastFromTrail($a){
	$a = preg_replace("/\|[0-9]+[\s]/"," ", $a);
	return $a;
}

function getCurrentFromTrail($a){
	preg_match("/\|*[0-9]*[\s]/",$a, $b);

	preg_match("/[0-9]+/", $b[0], $str);
	return (isset($str[0])) ? $str[0]: " ";
}

if(!isset($_SESSION["trail"])) $_SESSION["trail"] = " ";
if(!isset($_SESSION["a"])) $_SESSION["a"] = "u";
if(isset($_GET["a"])) $_SESSION["na"] = $_GET["a"];
if($_SESSION["na"] == "r"){ 
	$_SESSION =  array();
	session_destroy();
	header("Location: /soloAdv/");
}
if(isset($_GET["l"])) {
	$_SESSION["a"] = $_SESSION["na"];
	$_SESSION["l"] = $_GET["l"];
	if(getCurrentFromTrail($_SESSION["trail"]) != $_GET["l"]){
		$_SESSION["trail"] = addToTrail($_SESSION["trail"],  $_GET["l"]);
	}
}
if($_SESSION["na"] == "b"){
	$_SESSION["na"] = "u";
	$str = getPrevLocation($_SESSION["trail"]);
	if(strlen($_SESSION["trail"]) != 1)
		$_SESSION["trail"] = removeLastFromTrail($_SESSION["trail"]);
	
	header("Location: /soloAdv/?l=" . $str);
} 
	
if(!isset($_SESSION["l"])) header("Location: /soloAdv/?a=u&l=0");
?>
<!DOCTYPE html>
<html>
<head>
	<title></title>
	<style type="text/css">
		body{
			width: 80%;
			margin: 0 auto;
			text-anchor: top-left;
		
		}
		div{
			font-size: 1.2em;
		}
		a{
			text-decoration: none;
			font-weight: bold;
			color:black;
		}
		.alink{	
			border: 2px solid black;
			text-decoration: none;
			color: black;
		}
		.active{	
			border: 2px solid black;
			text-decoration: none;
			color: gray;
		}
		#text{
			border: 2px solid black;
			display: inline-block;
			width: 60%;
			margin-right: 0; 
		}
		#inventory{
			display: inline-block;
			border: 2px solid black;
			width: 30%;
			vertical-align: top;

		}

	</style>
</head>
<body>


	<div id="text">
		<p>
			<?php
				$str = "use";
				switch ($_SESSION["a"]) {
					case 'u':
						$str = "use";
						break;
					case 'i':
						$str = "inspect";
			            break;
			        case 'p':
			        	$str = "pick up";
			        	break;
					default:
						echo "error";
						break;
				}
				include "pdo.php";

				$trimStr = trim($str);

				$stmt = $pdo->prepare('SELECT `text` FROM `'. $str . '` WHERE targetId=:id');
				if($str == "inspect"){
					$prevLoc = getPrevLocation($_SESSION["trail"]);
					$stmt = $pdo->prepare('SELECT `text` FROM `'. $str . '` WHERE targetId=:id AND from_id=:fid');
					$stmt->bindParam(":fid", $prevLoc);	
				}
				$stmt->bindParam(":id", $_SESSION["l"]);
				$stmt->execute();

				$row = $stmt->fetch(PDO::FETCH_ASSOC);
				preg_match_all("/link\([0-9]+\)/",$row["text"], $linkIds);

				for($i = 0; $i < sizeof($linkIds[0]); $i++){
					preg_match("/[0-9]+/", $linkIds[0][$i], $linkId);

					if($_SESSION["a"] == "i")
						$str = getPrevLocation($_SESSION["trail"]);
					else
						$str = $_SESSION["l"];

					$stmt = $pdo->prepare('SELECT * FROM `links` WHERE story_id=:id AND order_nr=:sid');
					$stmt->bindParam(":id", $str);
					$stmt->bindParam(":sid", $linkId[0]);
					$stmt->execute(); 

					$texts = $stmt->fetch(PDO::FETCH_ASSOC);
		
					$v = ("/link\(" . $linkId[0] . "\)/");
					$row["text"] = preg_replace($v, '<a href="/soloAdv/?l=' . $texts["target_id"] . '">' . $texts['text'] . "</a>" , $row["text"]);
				}

				if($row["text"])
					echo $row["text"];
				else
					echo "Could not " . $str;

				/*var_dump($_SESSION);
				var_dump($_GET);*/

		 	?>
		 </p>
	</div>
	
	<div id="inventory">
		<p>Inventory</p>
	</div>

	<div id="actions">
		<a class="alink"href="/soloAdv/?a=b">Back</a>	
		<a class="alink <?php if(isset($_SESSION['na']) && $_SESSION['na'] == 'u') echo 'active'; ?>"href="/soloAdv/?a=u">Use</a>
		<a class="alink <?php if(isset($_SESSION['na']) && $_SESSION['na'] == 'i') echo 'active'; ?>"href="/soloAdv/?a=i">Inspect</a>
		<a class="alink <?php if(isset($_SESSION['na']) && $_SESSION['na'] == 'p') echo 'active'; ?>"href="/soloAdv/?a=p">Pick up</a>
		<a class="alink"href="/soloAdv/?a=r">Reset</a>
		<a class="alink"href="/soloAdv/edit.php<?php if(isset($_SESSION['l'])) echo ('?l=' . $_SESSION['l']) . ('&a=' . $_SESSION['a']). '&t='. getPrevLocation($_SESSION["trail"]);?>">Edit</a>
	</div>


</body>
</html>