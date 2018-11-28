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
	header("Location: /webb_story/");
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
	
	header("Location: /webb_story/?l=" . $str);
} 
	
if(!isset($_SESSION["l"])) header("Location: /webb_story/?a=u&l=0");
?>
<!DOCTYPE html>
<html>
<head>
	<title>Bip bop</title>
	<meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
	<style type="text/css">
		body{
			width: 80%;
			margin: 0 auto;		
		}
		a{
			text-decoration: none;
			font-weight: bold;
			color:black;
		}
		.active{	
			color: gray;
		}
		.border-2 {
		    border-width:2px !important;
		}

	</style>
</head>
<body>

	<div class="container">
		<div class="row">
			<div class="col-8 border border-2 border-dark">
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
							$row["text"] = preg_replace($v, '<a href="/webb_story/?l=' . $texts["target_id"] . '">' . $texts['text'] . "</a>" , $row["text"]);
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
			<div class="col-4 border border-2 border-dark text-center">
				<p>Inventory</p>
			</div>
		</div>
		<div class="row text-center">
			<a class="col-1 p-0 border border-2 border-dark" href="/webb_story/?a=b">Back</a>	
			<a class="col-1 p-0 border border-2 border-dark <?php if(isset($_SESSION['na']) && $_SESSION['na'] == 'u') echo 'active'; ?>" href="/webb_story/?a=u">Use</a>
			<a class="col-1 p-0 border border-2 border-dark <?php if(isset($_SESSION['na']) && $_SESSION['na'] == 'i') echo 'active'; ?>" href="/webb_story/?a=i">Inspect</a>
			<a class="col-1 p-0 border border-2 border-dark <?php if(isset($_SESSION['na']) && $_SESSION['na'] == 'p') echo 'active'; ?>" href="/webb_story/?a=p">Pick up</a>
			<a class="col-1 p-0 border border-2 border-dark" href="/webb_story/?a=r">Reset</a>
			<a class="col-1 p-0 border border-2 border-dark" href="/webb_story/edit.php<?php if(isset($_SESSION['l'])) echo ('?l=' . $_SESSION['l']) . ('&a=' . $_SESSION['a']). '&t='. getPrevLocation($_SESSION["trail"]);?>">Edit</a>
		</div>
	</div>



	<!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>
</body>
</html>