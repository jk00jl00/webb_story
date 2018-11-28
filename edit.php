<!DOCTYPE html>
<html>
<head>
	<title>Edit</title>
	<meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
	<style type="text/css">
		textarea{
			width: 250px;
			height: 250px;
			resize: none;e;
		}
	</style>
</head>
<body>	
	<?php
	if(isset($_GET["l"])){

		$str = "use";
		switch ($_GET["a"]) {
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

		$stmt = $pdo->prepare('SELECT * FROM `'. $trimStr . '` WHERE targetId=:id');
		if($str == "inspect"){
			$prevLoc = ($_GET["t"]);
			$stmt = $pdo->prepare('SELECT `text` FROM `'. $str . '` WHERE targetId=:id AND from_id=:fid');
			$stmt->bindParam(":fid", $prevLoc);	
		}
		$stmt->bindParam(":id", $_GET["l"]);
		$stmt->execute();

		$row = $stmt->fetch(PDO::FETCH_ASSOC);

		echo '
			<form id="textForm" method="post">
				<div class="form-group">
					<label for="textFieldInput">Text:</label>
					<textarea class="form-control" id="textFieldInput" name="textfield" form="textForm" rows="10" style="max-width:250px;">'.$row["text"].'</textarea>
					<input type="submit" name="submit">
					<input type="submit" name="deleteText" value="delete">
				</div>
			</form>';
		
		preg_match_all("/link\([0-9]+\)/",$row["text"], $linkIds);
					

		for($i = 0; $i < sizeof($linkIds[0]); $i++){
			preg_match("/[0-9]+/", $linkIds[0][$i], $linkId);

			if($_GET["a"] == "i")
				$str = $_GET["t"];
			else
				$str = $_GET["l"];

			$stmt = $pdo->prepare('SELECT * FROM `links` WHERE story_id=:id AND order_nr=:sid');
			$stmt->bindParam(":id", $str);
			$stmt->bindParam(":sid", $linkId[0]);
			$stmt->execute(); 

			$texts = $stmt->fetch(PDO::FETCH_ASSOC);
			
			echo '
				<form id="linkForm" method="post">
					<div class="form-group">

						<input type="hidden" name="linkId" value="'.$linkId[0].'">
						<label>Link '.$linkId[0].':</label>
						<label>Link text:</label>:
						<input type="text" name="linkText" value="'. $texts["text"]. '">
					</div>
					<div class="form-group">
						<label>Link target:</label>:
						<input type="text" name="linkTarget" value="'. $texts["target_id"]. '">
					</div>
					<div class="form-group">
						<input type="submit" name="submitLinks">
						<input type="submit" name="deleteLinks" value="delete">
					</div>
				</form>';
		}

		if(isset($_POST["deleteText"])){
			$str = "use";
			switch ($_GET["a"]) {
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
	
			preg_match_all("/link\([0-9]+\)/",$row["text"], $linkIds);					

			$stmt = $pdo->prepare('DELETE FROM `'.$str.'` WHERE targetId=:id');
			$stmt->bindParam(":id", $_GET["l"]);
			$stmt->execute();

			for($i = 0; $i < sizeof($linkIds[0]); $i++){
				preg_match("/[0-9]+/", $linkIds[0][$i], $linkId);

				if($_GET["a"] == "i")
					$str = $_GET["t"];
				else
					$str = $_GET["l"];

				$stmt = $pdo->prepare('DELETE FROM `links` WHERE story_id=:id AND order_nr=:sid');
				$stmt->bindParam(":id", $str);
				$stmt->bindParam(":sid", $linkId[0]);
				$stmt->execute(); 
			}
			header("Location: /webb_story/");
		}
		if(isset($_POST["deleteLinks"])){
			if($_GET["a"] == "i")
				$str = $_GET["t"];
			else
				$str = $_GET["l"];
			$stmt = $pdo->prepare('DELETE FROM `links` WHERE story_id=:id AND order_nr=:sid');
			$stmt->bindParam(":id", $str);
			$stmt->bindParam(":sid", $_POST["linkId"]);
			$stmt->execute(); 
		}
		

		if(isset($_POST["submit"])){
			if(!$row){           
				$str = $_GET["l"];
				$stmt = $pdo->prepare('INSERT INTO `'.$trimStr.'` (`targetId`, `text`) VALUES (:id, :te)');
				if($trimStr == "inspect"){
					$stmt = $pdo->prepare('INSERT INTO `'.$trimStr.'` (`targetId`, `text`, `from_id`) VALUES (:id, :te, :fid)');
					$stmt->bindParam(":fid", $_GET["t"]);	
				}
				$stmt->bindParam(":id", $str);
				$stmt->bindParam(":te", $_POST["textfield"]);
				$stmt->execute(); 
			} else{
				$str = $_GET["l"];
				$stmt = $pdo->prepare('UPDATE `'.$trimStr.'` SET text= :te WHERE targetId = :id');
				$stmt->bindParam(":id", $str);
				$stmt->bindParam(":te", $_POST["textfield"]);
				$stmt->execute(); 
			}
			header("Location: /webb_story/");
		} else if(isset($_POST["submitLinks"])){
			if($_GET["a"] == "i")
				$str = $_GET["t"];
			else
				$str = $_GET["l"];

			$stmt = $pdo->prepare('SELECT * FROM `links` WHERE story_id=:id AND order_nr=:sid');
			$stmt->bindParam(":id", $str);
			$stmt->bindParam(":sid", $_POST["linkId"]);
			$stmt->execute(); 

			$texts = $stmt->fetch(PDO::FETCH_ASSOC);

			if(!$texts){           
				$stmt = $pdo->prepare('INSERT INTO `links` (story_id, order_nr, target_id, `text`) VALUES (:sid, :onr, :tid, :te)');
				$stmt->bindParam(":sid", $str);
				$stmt->bindParam(":onr", $_POST["linkId"]);
				$stmt->bindParam(":tid", $_POST["linkTarget"]);
				$stmt->bindParam(":te", $_POST["linkText"]);
				$stmt->execute(); 
			} else{
				$stmt = $pdo->prepare('UPDATE `links` SET `text`= :te, target_id=:tid WHERE story_id=:id AND order_nr=:sid');
				$stmt->bindParam(":id", $str);
				$stmt->bindParam(":sid", $_POST["linkId"]);
				$stmt->bindParam(":tid", $_POST["linkTarget"]);
				$stmt->bindParam(":te", $_POST["linkText"]);
				$stmt->execute(); 
			}
		}
} 

 ?>
 <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
   <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
   <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
   <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>
</body>
</html>