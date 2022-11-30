function onResize(animate = true) {
	console.log("Resizing");
	
	// Based on the size of the game-holder, set the correct position/rotation of the pad
	// TODO : There should be a minimum height
	// If this is negative, or tiny, then fill up all space instead?
	const holderRect = $('page-content').getBoundingClientRect();
	const preRect = $('sbk-game-holder').getBoundingClientRect();
	let targetHeight = (window.innerHeight - (window.pageYOffset + preRect.top)) - 10;
	const minimumHeight = 400;
	if (targetHeight < minimumHeight) {
		//alert(targetHeight + ' < 0: ' + holderRect.height);
		targetHeight = Math.max(minimumHeight, holderRect.height);
	}
	
	let targetWidth = targetHeight * (2244/1955);
	let totalWidth = targetWidth + (targetHeight * 0.128);
	if (totalWidth > preRect.width) {
		targetWidth = preRect.width - (targetHeight * 0.128);
		targetHeight = targetWidth / (2244/1955);
	}
	
	// Size pirogue tokens for display
	const pirogueWidth = targetHeight * 0.128;
	const pirogueHeight = targetHeight * 0.111;
	
	const tileWidth = targetHeight * 0.131;
	
	dojo.removeClass($('sbk-game-holder'), 'layout-wide layout-narrow');
	if (preRect.width - targetWidth > 400) {
		dojo.addClass($('sbk-game-holder'), 'layout-wide');
	} else {
		dojo.addClass($('sbk-game-holder'), 'layout-narrow');
	}
	
	this.tileWidth = tileWidth;
	this.pirogueWidth = pirogueWidth;
	this.pirogueHeight = pirogueHeight;
	
	// Size board for display
	dojo.style($('sbk-board-holder'), {
		width: targetWidth + 'px',
		height: targetHeight + 'px',
		marginLeft: (pirogueWidth * 0.5) + 'px'
	});
	
	dojo.query('#sbk-game-holder .sprite-pirogue').style({
		width: (pirogueWidth) + 'px',
		height: (pirogueHeight) + 'px',
		marginTop: (-pirogueHeight * 0.5) + 'px',
		marginLeft: (-pirogueWidth * 0.5) + 'px'
	});
	dojo.query('#sbk-game-holder .sprite-tile').style({
		width: (tileWidth) + 'px',
		height: (tileWidth) + 'px',
		marginTop: (-tileWidth * 0.5) + 'px',
		marginLeft: (-tileWidth * 0.5) + 'px'
	});
	dojo.query('#sbk-game-holder .sprite-ankh').style({
		width: (tileWidth) + 'px',
		height: (tileWidth) + 'px',
		marginTop: (-tileWidth * 0.5) + 'px',
		marginLeft: (-tileWidth * 0.5) + 'px'
	});
}

function setup ( gamedatas ) {
	console.log( "Starting game setup", gamedatas );
	this.gamedatas = gamedatas;

	if (!gamedatas.treasuresOfThePharaohExpansion) {
		this.dontPreloadImage(`royal-corruption.png`);
		this.dontPreloadImage(`tiles-treasures-of-the-pharaoh.jpg`);
	}
	
	const numPlayers = Object.keys(gameui.gamedatas.players).length;
	
	// Setting up player boards
	this.playersById = {};
	
	onResize.call(this, false);
	
	// Players
	for( let playerId in gamedatas.players ) {
		const po = gamedatas.players[playerId];
		const player = new Player(this, po);
		this.playersById[playerId] = player;
		
		const playerBoardDiv = $('player_board_'+playerId);
		dojo.place( this.format_block('jstpl_player_board', po ), playerBoardDiv );
		
		// Player hand breakdowns!
		if (playerId != this.player_id) {
			const handBreakdownDiv = $('hand-backs-holder-p'+playerId);
			for (let i = 0; i < po.hand_starting_size; i++) {
				handBreakdownDiv.innerHTML += `<div class="sprite sprite-tile sprite-starting-back"></div>`;
			}
			for (let i = 0; i < po.hand_good_size; i++) {
				handBreakdownDiv.innerHTML += `<div class="sprite sprite-tile sprite-good-back"></div>`;
			}
			for (let i = 0; i < po.hand_character_size; i++) {
				handBreakdownDiv.innerHTML += `<div class="sprite sprite-tile sprite-character-back"></div>`;
			}
			for (let i = 0; i < po.hand_pharaoh_size; i++) {
				handBreakdownDiv.innerHTML += `<div class="sprite sprite-tile sprite-pharaoh-back"></div>`;
			}
		}
	
		if (po.debens != null) {
			for (let i in po.debens) {
				const deben = po.debens[i];
				
				dojo.place( '<div class="sprite sprite-deben sprite-deben-'+deben.value+'"></div> ', $('deben-holder-p'+playerId) );
			}
		} else {
			for (let i = 0; i < po.deben_count; i++) {
				dojo.place( '<div class="sprite sprite-deben sprite-deben-back"></div> ', $('deben-holder-p'+playerId) );
			}
		}
	
		if (po.royalCorruptions != null) {
			for (let i in po.royalCorruptions) {
				const royalCorruption = po.royalCorruptions[i];
				
				dojo.place( '<div class="sprite sprite-royal-corruption sprite-royal-corruption-'+royalCorruption.value+'"></div> ', $('royal-corruption-holder-p'+playerId) );
			}
		} else {
			for (let i = 0; i < po.royalCorruption_count; i++) {
				dojo.place( '<div class="sprite sprite-royal-corruption sprite-royal-corruption-back"></div> ', $('royal-corruption-holder-p'+playerId) );
			}
		}
		
		dojo.place( this.format_block('jstpl_player_sold_sets', {
			title: playerId == this.player_id ? 
				_('My sold goods') : 
				dojo.string.substitute(
					_('${username}\'s sold goods'),
					{ username: po.name }
				),
			id: po.id,
			message: _("No sold goods.")
		} ), $('sbk-sold-sets-holder'), playerId == this.player_id ? "first" : "last" );
		
		// Add their sold sets...
		const soldSetsHolder = $('sbk-sets-p' + playerId);
		for (let i in po.sold) {
			const tile = po.sold[i];
			const resource = tile.resource;
			
			// If there isn't a set for this resource, add one
			const q = dojo.query('.sold-set[data-resource="'+resource+'"]', soldSetsHolder);
			let set = null;
			if (q.length == 0) {
				set = dojo.place(`<div class="sold-set" data-resource="${resource}"></div>`, soldSetsHolder);
			} else {
				set = q[0];
			}
			
			const placed = dojo.place(this.makeTileFragment(tile), set);
			placed.tile = tile;
			this.addTooltipToTile(placed);
			set.appendChild(document.createTextNode (" "));
			
			// Hide the message!
			const message = dojo.query(".sbk-no-sold-sets-message", soldSetsHolder);
			message[0].style.display = "none";
		}
		
		// resource_score
		for (let r in po.resource_score.resources) {
			const s = po.resource_score.resources[r];
			const q = dojo.query('.'+r+'_num', $('cp_board_p' + playerId));
			if (q.length > 0) {
				q[0].innerHTML = s[0] + ' &times; ' + s[1];
			}
		}
	}
	
	// If you have a hand, place it!
	let me = gamedatas.players[this.player_id];
	if (me && me.hand != null) {
		for( let i in me.hand ) {
			const tile = me.hand[i];
			const placed = dojo.place( 
				this.makeTileFragment(tile),
				$('sbk-my-hand')
			);
			placed.tile = tile;
			this.addTooltipToTile(placed);
			$('sbk-my-hand').appendChild(document.createTextNode (" "));
		}
		$('sbk-no-hand-message').style.display = me.hand.length == 0 ? 'block' : 'none';
	} else {
		// Delete the div
		$('sbk-hand-holder').remove();
	}
	if (me && me.corruption != null) {
		for( let i in me.corruption ) {
			const tile = me.corruption[i];
			const placed = dojo.place( 
				this.makeTileFragment(tile),
				$('sbk-my-corruption')
			);
			placed.tile = tile;
			this.addTooltipToTile(placed);
			$('sbk-my-corruption').appendChild(document.createTextNode (" "));
		}
		$('sbk-no-corruption-message').style.display = me.corruption.length == 0 ? 'block' : 'none';
	} else {
		// Delete the div
		$('sbk-corruption-holder').remove();
	}
	
	// Board
	for ( let i in gamedatas.board ) {
		const tile = gamedatas.board[i];
		const placed = dojo.place( 
			this.makeTileFragment(tile),
			$('tiles-holder')
		);
		placed.tile = tile;
		this.addTooltipToTile(placed);
	}
	
	const pirogueCorruptionPerPlayer = {};
	
	// Pirogues
	for ( let i in gamedatas.pirogues ) {
		const pirogue = gamedatas.pirogues[i];
		if (pirogue.location == 'slot') {
			if (pirogue.ability != null) {
				const p = dojo.place( 
					'<div data-pirogue-id="'+pirogue.pirogue_id+'" data-slot="'+pirogue.slot+'" class="sprite sprite-pirogue sprite-pirogue-0'+pirogue.ability+' token pirogue-slot-'+pirogue.slot+'"></div>',
					$('pirogue-holder')
				);
				this.addTooltipToPirogue(p, pirogue);
			} else {
				dojo.place( 
					'<div data-pirogue-id="'+pirogue.pirogue_id+'" data-slot="'+pirogue.slot+'" class="sprite sprite-pirogue sprite-pirogue-back token pirogue-slot-'+pirogue.slot+'"></div>',
					$('pirogue-holder')
				);
			}
		} else if (pirogue.location == 'player') {
			if (pirogue.ability == 1 || pirogue.ability == 2) {
				if ( ! pirogueCorruptionPerPlayer[pirogue.player_id] ) {
					pirogueCorruptionPerPlayer[pirogue.player_id] = 0;
				}
				pirogueCorruptionPerPlayer[pirogue.player_id] += +pirogue.ability;
			}
			dojo.place( 
				'<div class="sprite sprite-pirogue sprite-pirogue-0'+pirogue.ability+'"></div> ', 
				$('pirogue-holder-p'+pirogue.player_id)
			);
		} else if (pirogue.location == 'board') {
			const placed = dojo.place( 
				'<div class="sprite sprite-pirogue sprite-pirogue-0'+pirogue.ability+' token tile-x-'+pirogue.col+' tile-y-'+pirogue.row+'"></div>',
				$('tiles-holder')
			);
			placed.style.width = this.pirogueWidth + 'px';
			placed.style.height = this.pirogueHeight + 'px';
			placed.style.pointerEvents = 'none';
		} else if (pirogue.location == 'soldset') {
			// Place pirogues in sold sets
			const soldSetsHolder = $('sbk-sets-p' + pirogue.player_id);
			const q = dojo.query('.sold-set[data-resource="'+pirogue.resource+'"]', soldSetsHolder);
			if (q.length > 0) {
				const set = q[0];
				const placed = dojo.place(
					'<div class="sprite sprite-pirogue sprite-pirogue-0'+pirogue.ability+'"></div>', 
					set,
					"first"
				);
			}
		}
	}
	
	// Pirogue corruption
	for (const pid in pirogueCorruptionPerPlayer) {
		$('corruption_pirogue_num_holder_p' + pid).style.display = 'inline';
		$('corruption_pirogue_num_p' + pid).innerHTML = pirogueCorruptionPerPlayer[pid];
	}
	
	// Ankh
	if (gamedatas.ankh.col >= null) {
		const a = gamedatas.ankh;
		const placed = dojo.place( 
			`<div id="sbk-ankh" class="sprite sprite-ankh sprite-ankh-${a.dir} token tile-x-${a.col} tile-y-${a.row}"></div>`,
			$('tiles-holder')
		);
	} else {
		const placed = dojo.place( 
			`<div id="sbk-ankh" class="sprite sprite-ankh sprite-ankh-h token ankh-hidden"></div>`,
			$('tiles-holder')
		);
	}
	
	$('deck_size').innerHTML = gamedatas.deck_size;
	
	// Tooltips
	this.addTooltipToClass( 'tt-ivory', _( 'Ivory' ), "" );
	this.addTooltipToClass( 'tt-marble', _( 'Marble' ), "" );
	this.addTooltipToClass( 'tt-ebony', _( 'Ebony' ), "" );
	this.addTooltipToClass( 'tt-fish', _( 'Fish' ), "" );
	this.addTooltipToClass( 'tt-livestock', _( 'Livestock' ), "" );
	this.addTooltipToClass( 'tt-wheat', _( 'Wheat' ), "" );
	this.addTooltipToClass( 'tt-hand', _( 'Tiles in hand' ), "" );

	let tilesInHandHtml = `
		<div>${_( 'Tiles in hand' )}</div>
		<div>
			<div class="sprite-tile-holder"><div class="sprite sprite-tile sprite-starting-back"></div> ${_( 'Starting tile' )}</div>
			<div class="sprite-tile-holder"><div class="sprite sprite-tile sprite-good-back"></div> ${_( 'Goods tile' )}</div>
			<div class="sprite-tile-holder"><div class="sprite sprite-tile sprite-character-back"></div> ${_( 'Character tile' )}</div>`;
	if (gamedatas.treasuresOfThePharaohExpansion) {
		tilesInHandHtml += `<div class="sprite-tile-holder"><div class="sprite sprite-tile sprite-pharaoh-back"></div> ${_( 'Pharaoh tile' )}</div>`;
	}

	tilesInHandHtml += `	</div>`;

	this.addTooltipHtmlToClass( 'hand-backs-holder', tilesInHandHtml, 0 );
	//this.addTooltipToClass( 'hand-backs-holder', _( 'Tiles in hand' ), "" );
	this.addTooltipToClass( 'deben-holder', _( 'Deben tokens' ), "" );
	this.addTooltipToClass( 'pirogue-holder', _( 'Pirogue tokens' ), "" );
	this.addTooltipToClass( 'tt-corruption', _( 'Tiles on Corruption board (+ from Pirogue tokens)' ), "" );
	
	onResize.call(this, false);
	
	// Todo: investigate using onScreenWidthChange instead?
	dojo.connect(window, "onresize", this.debounce(onResize.bind(this, true), 200));
	
	// Clickers
	dojo.connect($('tiles-holder'), 'onclick', this, 'onClickBoard');
	dojo.connect($('sbk-my-hand'), 'onclick', this, 'onClickHand');
	dojo.connect($('pirogue-holder'), 'onclick', this, 'onClickPirogue');
	dojo.connect($('sbk-extra'), 'onclick', this, 'onClickExtra');
	
	// Modal
	dojo.connect($('sbk-modal'), "onclick", function() {
		dojo.style($('sbk-modal'), {
			display: 'none'
		});
	});
	
	// Add player aid buttons
	const modalButtonHolder = dojo.place('<div id="right-side-buttons" style="text-align: center;"></div>', $('right-side-second-part'), 'before');
	const playerAid = dojo.place('<button type="button" class="action-button bgabutton bgabutton_blue">'+_('Characters reference')+'</button>', modalButtonHolder, 'first');
	const pirogueAid = dojo.place('<button type="button" class="action-button bgabutton bgabutton_blue">'+_('Pirogue reference')+'</button>', modalButtonHolder, 'first');
	dojo.connect(playerAid, "onclick", function() {
		dojo.style($('sbk-modal'), {
			display: 'block'
		});
		dojo.style($('player-aid-character-wrapper'), {
			display: 'flex'
		});
		dojo.style($('player-aid-pirogue-wrapper'), {
			display: 'none'
		});
		dojo.style($('player-aid-played-characters'), {
			display: 'none'
		});
	});
	dojo.connect(pirogueAid, "onclick", function() {
		dojo.style($('sbk-modal'), {
			display: 'block'
		});
		dojo.style($('player-aid-character-wrapper'), {
			display: 'none'
		});
		dojo.style($('player-aid-pirogue-wrapper'), {
			display: 'flex'
		});
		dojo.style($('player-aid-played-characters'), {
			display: 'none'
		});
	});

	if (gamedatas.treasuresOfThePharaohExpansion) {
		const playedCharactersAid = dojo.place('<button type="button" class="action-button bgabutton bgabutton_blue">'+_('Played characters')+'</button>', modalButtonHolder, 'last');
		dojo.connect(playedCharactersAid, "onclick", function() {
			dojo.style($('sbk-modal'), {
				display: 'block'
			});
			dojo.style($('player-aid-character-wrapper'), {
				display: 'none'
			});
			dojo.style($('player-aid-pirogue-wrapper'), {
				display: 'none'
			});
			dojo.style($('player-aid-played-characters-wrapper'), {
				display: 'flex'
			});
		});

		if (gamedatas.playedCharacters.length) {
			gamedatas.playedCharacters.forEach(character => dojo.place('<div class="sprite sprite-tile sprite-character-' + character.ability.padStart(2, '0') + '"></div>', 'player-aid-played-characters'));
		} else {
			$('player-aid-played-characters').innerHTML = '<div id="no-played-characters">' + _('No played character') + '</div>';
		}
	}
	
	const toTranslate = document.getElementsByClassName('sbk-to-localise');
	for (let i = 0; i < toTranslate.length; i++) {
		const e = toTranslate[i];
		const english = dojo.attr(e, 'data-text');
		e.innerHTML = _(english).replace('${num}', '');
	}

	if (gamedatas.treasuresOfThePharaohExpansion) {
		dojo.style($('player-aid-character-pharaoh'), {
			display: 'block'
		});
		dojo.style($('player-aid-pirogue-pharaoh'), {
			display: 'block'
		});
	}
	
	this.setupNotifications();

	console.log( "Ending game setup" );
}

define({ setup });