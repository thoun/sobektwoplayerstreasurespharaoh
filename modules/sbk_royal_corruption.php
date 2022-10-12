<?php

class RoyalCorruption
{	
	public static function setup() {
		$sql = "INSERT INTO `royal_corruption` (`value`) VALUES 
			(1), 
			(2), 
			(3), 
			(4), 
			(5), 
			(6);";
		
		SobekTwoPlayersTreasuresPharaoh::DbQuery( $sql );
	}
	
	public static function getBag() {
		$deck = SobekTwoPlayersTreasuresPharaoh::getObjectList( "SELECT * FROM `royal_corruption` WHERE location = 'bag'" );
		shuffle($deck);
		return $deck;
	}
	
	public static function getOwned(int $player_id) {
		$deck = SobekTwoPlayersTreasuresPharaoh::getObjectList( "SELECT * FROM `royal_corruption` WHERE location = 'player' AND player_id = $player_id" );
		return $deck;
	}
	
	public static function draw(int $player_id, $royalCorruption = NULL) {
		if (! isset($royalCorruption)) {
			$bag = self::getBag();
			if (count($bag) == 0) return null;
			$royalCorruption = array_pop($bag);
		}
		
		SobekTwoPlayersTreasuresPharaoh::DbQuery( "UPDATE `royal_corruption` SET location = 'player', player_id = $player_id WHERE `royal_corruption_id` = $royalCorruption[royal_corruption_id] LIMIT 1" );
		$royalCorruption["location"] = 'player';
		$royalCorruption["player_id"] = $player_id;
		return $royalCorruption;
	}
}