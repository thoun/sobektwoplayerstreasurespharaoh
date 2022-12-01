<?php

trait DebugUtilTrait {

//////////////////////////////////////////////////////////////////////////////
//////////// Utility functions
////////////

    function debugSetup() {
        if ($this->getBgaEnvironment() != 'studio') { 
            return;
        } 

        $this->debugSetMermaids();
        $this->debugSetMermaidOnDeckTop();
    }

    function debugTakeAllDeck() {
        $playerId = 2343492;
        SobekTwoPlayersTreasuresPharaoh::DbQuery( "UPDATE tile SET location = 'hand', player_id = $playerId WHERE location = 'deck'" );
    }

    function debugTakeAllPharaoh() {
        $playerId = 2343492;
        SobekTwoPlayersTreasuresPharaoh::DbQuery( "UPDATE tile SET location = 'hand', player_id = $playerId WHERE deck = 'pharaoh'" );
    }

    public function debugReplacePlayersIds() {
        if ($this->getBgaEnvironment() != 'studio') { 
            return;
        } 

		// These are the id's from the BGAtable I need to debug.
		$ids = [
            92432695,
            87587865
		];

		// Id of the first player in BGA Studio
		$sid = 2343492;
		
		foreach ($ids as $id) {
			// basic tables
			$this->DbQuery("UPDATE player SET player_id=$sid WHERE player_id = $id" );
			$this->DbQuery("UPDATE global SET global_value=$sid WHERE global_value = $id" );

			// 'other' game specific tables. example:
			// tables specific to your schema that use player_ids
			$this->DbQuery("UPDATE tile SET player_id=$sid WHERE player_id = $id" );
			$this->DbQuery("UPDATE deben SET player_id=$sid WHERE player_id = $id" );
			$this->DbQuery("UPDATE pirogue SET player_id=$sid WHERE player_id = $id" );
			$this->DbQuery("UPDATE royal_corruption SET player_id=$sid WHERE player_id = $id" );
			
			++$sid;
		}
	}

    function debug($debugData) {
        if ($this->getBgaEnvironment() != 'studio') { 
            return;
        }die('debug data : '.json_encode($debugData));
    }
}
