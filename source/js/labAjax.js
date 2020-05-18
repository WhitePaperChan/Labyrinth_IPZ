var game_id = undefined;
var player_no = undefined;

/*window.onbeforeunload = function() { // 
	return "Dude, are you sure you want to leave? Think of the kittens!";
}*/

function printTextAnswer(answer) {
	$('#textAnswer').empty(); // arr[0].answer
	$('#textAnswer').prepend('<h4>' + answer + '</h4>'); // arr[0].answer
}
	
function printInventory(inventory) {
	//console.log(arr[1].inventory);
	//var inventory = arr[1].inventory;
	$('#inventory h3').remove();
	$('#inventory').prepend('<h3>Inventory</h3>');
	$('#inventorytList').empty(); 
	$.each(inventory, function(index, value) {
		$('#inventorytList').append('<li>' 
			+ index + ': ' 
			+ value
			+ '</li>'
		);
	});
}
	
function printTurns(turns) { // arr[2].turns
	//var turns = arr[2].turns;
	$('#turns h3').remove();
	$('#turns').prepend('<h3>Turns</h3>');
	//console.log(turns);
	$('#turntList').empty(); 
	$.each(turns, function(index, element) {
		var turn = element.turn;
		//console.log(element.turn);
		$('#turntList').append('<li>' + turn.turn_no + '. ' + turn.player_name + '</li>');
		var subturns = element.subturns;
		//console.log(subturns);
		$('#turntList:last-child').append($('<ul>'));
		$.each(subturns, function(index, element) {
			//console.log(element);
			//console.log(element.reaction);
			$('#turntList').append('<li>' 
				+ turn.turn_no + '.' 
				+ element.subturn_no + '. ' 
				+ element.reaction 
				+ '</li>'
			);
			$('#turntList').append('<li>' 
				+ element.answer
				+ '</li>'
			);
			$('#turntList').append('<li>' 
				+ '</li>'
			);
		});
	});
}
	
function refreshReaction(button){
	/*e.preventDefault();
	console.log('!!!');
	//return;
	var button = $(e.target);*/
	var result = button.parents('form').serialize() 
		+ '&' 
		+ encodeURI(button.attr('name'))
		+ '='
		+ encodeURI(button.attr('value'))
		+ '&game_id='
		+ game_id
		+ '&player_no='
		+ player_no
		;
	console.log(result);
	console.log("player_no="+player_no);
	$.ajax({
		type: 'post',
		url: 'LabMySQLapi.php',
		data: result,
		success: function (data) {
			//console.log(data);
			var arr = $.parseJSON(data);
			console.log(arr);
			printTextAnswer(arr[0].answer);
			var formType = arr[0].form;
			printInventory(arr[1].inventory);
			printTurns(arr[2].turns);
			
			button.parents('form').hide();
			if (formType == "start"){
				$('#StartForm').show();
			} else if (formType == "refresh"){
				$('#RefreshForm').show();
			} else if (formType == "turn"){
				$('#TurnForm').show();
			}
		}
	});
}
		
$.getJSON('LabMySQLapi.php', function(data) { 
	$.each(data, function(index, element) {
		$('#testTable tbody').append($('<tr>').append(
			$('<td>').text(element.id),
			$('<td>').text(element.initiator),
			$('<td>').text(element.started),
			$('<td>').html('<input class="JoinButton" type="submit" name="Join" value="Join game ' + element.id + '">')
		));
	});
});

$(document).ready(function () {
	$('form').hide();
	$('#gamesToJoin').show();
	
	$('#NewButton').click(function (e) {
		e.preventDefault();
		var button = $(e.target);
		button.parents('form').hide();
		$('#GenerateButton').parents('form').show();
	});

	$('#gamesToJoin').on('click', "input.JoinButton", function(e){ // on тк дрруие способы для сгенерированных кнопок не работают
		e.preventDefault();
		game_id = this.value.substring(10)
		$('#gamesToJoin').hide();
		$('#NewPlayerButton').parents('form').show();
	});

	$('#GenerateButton').click(function (e) {
		e.preventDefault();
		var button = $(e.target);
		player_no = 1; // Создатель лабиринта всегда 1
		var result = button.parents('form').serialize() 
			+ '&' 
			+ encodeURI(button.attr('name'))
			+ '='
			+ encodeURI(button.attr('value'))
			;
		console.log(result);
		$.ajax({
			type: 'post',
			url: 'LabMySQLapi.php',
			data: result,
			success: function (data) {
				var arr = $.parseJSON(data);
				console.log(arr);
				printTextAnswer(arr[0].answer);
				game_id = arr[0].game_id;
				printInventory(arr[1].inventory);
				printTurns(arr[2].turns);
			}
		});
		button.parents('form').hide();
		$('#StartButton').parents('form').show();
	});

	$('#StartButton').click(function (e) {
		e.preventDefault();
		var button = $(e.target);
		var result = button.parents('form').serialize() 
			+ '&' 
			+ encodeURI(button.attr('name'))
			+ '='
			+ encodeURI(button.attr('value'))
			+ '&game_id='
			+ game_id
			+ '&player_no='
			+ player_no
			;
		console.log(result);
		$.ajax({
			type: 'post',
			url: 'LabMySQLapi.php',
			data: result,
			success: function (data) {
				//console.log(data);
				var arr = $.parseJSON(data);
				console.log(arr);
				printTextAnswer(arr[0].answer);
				printInventory(arr[1].inventory);
				printTurns(arr[2].turns);
			}
		});
		button.parents('form').hide();
		$('#TurnForm').show();
	});

	$('#StartForm').on('click', '#RefreshButton', function(e){
		e.preventDefault();
		refreshReaction($(e.target));
	});
	$('#RefreshForm').on('click', '#RefreshButton', function(e){
		e.preventDefault();
		refreshReaction($(e.target));
	});
	$('#TurnForm').on('click', '#RefreshButton', function(e){
		e.preventDefault();
		refreshReaction($(e.target));
	});

	$('#NewPlayerButton').click(function (e) {
		e.preventDefault();
		var button = $(e.target);
		var result = button.parents('form').serialize() 
			+ '&' 
			+ encodeURI(button.attr('name'))
			+ '='
			+ encodeURI(button.attr('value'))
			+ '&' 
			+ 'game_id'
			+ '='
			+ game_id
			;
		console.log(result);
		$.ajax({
			type: 'post',
			url: 'LabMySQLapi.php',
			data: result,
			success: function (data) {
				console.log(data);
				var arr = $.parseJSON(data);
				//console.log(arr);
				printTextAnswer(arr[0].answer);
				player_no = arr[0].player_no;
				printInventory(arr[1].inventory);
				printTurns(arr[2].turns);
			}
		});
		button.parents('form').hide();
		$('#RefreshForm').show();
					//$('#StartForm').show();
	});
	
	$('#TurnForm').on('click', ".turnButton", function(e){ 
		e.preventDefault();
		var button = $(e.target);
		var result = button.parents('form').serialize() 
			+ '&' 
			+ encodeURI(button.attr('name'))
			+ '='
			+ encodeURI(button.attr('value'))
			+ '&game_id='
			+ game_id
			+ '&player_no='
			+ player_no
			;
		console.log(result);
		console.log("player_no="+player_no);
		$.ajax({
			type: 'post',
			url: 'LabMySQLapi.php',
			data: result,
			success: function (data) {
				//console.log(data);
				var arr = $.parseJSON(data);
				console.log(arr);
				printTextAnswer(arr[0].answer);
				var formType = arr[0].form;
				printInventory(arr[1].inventory);
				printTurns(arr[2].turns);
				$('#move').prop('checked',true);
			}
		});
	});

}); // $(document).ready(function () {
