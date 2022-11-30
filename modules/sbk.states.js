function onEnteringState( stateName, args ) {
	console.log( 'Entering state: '+stateName, args );
	this.stateName = stateName;
	
	dojo.query('.sprite-tile', $('sbk-my-hand')).removeClass('selected');
	//this.enableAllPlayerPanels();
	
	$('sbk-extra').style.display = 'none';
	
	if (stateName == "playerTurn") {
		let numberOptions = 0;
		if (args.args.can_sell) numberOptions++;
		if (args.args.can_take) numberOptions++;
		if (args.args.can_refill) numberOptions++;
		if (args.args.can_play_character) numberOptions++;
		
		let framework = "";
		if (numberOptions == 1) {
			framework = _('${you} must ${option_1}');
		} else if (numberOptions == 2) {
			framework = _('${you} must ${option_1} or ${option_2}');
		} else if (numberOptions == 3) {
			framework = _('${you} must ${option_1}, ${option_2} or ${option_3}');
		} else if (numberOptions == 4) {
			framework = _('${you} must ${option_1}, ${option_2}, ${option_3} or ${option_4}');
		}
		
		let currentOption = 1;
		if (args.args.can_take) {
			framework = framework.replace("${option_" + currentOption + "}", _("take a tile from the Market"));
			currentOption++;
		}
		if (args.args.can_refill) {
			framework = framework.replace("${option_" + currentOption + "}", _("refill the Market"));
			currentOption++;
		}
		if (args.args.can_sell) {
			framework = framework.replace("${option_" + currentOption + "}", _("sell a set of goods"));
			currentOption++;
		}
		if (args.args.can_play_character) {
			framework = framework.replace("${option_" + currentOption + "}", _("play a character"));
			currentOption++;
		}
		this.gamedatas.gamestate.descriptionmyturn = framework;
		this.updatePageTitle();
	}
	
	switch( stateName )
	{
		case 'characterMerchant':
			if( this.isCurrentPlayerActive() )
				dojo.query('#tiles-holder .sprite-tile').addClass('available');
			break;
		case 'playerTurn': case 'playerTurn2': case 'pirogue04':
			// Only highlight available tiles
			dojo.query('#tiles-holder .sprite-tile').removeClass('available');
			if( this.isCurrentPlayerActive() ) {
				for (let i in args.args.available_tiles) {
					const coords = args.args.available_tiles[i];
					dojo.query('#tiles-holder .sprite-tile.tile-x-'+coords[0]+'.tile-y-'+coords[1]).addClass('available');
				}
			}
			if (stateName == 'pirogue04') {
				// Face up pirogue...
				const p = args.args.pirogue;
				const q = dojo.query('.pirogue-slot-' + p.slot, $('pirogue-holder'));
				if (q.length > 0) {
					dojo.removeClass(q[0], 'sprite-pirogue-back');
					dojo.addClass(q[0], 'sprite-pirogue-0' + +p.ability);
					
					this.addTooltipToPirogue(q[0], p);
				}
			}
			break;
		case 'pirogue07':
		case 'pirogue11':
			// Face up pirogue...
			const p = args.args.pirogue;
			const q = dojo.query('.pirogue-slot-' + p.slot, $('pirogue-holder'));
			if (q.length > 0) {
				dojo.removeClass(q[0], 'sprite-pirogue-back');
				dojo.addClass(q[0], 'sprite-pirogue-0' + +p.ability);
				
				this.addTooltipToPirogue(q[0], p);
			}
			break;
		case 'pirogue':
			// Face up pirogues!
			if (args.args._private && args.args._private.pirogues) {
				for (let i in args.args._private.pirogues) {
					const p = args.args._private.pirogues[i];
					const q = dojo.query('.pirogue-slot-' + p.slot, $('pirogue-holder'));
					if (q.length > 0) {
						dojo.removeClass(q[0], 'sprite-pirogue-back');
						dojo.addClass(q[0], 'sprite-pirogue-0' + +p.ability);
						this.addTooltipToPirogue(q[0], p);
					}
				}
			}
			break;
		case 'characterArchitect':
			// Show the Pirogues you can take at the top of the board...
			if (args.args._private && args.args._private.pirogues) {
				$('sbk-extra').style.display = 'block';
				$('sbk-extra').innerHTML = '';
				for (let i in args.args._private.pirogues) {
					const pirogue = args.args._private.pirogues[i];
					const placed = dojo.place( 
						'<div data-pirogue-id="'+pirogue.pirogue_id+'" data-slot="'+pirogue.slot+'" class="sprite sprite-pirogue sprite-pirogue-0'+pirogue.ability+'"></div>',
						$('sbk-extra')
					);
					this.addTooltipToPirogue(placed, pirogue);
					$('sbk-extra').appendChild(document.createTextNode (" "));
					placed.style.width = this.pirogueWidth + 'px';
					placed.style.height = this.pirogueHeight + 'px';
				}
			}
			break;
		case 'characterVizier':
			// Show the tiles you can take at the top of the board...
			if (args.args._private && args.args._private.opponents_corruption) {
				$('sbk-extra').style.display = 'block';
				$('sbk-extra').innerHTML = '';
				for (let i in args.args._private.opponents_corruption) {
					const tile = args.args._private.opponents_corruption[i];
					const placed = dojo.place( 
						this.makeTileFragment(tile),
						$('sbk-extra')
					);
					$('sbk-extra').appendChild(document.createTextNode (" "));
					placed.style.width = this.tileWidth + 'px';
					placed.style.height = this.tileWidth + 'px';
				}
			}
			break;
		case 'characterThief':
			// Show the tiles you can take at the top of the board...
			if (args.args._private && args.args._private.num_per_deck) {
				$('sbk-extra').style.display = 'block';
				$('sbk-extra').innerHTML = '';
				for (let deck in args.args._private.num_per_deck) {
					const num = args.args._private.num_per_deck[deck];
					for (let i = 0; i < num; i++) {
						const placed = dojo.place( 
							`<div data-tile-id="${deck}" class="sprite sprite-tile sprite-${deck}-back" style="width: ${this.tileWidth}px;height: ${this.tileWidth}px"></div>`,
							$('sbk-extra')
						);
						$('sbk-extra').appendChild(document.createTextNode (" "));
						placed.style.width = this.tileWidth + 'px';
						placed.style.height = this.tileWidth + 'px';
					}
				}
			}
			break;
		case 'orientation':
			// Show the orientation wheel
			// - Create a div (tile sized)
			// - Place the direction arrows on it
			// - Place it on the ankh (via css so resize works)
			// - Connect hovering to rotating ankh + clicking to choosing
			// - Maybe action buttons to, for when on mobile?
			break;
		case 'dummmy':
			break;
	}
}

function onLeavingState( stateName ) {
	console.log( 'Leaving state: '+stateName );
	
	switch( stateName )
	{
		case 'playerTurn': case 'playerTurn2': case 'pirogue04': case 'characterMerchant':
			dojo.query('#tiles-holder .sprite-tile').removeClass('available');
			break;
		case 'orientation':
			// Hide the orientation wheel
			// ...
			break;
		case 'pirogue':
			// Face down pirogues!
			// const q = dojo.query('.sprite-pirogue', $('pirogue-holder'));
			// for (let i = 0; i < q.length; i++) {
			// 	dojo.removeClass(q[i], 'sprite-pirogue-01 sprite-pirogue-02 sprite-pirogue-03 sprite-pirogue-04 sprite-pirogue-05 sprite-pirogue-06 sprite-pirogue-07 sprite-pirogue-08 sprite-pirogue-09 ');
			// 	dojo.addClass(q[i], 'sprite-pirogue-back');
			// 	
			// 	this.removeTooltip(q[i].id);
			// }
			break;
		case 'dummmy':
			break;
	}
}

function onUpdateActionButtons( stateName, args ) {
	if( this.isCurrentPlayerActive() )
	{				
		switch( stateName )
		{
			case 'playerTurn':
				// If there are no available tiles, you can refill the board
				if (args.can_refill) {
					this.addActionButton( 'refill_button', _('Refill board'), 'onRefillBoard', null, null, 'blue' );
				}
				if (args.can_sell) {
					this.addActionButton( 'sell_button', _('Sell set'), 'onSellSet', null, null, 'gray' );
					dojo.addClass($('sell_button'), "not-allowed");
				}
				this.addActionButton( 'play_button', _('Play character'), 'onPlayCharacter' );
				$('play_button').style.display = "none";
				
				// $arg["can_sell"] && ! $arg["can_take"] && ! $arg["can_refill"] && ! $arg["can_play_character"]
				
				break;
			case 'characterCourtesan':
				this.addActionButton( 'sell_button', _('Add to set'), 'onSellSet', null, null, 'gray' );
				break;
			case 'characterScribe':
				this.addActionButton( 'sell_button', _('Discard'), 'onSellSet', null, null, 'gray' );
				break;
			case 'deben':
				this.addActionButton( 'yes_button', _('Take Deben'), 'onAnswer' );
				this.addActionButton( 'no_button', _('Keep tile'), 'onAnswer' );
				break;
			case 'orientation':
				['v', 'f', 'h', 'b'].forEach(orientation => {
					this.addActionButton( `${orientation}_button`, `<div class="sprite sprite-ankh sprite-ankh-${orientation}"></div>`, 'onAnswer' );

					document.getElementById(`${orientation}_button`).classList.toggle('disabled', !args.possibleDirections[orientation]);
				});
				break;
			case 'pickResource':
				this.addActionButton( 'wheat_button', _('Wheat'), 'onAnswer' );
				this.addActionButton( 'fish_button', _('Fish'), 'onAnswer' );
				this.addActionButton( 'livestock_button', _('Livestock'), 'onAnswer' );
				this.addActionButton( 'marble_button', _('Marble'), 'onAnswer' );
				this.addActionButton( 'ebony_button', _('Ebony'), 'onAnswer' );
				this.addActionButton( 'ivory_button', _('Ivory'), 'onAnswer' );
				break;
			case 'pirogue07':
				const soldSetsHolder = $('sbk-sets-p' + this.player_id);
				if (dojo.query('.sold-set[data-resource="wheat"]', soldSetsHolder).length > 0)
					this.addActionButton( 'wheat_button', _('Wheat'), 'onAnswer' );
				if (dojo.query('.sold-set[data-resource="fish"]', soldSetsHolder).length > 0)
					this.addActionButton( 'fish_button', _('Fish'), 'onAnswer' );
				if (dojo.query('.sold-set[data-resource="livestock"]', soldSetsHolder).length > 0)
					this.addActionButton( 'livestock_button', _('Livestock'), 'onAnswer' );
				break;
			case 'pirogue11':
				const soldSetsHolder11 = $('sbk-sets-p' + this.player_id);
				if (dojo.query('.sold-set[data-resource="marble"]', soldSetsHolder11).length > 0)
					this.addActionButton( 'marble_button', _('Marble'), 'onAnswer' );
				if (dojo.query('.sold-set[data-resource="ebony"]', soldSetsHolder11).length > 0)
					this.addActionButton( 'ebony_button', _('Ebony'), 'onAnswer' );
				break;
			case 'characterHighPriest':
				const resources = ['wheat', 'fish', 'livestock', 'marble', 'ebony', 'ivory'];
				const counts = { wheat: 0, fish: 0, livestock: 0, marble: 0, ebony: 0, ivory: 0, statue: 0 };
				const q = dojo.query('.sprite-tile', $('sbk-my-corruption'));
				for (let i = 0; i < q.length; i++) {
					const tile = q[i].tile;
					let keys = typeof tile.resource == 'string' ? tile.resource.split('-or-') : [];
					if (+tile.statue) {
						keys = ["statue"];
					}
					keys.forEach(key => {
						if (! counts[key]) {
							counts[key] = 0;
						}
						counts[key]++;
					});
				}
				this.addActionButton( 'statue_button', _('Statues') + ' ('+counts['statue']+')', 'onAnswer' );
				for (let i in resources) {
					const r = resources[i];
					const capR = r.charAt(0).toUpperCase() + r.slice(1);
					this.addActionButton( r + '_button', _(capR) + ' ('+counts[r]+')', 'onAnswer' );
				}
				break;
			case 'characterSpy':
				args.playedCharacters.forEach(character => {
					this.addActionButton( `playCharacter${character['tile_id']}_button`, `<div class="sprite sprite-tile  sprite-character-${character['ability'].toString().padStart(2, '0')}"></div>`, () => playCharacter.bind(this)(character['tile_id']) )
					const element = $(`playCharacter${character['tile_id']}_button`);
					this.addTooltipToTile(element, character);
				});
		}
	}
}

define({ onEnteringState, onLeavingState, onUpdateActionButtons });