const moveTime = 500;
const delayTime = 200;

function handleTakeTile( notif ) {
	console.log(notif);
	const playerId = notif.args.player_id;
	const tile = notif.args.tile;
	const corruptionTiles = notif.args.corruption_tiles;
	const corruptionTilesObjects = notif.args.corruption_tiles_objects;
	const ignoreSelf = notif.args.ignore_self;
	const moveAnkh = notif.args.move_ankh;
	
	if (moveAnkh) {
		// Move Ankh to the place
		const ankh = $('sbk-ankh');
		dojo.removeClass(ankh, 'ankh-hidden tile-x-0 tile-x-1 tile-x-2 tile-x-3 tile-x-4 tile-x-5 tile-y-0 tile-y-1 tile-y-2 tile-y-3 tile-y-4 tile-y-5');
		dojo.addClass(ankh, 'tile-x-'+tile.col+' tile-y-'+tile.row);
		
		// If there is a direction, add it!
		if (tile.direction) {
			dojo.removeClass(ankh, 'sprite-ankh-v sprite-ankh-h sprite-ankh-f sprite-ankh-b');
			dojo.addClass(ankh, 'sprite-ankh-'+tile.direction);
		}
	}
	
	if (ignoreSelf && playerId == this.player_id) {
		return;
	}
	
	{
		// Move tile to the player
		const tileElement = this.getTileAt(tile.col, tile.row);
		if (tileElement) {
			if (playerId == this.player_id) {
				// If me, move to hand and destroy
				dojo.addClass(tileElement, 'opaque');
				this.slideToObjectAndDestroy( tileElement, $('sbk-my-hand'), moveTime, corruptionTiles.length * delayTime );
				
				// Once there, add to hand
				setTimeout(() => {
					const placed = dojo.place( 
						this.makeTileFragment(tile),
						$('sbk-my-hand')
					);
					placed.tile = tile;
					this.addTooltipToTile(placed);
					$('sbk-my-hand').appendChild(document.createTextNode (" "));
					$('sbk-no-hand-message').style.display = 'none';
				}, moveTime + corruptionTiles.length * delayTime);
			} else {
				// Otherwise, move to player board and destroy
				this.slideToObjectAndDestroy( tileElement, "cp_board_p" + playerId, moveTime, corruptionTiles.length * delayTime );
			}
			
			// Increment hand span
			setTimeout(() => {
				$('hand_num_p' + playerId).innerHTML = +$('hand_num_p' + playerId).innerHTML + 1;
			}, corruptionTiles.length * delayTime);
		}
	}
	
	{
		// Move corruptions cards to player
		let delay = 0;
		for (let i in corruptionTiles) {
			const c = corruptionTiles[i];
			const tileElement = this.getTileAt(c[0], c[1]);
			if (tileElement) {
				dojo.addClass(tileElement, 'opaque');
				// Move to player board and destroy
				this.slideToObjectAndDestroy( tileElement, "cp_board_p" + playerId, moveTime, delay );
				delay += delayTime;
				setTimeout(() => {
					$('corruption_num_p' + playerId).innerHTML = +$('corruption_num_p' + playerId).innerHTML + 1;
					if (playerId == this.player_id) {
						tileElement.tile.location = 'corruption';
						// Add character info
						if (tileElement.tile.deck == 'character' && corruptionTilesObjects) {
							for (let j in corruptionTilesObjects) {
								let ct = corruptionTilesObjects[j];
								if (ct.tile_id == tileElement.tile.tile_id) {
									tileElement.tile.ability = ct.ability;
									tileElement.tile.resource = ct.resource;
									tileElement.tile.statue = ct.statue;
								}
							}
						}
						const placed = dojo.place( 
							this.makeTileFragment(tileElement.tile),
							$('sbk-my-corruption')
						);
						placed.tile = tileElement.tile;
						this.addTooltipToTile(placed);
						$('sbk-my-corruption').appendChild(document.createTextNode (" "));
						$('sbk-no-corruption-message').style.display = 'none';
					}
				}, moveTime + delay);
			}
		}
	}	
	
	{
		// Discard any Pirogues
		const q = dojo.query('.sprite-pirogue', $('tiles-holder'));
		for (let i = 0; i < q.length; i++) {
			this.fadeOutAndDestroy( q[i], 500, 0 );
		}
	}
}

function handleDrawTiles( notif ) {
	const playerId = notif.args.player_id;
	const tiles = notif.args.tiles;
	const tilesNum = notif.args.tiles_num;
	const fromDeck = notif.args.from_deck;
	
	if (tilesNum) {
		// Update hand count
		$('hand_num_p' + playerId).innerHTML = +$('hand_num_p' + playerId).innerHTML + +tilesNum;
		
		if (fromDeck) {
			$('deck_size').innerHTML = +$('deck_size').innerHTML - +tilesNum;
		}
	}
	
	if (tiles && this.player_id == playerId) {
		// Add tiles to hand
		for (let i in tiles) {
			const tile = tiles[i];
			const placed = dojo.place( 
				this.makeTileFragment(tile),
				$('sbk-my-hand')
			);
			placed.tile = tile;
			this.addTooltipToTile(placed);
			$('sbk-my-hand').appendChild(document.createTextNode (" "));
			$('sbk-no-hand-message').style.display = 'none';
		}
	}
}

function handleDiscardTile( notif ) {
	const playerId = notif.args.player_id;
	const tile = notif.args.tile;
	const tiles = notif.args.tiles;
	const num = notif.args.num;
	const reason = notif.args.reason;
	const toCorruption = notif.args.to_corruption;
	
	// Remove from span
	if (num != null) {
		$('hand_num_p' + playerId).innerHTML = +$('hand_num_p' + playerId).innerHTML - +num;
	} else {
		$('hand_num_p' + playerId).innerHTML = +$('hand_num_p' + playerId).innerHTML - 1;
	}
	
	if (toCorruption) {
		if (num != undefined) {
			$('corruption_num_p' + playerId).innerHTML = +$('corruption_num_p' + playerId).innerHTML + +num;
		} else {
			$('corruption_num_p' + playerId).innerHTML = +$('corruption_num_p' + playerId).innerHTML + 1;
		}
	}
	
	if (playerId == this.player_id) {
		// If me, actually remove
		if (tiles) {
			for (let i in tiles) {
				const tile = tiles[i];
				const q = dojo.query('.sprite-tile[data-tile-id="'+tile.tile_id+'"]', $('sbk-my-hand'));
				if (q.length > 0) {
					this.fadeOutAndDestroy( q[0], 500, 0 );
				}
				if (toCorruption) {
					const placed = dojo.place( 
						this.makeTileFragment(tile),
						$('sbk-my-corruption')
					);
					placed.tile = tile;
					this.addTooltipToTile(placed);
					$('sbk-my-corruption').appendChild(document.createTextNode (" "));
					$('sbk-no-corruption-message').style.display = 'none';
				}
			}
		} else {
			const q = dojo.query('.sprite-tile[data-tile-id="'+tile.tile_id+'"]', $('sbk-my-hand'));
			if (q.length > 0) {
				this.fadeOutAndDestroy( q[0], 500, 0 );
			}
		}
	} else {
		// If not me, add deben?
		if (reason == 'deben') {
			dojo.place( '<div class="sprite sprite-deben sprite-deben-back"></div> ', $('deben-holder-p'+playerId) );
		}
	}
}

function handleRemoveCorruption( notif ) {
	const playerId = notif.args.player_id;
	const tiles = notif.args.tiles;
	const num = notif.args.num;
	
	// Remove from span
	$('corruption_num_p' + playerId).innerHTML = +$('corruption_num_p' + playerId).innerHTML - +num;
	
	if (playerId == this.player_id) {
		// If me, actually remove
		for (let i in tiles) {
			const tile = tiles[i];
			const q = dojo.query('.sprite-tile[data-tile-id="'+tile.tile_id+'"]', $('sbk-my-corruption'));
			if (q.length > 0) {
				this.fadeOutAndDestroy( q[0], 500, 0 );
			}
		}
	}
}

function handleRevealDebens( notif ) {
	const playerId = notif.args.player_id;
	let debens = notif.args.debens;
	
	if (debens) {
		$('deben-holder-p'+playerId).innerHTML = '';
		for (let i in debens) {
			const d = debens[i];
			dojo.place( '<div class="sprite sprite-deben sprite-deben-'+d.value+'"></div> ', $('deben-holder-p'+playerId) );
		}
	}
}

function handleDeben( notif ) {
	const playerId = notif.args.player_id;
	const deben = notif.args.deben;
	let debens = notif.args.debens;
	
	if (deben) {
		dojo.place( '<div class="sprite sprite-deben sprite-deben-'+deben.value+'"></div> ', $('deben-holder-p'+playerId) );
	} else if (debens != null) {
		for (let i in debens) {
			const d = debens[i];
			dojo.place( '<div class="sprite sprite-deben sprite-deben-'+d.value+'"></div> ', $('deben-holder-p'+playerId) );
		}
	} else {
		if (playerId != this.player_id) {
			dojo.place( '<div class="sprite sprite-deben sprite-deben-back"></div> ', $('deben-holder-p'+playerId) );
		}
	}
}

function handleTakePirogue( notif ) {
	console.log( notif );
	const playerId = notif.args.player_id;
	const pirogue = notif.args.pirogue;
	const targetPlayerId = notif.args.target_player_id;
	const discard = notif.args.discard;
	const board = notif.args.board;
	const soldset = notif.args.soldset;
	
	const q = dojo.query('.sprite-pirogue[data-pirogue-id="'+pirogue.pirogue_id+'"]', $('pirogue-holder'));
	
	const pirogueElement = q.length > 0 ? q[0] : null;
	
	if (targetPlayerId) {
		// Animate to player board, and place it there
		if (pirogueElement) this.slideToObjectAndDestroy( pirogueElement, "cp_board_p" + targetPlayerId, moveTime, 0 );
		
		if (pirogue.ability == 1 || pirogue.ability == 2) {
			$('corruption_pirogue_num_holder_p' + targetPlayerId).style.display = 'inline';
			$('corruption_pirogue_num_p' + targetPlayerId).innerHTML = +$('corruption_pirogue_num_p' + targetPlayerId).innerHTML + +pirogue.ability;
		}
		
		setTimeout(() => {
			dojo.place( 
				'<div class="sprite sprite-pirogue sprite-pirogue-0'+pirogue.ability+'"></div> ', 
				$('pirogue-holder-p'+pirogue.player_id)
			);
		}, moveTime);
	} else if (discard) {
		// Fade out and destroy
		if (pirogueElement) this.fadeOutAndDestroy( pirogueElement, 500, 0 );
	}
	
	if (board) {
		// Add pirogue to the board
		const placed = dojo.place( 
			'<div class="sprite sprite-pirogue sprite-pirogue-0'+pirogue.ability+' token tile-x-'+board[0]+' tile-y-'+board[1]+'"></div>',
			$('tiles-holder')
		);
		placed.style.width = this.pirogueWidth + 'px';
		placed.style.height = this.pirogueHeight + 'px';
		placed.style.marginTop = (-this.pirogueHeight/2) + 'px';
		placed.style.marginLeft = (-this.pirogueWidth/2) + 'px';
		placed.style.pointerEvents = 'none';
	} else if (soldset) {
		// Add pirogue to soldset
		const soldSetsHolder = $('sbk-sets-p' + pirogue.player_id);
		const q = dojo.query('.sold-set[data-resource="'+pirogue.resource+'"]', soldSetsHolder);
		if (q.length > 0) {
			const set = q[0];
			const placed = dojo.place(
				'<div class="sprite sprite-pirogue sprite-pirogue-0'+pirogue.ability+'"></div>', 
				set,
				"first"
			);
			placed.style.width = this.pirogueWidth + 'px';
			placed.style.height = this.pirogueHeight + 'px';
		}
	}
	
	if (+pirogue.ability == 5) {
		// Move corruption to hand!
		$('hand_num_p' + playerId).innerHTML = +$('hand_num_p' + playerId).innerHTML + +$('corruption_num_p' + playerId).innerHTML;
		$('corruption_num_p' + playerId).innerHTML = 0;
		
		if (playerId == this.player_id) {
			const q = dojo.query('.sprite-tile', $('sbk-my-corruption'));
			for (let i = 0; i < q.length; i++) {
				const e = q[i];
				const tile = e.tile;
				tile.location = 'hand';
				e.style.position = 'relative';
				this.slideToObjectAndDestroy( e, 'sbk-my-hand', moveTime, delayTime * i );
				setTimeout(() => {
					const placed = dojo.place( 
						this.makeTileFragment(tile),
						$('sbk-my-hand')
					);
					placed.tile = tile;
					this.addTooltipToTile(placed);
					$('sbk-my-hand').appendChild(document.createTextNode (" "));
					$('sbk-no-hand-message').style.display = 'none';
				}, moveTime + delayTime * i);
			}
		}
	}
}

function handleAnkhDir( notif ) {
	const ankhDir = notif.args.ankh_dir;
	
	const ankh = $('sbk-ankh');
	dojo.removeClass(ankh, 'sprite-ankh-v sprite-ankh-h sprite-ankh-f sprite-ankh-b');
	dojo.addClass(ankh, 'sprite-ankh-'+ankhDir);
}

function handleSold( notif ) {
	const playerId = notif.args.player_id;
	const tiles = notif.args.tiles;
	
	const soldSetsHolder = $('sbk-sets-p' + playerId);
	const resource = tiles[0].resource;
	
	// Hide the message!
	const message = dojo.query(".sbk-no-sold-sets-message", soldSetsHolder);
	message[0].style.display = "none";
	
	$('hand_num_p' + playerId).innerHTML = +$('hand_num_p' + playerId).innerHTML - tiles.length;
	
	let x = 0;
	for (let i in tiles) {
		const tile = tiles[i];
		
		// If there isn't a set for this resource, add one
		const q = dojo.query('.sold-set[data-resource="'+resource+'"]', soldSetsHolder);
		let set = null;
		if (q.length == 0) {
			set = dojo.place(`<div class="sold-set" data-resource="${resource}"></div>`, soldSetsHolder);
		} else {
			set = q[0];
		}
		
		if (playerId == this.player_id) {
			// If me, remove from hand...
			const q = dojo.query('.sprite-tile[data-tile-id="'+tile.tile_id+'"]', $('sbk-my-hand'));
			if (q.length > 0) {
				q[0].style.position = "relative";
				this.slideToObjectAndDestroy(q[0], set, moveTime, delayTime * x);
			}
		} else {
			// Otherwise, temporary over player panel...
			this.slideTemporaryObject( this.makeTileFragment(tile), "sbk-board-holder", "player_board_" + playerId, set, moveTime, delayTime * x );
		}
		x++;
		
		// Once time is up, add to the sold set!
		setTimeout(() => {
			const placed = dojo.place(this.makeTileFragment(tile), set);
			placed.tile = tile;
			this.addTooltipToTile(placed);
			set.appendChild(document.createTextNode (" "));
		}, moveTime + delayTime * x);
	}
}

function handleUpdateScores( notif ) {
	const playerId = notif.args.player_id;
	const resourceScore = notif.args.resource_score;
	const totalScore = notif.args.total_score;
	
	if (resourceScore) {
		for (let r in resourceScore.resources) {
			const s = resourceScore.resources[r];
			const q = dojo.query('.'+r+'_num', $('cp_board_p' + playerId));
			if (q.length > 0) {
				q[0].innerHTML = s[0] + ' &times; ' + s[1];
			}
		}
		this.scoreCtrl[ playerId ].toValue( resourceScore.score );
	} else {
		this.scoreCtrl[ playerId ].toValue( totalScore );
	}
}

function handleRefill( notif ) {
	const newTiles = notif.args.new_tiles;
	
	// Hide the ankh...
	const ankh = $('sbk-ankh');
	dojo.removeClass(ankh, 'tile-x-0 tile-x-1 tile-x-2 tile-x-3 tile-x-4 tile-x-5 tile-y-0 tile-y-1 tile-y-2 tile-y-3 tile-y-4 tile-y-5');
	dojo.addClass(ankh, 'ankh-hidden');
	
	// Add tiles to the board...
	let x = 0;
	for (let i in newTiles) {
		const tile = newTiles[i];
		const placed = dojo.place( 
			this.makeTileFragment(tile),
			$('tiles-holder')
		);
		placed.tile = tile;
		this.addTooltipToTile(placed);
		placed.style.marginTop = (-this.tileWidth/2) + 'px';
		placed.style.marginLeft = (-this.tileWidth/2) + 'px';
		placed.style.opacity = 0;
		
		setTimeout(() => {
			placed.style.opacity = '';
			$('deck_size').innerHTML = +$('deck_size').innerHTML - 1;
		}, delayTime * x);
		x++;
	}
}

function handleHandUpdate( notif ) {
	const playerId = notif.args.player_id;
	const numStarting = notif.args.hand_starting_size;
	const numGood = notif.args.hand_good_size;
	const numCharacter = notif.args.hand_character_size;
	
	if (playerId != this.player_id) {
		const handBreakdownDiv = $('hand-backs-holder-p'+playerId);
		handBreakdownDiv.innerHTML = '';
		for (let i = 0; i < numStarting; i++) {
			handBreakdownDiv.innerHTML += `<div class="sprite sprite-tile sprite-starting-back"></div>`;
		}
		for (let i = 0; i < numGood; i++) {
			handBreakdownDiv.innerHTML += `<div class="sprite sprite-tile sprite-good-back"></div>`;
		}
		for (let i = 0; i < numCharacter; i++) {
			handBreakdownDiv.innerHTML += `<div class="sprite sprite-tile sprite-character-back"></div>`;
		}
	}
}

function handleGameEndScoring( notif ) {
	const resourceScores = notif.args.resource_scores;
	const totalScores = notif.args.total_scores;
	const debenScores = notif.args.deben_scores;
	const pirogueScores = notif.args.pirogue_scores;
	const winnerId = notif.args.winner_player_id;
	
	const x = 500;
	
	dojo.style($('sbk-game-scoring'), 'display', 'block');
	
	// Show table
	let i = 1;
	for( const player_id in this.gamedatas.players ) {
		const player = this.gamedatas.players[player_id];
		
		// Names
		{
			let splitPlayerName = '';
			let chars = player.name.split("");
			for (let j in chars) {
				splitPlayerName += `<span>${chars[j]}</span>`;
			}
			
			const tds = dojo.query('#scoring-row-player-name td');
			const td = tds[i];
			td.innerHTML = `<span style="color:#${player.color};"><span>${splitPlayerName}</span></span>`;
			
			// Bounce the winner
			if (player_id == winnerId) {
				
				setTimeout(function () {
					dojo.addClass(td, 'wavetext');
				}, 18 * x);
			}
		}
		
		// Resources
		const resources = ['fish', 'wheat', 'livestock', 'marble', 'ivory', 'ebony'];
		for (const j in resources) {
			const r = resources[j];
			
			const tds = dojo.query(`#scoring-row-${r} td`);
			const td = tds[i];
			
			const soldSetsHolder = $('sbk-sets-p' + player_id);
			const soldSets = dojo.query('.sold-set[data-resource="'+r+'"]', soldSetsHolder);
			
			setTimeout(function () {
				if (soldSets.length == 0) {
					// Put nothing
					td.innerHTML = `-`;
				} else {
					const soldSet = soldSets[0];
					// Put all of that player's tiles here:
					const tiles = dojo.query('.sprite-tile', soldSet);
					for (let k = 0; k < tiles.length; k++) {
						const tile = tiles[k];
						td.innerHTML += '<div class="'+tile.className+' small-tile"></div> ';
					}
					
					// Then the times
					td.innerHTML += '  &times; ';
					
					// Then the scarab icons
					for (let k = 0; k < +resourceScores[player_id].resources[r][1]; k++) {
						td.innerHTML += `<div class="scarab-icon"></div> `;
					}
					
					// Then the total
					const total = +resourceScores[player_id].resources[r][0] * +resourceScores[player_id].resources[r][1];
					td.innerHTML += ' = ' + total;
				}
			}, j * 2 * x + (i - 1) * x);
		}
		
		// Debens
		{
			const tds = dojo.query('#scoring-row-deben td');
			const td = tds[i];
			
			const debens = dojo.query(`#deben-holder-p${player_id} .sprite-deben`);
			
			setTimeout(function () {
				if (debens.length == 0) {
					td.innerHTML = `-`;
				} else {
					for (let k = 0; k < debens.length; k++) {
						const deben = debens[k];
						td.innerHTML += '<div class="'+deben.className+' small-tile"></div> ';
					}
					td.innerHTML += ' = ' + debenScores[player_id];
				}
			}, 12 * x + (i - 1) * x);
		}
		
		// Priogues
		{
			const tds = dojo.query('#scoring-row-pirogue td');
			const td = tds[i];
			
			const debens = dojo.query(`#pirogue-holder-p${player_id} .sprite-pirogue`);
			
			setTimeout(function () {
				if (debens.length == 0) {
					td.innerHTML = `-`;
				} else {
					let included = 0;
					for (let k = 0; k < debens.length; k++) {
						const deben = debens[k];
						// Only include 6, 8
						if (dojo.hasClass(deben, 'sprite-pirogue-06') || dojo.hasClass(deben, 'sprite-pirogue-08')) {
							included++;
							td.innerHTML += '<div class="'+deben.className+' small-tile"></div> ';
						}
					}
					if (included == 0) {
						td.innerHTML = `-`;
					} else {
						td.innerHTML += ' = ' + pirogueScores[player_id];
					}
				}
			}, 14 * x + (i - 1) * x);
		}
		
		// Totals
		{
			const tds = dojo.query('#scoring-row-total td');
			const td = tds[i];
			
			setTimeout(function () {
				td.innerHTML = totalScores[player_id];
			}, 16 * x + (i - 1) * x);
		}
		
		i++;
	}
}

function setupNotifications( ) {
	console.log( 'Setting up notifications...' );
	
	// Register notifications, and add functions here
	dojo.subscribe("takeTile", handleTakeTile.bind(this));
	dojo.subscribe("discardTile", handleDiscardTile.bind(this));
	dojo.subscribe("drawTiles", handleDrawTiles.bind(this));
	dojo.subscribe("deben", handleDeben.bind(this));
	dojo.subscribe("revealDebens", handleRevealDebens.bind(this));
	dojo.subscribe("ankhDir", handleAnkhDir.bind(this));
	dojo.subscribe("sold", handleSold.bind(this));
	dojo.subscribe("updateScores", handleUpdateScores.bind(this));
	dojo.subscribe("takePirogue", handleTakePirogue.bind(this));
	dojo.subscribe("refill", handleRefill.bind(this));
	dojo.subscribe("removeCorruption", handleRemoveCorruption.bind(this));
	dojo.subscribe("handUpdate", handleHandUpdate.bind(this));
	
	// Game end stuff
	dojo.subscribe("discardTileSlow", handleDiscardTile.bind(this));
	dojo.subscribe("debenSlow", handleDeben.bind(this));
	dojo.subscribe("gameEndScoring", handleGameEndScoring.bind(this));
	this.notifqueue.setSynchronous( 'discardTileSlow', 1000 );
	this.notifqueue.setSynchronous( 'debenSlow', 1000 );
	
	// Depends on nbr. players
	let num_players = Object.keys(gameui.gamedatas.players).length;
	this.notifqueue.setSynchronous( 'gameEndScoring', 11000 );
	
	// Wait 3 seconds after starting next round
	//this.notifqueue.setSynchronous( 'nextRound', 3000 );
}

define({ setupNotifications });