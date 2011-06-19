<?php
	require "../classes/Candidate.php";
	require "../classes/Position.php";
	require "../classes/Election.php";
	require "../settings.php";
	
	$candidate = new Candidate();
	$candidateList = $candidate->getAllCandidatesByPosition();
	
	$position = new Position();
	$positionsList = $position->getAll();
	
	$election = new Election();
	$electionResults = $election->getTotalResults();
	
?>
<html>
<head>
<style type="text/css">
	body {
		font-family: Arial;
	}
	
	#resultTable {
		border-collapse: collapse;
		background-color: WHITE;
		border-style: solid;
		border-color: GREY;
		border-width: 3px;
	}
 	
	#resultTable th, td{
		border-width: 1px;
		border-style: solid;
		padding: 0px;
	}

	
	#resultTable th {
		background-color: #8BF0F6;
		padding: 3px;
	}

	#resultTable td {
		text-indent: 5px;
	}
	
	.cell {
		border-bottom-width: 1px;
		border-bottom-style: solid;
		margin-bottom: -1px;
		padding: 1px;
		border-color: GREY;
	}
	
	.numeric {
		text-align: right;
	}
	
	.candidateTotal {
		text-align: center;
	}
</style>
<meta http-equiv="refresh" content="3">
<title>Results</title>
</head>
<body>
	<table id = "resultTable" align = "center">
		<tr>
			<th style="max-width: 230px; width: 230px">Position</th>
			<th>Candidate</th>
			<?php 
				for ($a = 1; $a <= Settings::$precincts; $a++) {
					echo "<th>Precint $a</th>";
				}
			?>
			<th>Total Votes</th>
		</tr>
		
		<?php 
			$candidate = $candidate->getAllCandidatesByPosition();
			foreach($positionsList as $position) {
				$positionName = $position['name'];
				$positionId = $position['id'];
				echo "<tr>";
				echo "<td>$positionName</td>";
				echo "<td>";
				foreach ($candidateList[$positionId] as $candidate) {
					echo "<div class = \"cell\">{$candidate['name']}</div>";
				}
				echo "</td>";
				
				foreach ($electionResults as $total) {
					echo "<td>";
					foreach ($candidateList[$positionId] as $candidate) {
						echo '<div class = "cell numeric">';
						echo $total[$candidate['id']];
						echo "</div>";
					}
					echo "</td>";
				}
				echo "</tr>";
			}
		?>
	</table>	
</body>
</html>