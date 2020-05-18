<?php 
?>
	<form method=post action="LabTraining.php">
		<input type="submit" name="Training" value="Play training game">
	</form>
	<form method=post action="<?=$script?>"><table class="controls">
	<tr><td colspan=4><input type="submit" name="New" value="New game"></td></tr>
	<?
	$db = new LabMySQL;
	$result = $db->games_to_join();
	?><tr><th><strong>id</strong></th><th><strong>initiator</strong></th><th><strong>started</strong></th><th><strong>Join</strong></th></tr><?
	while($row=$result->fetch_assoc()){
		?>
			<tr>
			<td><?=$row["id"]?></td>
			<td><?=$row["initiator"]?></td>
			<td><?=date('d.m.Y H:i', strtotime($row["started"]))?></td>
			<td><input type="submit" name="Join" value="Join game <?=$row['id']?>"></td>
			</tr>
		<?
	}
	?>
	</table></form>
