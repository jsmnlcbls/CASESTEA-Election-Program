<?php
require "settings.php";


$method = strtoupper($_SERVER['REQUEST_METHOD']);
$voteSubmitted = false;
if ($method == 'POST') {
	require "classes/Position.php";
	require "classes/Election.php";
	
	$position = new Position();
	$positionsList = $position->getAll();
	
	$inputData = filter_var_array($_POST, FILTER_VALIDATE_INT);
	if (FALSE === $inputData) {
		displayError("Validation failed.");
	}
	
	//check for multiple votes
	$errorMessage = array();
	foreach ($positionsList as $position) {
		$positionId = $position['id'];
		if (isset($inputData[$positionId])) {
			$composition = $position['composition'];
			if (count($inputData[$positionId]) > $composition) {
				$positionName = $position['name'];
				if ($composition == 1) {
					$errorMessage[] = "You can only vote 1 person for the position of $positionName.";
				} else {
					$errorMessage[] = "You can only vote up to $composition persons for the position of $positionName.";
				}
			}	
		}
	}
	
	//display errors and exit
	if (count($errorMessage) > 0) {
		displayError(implode("<br/>", $errorMessage));
		die();
	}
	
	$vote = array();
	foreach ($positionsList as $position) {
		$positionId = $position['id'];
		
		if (isset($inputData[$positionId])) {
			$columnName = "";
			$composition = $position['composition'];
			if ($composition == 1) {
				
				$columnName = "position_" . $positionId;
				$vote[$columnName] = current($inputData[$positionId]);
			} else {
				$a = 1;
				foreach ($inputData[$positionId] as $candidateId) {
					$columnName = "position_" . $positionId . "_" . $a;
					$vote[$columnName] = $candidateId;
					$a++;
				}
			}
		}
	}
	$election = new Election();
	$success = $election->castVote($vote, $inputData['precinct']);
	if (!$success) {
		displayError($election->getErrorMessage());
		die();
	}
	
?>
<html>
<head>
<script type="text/javascript" src = "jquery-1.5.1.min.js"></script>
<meta http-equiv="refresh" content="1"> 
<title>Success</title>
</head>
<body>
	<h1>OK</h1>
</body>
</html>
<?php
} else if ($method == "GET") {
	require "classes/Candidate.php";
	require "classes/Position.php";
	require "classes/Election.php";
	require_once  "settings.php";
	
	$maxPrecinct = Settings::$precincts;
	
	if (!isset($_GET['precinct'])) {
		displayError("You must specify a precinct number.");
	}
	
	$precinct = $_GET['precinct'];
	if ($precinct < 1 || $precinct > $maxPrecinct) {
		displayError("Invalid precinct.");
	}
	
	
	$candidate = new Candidate();
	$candidateList = $candidate->getAllCandidatesByPosition();
	
	$position = new Position();
	$positionsList = $position->getAll();
	
	$election = new Election();
	$precinctTotal = $election->getResultsByPrecint($precinct);
?>
<html>
<head>
<script src = "jquery-1.5.1.min.js"></script>
<style type="text/css">
	body {
		font-family: Arial;
	}

	#ballotTable {
		border-collapse: collapse;
		background-color: WHITE;
	}
	
	#ballotTable th, td{
		border-width: 1px;
		border-style: solid;
		padding: 0px;
		text-indent: 5px;
		border-color: GREY;
	}
	
	.cell:hover {
		background-color: #8BF0F6;
	}
	
	.cell {
		border-bottom-width: 1px;
		border-bottom-style: solid;
		margin-bottom: -1px;
		padding: 2px;
		padding-right: 4px;
		border-color: GREY;
		cursor:default;
	}
	
	.cell input {
		margin: 0 3px 0 0;
	}
	
	.numeric {
		text-align: right;
	}
	
	.candidateTotal {
		text-align: center;
	}
	
	#precinct {
		font-weight: bold;
		font-size: 16px;
		text-align: center;
	}
	
	#heading td {
		font-weight: bold;
		text-align: center;
	}
</style>
<title>Precinct <?php echo $precinct; ?></title>
</head>
<body>
	<form method = "post" action = <?php $_SERVER['SCRIPT_NAME']; ?>>
	<input type = "hidden" name = "precinct" value = "<?php echo $precinct ?>">
	<table id = "ballotTable" align="center">
		<tr>
			<td colspan = "3" id = "precinct" style = "background-color: #8BF0F6;">
			PRECINCT # <?php echo $precinct; ?> TALLY SHEET
			</td>
		</tr>
		<tr id = "heading">
			<td>Position</td>
			<td>Candidate</td>
			<td>Total</td>
		</tr>
		<?php 
			foreach($positionsList as $position) {
				$positionId = $position['id'];
				echo "<tr>";
				echo "<td>" . $position['name'] . "</td>";
				echo "<td>";
				foreach ($candidateList[$positionId] as $candidate) {
					$value = $candidate['id'];
					$name = $positionId . "[]";
					echo "<div class = \"cell\">";
					echo "<input type = \"checkbox\" name = \"$name\" value = \"$value\">";
					echo $candidate['name'];
					echo "</div>";
				}
				echo "</td>";
				echo "<td>";
				foreach ($candidateList[$positionId] as $candidate) {
					$candidateId = $candidate['id'];
					echo "<div class = \"cell numeric\">";
					if (empty($precinctTotal[$candidateId])) {
						echo "0";
					} else {
						echo $precinctTotal[$candidateId];
					}
					echo "</div>";
				}
				echo "</td>";
				echo "</tr>";
			}
		?>
		<tr>
			<td colspan = "3" align = "center"><input type = "submit" value = "ADD"></td>
		</tr>
	</table>
	</form>
	<script>
		<?php 
		$seats = array();
		
		foreach ($positionsList as $position) {
			$seats[$position['id']] = $position['composition'];
		}
		echo "var positionLimit = [0, " . implode(", ", $seats) . "];";
		?>
		jQuery(function(){
			$(":checkbox").click(function(evt) {
				if (evt.currentTarget.checked) {
					name = evt.currentTarget.name;
					position = name.replace("[]", "");
					checkedPositions = $(":checked[name|=\""+name+"\"]").length;
					
					if (checkedPositions > positionLimit[position]) {
						evt.preventDefault();
					}
				}
			});
		});
	</script>
</body>
</html>
<?php
}

function displayError($error) 
{
	echo "<h1>ERROR: " . $error . "</h1>";
	die();
}
?>