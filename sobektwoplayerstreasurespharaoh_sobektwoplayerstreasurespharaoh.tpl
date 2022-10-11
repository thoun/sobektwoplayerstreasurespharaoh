{OVERALL_GAME_HEADER}

<div id="background-holder"><div id="background-light"></div></div>

<div id="sbk-game-holder">
	<div id="sbk-extra" class="normal-tiles whiteblock">
	</div>
	<div id="sbk-main-holder">
		<div id="sbk-board-holder">
			<div id="deck-holder">
				<div class="sprite sprite-tile sprite-good-back"></div>
				<span id="deck_size"></span>
			</div>
			<div id="pirogue-holder">
			</div>
			<div id="tiles-holder">
			</div>
			<div id="sbk-game-scoring">
				<table>
					<tr id="scoring-row-player-name" class="line-below">
						<td class="first-column"></td>
						<td class="player-column">&nbsp;</td>
						<td class="player-column">&nbsp;</td>
					</tr>
					<tr id="scoring-row-fish">
						<td class="first-column">
							<div class="sprite sprite-tile sprite-fish-h-x-x"></div>
						</td>
						<td class="player-column">&nbsp;</td>
						<td class="player-column">&nbsp;</td>
					</tr>
					<tr id="scoring-row-wheat">
						<td class="first-column">
							<div class="sprite sprite-tile sprite-wheat-h-x-x"></div>
						</td>
						<td class="player-column">&nbsp;</td>
						<td class="player-column">&nbsp;</td>
					</tr>
					<tr id="scoring-row-livestock">
						<td class="first-column">
							<div class="sprite sprite-tile sprite-livestock-h-x-x"></div>
						</td>
						<td class="player-column">&nbsp;</td>
						<td class="player-column">&nbsp;</td>
					</tr>
					<tr id="scoring-row-marble">
						<td class="first-column">
							<div class="sprite sprite-tile sprite-marble-h-x-x"></div>
						</td>
						<td class="player-column">&nbsp;</td>
						<td class="player-column">&nbsp;</td>
					</tr>
					<tr id="scoring-row-ivory">
						<td class="first-column">
							<div class="sprite sprite-tile sprite-ivory-h-x-x"></div>
						</td>
						<td class="player-column">&nbsp;</td>
						<td class="player-column">&nbsp;</td>
					</tr>
					<tr id="scoring-row-ebony">
						<td class="first-column">
							<div class="sprite sprite-tile sprite-ebony-h-x-x"></div>
						</td>
						<td class="player-column">&nbsp;</td>
						<td class="player-column">&nbsp;</td>
					</tr>
					<tr id="scoring-row-deben">
						<td class="first-column">
							<div class="sprite sprite-deben sprite-deben-back"></div>
						</td>
						<td class="player-column">&nbsp;</td>
						<td class="player-column">&nbsp;</td>
					</tr>
					<tr id="scoring-row-pirogue" class="line-below">
						<td class="first-column">
							<div class="sprite sprite-pirogue sprite-pirogue-back"></div>
						</td>
						<td class="player-column">&nbsp;</td>
						<td class="player-column">&nbsp;</td>
					</tr>
					<tr id="scoring-row-total">
						<td class="first-column"></td>
						<td class="player-column">&nbsp;</td>
						<td class="player-column">&nbsp;</td>
					</tr>
				</table>
			</div>
		</div>
		<div id="sbk-sidebar">
			<div id="sbk-hand-holder" class="normal-tiles whiteblock">
				<h3 class="sbk-to-localise" data-text="My hand" id="txt-my-hand"></h3>
				<i class="sbk-to-localise" data-text="No tiles in hand." id="sbk-no-hand-message"></i>
				<div id="sbk-my-hand" class="sbk-hand">
				</div>
			</div>
			<div id="sbk-corruption-holder" class="normal-tiles whiteblock">
				<h3 class="sbk-to-localise" data-text="My corruption" id="txt-my-corruption"></h3>
				<i class="sbk-to-localise" data-text="No tiles in Corruption stack." id="sbk-no-corruption-message"></i>
				<div id="sbk-my-corruption" class="sbk-hand">
				</div>
			</div>
		</div>
	</div>
	<div id="sbk-sold-sets-holder">
	</div>
</div>

<div id="sbk-modal">
	<div id="player-aid-character">
		<h1 class="sbk-to-localise" data-text="The Character tiles"></h1>
		<div class="pa pa-architect">
			<h2 class="sbk-to-localise" data-text="Architect"></h2>
			<p class="sbk-to-localise" data-text="Randomly draw 3 Pirogue tokens from the ones set aside at the beginning of the game. Choose one and immediately apply its ability."></p>
		</div>
		<div class="pa pa-high-priest">
			<h2 class="sbk-to-localise" data-text="High Priest(ess)"></h2>
			<p class="sbk-to-localise" data-text="Remove some of the tiles on your Corruption board from the game. Choose between: either all Sobek Statues, including the Architect; or all the tiles of a single Goods type, including their Character(s)."></p>
		</div>
		<div class="pa pa-courtesan">
			<h2 class="sbk-to-localise" data-text="Courtesan"></h2>
			<p class="sbk-to-localise" data-text="Add 1 or 2 tiles from your hand to a previously sold set of tiles (of the same type)."></p>
		</div>
		<div class="pa pa-merchant">
			<h2 class="sbk-to-localise" data-text="Merchant"></h2>
			<p class="sbk-to-localise" data-text="Take any tile currently available anywhere on the Market into your hand without moving the Ankh pawn or taking any Corruption."></p>
		</div>
		<div class="pa pa-queen">
			<h2 class="sbk-to-localise" data-text="Queen"></h2>
			<p class="sbk-to-localise" data-text="Take the first 3 tiles from the Draw pile into your hand. If there are fewer than 3 remaining, take as many as possible."></p>
		</div>
		<div class="pa pa-vizier">
			<h2 class="sbk-to-localise" data-text="Vizier"></h2>
			<p class="sbk-to-localise" data-text="Look at all the tiles on your opponent’s Corruption board and add one of your choice to your hand."></p>
		</div>
		<div class="pa pa-scribe">
			<h2 class="sbk-to-localise" data-text="Scribe"></h2>
			<p class="sbk-to-localise" data-text="If your opponent has more than 6 tiles in their hand, they must place tiles on their Corruption board until they only have 6 in their hand."></p>
		</div>
		<div class="pa pa-thief">
			<h2 class="sbk-to-localise" data-text="Thief"></h2>
			<p class="sbk-to-localise" data-text="Randomly steal a tile from your opponent’s hand and add it to yours. You are allowed to look at the backs of the tiles to choose which one to steal. Your opponent cannot hide this information from you."></p>
		</div>
	</div>
	<div id="player-aid-pirogue">
		<h1 class="sbk-to-localise" data-text="The Pirogue tokens"></h1>
		<div class="pa pa-pyramid">
			<p class="sbk-to-localise" data-text="Play a full turn again. If you choose to take a tile, you may reorient the Ankh pawn as you wish on its square before playing."></p>
		</div>
		<div class="pa pa-scarabs">
			<p class="sbk-to-localise" data-text="Place this token on one of your previously sold sets corresponding to wheat, livestock or fish. It adds 2 Scarabs to this set at the end of the game during final score calculation. You may add this token to the set that you just sold this turn."></p>
		</div>
		<div class="pa pa-mummy">
			<p class="sbk-to-localise" data-text="Add all the tiles currently on your Corruption board to your hand."></p>
		</div>
		<div class="pa pa-snakes">
			<p class="sbk-to-localise" data-text="Give this token to your opponent. They must keep it close to their Corruption board. This token counts as ${num} extra Corruption points at the end of the game."></p>
		</div>
		<div class="pa pa-7deben">
			<p class="sbk-to-localise" data-text="Keep this token, it is worth 7 points during end of game scoring."></p>
		</div>
		<div class="pa pa-2deben">
			<p class="sbk-to-localise" data-text="Keep this token, it grants you 2 points at the end of the game. Also, draw 1 additional Deben token and keep that too."></p>
		</div>
		<div class="pa pa-horus">
			<p class="sbk-to-localise" data-text="Draw 2 Deben tokens and keep the higher one. Put the other back into the bag without revealing its value."></p>
		</div>
		<div class="pa pa-finger">
			<p class="sbk-to-localise" data-text="Place this token on a tile of your choice among the ones on the line indicated by the orientation of the Ankh pawn. Your opponent MUST use their action next turn to take that tile. They can neither choose any other action, nor any other tile, but they otherwise resolve this action normally and take any potential Corruption."></p>
		</div>
	</div>
</div>

<script type="text/javascript">

var jstpl_player_board = `<div id="cp_board_p\${id}" class="cp_board" data-player-id="\${id}">
<table class="table-row-2">
	<tbody>
		<tr>
			<td id="tt-hand-\${id}" class="tt-hand"><div class="sprite sprite-tile sprite-good-back"></div><span id="hand_num_p\${id}" class="hand_num">\${hand_size}</span></td>
			<td id="tt-corruption-\${id}" class="tt-corruption"><div class="sprite sprite-tile corruption-tile"></div><span id="corruption_num_p\${id}" class="corruption_num">\${corruption_size}</span><span id="corruption_pirogue_num_holder_p\${id}" style="display: none; font-size: 0.8em;"> (+ <span id="corruption_pirogue_num_p\${id}"></span>)</span></td>
		</tr>
	</tbody>
</table>
<table class="table-row-2">
<tbody>
	<tr>
		<td id="tt-ivory-\${id}" class="tt-ivory"><div class="sprite sprite-tile sprite-ivory-h-x-x"></div><span class="ivory_num">0 &times; 0</span></td>
		<td id="tt-ebony-\${id}" class="tt-ebony"><div class="sprite sprite-tile sprite-ebony-h-x-x"></div><span class="ebony_num">0 &times; 0</span></td>
	</tr>
	<tr>
		<td id="tt-marble-\${id}" class="tt-marble"><div class="sprite sprite-tile sprite-marble-h-x-x"></div><span class="marble_num">0 &times; 0</span></td>
		<td id="tt-wheat-\${id}" class="tt-wheat"><div class="sprite sprite-tile sprite-wheat-h-x-x"></div><span class="wheat_num">0 &times; 0</span></td>
	</tr>
	<tr>
		<td id="tt-fish-\${id}" class="tt-fish"><div class="sprite sprite-tile sprite-fish-h-x-x"></div><span class="fish_num">0 &times; 0</span></td>
		<td id="tt-livestock-\${id}" class="tt-livestock"><div class="sprite sprite-tile sprite-livestock-h-x-x"></div><span class="livestock_num">0 &times; 0</span></td>
	</tr>
</tbody>
</table>
<div class="hand-backs-holder" id="hand-backs-holder-p\${id}"></div>
<div class="deben-holder" id="deben-holder-p\${id}">
</div>
<div class="pirogue-holder" id="pirogue-holder-p\${id}">
</div>
</div>`;

var jstpl_player_sold_sets = `<div class="sbk-sets normal-tiles whiteblock" data-player-id="\${id}">
	<h3>\${title}</h3>
	<div id="sbk-sets-p\${id}" class="sold-sets">
		<i class="sbk-no-sold-sets-message">\${message}</i>
	</div>
</div>`;

</script>  

{OVERALL_GAME_FOOTER}
