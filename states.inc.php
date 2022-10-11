<?php

$machinestates = array(

	// The initial state. Please do not modify.
	1 => array(
		"name" => "gameSetup",
		"description" => "",
		"type" => "manager",
		"action" => "stGameSetup",
		"transitions" => array( "" => 2 )
	),
	
	// Note: ID=2 => your first state

	2 => array(
		"name" => "playerTurn",
		// TODO : Change description to only include the things you can do
		// TODO : Arg to say which tiles are pickable
		"description" => clienttranslate('${actplayer} must take a tile from the Market, sell a set of tiles or play a character'),
		"descriptionmyturn" => '',
		"args" => "argPlayerTurn",
		"action" => "stPlayerTurn",
		"type" => "activeplayer",
		"possibleactions" => array( "selectMarketTile", "sell", "playCharacter", "refill" ),
		"transitions" => array( "next" => 10, "pickResource" => 5, "deben" => 3, "orientation" => 4, "refill" => 25,
			"characterScribe" => 109,
			"characterArchitect" => 108,
			"characterCourtesan" => 107,
			"characterHighPriest" => 105,
			"characterMerchant" => 104,
			"characterThief" => 103,
			"characterVizier" => 102,
			"gameEnd" => 98,
		)
	),
	
	25 => array(
		"name" => "playerTurn2",
		"description" => clienttranslate('${actplayer} must take a tile from the market'),
		"descriptionmyturn" => clienttranslate('${you} must take a tile from the market'),
		"args" => "argPlayerTurn",
		"type" => "activeplayer",
		"possibleactions" => array( "selectMarketTile" ),
		"transitions" => array( "next" => 10, "deben" => 3, "orientation" => 4 )
	),
	
	3 => array(
		"name" => "deben",
		"description" => clienttranslate('${actplayer} must choose whether to take a Deben token instead of the tile'),
		"descriptionmyturn" => clienttranslate('${you} must choose whether to take a Deben token instead of the tile'),
		"type" => "activeplayer",
		"possibleactions" => array( "answer" ),
		"transitions" => array( "next" => 10 )
	),
	
	4 => array(
		"name" => "orientation",
		"description" => clienttranslate('${actplayer} must choose the orientation of the Ankh pawn'),
		"descriptionmyturn" => clienttranslate('${you} must choose the orientation of the Ankh pawn'),
		"type" => "activeplayer",
		"args" => "argOrientation",
		"possibleactions" => array( "answer" ),
		"transitions" => array( "next" => 10 )
	),
	
	5 => array(
		"name" => "pickResource",
		"description" => clienttranslate('${actplayer} must pick a resource for the set'),
		"descriptionmyturn" => clienttranslate('${you} must pick a resource for the set'),
		"type" => "activeplayer",
		"action" => "stPickResource",
		"possibleactions" => array( "answer" ),
		"transitions" => array( "next" => 6 )
	),
	
	51 => array(
		"name" => "pickResource",
		"description" => clienttranslate('${actplayer} must pick a resource for the set'),
		"descriptionmyturn" => clienttranslate('${you} must pick a resource for the set'),
		"type" => "activeplayer",
		"action" => "stPickResource",
		"possibleactions" => array( "answer" ),
		"transitions" => array( "next" => 10 )
	),
	
	6 => array(
		"name" => "pirogue",
		"description" => clienttranslate('${actplayer} must pick a Pirogue token'),
		"descriptionmyturn" => clienttranslate('${you} must pick a Pirogue token'),
		"type" => "activeplayer",
		"action" => "stPirogue",
		"args" => "argPirogue",
		"possibleactions" => array( "pickPirogue" ),
		"transitions" => array( "next" => 10, "extraTurn" => 2, "pirogue04" => 7, "pirogue07" => 8 )
	),
	
	7 => array(
		"name" => "pirogue04",
		"description" => clienttranslate('${actplayer} must choose a Tile that the opponent must take'),
		"descriptionmyturn" => clienttranslate('${you} must choose a Tile that your opponent must take'),
		"type" => "activeplayer",
		"action" => "stPirogue04",
		"args" => "argPirogue04",
		"possibleactions" => array( "selectMarketTile" ),
		"transitions" => array( "next" => 10 )
	),
	
	8 => array(
		"name" => "pirogue07",
		"description" => clienttranslate('${actplayer} must choose a resource to add the Pirogue token to'),
		"descriptionmyturn" => clienttranslate('${you} must choose a resource to add the Pirogue token to'),
		"type" => "activeplayer",
		"action" => "stPirogue07",
		"args" => "argPirogue07",
		"possibleactions" => array( "answer" ),
		"transitions" => array( "next" => 10 )
	),
	
	10 => array(
		"name" => "nextPlayer",
		"type" => "game",
		"action" => "stNextPlayer",
		"transitions" => array( "" => 2 ),
		"updateGameProgression" => true,
	),
	
	102 => array(
		"name" => "characterVizier",
		"description" => clienttranslate('${actplayer} must take a tile from the opponent\'s Corruption board'),
		"descriptionmyturn" => clienttranslate('${you} must take a tile from your opponent\'s Corruption board'),
		"type" => "activeplayer",
		"args" => "argCharacterVizier",
		"action" => "stCharacterVizier",
		"possibleactions" => array( "answer" ),
		"transitions" => array( "next" => 10 )
	),
	103 => array(
		"name" => "characterThief",
		"description" => clienttranslate('${actplayer} must take a tile from the opponent\'s hand'),
		"descriptionmyturn" => clienttranslate('${you} must take a tile from your opponent\'s hand'),
		"type" => "activeplayer",
		"args" => "argCharacterThief",
		"action" => "stCharacterThief",
		"possibleactions" => array( "answer" ),
		"transitions" => array( "next" => 10 )
	),
	104 => array(
		"name" => "characterMerchant",
		"description" => clienttranslate('${actplayer} must take a tile from the market'),
		"descriptionmyturn" => clienttranslate('${you} must take a tile from the market'),
		"type" => "activeplayer",
		"action" => "stCharacterMerchant",
		"possibleactions" => array( "selectMarketTile" ),
		"transitions" => array( "next" => 10 )
	),
	105 => array(
		"name" => "characterHighPriest",
		"description" => clienttranslate('${actplayer} must choose a type to remove from their Corruption board'),
		"descriptionmyturn" => clienttranslate('${you} must choose a type to remove from your Corruption board'),
		"type" => "activeplayer",
		"possibleactions" => array( "answer" ),
		"transitions" => array( "next" => 10 )
	),
	107 => array(
		"name" => "characterCourtesan",
		"description" => clienttranslate('${actplayer} must add 1 or 2 tiles from their hand to a previously sold set'),
		"descriptionmyturn" => clienttranslate('${you} must add 1 or 2 tiles from your hand to a previously sold set'),
		"type" => "activeplayer",
		"action" => "stCharacterCourtesan",
		"possibleactions" => array( "sell" ),
		"transitions" => array( "next" => 10, "pickResource" => 51 )
	),
	108 => array(
		"name" => "characterArchitect",
		"description" => clienttranslate('${actplayer} must choose a Pirogue token'),
		"descriptionmyturn" => clienttranslate('${you} must choose a Pirogue token'),
		"type" => "activeplayer",
		"args" => "argCharacterArchitect",
		"possibleactions" => array( "pickPirogue" ),
		"transitions" => array( "next" => 10, "extraTurn" => 2, "pirogue04" => 7, "pirogue07" => 8 )
	),
	109 => array(
		"name" => "preCharacterScribe",
		"type" => "game",
		"action" => "stNextPlayer",
		"transitions" => array( "" => 1092 )
	),
	1092 => array(
		"name" => "characterScribe",
		"description" => clienttranslate('${actplayer} must discard down to 6 tiles'),
		"descriptionmyturn" => clienttranslate('${you} must discard down to 6 tiles'),
		"type" => "activeplayer",
		"action" => "stCharacterScribe",
		"possibleactions" => array( "sell" ),
		"transitions" => array( "next" => 2 )
	),
	
	98 => array(
		"name" => "finalScoring",
		"type" => "game",
		"action" => "stFinalScoring",
		"transitions" => array( "" => 99 )
	),
	
	// Final state.
	// Please do not modify (and do not overload action/args methods).
	99 => array(
		"name" => "gameEnd",
		"description" => clienttranslate("End of game"),
		"type" => "manager",
		"action" => "stGameEnd",
		"args" => "argGameEnd"
	)
);
