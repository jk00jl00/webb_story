<!DOCTYPE html>
<html>
<head>
	<title></title>
	<style type="text/css">
		textarea{
			width: 250px;
			height: 250px;
			resize: none;
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

		echo '<form id="textForm"method="post">
				<label>Text:</label>
				<br><textarea name="textfield" form="textForm">'.$row["text"].'</textarea>
				<br><input type="submit" name="submit">
				<input type="submit" name="deleteText" value="delete">
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
			
			echo '<br><br><form id="linkForm"method="post">
					<input type="hidden" name="linkId" value="'.$linkId[0].'">
					<label>Link '.$linkId[0].':</label>
					<br><label>Link text:</label>:
					<br><input type="text" name="linkText" value="'. $texts["text"]. '">
					<br><label>Link target:</label>:
					<br><input type="text" name="linkTarget" value="'. $texts["target_id"]. '">
					<br><input type="submit" name="submitLinks">
					<input type="submit" name="deleteLinks" value="delete">
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
			header("Location: /soloAdv/");
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
			header("Location: /soloAdv/");
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
</body>
</html>