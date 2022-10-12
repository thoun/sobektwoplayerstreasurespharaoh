<?php
require_once( APP_GAMEMODULE_PATH.'module/table/table.game.php' );

require_once('modules/sbk_tile.php');
require_once('modules/sbk_deben.php');
require_once('modules/sbk_pirogue.php');
require_once('modules/sbk_royal_corruption.php');

class SobekTwoPlayersTreasuresPharaoh extends Table
{
	function __construct( )
	{
		parent::__construct();
		
		self::initGameStateLabels( array(
			//'next_place_id' => 10,
			'ankh_col' => 10,
			'ankh_row' => 11,
			'ankh_dir' => 12,
			
			'last_tile_taken' => 13,
			'ankh_any_dir' => 14,
			'just_picked_pirogue' => 15,
			
			'misc_1' => 16,
			'misc_2' => 17,
			'misc_3' => 18,
			
			'game_ended' => 19,
			
			'tiles_taken' => 20,

			'treasures_of_the_pharaoh_expansion' => 100,
		) );
	}
	
	protected function getGameName( )
	{
		return "sobektwoplayerstreasurespharaoh";
	}
		
	protected function setupNewGame( $players, $options = array() )
	{		
		// Set the colors of the players with HTML color code
		// The default below is red/green/blue/orange/brown
		// The number of colors defined here must correspond to the maximum number of players allowed for the gams
		$gameinfos = self::getGameinfos();
		$default_colors = $gameinfos['player_colors'];

		$isTreasuresOfThePharaohExpansion = $this->isTreasuresOfThePharaohExpansion();
		
		// Initialise tiles deck
		Tile::setup($isTreasuresOfThePharaohExpansion);
		Deben::setup();
		Pirogue::setup($isTreasuresOfThePharaohExpansion);
		if ($isTreasuresOfThePharaohExpansion) {
			RoyalCorruption::setup();
		}
		
		// Is this many queries slow??
		
		$starting_tiles = Tile::getDeck(true);
		// - 4 starting tiles for centre of board
		for ($col = 2; $col <= 3; $col++) {
			for ($row = 2; $row <= 3; $row++) {
				$s = array_pop($starting_tiles);
				self::DbQuery( "UPDATE tile SET location = 'board', col = $col, row = $row WHERE tile_id = $s[tile_id]" );
			}
		}
		
		$good_tiles = Tile::getDeck();
		// - 32 good/character tiles for rest of board
		for ($col = 0; $col <= 5; $col++) {
			for ($row = 0; $row <= 5; $row++) {
				if ($col == 2 || $col == 3) {
					if ($row == 2 || $row == 3) {
						// Already been filled by a starting tile
						continue;
					}
				}
				$s = array_pop($good_tiles);
				self::DbQuery( "UPDATE tile SET location = 'board', col = $col, row = $row WHERE tile_id = $s[tile_id]" );
			}
		}
		
		
		// - 5 random pirogue tiles for left hand side

		// Create players
		// Note: if you added some extra field on "player" table in the database (dbmodel.sql), you can initialize it there.
		$sql = "INSERT INTO player (player_id, player_color, player_canal, player_name, player_avatar) VALUES ";
		$values = array();
		foreach( $players as $player_id => $player )
		{
			// - 2 starting tiles for each player's hand
			for ($i = 0; $i < 2; $i++) {
				$s = array_pop($starting_tiles);
				Tile::giveToPlayer($s, $player_id);
			}
			
			$color = array_shift( $default_colors );
			$values[] = "('".$player_id."','$color','".$player['player_canal']."','".addslashes( $player['player_name'] )."','".addslashes( $player['player_avatar'] )."')";
		}
		$sql .= implode(',', $values);
		self::DbQuery( $sql );
		self::reattributeColorsBasedOnPreferences( $players, $gameinfos['player_colors'] );
		self::reloadPlayersBasicInfos();
		
		self::setGameStateInitialValue( 'ankh_col', -1 );
		self::setGameStateInitialValue( 'ankh_row', -1 );
		self::setGameStateInitialValue( 'ankh_dir', -1 );
		self::setGameStateInitialValue( 'last_tile_taken', -1 );
		self::setGameStateInitialValue( 'ankh_any_dir', 0 );
		self::setGameStateInitialValue( 'just_picked_pirogue', -1 );
		self::setGameStateInitialValue( 'misc_1', 0 );
		self::setGameStateInitialValue( 'misc_2', 0 );
		self::setGameStateInitialValue( 'misc_3', 0 );
		self::setGameStateInitialValue( 'game_ended', 0 );
		self::setGameStateInitialValue( 'tiles_taken', 0 );
		
		self::initStat( 'player', 'points_sets', 0 );
		self::initStat( 'player', 'points_deben', 0 );
		self::initStat( 'player', 'points_pirogue', 0 );
		self::initStat( 'player', 'turns_number', 0 );
		self::initStat( 'player', 'corruption', 0 );
		
		// Translations
		clienttranslate( "My corruption" );
		clienttranslate( "My hand" );
		clienttranslate( "No tiles in Corruption stack." );
		clienttranslate( "No tiles in hand." );
		clienttranslate( "The Character tiles" );
		clienttranslate( "The Pirogue tokens" );
		clienttranslate( "High Priest(ess)" );

		// TODO TEMP
		//RoyalCorruption::draw(2343492);
		//RoyalCorruption::draw(2343493);
		//RoyalCorruption::draw(2343493);
		// UPDATE `tile` SET `location` = 'discard' where location in ('board', 'deck')

		// Activate first player (which is in general a good idea :) )
		$this->activeNextPlayer();
	}
		
	protected function getAllDatas() {
		$isTreasuresOfThePharaohExpansion = $this->isTreasuresOfThePharaohExpansion();

		$result = [];

		$sql = "SELECT player_id id, player_score score, player_name name, player_seen_pirogues FROM player ";
		$players = self::getCollectionFromDb( $sql );
		
		$game_ended = self::getGameStateValue( 'game_ended' );
		
		$num_seen_pirogues = 0;
		$me_seen_pirogues = false;
		
		foreach ($players as $player_id => $player) {
			$hand = Tile::getHand( $player_id );
			if ($player_id == self::getCurrentPlayerId()) {
				$players[$player_id]['hand'] = $hand;
			}
			$players[$player_id]['hand_size'] = count($hand);
			
			
			$players[$player_id]['hand_starting_size'] = count(array_filter($hand, function($t) {return $t['deck'] == 'starting';}));
			$players[$player_id]['hand_good_size'] = count(array_filter($hand, function($t) {return $t['deck'] == 'good';}));
			$players[$player_id]['hand_character_size'] = count(array_filter($hand, function($t) {return $t['deck'] == 'character';}));
			
			$corruption = Tile::getCorruption( $player_id );
			if ($player_id == self::getCurrentPlayerId()) {
				$players[$player_id]['corruption'] = $corruption;
			}
			$players[$player_id]['corruption_size'] = count($corruption);
			
			$debens = Deben::getOwned( $player_id );
			if ($player_id == self::getCurrentPlayerId() || $game_ended) {
				$players[$player_id]['debens'] = $debens;
			}
			$players[$player_id]['deben_count'] = count($debens);
			
			
			$royalCorruptions = RoyalCorruption::getOwned( $player_id );
			if ($player_id == self::getCurrentPlayerId() || $game_ended) {
				$players[$player_id]['royalCorruptions'] = $royalCorruptions;
			}
			$players[$player_id]['royalCorruption_count'] = count($royalCorruptions);
			
			$players[$player_id]['sold'] = Tile::getSold($player_id);
			
			$players[$player_id]['resource_score'] = self::getPlayerResourceScore($player_id);
			
			if (+$player['player_seen_pirogues']) {
				$num_seen_pirogues++;
				if ($player_id == self::getCurrentPlayerId()) {
					$me_seen_pirogues = true;
				}
			}
		}
		
		$result['players'] = $players;
		
		$board = Tile::getBoard();
		self::redactCharacters($board);
		$result['board'] = $board;
		
		$pirogues = Pirogue::getAll();
		// Only redact if you haven't seen them!
		if ($num_seen_pirogues < 2 && ! $me_seen_pirogues) {
			self::redactSlotPirogues($pirogues);
		}
		$result['pirogues'] = $pirogues;
		
		$deck = Tile::getDeck();
		$result['deck_size'] = count($deck);
		
		$result['ankh'] = array(
			'col' => self::getGameStateValue( 'ankh_col' ),
			'row' => self::getGameStateValue( 'ankh_row' ),
			'dir' => self::getAnkhDir()
		);

		$result['treasuresOfThePharaohExpansion'] = $isTreasuresOfThePharaohExpansion;

		return $result;
	}

    function isTreasuresOfThePharaohExpansion() {
        return intval($this->getGameStateValue('treasures_of_the_pharaoh_expansion')) === 2;
    }
	
	function getPlayerResourceScore($player_id) {
		$sold = Tile::getSold($player_id);
		$resources = array();
		/*
			[
				wheat => [1, 2] // 1 tile * 2 scarabs
			]
		*/
		foreach ($sold as $t) {
			$r = $t['resource'];
			if (! isset($resources[$r])) {
				$resources[$r] = [0, 0];
			}
			$resources[$r][0]++;
			$resources[$r][1] += $t["scarabs"];
		}
		
		// Add points from assigned Pirogue tokens
		$solds = Pirogue::getSoldSets($player_id);
		foreach ($solds as $p) {
			$resources[$p["resource"]][1] += 2;
		}
		
		$score = 0;
		foreach ($resources as $r => $s) {
			$score += $s[0] * $s[1];
		}
		
		return array(
			"resources" => $resources,
			"score" => $score
		);
	}
	
	function getAnkhDir() {
		$ankh_dir = null;
		$ankh_dir_int = self::getGameStateValue( 'ankh_dir' );
		if ($ankh_dir_int == 0) $ankh_dir = 'v';
		else if ($ankh_dir_int == 1) $ankh_dir = 'h';
		else if ($ankh_dir_int == 2) $ankh_dir = 'f';
		else if ($ankh_dir_int == 3) $ankh_dir = 'b';
		return $ankh_dir;
	}
	
	function setAnkhDir($dir) {
		$ankh_dir_int = -1;
		if ($dir == 'v') $ankh_dir_int = 0;
		else if ($dir == 'h') $ankh_dir_int = 1;
		else if ($dir == 'f') $ankh_dir_int = 2;
		else if ($dir == 'b') $ankh_dir_int = 3;
		self::setGameStateValue( 'ankh_dir', $ankh_dir_int );
		return $ankh_dir_int;
	}
	
	function redactSlotPirogues( &$pirogues ) {
		foreach ($pirogues as $key => $pirogue) {
			if ($pirogue['location'] == 'slot') {
				$pirogues[$key]['ability'] = null;
			}
		}
	}
	
	function redactCharacters( &$tiles ) {
		foreach ($tiles as $key => $tile) {
			if ($tile['deck'] == 'character' || $tile['deck'] == 'pharaoh') {
				$tiles[$key]['ability'] = null;
				$tiles[$key]['resource'] = null;
				$tiles[$key]['displayed_resource'] = null;
				$tiles[$key]['statue'] = 0;
			}
		}
	}
	
	function sell($tile_ids) {
		self::checkAction( 'sell' );
		
		self::setGameStateValue( 'ankh_any_dir', 0 );
		
		$pirogue_board = Pirogue::getBoard();
		if (isset($pirogue_board)) {
			// You have to take this token!
			throw new BgaUserException( self::_("You must take the Tile indicated by the Pirogue token") );
		}
		
		$player_id = self::getActivePlayerId();
		
		// Sell set!
		$state = $this->gamestate->state();
		if ($state['name'] == 'characterCourtesan') {
			if (count($tile_ids) != 1 && count($tile_ids) != 2) {
				throw new BgaUserException( self::_("You must sell 1 or 2 tiles.") );
			}
		} else if ($state['name'] == 'characterScribe') {
			$hand = Tile::getHand($player_id);
			if (count($hand) - count($tile_ids) != 6) {
				throw new BgaUserException( self::_("You must discard down to 6 tiles.") );
			}
		} else {
			if (count($tile_ids) < 3) {
				throw new BgaUserException( self::_("You must sell 3 or more tiles.") );
			}
		}
		
		$resources = null;
		$tiles = array();
		foreach ($tile_ids as $tile_id) {
			$tile = Tile::get($tile_id);
			$tiles[] = $tile;
			if ($tile['location'] != 'hand' || $tile['player_id'] != $player_id) {
				throw new BgaVisibleSystemException( "You can only sell tiles in your hand." );
			}
			if ($state['name'] != 'characterScribe') {
				if ($tile['statue'] == 1) {
					//
				} else if ($resources == null) {
					$resources = explode('-or-', $tile["resource"]);
				} else {
					$otherResources = explode('-or-', $tile["resource"]);
					$resources = array_values(array_filter($resources, fn($resource) => in_array($resource, $otherResources) ));

					if (count($resources) == 0) {
						throw new BgaUserException( self::_("You must sell tiles of the same resource.") );
					}
				}
			}
		}	
		
		if ($state['name'] == 'characterScribe') {
			self::DbQuery('UPDATE tile SET location = \'corruption\' WHERE tile_id IN ('.join(',', $tile_ids).')');
			self::notifyHandChange($player_id);
			self::notifyAllPlayers( "discardTile", clienttranslate('${player_name} discards ${num} tiles'), array(
				'player_id' => $player_id,
				'player_name' => self::getActivePlayerName(),
				'to_corruption' => true,
				'tiles' => $tiles,
				'num' => count($tiles)
			));
			
			$this->gamestate->nextState( "next" );
		} else {
			if ($resources == null || $state['name'] == 'characterCourtesan') {
				// Need to add onto another set...
				$sold = Tile::getSold($player_id);
				if ($resources == null) {
					// Only statues - any sold set is fine
					if (count($sold) == 0) {
						throw new BgaUserException( self::_("You cannot sell only statues if there are no sold sets to add onto") );
					}
				} else {
					// Courtesan - need a matching sold set
					$found = [];
					foreach ($sold as $s) {
						if (in_array($s["resource"], $resources)) {
							$found[] = $s["resource"];
							break;
						}
					}
					if (count($found) == 0) {
						throw new BgaUserException( self::_("You must add onto an existing sold set") );
					} else if (count($found) > 1) {
						throw new BgaUserException( self::_("No clearly defined resource") );// TODOTP
					}
				}
			} else {
				if (count($resources) > 1) {
					throw new BgaUserException( self::_("No clearly defined resource") );// TODOTP
				}	
			}
			
			self::DbQuery('UPDATE tile SET just_sold = 1 WHERE tile_id IN ('.join(',', $tile_ids).')');
			
			$this->gamestate->nextState( "pickResource" );
		}
	}
	
	function playCharacter($tile_id) {
		self::checkAction( 'playCharacter' );
		
		self::setGameStateValue( 'ankh_any_dir', 0 );
		
		$player_id = self::getActivePlayerId();
		
		$pirogue_board = Pirogue::getBoard();
		if (isset($pirogue_board)) {
			// You have to take this token!
			throw new BgaUserException( self::_("You must take the Tile indicated by the Pirogue token") );
		}
		
		// You must own this tile
		$tile = Tile::get($tile_id);
		
		if (! isset($tile)) {
			throw new BgaVisibleSystemException( "Tile doesn't exist." );
		}
		if ($tile["location"] != "hand" || $tile["player_id"] != $player_id) {
			throw new BgaVisibleSystemException( "You can only play tiles in your hand." );
		}
		if ($tile["deck"] != "character") {
			throw new BgaVisibleSystemException( "That is not a character tile." );
		}
		
		$ability = $tile["ability"];
		$padability = "10";
		if ($ability < 10) {
			$padability = "0" . $ability;	
		}
		
		Tile::discard($tile);
		self::notifyAllPlayers( "discardTile", clienttranslate('${player_name} plays a Character: ${image}'), array(
			'player_id' => $player_id,
			'player_name' => self::getActivePlayerName(),
			'tile' => $tile,
			'image' => '<div class="sprite sprite-tile sprite-character-'.$padability.'"></div>'
		));
		
		// Do different things depending on the ability...
		$transition = "next";
		
		if ($ability == 1) {
			// Queen
			// Draw (up to) three cards from the deck
			$new_tiles = array();
			$deck = Tile::getDeck();
			for ($i = 0; $i < 3; $i++) {
				if (count($deck) == 0) break;
				$s = array_pop($deck);
				$new_tiles[] = $s;
				Tile::giveToPlayer($s, $player_id);
			}
			self::notifyHandChange($player_id);
			self::incGameStateValue( 'tiles_taken', count($new_tiles) );
			self::notifyPlayer( $player_id, "drawTiles", '', array(
				'player_id' => $player_id,
				'player_name' => self::getActivePlayerName(),
				'tiles' => $new_tiles,
			));
			self::notifyAllPlayers( "drawTiles", clienttranslate('${player_name} draws ${tiles_num} tiles'), array(
				'player_id' => $player_id,
				'player_name' => self::getActivePlayerName(),
				'tiles_num' => count($new_tiles),
				'from_deck' => true
			));
			// Do it...
			$transition = "next";
		} else if ($ability == 2) {
			// Take a tile from opponent's corruption board
			$transition = "characterVizier";
		} else if ($ability == 3) {
			// Randomly steal a tile from opponent's hand (you get to choose the back)
			$transition = "characterThief";
		} else if ($ability == 4) {
			// Take any tile from the market
			$transition = "characterMerchant";
		} else if ($ability == 5 || $ability == 6) {
			// Remove all statues of resources of one type from your corruption
			$transition = "characterHighPriest";
		} else if ($ability == 7) {
			// Add 1 or 2 tiles from your hand to previously sold set
			$transition = "characterCourtesan";
		} else if ($ability == 8) {
			// Draw 3 Pirogues, and pick one
			$bag = Pirogue::getBag();
			
			self::setGameStateValue( 'misc_1', $bag[0]["pirogue_id"] );
			self::setGameStateValue( 'misc_2', $bag[1]["pirogue_id"] );
			self::setGameStateValue( 'misc_3', $bag[2]["pirogue_id"] );
			
			$transition = "characterArchitect";
		} else if ($ability == 9 || $ability == 10) {
			// Opponent must discard down to 6 tiles in hand (extras to corruption)
			$transition = "characterScribe";
		}
				
		// Play character!
		$this->gamestate->nextState( $transition );
	}
	
	function argCharacterArchitect() {
		return array(
			'_private' => array(
				'active' => array(
					'pirogues' => array(
						Pirogue::get( self::getGameStateValue( 'misc_1' ) ),
						Pirogue::get( self::getGameStateValue( 'misc_2' ) ),
						Pirogue::get( self::getGameStateValue( 'misc_3' ) ),
					)
				)
			)
		);
	}
	
	function argCharacterVizier() {
		$player_id = self::getActivePlayerId();
		
		return array(
			'_private' => array(
				'active' => array(
					'opponents_corruption' => Tile::getCorruption(self::getPlayerAfter($player_id))
				)
			)
		);
	}
	
	function argCharacterThief() {
		$player_id = self::getActivePlayerId();
		
		$hand = Tile::getHand(self::getPlayerAfter($player_id));
		
		$num_per_deck = array();
		foreach ($hand as $t) {
			$d = $t["deck"];
			if (! isset($num_per_deck[$d])) {
				$num_per_deck[$d] = 0;
			}
			$num_per_deck[$d]++;
		}
		
		return array(
			'_private' => array(
				'active' => array(
					'num_per_deck' => $num_per_deck
				)
			)
		);
	}
	
	function answer($answer) {
		self::checkAction( 'answer' );
		
		$player_id = self::getActivePlayerId();
		
		$state = $this->gamestate->state();
		if ($state['name'] == 'deben')
		{
			if ($answer == "yes") {
				// Take deben
				$tile_id = self::getGameStateValue( 'last_tile_taken' );
				$tile = Tile::get($tile_id);
				$deben = Deben::draw( $player_id );
				
				if ($deben == null) {
					throw new BgaUserException( self::_("There are no Deben tokens left") );
				}
				
				// - Discard the tile
				Tile::discard($tile);
				self::notifyHandChange($player_id);
				self::notifyAllPlayers( "discardTile", clienttranslate('${player_name} discards the tile for a Deben'), array(
					'player_id' => $player_id,
					'player_name' => self::getActivePlayerName(),
					
					'tile' => $tile,
					'reason' => 'deben',
				));
				
				// - Draw a random Deben tile
				self::notifyPlayer( $player_id, "deben", '', array(
					'player_id' => $player_id,
					'player_name' => self::getActivePlayerName(),
					
					'deben' => $deben,
				));
				
				$this->gamestate->nextState( "next" );
			} else if ($answer == "no") {
				// Do nothing
				$this->gamestate->nextState( "next" );
			} else {
				// Urm...
				throw new BgaVisibleSystemException( "Invalid answer." );
			}
		} else if ($state['name'] == "orientation")
		{
			if ($answer == "v" || $answer == "h" || $answer == "f" || $answer == "b") {
				if (count(self::availableTiles( $answer )) == 0) {
					// If there are no tiles in that direction, you can't pick it...
					if (count(self::availableTiles( 'v' )) > 0 ||
						count(self::availableTiles( 'h' )) > 0 ||
						count(self::availableTiles( 'f' )) > 0 ||
						count(self::availableTiles( 'b' )) > 0)
					{
						throw new BgaUserException( self::_("You must choose a direction which points towards tiles if possible") );
					}
				}
					
				// Set orientation of ankh
				self::setAnkhDir( $answer );
				
				self::notifyAllPlayers( "ankhDir", '', array(
					'ankh_dir' => $answer,
				));
				$this->gamestate->nextState( "next" );
			} else {
				// Urm...
				throw new BgaVisibleSystemException( "Invalid answer." );
			}
		} else if ($state['name'] == "pickResource")
		{
			if ($answer == "wheat" || $answer == "ebony" || $answer == "ivory" || $answer == "marble" || $answer == "fish" || $answer == "livestock") {
				$just_sold = self::getObjectList('SELECT * FROM tile WHERE just_sold = 1');
				foreach ($just_sold as $k => $v) {
					$just_sold[$k]['resource'] = $answer;
					$just_sold[$k]['just_sold'] = 0;
					$just_sold[$k]['location'] = 'sold';
				}
				self::notifyAllPlayers( "sold", clienttranslate('${player_name} sells some tiles'), array(
					'player_id' => $player_id,
					'player_name' => self::getActivePlayerName(),
					'tiles' => $just_sold
				));
				
				self::DbQuery("UPDATE tile SET resource = '$answer', just_sold = 0, location = 'sold' WHERE just_sold");
				self::notifyHandChange($player_id);
				// Update player score...
				$score = self::getPlayerResourceScore($player_id);
				self::dbSetScore($player_id, $score['score']);
				self::notifyAllPlayers( "updateScores", '', array(
					'player_id' => $player_id,
					'resource_score' => $score
				));

				// TODOTP take royal corruption
				
				$this->gamestate->nextState( "next" );
			} else {
				// Urm...
				throw new BgaVisibleSystemException( "Invalid answer." );
			}
		} else if ($state['name'] == "pirogue07")
		{
			if ($answer == "wheat" || $answer == "fish" || $answer == "livestock") {
				$sold = Tile::getSold($player_id);
				$found = false;
				foreach ($sold as $t) {
					if ($t['resource'] == $answer) {
						$found = true;
						break;
					}
				}
				if (! $found) {
					throw new BgaUserException( self::_("You have no sold sets of that type") );
				} else {
					// Move the pirogue there, and update score
					$pirogue = Pirogue::get( self::getGameStateValue( 'just_picked_pirogue' ) );
					self::DbQuery("UPDATE pirogue SET location='soldset', resource='$answer', player_id=$player_id WHERE pirogue_id=$pirogue[pirogue_id]");
					$pirogue['location'] = 'soldset';
					$pirogue['resource'] = $answer;
					$pirogue['player_id'] = $player_id;
					self::notifyAllPlayers( "takePirogue", '', array(
						'pirogue' => $pirogue,
						'discard' => true,
						'soldset' => true,
					));
					
					// Update player score...
					$score = self::getPlayerResourceScore($player_id);
					self::dbSetScore($player_id, $score['score']);
					self::notifyAllPlayers( "updateScores", '', array(
						'player_id' => $player_id,
						'resource_score' => $score
					));
					
					$this->gamestate->nextState( 'next' );
				}
			} else {
				// Urm...
				throw new BgaVisibleSystemException( "Invalid answer." );
			}
		} else if ($state['name'] == "pirogue11")
		{
			if ($answer == "marble" || $answer == "ebony") {
				$sold = Tile::getSold($player_id);
				$found = false;
				foreach ($sold as $t) {
					if ($t['resource'] == $answer) {
						$found = true;
						break;
					}
				}
				if (! $found) {
					throw new BgaUserException( self::_("You have no sold sets of that type") );
				} else {
					// Move the pirogue there, and update score
					$pirogue = Pirogue::get( self::getGameStateValue( 'just_picked_pirogue' ) );
					self::DbQuery("UPDATE pirogue SET location='soldset', resource='$answer', player_id=$player_id WHERE pirogue_id=$pirogue[pirogue_id]");
					$pirogue['location'] = 'soldset';
					$pirogue['resource'] = $answer;
					$pirogue['player_id'] = $player_id;
					self::notifyAllPlayers( "takePirogue", '', array(
						'pirogue' => $pirogue,
						'discard' => true,
						'soldset' => true,
					));
					
					// Update player score...
					$score = self::getPlayerResourceScore($player_id);
					self::dbSetScore($player_id, $score['score']);
					self::notifyAllPlayers( "updateScores", '', array(
						'player_id' => $player_id,
						'resource_score' => $score
					));
					
					$this->gamestate->nextState( 'next' );
				}
			} else {
				// Urm...
				throw new BgaVisibleSystemException( "Invalid answer." );
			}
		} else if ($state['name'] == "characterHighPriest")
		{
			if ($answer == "statue" || $answer == "wheat" || $answer == "ebony" || $answer == "ivory" || $answer == "marble" || $answer == "fish" || $answer == "livestock") {
				// Remove all matching tiles from corruption...
				if ($answer == "statue") {
					$to_remove = self::getObjectList("SELECT * FROM tile WHERE location = 'corruption' AND player_id = $player_id AND statue");
				} else {
					$to_remove = self::getObjectList("SELECT * FROM tile WHERE location = 'corruption' AND player_id = $player_id AND resource = '$answer'");
				}
				
				$to_remove_ids = [];
				foreach ($to_remove as $t) {
					$to_remove_ids[] = $t["tile_id"];
				}
				
				if (count($to_remove_ids) > 0) {
					self::DbQuery("UPDATE tile SET location = 'discard' WHERE tile_id IN (".implode(",", $to_remove_ids).")");
				}
				
				self::notifyAllPlayers( "removeCorruption", clienttranslate('${player_name} discards ${num} tiles from their Corruption board'), array(
					'player_id' => $player_id,
					'player_name' => self::getActivePlayerName(),
					'tiles' => $to_remove,
					'num' => count($to_remove)
				));
				
				$this->gamestate->nextState( "next" );
			} else {
				// Urm...
				throw new BgaVisibleSystemException( "Invalid answer." );
			}
		} else if ($state['name'] == "characterVizier")
		{
			$answer = intval($answer);
			
			$tile = Tile::get($answer);
			if (! isset($tile)) {
				throw new BgaVisibleSystemException( "Tile not found." );
			}
			$target_player_id = self::getPlayerAfter($player_id);
			if ($tile["location"] != "corruption" || $tile["player_id"] != $target_player_id) {
				throw new BgaUserException( self::_("Tile must be on opponent's corruption board.") );
			}
			
			// Give to the player...
			Tile::giveToPlayer($tile, $player_id);
			self::notifyHandChange($player_id);
			$players = self::loadPlayersBasicInfos();
			
			self::notifyPlayer( $player_id, "drawTiles", '', array(
				'player_id' => $player_id,
				'player_name' => self::getActivePlayerName(),
				'tiles' => array($tile),
			));
			self::notifyAllPlayers( "drawTiles", '', array(
				'player_id' => $player_id,
				'player_name' => self::getActivePlayerName(),
				'tiles_num' => 1,
			));
			self::notifyAllPlayers( "removeCorruption", clienttranslate('${player_name2} takes a tile from ${player_name}\'s Corruption board: ${image}'), array(
				'player_id' => $target_player_id,
				'player_name2' => self::getActivePlayerName(),
				'player_name' => $players[$target_player_id]["player_name"],
				'tiles' => array($tile),
				'num' => 1,
				'image' => self::makeImage($tile)
			));
			
			$this->gamestate->nextState( "next" );
		} else if ($state['name'] == "characterThief")
		{
			if ($answer == "good" || $answer == "starting" || $answer == "character") {
				$target_player_id = self::getPlayerAfter($player_id);
				
				// Get a random card of this type from opponent's hand...
				$tile = SobekTwoPlayersTreasuresPharaoh::getObject( "SELECT * FROM tile WHERE deck = '$answer' AND player_id = $target_player_id AND location = 'hand' ORDER BY RAND() LIMIT 1" );
				if (! isset($tile)) {
					throw new BgaVisibleSystemException( "Opponent has no tiles of that type." );
				}
				
				// Give this tile to player...
				Tile::giveToPlayer($tile, $player_id);
				self::notifyHandChange($player_id);
				$players = self::loadPlayersBasicInfos();
				
				self::notifyPlayer( $player_id, "drawTiles", '', array(
					'player_id' => $player_id,
					'player_name' => self::getActivePlayerName(),
					'tiles' => array($tile),
				));
				self::notifyAllPlayers( "drawTiles", '', array(
					'player_id' => $player_id,
					'player_name' => self::getActivePlayerName(),
					'tiles_num' => 1,
				));
				self::notifyAllPlayers( "discardTile", clienttranslate('${player_name2} steals a tile from ${player_name}\'s hand: ${image}'), array(
					'player_id' => $target_player_id,
					'player_name2' => self::getActivePlayerName(),
					'player_name' => $players[$target_player_id]["player_name"],
					'tile' => $tile,
					'image' => self::makeImage($tile)
				));
				
				$this->gamestate->nextState( "next" );
			} else {
				throw new BgaVisibleSystemException( "Invalid answer." );
			}
		} else {
			throw new BgaVisibleSystemException( "Invalid state." );
		}
	}
	
	function pickPirogue($slot) {
		self::checkAction( 'pickPirogue' );
		
		$player_id = self::getActivePlayerId();
		
		// Does this slot exist?
		// Apply automatically, or go to appropriate state?
		
		$state = $this->gamestate->state();
		if ($state['name'] != 'characterArchitect') {
			// You have seen the pirogues
			self::DbQuery( "UPDATE `player` SET `player_seen_pirogues` = 1 WHERE player_id = $player_id");
		}
		
		$pirogue = Pirogue::get($slot);
		if (! isset($pirogue)) {
			throw new BgaVisibleSystemException( "That Pirogue does not exist." );
		}
		
		// Depending on mode, must be in a slot
		if ($state['name'] == 'pirogue') {
			if ($pirogue["location"] != 'slot') {
				throw new BgaVisibleSystemException( "That Pirogue is not in a slot." );
			}
		} else if ($state['name'] == 'characterArchitect') {
			$misc1 = self::getGameStateValue( 'misc_1' );
			$misc2 = self::getGameStateValue( 'misc_2' );
			$misc3 = self::getGameStateValue( 'misc_3' );
			if ($slot != $misc1 && $slot != $misc2 && $slot != $misc3) {
				throw new BgaVisibleSystemException( "That Pirogue is not available." );
			}
		}
		
		// Depending on ability...
		$ability = $pirogue["ability"];
		
		$target_player_id = null;
		$discard = false;
		$transition = "next";
		$message = '';
		$num = 0;
		
		if ($ability == 1 || $ability == 2) {
			// Goes to opponent, counts as $ability corruption (player)
			$message = clienttranslate('${player_name} gives a Pirogue token to ${player_name2}: ${image}');
			$target_player_id = self::getPlayerAfter( $player_id );
		} else if ($ability == 3) {
			// Take an extra turn - allow player to pick with any orientation (discard)
			$discard = true;
			$message = clienttranslate('${player_name} takes a Pirogue token and takes an extra turn: ${image}');
			self::setGameStateValue( 'ankh_any_dir', 1 );
			$transition = "extraTurn";
		} else if ($ability == 4) {
			// Put on an available tile - the other player must take this tile on their turn (board)
			$message = clienttranslate('${player_name} takes a Pirogue token: ${image}');
			self::setGameStateValue( 'just_picked_pirogue', $pirogue["pirogue_id"] );
			$transition = "pirogue04";
		} else if ($ability == 5) {
			// Take all corruption tiles to hand (discard)
			$corruption = Tile::getCorruption($player_id);
			Tile::drawCorruption($player_id);
			$num = count($corruption);
			$message = clienttranslate('${player_name} takes a Pirogue token and takes ${num} corruption cards into their hand: ${image}');
			$discard = true;
		} else if ($ability == 6) {
			// Counts as 7 points eventually (player)
			$target_player_id = $player_id;
			$message = clienttranslate('${player_name} takes a Pirogue token worth points at the end of the game: ${image}');
		} else if ($ability == 7) {
			// Counts as 2 scarabs for wheat/fish/livestock (sold set)
			$message = clienttranslate('${player_name} takes a Pirogue token: ${image}');
			self::setGameStateValue( 'just_picked_pirogue', $pirogue["pirogue_id"] );
			$transition = "pirogue07";
		} else if ($ability == 8) {
			// 2 points eventually (player)
			$target_player_id = $player_id;
			$message = clienttranslate('${player_name} takes a Pirogue token worth points at the end of the game, and draws a Deben token: ${image}');
			// Plus they get a deben
			$deben = Deben::draw( $player_id );
			
			if ($deben != null) {
				self::notifyPlayer( $player_id, "deben", '', array(
					'player_id' => $player_id,
					'player_name' => self::getActivePlayerName(),
					
					'deben' => $deben,
				));
				
				self::notifyAllPlayers( "deben", '', array(
					'player_id' => $player_id,
					'player_name' => self::getActivePlayerName()
				));
			}
		} else if ($ability == 9) {
			// Draw 2 deben, and keep the higher (discard)
			$discard = true;
			$bag = Deben::getBag();
			$message = clienttranslate('${player_name} takes a Pirogue token and draws a Deben token: ${image}');
			if (count($bag) > 0) {
				$deben = array_pop($bag);
				if (count($bag) > 1) {
					$deben2 = array_pop($bag);
				}
				
				if (isset($deben2)) {
					if ($deben2["value"] > $deben["value"]) {
						$deben = $deben2;
					}
				}
				
				$deben = Deben::draw( $player_id, $deben );
				
				self::notifyPlayer( $player_id, "deben", clienttranslate('You look at Deben tokens of value ${value1} and ${value2}, and keep the one of value ${value1}'), array(
					'player_id' => $player_id,
					'player_name' => self::getActivePlayerName(),
					
					'deben' => $deben,
					'value1' => $deben["value"],
					'value2' => $deben2["value"],
				));
				
				self::notifyAllPlayers( "deben", '', array(
					'player_id' => $player_id,
					'player_name' => self::getActivePlayerName()
				));
			}
		} else if ($ability == 10) {
			$discard = true;
			$opponentId = self::getPlayerAfter($player_id);
			$opponentDebens = Deben::getOwned($opponentId);

			if (count($opponentDebens) > 0) {
				$discardedDeben = $opponentDebens[bga_rand(0, count($opponentDebens)-1)];
				self::DbQuery("UPDATE `deben` SET `location`='discard' WHERE `deben_id` = $discardedDeben[deben_id]");

				self::notifyPlayer( $opponentId, "discardDeben", '', array(
					'player_id' => $opponentId,
					'deben' => $discardedDeben,
				));
				
				self::notifyAllPlayers( "discardDeben", '', array(
					'player_id' => $opponentId,
				));
			} else {
				self::notifyAllPlayers("log", clienttranslate('There is no deben to discard'), []);
			}
		} else if ($ability == 11) {
			// Counts as 2 scarabs for wheat/fish/livestock (sold set)
			$message = clienttranslate('${player_name} takes a Pirogue token: ${image}');
			self::setGameStateValue( 'just_picked_pirogue', $pirogue["pirogue_id"] );
			$transition = "pirogue11";
		} else {
			throw new BgaVisibleSystemException( "Unrecognised Pirogue ability." );
		}
		
		$players = self::loadPlayersBasicInfos();
		
		if ($target_player_id != null) {
			self::DbQuery("UPDATE pirogue SET location='player', player_id='$target_player_id' WHERE pirogue_id=$pirogue[pirogue_id]");
			$pirogue['location'] = 'player';
			$pirogue['player_id'] = $target_player_id;
			self::notifyAllPlayers( "takePirogue", $message, array(
				'player_id' => $player_id,
				'player_name' => self::getActivePlayerName(),
				'pirogue' => $pirogue,
				'target_player_id' => $target_player_id,
				'player_name2' => $players[$target_player_id]["player_name"],
				'num' => $num,
				'image' => '<div class="sprite sprite-pirogue sprite-pirogue-0'.$ability.'"></div>'
			));
		} else if ($discard) {
			// Discard the token...
			self::DbQuery("UPDATE pirogue SET location='discard' WHERE pirogue_id=$pirogue[pirogue_id]");
			$pirogue['location'] = 'discard';
			self::notifyAllPlayers( "takePirogue", $message, array(
				'player_id' => $player_id,
				'player_name' => self::getActivePlayerName(),
				'player_name2' => '',
				'pirogue' => $pirogue,
				'discard' => true,
				'num' => $num,
				'image' => '<div class="sprite sprite-pirogue sprite-pirogue-0'.$ability.'"></div>'
			));
		} else {
			// State...
			self::notifyAllPlayers( "takePirogue", $message, array(
				'player_id' => $player_id,
				'player_name' => self::getActivePlayerName(),
				'player_name2' => '',
				'pirogue' => $pirogue,
				'image' => '<div class="sprite sprite-pirogue sprite-pirogue-0'.$ability.'"></div>'
			));
		}
		
		$this->gamestate->nextState( $transition );
	}
	
	function selectMarketTile($col, $row) {
		self::checkAction( 'selectMarketTile' );

		$player_id = self::getActivePlayerId();
		$state = $this->gamestate->state();
		
		// Tile must exist
		$tile = Tile::getAtCoords($col, $row);
		if (! isset($tile)) {
			throw new BgaVisibleSystemException( "There is no tile at that location." );
		}
		
		self::incGameStateValue( 'tiles_taken', 1 );
		
		$ankh_dir = null;
		if ($state['name'] != 'characterMerchant') {
			// Tile must be available, according to the ankh
			$available_tiles = self::availableTiles();
			$found = false;
			foreach ($available_tiles as $coords) {
				if ($coords[0] == $col && $coords[1] == $row) {
					$found = true;
					$ankh_dir = $coords[2];
					break;
				}
			}
			if (! $found) {
				throw new BgaUserException( self::_("That tile is not permitted by the Ankh pawn") );
			}
		}
		
		// Depending on state...
		
		if ($state['name'] == 'pirogue04') {
			// Put the pirogue here, tell people, and then next turn!
			$pirogue = Pirogue::get( self::getGameStateValue( 'just_picked_pirogue' ) );
			self::DbQuery("UPDATE pirogue SET location='board', row=$row, col=$col WHERE pirogue_id=$pirogue[pirogue_id]");
			$pirogue['location'] = 'board';
			$pirogue['row'] = $row;
			$pirogue['col'] = $col;
			self::notifyAllPlayers( "takePirogue", '', array(
				'pirogue' => $pirogue,
				'discard' => true,
				'board' => [$col, $row]
			));
			$this->gamestate->nextState( 'next' );
		} else {
			self::setGameStateValue( 'ankh_any_dir', 0 );
			
			// Check if there are any pirogues on the board
			$pirogue_board = Pirogue::getBoard();
			if (isset($pirogue_board)) {
				// You have to take this token!
				if ($col != $pirogue_board["col"] || $row != $pirogue_board["row"]) {
					throw new BgaUserException( self::_("You must take the Tile indicated by the Pirogue token") );
				}
				
				// Discard this token
				self::DbQuery("UPDATE pirogue SET location='discard' WHERE pirogue_id=$pirogue_board[pirogue_id]");
			}
			
			// Add to player's hand
			Tile::giveToPlayer($tile, $player_id);
			$corruption_tiles_objects = [];
			if ($state['name'] != 'characterMerchant') {
				// Take corruption tiles
				$corruption_tiles = self::getCorruptionTiles($col, $row, $ankh_dir);
				foreach ($corruption_tiles as $corrupted) {
					$ct = Tile::getAtCoords($corrupted[0], $corrupted[1]);
					if (isset($ct)) {
						$corruption_tiles_objects[] = $ct;
						Tile::giveToPlayer($ct, $player_id, true);
					}
				}
				
				// Move ankh to the tile + direction
				self::setGameStateValue( 'ankh_col', $col );
				self::setGameStateValue( 'ankh_row', $row );
				if (isset($tile["direction"])) {
					self::setAnkhDir( $tile["direction"] );
				}
			} else {
				$corruption_tiles = [];
			}
			
			// Notification of all this!
			if ($tile["deck"] == 'character') {
				// If character, reveal character only to the player
				self::notifyPlayer( $player_id, "takeTile", '', array(
					'player_id' => $player_id,
					'player_name' => self::getActivePlayerName(),
					'ignore_self' => false,
					'tile' => $tile,
					'corruption_tiles' => $corruption_tiles,
					'corruption_tiles_objects' => $corruption_tiles_objects,
				));
				$tile['ability'] = null;
				$tile['resource'] = null;
				$tile['statue'] = 0;
			}
			self::notifyHandChange($player_id);
			self::incGameStateValue( 'tiles_taken', count($corruption_tiles_objects) );
			self::notifyAllPlayers( "takeTile", clienttranslate('${player_name} takes a tile from the market (with ${num} Corruption): ${image}'), array(
				'player_id' => $player_id,
				'player_name' => self::getActivePlayerName(),
				'ignore_self' => $tile["deck"] == 'character',
				'tile' => $tile,
				'move_ankh' => $state['name'] != 'characterMerchant',
				'corruption_tiles' => $corruption_tiles,
				'num' => count($corruption_tiles_objects),
				'corruption_tiles_objects' => $corruption_tiles_objects,
				'image' => self::makeImage($tile)
			));
			
			// Next state:
			if ($tile["deben"] > 0 && $state['name'] != 'characterMerchant') {
				// - if deben, deben choice
				self::setGameStateValue( 'last_tile_taken', $tile['tile_id'] );
				$this->gamestate->nextState( "deben" );
			} else if ($tile["deck"] == 'character' && $state['name'] != 'characterMerchant') {
				// - if character, direction choice
				$this->gamestate->nextState( "orientation" );
			} else {
				// - otherwise, next player
				$this->gamestate->nextState( "next" );
			}
		}
	}
	
	function refill() {
		self::checkAction( 'refill' );

		$player_id = self::getActivePlayerId();
		
		// Deck must not be empty
		$deck = Tile::getDeck();
		if (count($deck) == 0) {
			throw new BgaUserException( self::_('There are no Tiles in the deck, and the board cannot be refilled') );
		}
		
		// Refill board
		$board = Tile::getBoard();
		$locations = [
			// 1 ->
			[2, 2], [3, 2], [3, 3], [2, 3],
			// 2 ->
			[1, 1], [2, 1], [3, 1], [4, 1],
			[4, 2], [4, 3], [4, 4],
			[3, 4], [2, 4], [1, 4],
			[1, 3], [1, 2],
			// 3 ->
			[0, 0], [1, 0], [2, 0], [3, 0], [4, 0], [5, 0], 
			[5, 1], [5, 2], [5, 3], [5, 4], [5, 5], 
			[4, 5], [3, 5], [2, 5], [1, 5], [0, 5], 
			[0, 4], [0, 3], [0, 2], [0, 1]
		];
		$new_tiles = [];
		foreach ($locations as $l) {
			$found = false;
			foreach ($board as $t) {
				if ($t["col"] == $l[0] && $t["row"] == $l[1]) {
					$found = true;
					break;
				}
			}
			if (! $found) {
				// Draw a tile!
				$s = array_pop($deck);
				$s["location"] = "board";
				$s["col"] = $l[0];
				$s["row"] = $l[1];
				$new_tiles[] = $s;
				self::DbQuery( "UPDATE tile SET location = 'board', col = $l[0], row = $l[1] WHERE tile_id = $s[tile_id]" );
				
				if (count($deck) == 0) {
					break;
				}
			}
		}
		
		// Remove Ankh
		self::setGameStateValue( 'ankh_col', -1 );
		self::setGameStateValue( 'ankh_row', -1 );
		self::setGameStateValue( 'ankh_dir', -1 );
		
		// Notification about new tiles
		self::redactCharacters($new_tiles);
		self::notifyAllPlayers( "refill", clienttranslate('${player_name} refills the board'), array(
			'player_id' => $player_id,
			'player_name' => self::getActivePlayerName(),
			'new_tiles' => $new_tiles
		));
		
		$this->gamestate->nextState( "refill" );
	}
	
	function argPirogue() {
		// If there are no Pirogues left, skip this!
		return array(
			'_private' => array(
				'active' => array(
					'pirogues' => Pirogue::getSlots()
				)
			)
		);
	}
	
	function stPlayerTurn() {
		// If you can do any action, then the end of game is triggered...
		$arg = self::argPlayerTurn();
		
		$player_id = self::getActivePlayerId();
		
		if (! $arg["can_sell"] && ! $arg["can_take"] && ! $arg["can_refill"] && ! $arg["can_play_character"]) {
			self::notifyAllPlayers( "message", clienttranslate('${player_name} has no valid action which triggers the end of the game'), array(
				'player_id' => $player_id,
				'player_name' => self::getActivePlayerName(),
			));
			$this->gamestate->nextState( 'gameEnd' );
		} else {
			self::incStat( 1, 'turns_number', $player_id );
			self::incStat( 1, 'turns_number' );
			
			self::giveExtraTime( $player_id );
		}
	}
	
	static function sortByLength($a, $b) {
		$al = count($a);
		$bl = count($b);
		if ($al == $bl) return 0;
		return ($a < $b) ? 1 : -1;
	}
	
	function stFinalScoring() {
		self::setGameStateValue( 'game_ended', 1 );
		
		$player_id = self::getActivePlayerId();
		$opponent_player_id = self::getPlayerAfter( $player_id );
		
		$players = self::loadPlayersBasicInfos();
		
		// Other player discards sets they could have sold...
		$opponent_hand = Tile::getHand( $opponent_player_id );
		
		$resources = array();
		$statues = array();
		
		foreach ($opponent_hand as $t) {
			if ($t["statue"] == 1) {
				$statues[] = $t;
			} else {
				$r = $t["resource"];
				if (!isset($resources[$r])) {
					$resources[$r] = array();
				}
				$resources[$r][] = $t;
			}
		}
		
		uasort($resources, 'SobekTwoPlayersTreasuresPharaoh::sortByLength');
		
		$discards = array();
		
		// From biggest set to smallest, discard sets!
		foreach ($resources as $r => $list) {
			$len = count($list);
			if ($len >= 3) {
				// Discard this set!
				$discards[] = $list;
			} else {
				// Are there enough statues to discard this?
				$shortfall = 3 - $len;
				if (count($statues) >= $shortfall) {
					// Yes!
					for ($i = 0; $i < $shortfall; $i++) {
						$list[] = array_pop($statues);
					}
					$discards[] = $list;
				}
			}
		}
		
		if (count($statues) > 0 && count($discards) > 0) {
			// If there are statues left, add them to the last list
			$last_discard = $discards[count($discards) - 1];
			$discards[count($discards) - 1] = array_merge($last_discard, $statues);
		} else if (count($statues) >= 3) {
			// Discard statues on their own
			$discards[] = $statues;
		}
		
		foreach ($discards as $tiles) {
			$images = '';
			foreach ($tiles as $t) {
				$images .= self::makeImage($t, true) . " ";
				
				// Actually discard the tile...
				Tile::discard($t);
			}
			self::notifyAllPlayers( "discardTileSlow", clienttranslate('${player_name} discards tiles which could have formed a set:<br>${images}'), array(
				'player_id' => $opponent_player_id,
				'player_name' => $players[$opponent_player_id]["player_name"],
				
				'tiles' => $tiles,
				'num' => count($tiles),
				'images' => $images
			));
		}
		
		// Both players add cards in hand to corruption...
		foreach ($players as $pid => $p) {
			self::notifyHandChange($pid);
			$hand = Tile::getHand($pid);
			self::notifyAllPlayers( "discardTileSlow", clienttranslate('${player_name} adds ${num} card(s) to Corruption from their hand'), array(
				'player_id' => $pid,
				'player_name' => $players[$pid]["player_name"],
				'to_corruption' => true,
				'tiles' => $hand,
				'num' => count($hand)
			));
		}
		self::DbQuery("UPDATE tile SET location='corruption' WHERE location='hand'");
		
		// Give Deben tokens to player with fewest corruption
		$amount_corruption = array();
		$pirogue_points = array();
		$resource_points = array();
		$deben_points = array();
		$total_points = array();
		$total_and_tie_points = array();
		foreach ($players as $pid => $p) {
			// Reveal Debens to the other player
			self::notifyPlayer( self::getPlayerAfter($pid), "revealDebens", '', array(
				'player_id' => $pid,
				'debens' => Deben::getOwned($pid)
			));
			// Get corruption amount
			$amount_corruption[$pid] = count(Tile::getCorruption($pid));
			$pirogue_points[$pid] = 0;
			$deben_points[$pid] = 0;
			$total_and_tie_points[$pid] = 0;
			$resource_points[$pid] = self::getPlayerResourceScore($pid);
			// Add on pirogues...
			$pirogues = Pirogue::getOwned($pid);

			// Add royal corruption
			$royalCorruptions = RoyalCorruption::getOwned($pid);
			foreach ($royalCorruptions as $royalCorruption) {
				$amount_corruption[$pid] += intval($royalCorruption['value']);
			}

			foreach ($pirogues as $pirogue) {
				if ($pirogue['ability'] == 1) {
					$amount_corruption[$pid] += 1;
				} else if ($pirogue['ability'] == 2) {
					$amount_corruption[$pid] += 2;
				} else if ($pirogue['ability'] == 6) {
					$pirogue_points[$pid] += 7;
				} else if ($pirogue['ability'] == 8) {
					$pirogue_points[$pid] += 2;
				}
			}
			
			// Tie break = negative corruption
			self::dbSetAuxScore($pid, -$amount_corruption[$pid]);
			$total_and_tie_points[$pid] += -$amount_corruption[$pid] / 100;
		}
		
		$debens = 0;
		if ($amount_corruption[$player_id] < $amount_corruption[$opponent_player_id]) {
			$debens_player = $player_id;
			$debens_other_player = $opponent_player_id;
		} else if ($amount_corruption[$player_id] > $amount_corruption[$opponent_player_id]) {
			$debens_player = $opponent_player_id;
			$debens_other_player = $player_id;
		} else {
			// Same! Don't give debens
		}
		if (isset($debens_player)) {
			$diff = $amount_corruption[$debens_other_player] - $amount_corruption[$debens_player];
			$debens = 1 + floor($diff / 3);
			if ($debens > 0) {
				$ds = array();
				for ($i = 0; $i < $debens; $i++) {
					$deben = Deben::draw( $debens_player );
					if (isset($deben)) {
						$ds[] = $deben;
					}
				}
				self::notifyAllPlayers("debenSlow", clienttranslate('${player_name} draws ${num} Deben tokens for having ${amt} fewer Corruption than ${player_name2}'), array(
					'player_id' => $debens_player,
					'player_name' => $players[$debens_player]["player_name"],
					'player_name2' => $players[$debens_other_player]["player_name"],
					'amt' => $diff,
					'num' => count($ds),
					'debens' => $ds
				));
			}
		}
		
		foreach ($players as $pid => $p) {
			$debens = Deben::getOwned($pid);
			foreach ($debens as $d) {
				$deben_points[$pid] += $d["value"];
			}
		}
		
		// - Scores per sets
		// - Scores per Pirogue tokens
		// - Scores per Deben tokens

		foreach ($players as $pid => $p) {
			self::setStat( $resource_points[$pid]["score"], 'points_sets', $pid );
			self::setStat( $deben_points[$pid], 'points_deben', $pid );
			self::setStat( $pirogue_points[$pid], 'points_pirogue', $pid );
			self::setStat( $amount_corruption[$pid], 'corruption', $pid );
			$total_points[$pid] = $pirogue_points[$pid] + $deben_points[$pid] + $resource_points[$pid]["score"];
			$total_and_tie_points[$pid] += $total_points[$pid];
			self::dbSetScore($pid, $total_points[$pid]);
			
			self::notifyAllPlayers( "updateScores", '', array(
				'player_id' => $pid,
				'total_score' => $total_points[$pid],
			));
		}
		
		$winner_id = 0;
		$value = NULL;
		foreach ($total_and_tie_points as $pid => $score) {
			if (! isset($value) || $value < $score) {
				$value = $score;
				$winner_id = $pid;
			}
		}
		
		self::notifyAllPlayers( "gameEndScoring", '', array(
			'total_scores' => $total_points,
			'resource_scores' => $resource_points,
			'deben_scores' => $deben_points,
			'pirogue_scores' => $pirogue_points,
			'winner_player_id' => $winner_id,
		));
		
		$this->gamestate->nextState( '' );
	}
	
	function makeImage($t, $inline = false) {
		$inline_class = $inline ? "sprite-ib " : "";
		if ($t['deck'] == 'character') {
			if ($t['ability'] == 10) {
				return '<div class="'.$inline_class.' sprite sprite-tile sprite-character-10"></div>';
			} else {
				return '<div class="'.$inline_class.' sprite sprite-tile sprite-character-0'.$t['ability'].'"></div>';
			}
		} else if ($t['statue'] == 1) {
			return '<div class="'.$inline_class.' sprite sprite-tile sprite-statue-'.$t['direction'].'"></div>';
		} else {
			if ($t['scarabs'] > 0) {
				$s = 's';
			} else {
				$s = 'x';
			}
			if ($t['deben'] > 0) {
				$d = 'd';
			} else {
				$d = 'x';
			}
			return "<div class=\"$inline_class sprite sprite-tile sprite-$t[resource]-$t[direction]-$s-$d\"></div>";
		}
		return '';
	}
	
	function stPirogue04() {
		if (count(self::availableTiles()) == 0) {
			// Discard the token! There is no where to put it
			$pirogue = Pirogue::get( self::getGameStateValue( 'just_picked_pirogue' ) );
			self::DbQuery("UPDATE pirogue SET location='discard' WHERE pirogue_id=$pirogue[pirogue_id]");
			$pirogue['location'] = 'discard';
			self::notifyAllPlayers( "takePirogue", clienttranslate('There is nowhere to put the Pirogue token, so it gets discarded'), array(
				'pirogue' => $pirogue,
				'discard' => true
			));
			$this->gamestate->nextState( 'next' );
		}
	}
	
	function stPirogue07() {
		// If you have no relevant sold sets, next!
		$player_id = self::getActivePlayerId();
		$sold = Tile::getSold($player_id);
		$found = false;
		foreach ($sold as $t) {
			if ($t['resource'] == 'fish' || $t['resource'] == 'livestock' || $t['resource'] == 'wheat') {
				$found = true;
				break;
			}
		}
		if (! $found) {
			// Discard the token! There is nowhere to put it
			$pirogue = Pirogue::get( self::getGameStateValue( 'just_picked_pirogue' ) );
			self::DbQuery("UPDATE pirogue SET location='discard' WHERE pirogue_id=$pirogue[pirogue_id]");
			$pirogue['location'] = 'discard';
			self::notifyAllPlayers( "takePirogue", clienttranslate('There is nowhere to put the Pirogue token, so it gets discarded'), array(
				'pirogue' => $pirogue,
				'discard' => true
			));
			$this->gamestate->nextState( 'next' );
		}
	}
	
	function stPirogue11() {
		// If you have no relevant sold sets, next!
		$player_id = self::getActivePlayerId();
		$sold = Tile::getSold($player_id);
		$found = false;
		foreach ($sold as $t) {
			if ($t['resource'] == 'marble' || $t['resource'] == 'ebony') {
				$found = true;
				break;
			}
		}
		if (! $found) {
			// Discard the token! There is nowhere to put it
			$pirogue = Pirogue::get( self::getGameStateValue( 'just_picked_pirogue' ) );
			self::DbQuery("UPDATE pirogue SET location='discard' WHERE pirogue_id=$pirogue[pirogue_id]");
			$pirogue['location'] = 'discard';
			self::notifyAllPlayers( "takePirogue", clienttranslate('There is nowhere to put the Pirogue token, so it gets discarded'), array(
				'pirogue' => $pirogue,
				'discard' => true
			));
			$this->gamestate->nextState( 'next' );
		}
	}
	
	function stCharacterMerchant() {
		// If nothing on the board...
		$board = Tile::getBoard();
		if (count($board) == 0) {
			$this->gamestate->nextState( 'next' );
		}
	}
	
	function stCharacterCourtesan() {
		// If you have no relevant sold sets, next!
		$player_id = self::getActivePlayerId();
		$sold = Tile::getSold($player_id);
		$hand = Tile::getHand($player_id);
		$found = false;
		
		foreach ($sold as $s) {
			foreach ($hand as $h) {
				if ($s["resource"] == $h["resource"] || $h["statue"] == 1) {
					// Found!
					$found = true;
					break 2;
				}
			}
		}
		
		if (! $found) {
			$this->gamestate->nextState( 'next' );
		}
	}
	
	function stCharacterThief() {
		$player_id = self::getActivePlayerId();
		$hand = Tile::getHand(self::getPlayerAfter($player_id));
		if (count($hand) == 0) {
			// Nothing to take
			$this->gamestate->nextState( 'next' );
		}
	}
	
	function stCharacterScribe() {
		$player_id = self::getActivePlayerId();
		$hand = Tile::getHand($player_id);
		if (count($hand) <= 6) {
			// Nothing to discard
			$this->gamestate->nextState( 'next' );
		}
	}
	
	function stCharacterVizier() {
		$player_id = self::getActivePlayerId();
		$corruption = Tile::getCorruption(self::getPlayerAfter($player_id));
		if (count($corruption) == 0) {
			// Nothing to take
			$this->gamestate->nextState( 'next' );
		}
	}
	
	function stPirogue() {
		// If there are no Pirogues left, skip this!
		$pirogues = Pirogue::getSlots();
		if (count($pirogues) == 0) {
			$this->gamestate->nextState( 'next' );
		}
	}
	
	function stPickResource() {
		$just_sold = self::getObjectList('SELECT * FROM tile WHERE just_sold = 1');
		
		// If there is any tile with a resource, pick automatically...
		foreach ($just_sold as $tile) {
			if (isset($tile['resource'])) {
				self::answer($tile['resource']);
				break;
			}
		}
	}
	
	function stNextPlayer() {
		$this->activeNextPlayer();
		$this->gamestate->nextState( );
	}
	
	function getCorruptionTiles($inCol, $inRow, $ankhDir = null) {
		$ankh_col = self::getGameStateValue( 'ankh_col' );
		$ankh_row = self::getGameStateValue( 'ankh_row' );
		$ankh_dir = isset($ankhDir) ? $ankhDir : self::getAnkhDir();
		
		if ($ankh_col >= 0) {
			// Only goods lined up with the ankh
			$dx = 0;
			$dy = 0;
			if ($ankh_dir == 'v') {
				$dy = 1;
			} else if ($ankh_dir == 'h') {
				$dx = 1;
			} else if ($ankh_dir == 'f') {
				$dx = 1;
				$dy = -1;
			} else if ($ankh_dir == 'b') {
				$dx = 1;
				$dy = 1;
			}
			$corruption_tiles = [];
			// Positive
			$col = $ankh_col;
			$row = $ankh_row;
			while (true) {
				$col += $dx;
				$row += $dy;
				if ($col >= 0 && $col < 6 && $row >= 0 && $row < 6) {
					if ($col == $inCol && $row == $inRow) {
						return $corruption_tiles;
					}
					$corruption_tiles[] = [$col, $row];
				} else {
					break;
				}
			}
			$corruption_tiles = [];
			// Negative
			$col = $ankh_col;
			$row = $ankh_row;
			while (true) {
				$col -= $dx;
				$row -= $dy;
				if ($col >= 0 && $col < 6 && $row >= 0 && $row < 6) {
					if ($col == $inCol && $row == $inRow) {
						return $corruption_tiles;
					}
					$corruption_tiles[] = [$col, $row];
				} else {
					break;
				}
			}
		}
		return [];
	}
	
	function availableTiles( $dir = null ) {
		$available_tiles = array();
		
		$ankh_col = self::getGameStateValue( 'ankh_col' );
		$ankh_row = self::getGameStateValue( 'ankh_row' );
		$ankh_any_dir = self::getGameStateValue( 'ankh_any_dir' );
		
		$board = Tile::getBoard();
		
		if ($ankh_any_dir && ! isset($dir) && $ankh_col >= 0) {
			// Any dir is ok!
			$available_tiles = array_merge($available_tiles, self::availableTiles( 'h' ));
			$available_tiles = array_merge($available_tiles, self::availableTiles( 'v' ));
			$available_tiles = array_merge($available_tiles, self::availableTiles( 'f' ));
			$available_tiles = array_merge($available_tiles, self::availableTiles( 'b' ));
			return $available_tiles;
		}
		
		$ankh_dir = isset($dir) ? $dir : self::getAnkhDir();
		
		if ($ankh_col >= 0) {
			// Only goods lined up with the ankh
			$dx = 0;
			$dy = 0;
			if ($ankh_dir == 'v') {
				$dy = 1;
			} else if ($ankh_dir == 'h') {
				$dx = 1;
			} else if ($ankh_dir == 'f') {
				$dx = 1;
				$dy = -1;
			} else if ($ankh_dir == 'b') {
				$dx = 1;
				$dy = 1;
			}
			// Positive
			$col = $ankh_col;
			$row = $ankh_row;
			while (true) {
				$col += $dx;
				$row += $dy;
				if ($col >= 0 && $col < 6 && $row >= 0 && $row < 6) {
					$available_tiles[] = [$col, $row, $ankh_dir];
				} else {
					break;
				}
			}
			// Negative
			$col = $ankh_col;
			$row = $ankh_row;
			while (true) {
				$col -= $dx;
				$row -= $dy;
				if ($col >= 0 && $col < 6 && $row >= 0 && $row < 6) {
					$available_tiles[] = [$col, $row, $ankh_dir];
				} else {
					break;
				}
			}
		} else {
			// Only starting goods
			$available_tiles[] = [2, 2, null];
			$available_tiles[] = [2, 3, null];
			$available_tiles[] = [3, 2, null];
			$available_tiles[] = [3, 3, null];
		}
		
		// Filter out any entries which are not actually tiles
		foreach ($available_tiles as $k => $v) {
			$found = false;
			foreach ($board as $t) {
				if ($t["col"] == $v[0] && $t["row"] == $v[1]) {
					$found = true;
					break;
				}
			}
			if (! $found) {
				unset($available_tiles[$k]);
			}
		}
		
		return $available_tiles;
	}
	
	function argPirogue04() {
		$pirogue = Pirogue::get( self::getGameStateValue( 'just_picked_pirogue' ) );
		return array(
			"pirogue" => $pirogue,
			"available_tiles" => self::availableTiles()
		);
	}
	
	function argPirogue07() {
		$pirogue = Pirogue::get( self::getGameStateValue( 'just_picked_pirogue' ) );
		return array(
			"pirogue" => $pirogue
		);
	}
	
	function argPirogue11() {
		$pirogue = Pirogue::get( self::getGameStateValue( 'just_picked_pirogue' ) );
		return array(
			"pirogue" => $pirogue
		);
	}
	
	function argPlayerTurn() {
		$pirogue_board = Pirogue::getBoard();
		$player_id = self::getActivePlayerId();
		if (isset($pirogue_board)) {
			$available_tiles = [
				[$pirogue_board["col"], $pirogue_board["row"]]
			];
			$base_available_tiles = $available_tiles;
		} else {
			$available_tiles = self::availableTiles();
			
			// If you can take from any direction, we use the existing Ankh direction to determine whether you can refill or not
			$ankh_any_dir = self::getGameStateValue( 'ankh_any_dir' );
			if ( $ankh_any_dir ) {
				$ankh_dir = self::getAnkhDir();
				$base_available_tiles = self::availableTiles($ankh_dir);
			} else {
				$base_available_tiles = $available_tiles;
			}
		}
		$deck = Tile::getDeck();
		$hand = Tile::getHand($player_id);
		
		$can_sell = false;
		$has_character = false;
		$num_statues = 0;
		$num_per_resource = array();
		if (! isset($pirogue_board)) {
			foreach ($hand as $t) {
				if ($t["deck"] == "character") {
					$has_character = true;
				}
				if (+$t["statue"]) {
					$num_statues++;
				} if ($t["deck"] == "pharaoh") {
					$resources = explode('-or-', $t["resource"]);
					foreach($resources as $r) {
						if (! isset($num_per_resource[$r])) {
							$num_per_resource[$r] = 0;
						}
						$num_per_resource[$r]++;
					}
				} else {
					$r = $t["resource"];
					if (! isset($num_per_resource[$r])) {
						$num_per_resource[$r] = 0;
					}
					$num_per_resource[$r]++;
				}
			}
			
			// If you have 3 of anything, you can sell
			foreach ($num_per_resource as $r => $num) {
				if ($num + $num_statues >= 3) {
					$can_sell = true;
					break;
				}
			}
		
			// If you don't... but you have 3 statues, you can maybe sell
			if (! $can_sell && $num_statues >= 3) {
				// If you have a sold set?
				$sold = Tile::getSold($player_id);
				if (count($sold) > 0) {
					$can_sell = true;
				}
			}
		}
		
		return array(
			"available_tiles" => $available_tiles,
			"can_take" => count($available_tiles) > 0,
			"can_refill" => count($base_available_tiles) == 0 && count($deck) > 0,
			"can_play_character" => $has_character,
			"can_sell" => $can_sell,
		);
	}

	function argOrientation() {
		$possibleDirections = [];

		foreach(['v', 'h', 'f', 'b'] as $direction) {
			$possibleDirections[$direction] = count(self::availableTiles($direction)) > 0;
		}

		// in case there is no possible direction, we don't gray the buttons
		if (!in_array(true, $possibleDirections)) {
			foreach(['v', 'h', 'f', 'b'] as $direction) {
				$possibleDirections[$direction] = true;
			}
		}

		return [
			'possibleDirections' => $possibleDirections,
		];
	}
	
	function notifyHandChange($player_id) {
		$hand = Tile::getHand( $player_id );
		
		self::notifyAllPlayers( "handUpdate", '', array(
			'player_id' => $player_id,
			'hand_starting_size' => count(array_filter($hand, function($t) {return $t['deck'] == 'starting';})),
			'hand_good_size' => count(array_filter($hand, function($t) {return $t['deck'] == 'good';})),
			'hand_character_size' => count(array_filter($hand, function($t) {return $t['deck'] == 'character';})),
		));
	}
		
	function getGameProgression()
	{
		$perc = self::getGameStateValue( 'tiles_taken' ) / 36;
		if ($perc < 0) return 0;
		if ($perc > 1) return 100;
		return $perc * 100;
	}

	// get score
	function dbGetScore($player_id) {
		return $this->getUniqueValueFromDB("SELECT player_score FROM player WHERE player_id='$player_id'");
	}
	
	// set score
	function dbSetScore($player_id, $count) {
		$this->DbQuery("UPDATE player SET player_score='$count' WHERE player_id='$player_id'");
	}
	
	// set aux score (tie breaker)
	function dbSetAuxScore($player_id, $score) {
		$this->DbQuery("UPDATE player SET player_score_aux=$score WHERE player_id='$player_id'");
	}
	
	// increment score (can be negative too)
	function dbIncScore($player_id, $inc) {
		$count = $this->dbGetScore($player_id);
		if ($inc != 0) {
			$count += $inc;
			$this->dbSetScore($player_id, $count);
		}
		return $count;
	}

	function zombieTurn( $state, $active_player )
	{
		$statename = $state['name'];
		
		if ($state['type'] === "activeplayer") {
			switch ($statename) {
				default:
					$this->gamestate->nextState( "zombiePass" );
					break;
			}

			return;
		}

		if ($state['type'] === "multipleactiveplayer") {
			// Make sure player is in a non blocking status for role turn
			$this->gamestate->setPlayerNonMultiactive( $active_player, 'zombiePass' );
			
			return;
		}

		throw new feException( "Zombie mode not supported at this game state: ".$statename );
	}
	
	function upgradeTableDb( $from_version ) {
		if ($from_version == "210505-1602") {
			self::DbQuery( "ALTER TABLE `player` ADD `player_seen_pirogues` SMALLINT UNSIGNED NOT NULL DEFAULT '0'");
		}
	}
	
	// Hacks
	public static function getCollection( $sql ) { return self::getCollectionFromDb( $sql ); }
	public static function getObjectList( $sql ) { return self::getObjectListFromDB( $sql ); }
	public static function getObject( $sql ) { return self::getObjectFromDB( $sql ); }
	public static function getValue( $sql ) { return self::getUniqueValueFromDB( $sql ); }
}
