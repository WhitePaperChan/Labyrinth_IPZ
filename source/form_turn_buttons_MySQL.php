<?php 
	/**form for multiplayer*/
?>
<form method=post action="<?=$script?>">
<input type="hidden" name="game_id" value="<?=$game_id?>">
<input type="hidden" name="player_no" value="<?=$player_no?>">
<table>
<tr>
	<td>
<input type="radio" id="bum" name="action" value="bum" title="Bum"><label for="bum">B</label>
	</td><td>
<input id="North-submit" type="submit" name="North" value="&uarr;">
	</td><td>
<input type="radio" id="shoot" name="action" value="shoot" title="Shoot"><label for="shoot">S</label>
	</td>
</tr>
<tr>
	<td>
<input id="West-submit" type="submit" name="West" value="&larr;">
	</td><td>
<input id="center-submit" type="submit" name="Center" value="&#9931;">
	</td><td>
<input id="East-submit" type="submit" name="East" value="&rarr;">
	</td>
</tr>
<tr>
	<td>
<input type="radio" id="cement" name="action" value="cement" title="cement"><label for="Cement">C</label>
	</td><td>
<input id="South-submit" type="submit" name="South" value="&darr;">
	</td><td>
<input type="radio" id="move" name="action" value="move" title="Move" checked="checked"><label for="move">M</label>
	</td>
</tr>
<tr>
	<td colspan=3>
<input id="Refresh-submit" type="submit" name="Refresh" value="Refresh">
	</td>
</tr>
</table>
</form>
