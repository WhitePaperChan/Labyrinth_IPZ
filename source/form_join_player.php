<?php
/**
*form for joining
*/ 
?>
<form method=post action="<?=$script?>">
<input type="hidden" name="game_id" value="<?=$game_id?>">
<table>
<tr>
	<td><b>Your nickname:</b></td>
	<td><input type="text" name="nickname" size=10 value="<?=$nickname?>"></td>
</tr>
<tr><td colspan=2>
<input type="submit" name="NewPlayer" value="Join the game">
</td></tr>
</table>
</form>
