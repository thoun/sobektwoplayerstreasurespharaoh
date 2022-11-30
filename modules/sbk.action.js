function onClickBoard( event ) {
	dojo.stopEvent( event );
	
	if ( ! this.checkAction( 'selectMarketTile' ) ) {
		return;
	}
	
	if ( ! dojo.hasClass(event.target, 'sprite-tile') ) {
		// Clicking something which isn't a tile...
		return;
	}
	
	this.ajaxcall( "/sobektwoplayerstreasurespharaoh/sobektwoplayerstreasurespharaoh/selectMarketTile.html", { 
		lock: true,
		col: event.target.tile.col,
		row: event.target.tile.row
	}, this,
		function( result ) {},
		function( is_error) {}
	);
}

function onClickHand( event ) {
	if( ! this.isCurrentPlayerActive() )
		return;
	
	// Toggle selected on this tile (for now!)
	if ( ! dojo.hasClass(event.target, 'sprite-tile') ) {
		// Clicking something which isn't a tile...
		return;
	}
	
	// Loop through currently existing tiles...
	
	
	if (dojo.hasClass(event.target, 'selected')) {
		dojo.removeClass(event.target, 'selected');
	} else {
		if (this.stateName != "characterScribe") {
			if (+event.target.tile.statue == 0) {
				const preQuery = dojo.query('.sprite-tile.selected', $('sbk-my-hand'));
				let match = true;
				if (preQuery.length > 0) {
					// Deselect these if there any of a different type
					for (let i = 0; i < preQuery.length; i++) {
						if (+preQuery[i].tile.statue == 0) {
							const preQueryResources = preQuery[i].tile.resource.split('-or-');
							const targetResources = event.target.tile.resource.split('-or-');
							const sameResource = preQueryResources.some(preQueryResource => targetResources.includes(preQueryResource));
							if (!sameResource) {
								match = false;
								break;
							}
						}
					}
				}
				if (! match) {
					preQuery.removeClass('selected');
				}
			}
		}
		dojo.addClass(event.target, 'selected');
	}
	
	// Update buttons...
	const postQuery = dojo.query('.sprite-tile.selected', $('sbk-my-hand'));
	let canPlayChar = false;
	let canSell = false;
	
	if (postQuery.length === 1) {
		if (postQuery[0].tile.deck === 'character') {
			canPlayChar = true;
		}
	}
	if (this.stateName == "characterScribe") {
		// Show "discard" only if you have selected (handSize - 6)
		const handQuery = dojo.query('.sprite-tile', $('sbk-my-hand'));
		canSell = (handQuery.length - postQuery.length) == 6;
	} else if ((this.stateName == "characterCourtesan" && (postQuery.length == 1 || postQuery.length == 2)) || 
		(this.stateName != "characterCourtesan" && postQuery.length >= 3)) {
		// Show "sell" only if there are tiles all of the same type
		let types = null;
		canSell = true;
		for (let i = 0; i < postQuery.length; i++) {
			if (+postQuery[i].tile.statue == 0) {
				if (types == null) {
					types = postQuery[i].tile.resource.split('-or-');
				} else {
					postQueryTypes = postQuery[i].tile.resource.split('-or-');
					types = types.filter(type => postQueryTypes.includes(type));
					if (!types.length) {
						canSell = false;
						break;
					}
				}
			}
		}
		if (types && types.length > 1) {
			canSell = false;
		}
	}
	
	if ($('play_button'))
		$('play_button').style.display = canPlayChar ? "inline-block" : "none";
	if ($('sell_button')) {
		dojo.removeClass($('sell_button'), 'bgabutton_gray bgabutton_blue');
		dojo.addClass($('sell_button'), canSell ? 'bgabutton_blue' : 'bgabutton_gray');
	}
}

function onClickPirogue( event ) {
	dojo.stopEvent( event );
	
	if ( ! this.checkAction( 'pickPirogue' ) ) {
		return;
	}
	
	if ( ! dojo.hasClass(event.target, 'sprite-pirogue') ) {
		// Clicking something which isn't a tile...
		return;
	}
	
	this.ajaxcall( "/sobektwoplayerstreasurespharaoh/sobektwoplayerstreasurespharaoh/pickPirogue.html", { 
		lock: true,
		slot: dojo.attr(event.target, "data-pirogue-id"),
	}, this,
		function( result ) {},
		function( is_error) {}
	);
}

function onClickExtra( event ) {
	dojo.stopEvent( event );
	
	if ( dojo.hasClass(event.target, 'sprite-pirogue') ) {
		this.ajaxcall( "/sobektwoplayerstreasurespharaoh/sobektwoplayerstreasurespharaoh/pickPirogue.html", { 
			lock: true,
			slot: dojo.attr(event.target, "data-pirogue-id"),
		}, this,
			function( result ) {},
			function( is_error) {}
		);
	} else if ( dojo.hasClass(event.target, 'sprite-tile') ) {
		// Laziness. New action?
		this.ajaxcall( "/sobektwoplayerstreasurespharaoh/sobektwoplayerstreasurespharaoh/answer.html", { 
			lock: true,
			answer: dojo.attr(event.target, "data-tile-id"),
		}, this,
			function( result ) {},
			function( is_error) {}
		);
	}
}

function onAnswer( event ) {
	dojo.stopEvent( event );
	
	if ( ! this.checkAction( 'answer' ) ) {
		return;
	}
	
	const pieces = event.target.id.split('_');
	
	this.ajaxcall( "/sobektwoplayerstreasurespharaoh/sobektwoplayerstreasurespharaoh/answer.html", { 
		lock: true,
		answer: pieces[0],
	}, this,
		function( result ) {},
		function( is_error) {}
	);
}

function onPlayCharacter() {
	dojo.stopEvent( event );
	
	const q = dojo.query('.sprite-tile.selected', $('sbk-my-hand'));

	if (q.length == 1) {
		playCharacter.bind(this)(q[0].tile.tile_id);
	}
}

function playCharacter(tile_id) {	
	if ( ! this.checkAction( 'playCharacter' ) ) {
		return;
	}
	
	this.ajaxcall( "/sobektwoplayerstreasurespharaoh/sobektwoplayerstreasurespharaoh/playCharacter.html", { 
		lock: true,
		tile_id: tile_id,
	}, this,
		function( result ) {},
		function( is_error) {}
	);
}

function onSellSet() {
	dojo.stopEvent( event );
	
	if ( ! this.checkAction( 'sell' ) ) {
		return;
	}
	
	let tiles = "";
	
	const query = dojo.query('.sprite-tile.selected', $('sbk-my-hand'));
	for (let i = 0; i < query.length; i++) {
		if (tiles.length > 0) {
			tiles += ";";
		}
		tiles += query[i].tile.tile_id;
	}
	
	this.ajaxcall( "/sobektwoplayerstreasurespharaoh/sobektwoplayerstreasurespharaoh/sell.html", { 
		lock: true,
		tile_ids: tiles,
	}, this,
		function( result ) {},
		function( is_error) {}
	);
}

function onRefillBoard() {
	dojo.stopEvent( event );
	
	if ( ! this.checkAction( 'refill' ) ) {
		return;
	}
	
	this.ajaxcall( "/sobektwoplayerstreasurespharaoh/sobektwoplayerstreasurespharaoh/refill.html", { 
		lock: true
	}, this,
		function( result ) {},
		function( is_error) {}
	);
}

define({ onClickBoard, onAnswer, onClickHand, onClickPirogue, onClickExtra, onPlayCharacter, onSellSet, onRefillBoard });