<?php 
	
	// defaults
$Xmax = 5;
$Ymax = 5;
$minRiverLengthIndex = 5; // min river length 2do
$maxRiverLengthIndex = 3; // max river length 2do
$allThingsTogether = false;
$showMap = false;
?>
<form method=post action="<?=$script?>">
<input type="hidden" name="table" value="">
<table>
<tr>
	<td><b>Your nickname:</b></td>
	<td><input type="text" name="nickname" size=10 value="<?=$nickname?>"></td>
</tr>
<tr>
	<td><b>Xmax:</b></td>
	<td><input type="text" name="Xmax" size=10 value="<?=$Xmax?>"></td>
</tr>
<tr>
	<td><b>Ymax:</b></td>
	<td><input type="text" name="Ymax" size=10 value="<?=$Ymax?>"></td>
</tr>
<tr>
	<td><b>minRiverLengthIndex:</b></td>
	<td><input type="text" name="minRiverLengthIndex" size=10 value="<?=$minRiverLengthIndex?>"></td>
</tr>
<tr>
	<td><b>maxRiverLengthIndex:</b></td>
	<td><input type="text" name="maxRiverLengthIndex" size=10 value="<?=$maxRiverLengthIndex?>"></td>
</tr>
<tr>
	<td><b>allThingsTogether:</b></td>
	<td><input type="checkbox" name="allThingsTogether" <?=($allThingsTogether ? 'checked' : '')?>></td>
</tr>
<tr>
	<td><b>showMap:</b></td>
	<td><input type="checkbox" name="showMap" <?=($showMap ? 'checked' : '')?>></td>
</tr>
<tr><td colspan=2>
<input type="submit" name="Generate" value="Generate">
</td></tr>
</table>
</form>
