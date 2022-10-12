<?php

class Tile
{
	public static function setup($isExpansion) {
		$sql = "INSERT INTO tile (resource, displayed_resource, deck, `statue`, direction, scarabs, deben, ability, location) VALUES ";
		
		// Starting
		$starting = [
			[ 'livestock', 'v', 0, 0 ],
			[ 'livestock', 'h', 0, 0 ],
			[ 'wheat', 'f', 0, 0 ],
			[ 'wheat', 'v', 0, 0 ],
			[ 'wheat', 'h', 0, 0 ],
			[ 'ebony', 'h', 0, 0 ],
			[ 'ivory', 'h', 0, 0 ],
			[ 'marble', 'h', 0, 0 ],
			[ 'fish', 'v', 0, 0 ],
			[ 'fish', 'h', 0, 0 ],
		];
		foreach ($starting as $g) {
			$sql .= "('$g[0]', '$g[0]', 'starting', 0, '$g[1]', $g[2], $g[3], NULL, 'deck'), ";
		}
		
		// Goods
		$goods = [
			// Livestock
			[ 'livestock', 'b', 0, 1 ],
			[ 'livestock', 'b', 1, 0 ],
			[ 'livestock', 'f', 0, 0 ],
			[ 'livestock', 'f', 1, 0 ],
			[ 'livestock', 'f', 1, 0 ],
			[ 'livestock', 'v', 1, 0 ],
			[ 'livestock', 'h', 1, 0 ],
			
			// Fish
			[ 'fish', 'b', 0, 0 ],
			[ 'fish', 'b', 1, 0 ],
			[ 'fish', 'b', 0, 0 ],
			[ 'fish', 'f', 0, 0 ],
			[ 'fish', 'f', 0, 0 ],
			[ 'fish', 'v', 1, 0 ],
			[ 'fish', 'v', 0, 1 ],
			[ 'fish', 'h', 1, 0 ],
			
			// Wheat
			[ 'wheat', 'f', 0, 1 ],
			[ 'wheat', 'b', 0, 0 ],
			[ 'wheat', 'h', 1, 0 ],
			[ 'wheat', 'h', 1, 0 ],
			[ 'wheat', 'f', 1, 0 ],
			[ 'wheat', 'b', 1, 0 ],
			[ 'wheat', 'v', 1, 0 ],
			[ 'wheat', 'f', 0, 0 ],
			
			// Marble
			[ 'marble', 'h', 2, 0 ],
			[ 'marble', 'f', 0, 1 ],
			[ 'marble', 'v', 0, 0 ],
			[ 'marble', 'b', 0, 0 ],
			[ 'marble', 'v', 2, 0 ],
			[ 'marble', 'b', 2, 0 ],
			
			// Ebony
			[ 'ebony', 'h', 2, 0 ],
			[ 'ebony', 'v', 2, 0 ],
			[ 'ebony', 'v', 0, 1 ],
			[ 'ebony', 'f', 0, 0 ],
			[ 'ebony', 'f', 2, 0 ],
			[ 'ebony', 'b', 2, 0 ],
			
			// Ivory
			[ 'ivory', 'b', 0, 1 ],
			[ 'ivory', 'f', 0, 0 ],
			[ 'ivory', 'f', 3, 0 ],
			[ 'ivory', 'b', 3, 0 ],
			[ 'ivory', 'v', 3, 0 ],
		];
		foreach ($goods as $g) {
			$sql .= "('$g[0]', '$g[0]', 'good', 0, '$g[1]', $g[2], $g[3], NULL, 'deck'), ";
		}
		
		// Statues
		$statues = [
			[ 'v' ],
			[ 'h' ],
			[ 'f' ],
			[ 'b' ],
			[ 'b' ],
		];
		foreach ($statues as $g) {
			$sql .= "(NULL, NULL, 'good', 1, '$g[0]', 0, 0, NULL, 'deck'), ";
		}

		// Pharaoh
		if ($isExpansion) {
			$pharaohs = [
				'fish-or-ebony', 'ebony-or-livestock', 'livestock-or-ivory', 'ivory-or-wheat', 'wheat-or-marble', 'marble-or-fish',
			];
			foreach ($pharaohs as $g) {
				$sql .= "('$g', '$g', 'pharaoh', 0, NULL, 2, 0, NULL, 'deck'), ";
			}
		}

		// Characters
		$characters = [
			[ 'ivory', '1' ],
			[ 'livestock', '2' ],
			[ 'fish', '3' ],
			[ 'wheat', '4' ],
			[ 'ebony', '5' ],
			[ 'marble', '6' ],
			[ 'wheat', '7' ],
			[ 'livestock', '9' ],
			[ 'fish', '10' ],
		];
		if ($isExpansion) {
			$characters[] = [ 'ivory', '11' ];
		}
		shuffle($characters);
		
		foreach ($characters as $g) {
			$sql .= "('$g[0]', '$g[0]', 'character', 0, NULL, 0, 0, $g[1], 'deck'), ";
		}
		
		// TODO : Should shuffle this one in too...
		// Statue character
		if ($isExpansion) {
			$sql .= "(NULL, NULL, 'character', 1, NULL, 0, 0, 12, 'deck'), ";
		}
		$sql .= "(NULL, NULL, 'character', 1, NULL, 0, 0, 8, 'deck')";
		
		SobekTwoPlayersTreasuresPharaoh::DbQuery( $sql );
	}
	
	public static function getDeck(bool $starting = false) {
		$deskSign = $starting ? '=' : '<>';
		$deck = SobekTwoPlayersTreasuresPharaoh::getObjectList( "SELECT * FROM tile WHERE deck $deskSign 'starting' AND location = 'deck'" );
		shuffle($deck);
		return $deck;
	}
	
	public static function getBoard() {
		$deck = SobekTwoPlayersTreasuresPharaoh::getObjectList( "SELECT * FROM tile WHERE location = 'board'" );
		return $deck;
	}
	
	public static function getHand($player_id) {
		$deck = SobekTwoPlayersTreasuresPharaoh::getObjectList( "SELECT * FROM tile WHERE location = 'hand' AND player_id = $player_id" );
		return $deck;
	}
	
	public static function getSold($player_id) {
		$deck = SobekTwoPlayersTreasuresPharaoh::getObjectList( "SELECT * FROM tile WHERE location = 'sold' AND player_id = $player_id" );
		return $deck;
	}
	
	public static function getCorruption($player_id) {
		$deck = SobekTwoPlayersTreasuresPharaoh::getObjectList( "SELECT * FROM tile WHERE location = 'corruption' AND player_id = $player_id" );
		return $deck;
	}
	public static function drawCorruption($player_id) {
		SobekTwoPlayersTreasuresPharaoh::DbQuery( "UPDATE tile SET location = 'hand' WHERE location = 'corruption' AND player_id = $player_id" );
	}
	
	public static function getAtCoords($col, $row) {
		$tile = SobekTwoPlayersTreasuresPharaoh::getObject( "SELECT * FROM tile WHERE location = 'board' AND col = $col AND row = $row" );
		return $tile;
	}
	
	public static function get($tile_id) {
		$tile = SobekTwoPlayersTreasuresPharaoh::getObject( "SELECT * FROM tile WHERE tile_id = $tile_id" );
		return $tile;
	}
	
	public static function getPlayedCharacters() {
		$deck = SobekTwoPlayersTreasuresPharaoh::getObjectList( "SELECT * FROM tile WHERE location = 'played' AND ability <> 12" );
		return $deck;
	}
	
	public static function giveToPlayer(&$tile, $player_id, $corruption = false) {
		$location = $corruption ? 'corruption' : 'hand';
		$tile['location'] = $location;
		$tile['player_id'] = $player_id;
		SobekTwoPlayersTreasuresPharaoh::DbQuery( "UPDATE tile SET location = '$location', player_id = $player_id WHERE tile_id = $tile[tile_id]" );
	}
	
	public static function discard(&$tile) {
		$tile['location'] = 'discard';
		SobekTwoPlayersTreasuresPharaoh::DbQuery( "UPDATE tile SET location = 'discard' WHERE tile_id = $tile[tile_id]" );
	}
	
	public static function discardPlayedCharacter(&$tile) {
		$tile['location'] = 'discard';
		SobekTwoPlayersTreasuresPharaoh::DbQuery( "UPDATE tile SET location = 'played' WHERE tile_id = $tile[tile_id]" );
	}
}