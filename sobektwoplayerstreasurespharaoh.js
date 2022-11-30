define([
   "dojo","dojo/_base/declare",
   "dojo/debounce",
   "ebg/core/gamegui",
   g_gamethemeurl + "modules/sbk.setup.js",
   g_gamethemeurl + "modules/sbk.states.js",
   g_gamethemeurl + "modules/sbk.notifications.js",
   g_gamethemeurl + "modules/sbk.action.js",
   "ebg/counter",
   g_gamethemeurl + "modules/anime.min.js",
   
   // Classes
   g_gamethemeurl + "modules/Player.js",
],
function (dojo, declare, debounce, gamegui, setup, states, notifications, action, counter, anime) {
   return declare("bgagame.sobektwoplayerstreasurespharaoh", gamegui, {
      constructor: function(){
            console.log('sobektwoplayerstreasurespharaoh constructor');
            this.debounce = debounce;
            this.anime = anime;
            
            this.setup = setup.setup.bind(this);
            this.onEnteringState = states.onEnteringState.bind(this);
            this.onLeavingState = states.onLeavingState.bind(this);
            this.onUpdateActionButtons = states.onUpdateActionButtons.bind(this);
            this.setupNotifications = notifications.setupNotifications.bind(this);
            
            this.onClickBoard = action.onClickBoard.bind(this);
            this.onClickHand = action.onClickHand.bind(this);
            this.onClickExtra = action.onClickExtra.bind(this);
            this.onClickPirogue = action.onClickPirogue.bind(this);
            this.onPlayCharacter = action.onPlayCharacter.bind(this);
            this.onSellSet = action.onSellSet.bind(this);
            this.onRefillBoard = action.onRefillBoard.bind(this);
            this.onAnswer = action.onAnswer.bind(this);
            
            this.tooltipIds = 0;
            
            // Add browser-apple class to body for safari
            if (/apple/i.test(navigator.vendor)) {
               document.body.classList.add('browser-apple');
            }
      },
      
      makeTileFragment: function(tile) {
         let spriteName = '';
         if (tile.deck == 'character') {
            if (tile.ability != null) {
               spriteName = 'sprite-character-' + tile.ability.toString().padStart(2, '0');
            } else {
               spriteName = 'sprite-character-back';
            }
         } else if (+tile.statue) {
            spriteName = 'sprite-statue-' + tile.direction;
         } else if (tile.deck == 'pharaoh') {
            if (tile.displayed_resource != null) {
               spriteName = `sprite-pharaoh sprite-${tile.displayed_resource}`;
            } else {
               spriteName = `sprite-pharaoh-back`;
            }
         } else {
            spriteName = `sprite-${tile.resource}-${tile.direction}-${tile.scarabs > 0 ? 's' : 'x'}-${tile.deben > 0 ? 'd' : 'x'}`;
         }
         let token = '';
         let coords = '';
         if (tile.location == 'board') {
            token = 'token';
            coords = `tile-x-${tile.col} tile-y-${tile.row}`;
         }
         return `<div data-tile-id="${tile.tile_id}" class="sprite sprite-tile ${token} ${spriteName} ${coords}" style="width: ${this.tileWidth}px;height: ${this.tileWidth}px"></div>`;
      },
      addTooltipToPirogue: function(element, pirogue) {
         if (! element.id) {
            element.id = 'tooltip-id-' + this.tooltipIds;
         }
         this.tooltipIds++;
         if (pirogue.ability) {
            let desc = "";
            const tile = pirogue;
            if (tile.ability == 1 || tile.ability == 2) {
               desc = _("Give this token to your opponent. They must keep it close to their Corruption board. This token counts as ${num} extra Corruption points at the end of the game.");
               desc = dojo.string.substitute(desc, {num: tile.ability});
            } else if (tile.ability == 3) {
               desc = _("Play a full turn again. If you choose to take a tile, you may reorient the Ankh pawn as you wish on its square before playing.");
            } else if (tile.ability == 4) {
               desc = _("Place this token on a tile of your choice among the ones on the line indicated by the orientation of the Ankh pawn. Your opponent MUST use their action next turn to take that tile. They can neither choose any other action, nor any other tile, but they otherwise resolve this action normally and take any potential Corruption.");
            } else if (tile.ability == 5) {
               desc = _("Add all the tiles currently on your Corruption board to your hand.");
            } else if (tile.ability == 6) {
               desc = _("Keep this token, it is worth 7 points during end of game scoring.");
            } else if (tile.ability == 7) {
               desc = _("Place this token on one of your previously sold sets corresponding to wheat, livestock or fish. It adds 2 Scarabs to this set at the end of the game during final score calculation. You may add this token to the set that you just sold this turn.");
            } else if (tile.ability == 8) {
               desc = _("Keep this token, it grants you 2 points at the end of the game. Also, draw 1 additional Deben token and keep that too.");
            } else if (tile.ability == 9) {
               desc = _("Draw 2 Deben tokens and keep the higher one. Put the other back into the bag without revealing its value.");
            } else if (tile.ability == 10) {
               desc = _("Your opponent must randomly discard 1 Deben token among the ones they have.");
            } else if (tile.ability == 11) {
               desc = _("Place this token on one of your previously sold sets corresponding to one of the 2 types shown. It adds 2 Scarabs to this set at the end of the game during final score calculation. You may add this token to the ");
            }

            html = `${desc}`;
            this.addTooltipHtml( element.id, html );
         }
      },
      addTooltipToTile: function(element, tileInfos = null) {
         const tile = element.tile || tileInfos;
         element.id = 'tooltip-id-' + this.tooltipIds;
         this.tooltipIds++;
         if (tile && tile.deck == 'character') {
            if (tile.ability) {
               let html = "";
               let name = "";
               let desc = "";
               if (tile.ability == 1) {
                  name = _("Queen");
                  desc = _("Take the first 3 tiles from the Draw pile into your hand. If there are fewer than 3 remaining, take as many as possible.");
               } else if (tile.ability == 2) {
                  name = _("Vizier");
                  desc = _("Look at all the tiles on your opponent’s Corruption board and add one of your choice to your hand.");
               } else if (tile.ability == 3) {
                  name = _("Thief");
                  desc = _("Randomly steal a tile from your opponent’s hand and add it to yours. You are allowed to look at the backs of the tiles to choose which one to steal. Your opponent cannot hide this information from you.");
               } else if (tile.ability == 4) {
                  name = _("Merchant");
                  desc = _("Take any tile currently available anywhere on the Market into your hand without moving the Ankh pawn or taking any Corruption.");
               } else if (tile.ability == 5) {
                  name = _("High Priest");
                  desc = _("Remove some of the tiles on your Corruption board from the game. Choose between: either all Sobek Statues, including the Architect; or all the tiles of a single Goods type, including their Character(s).");
               } else if (tile.ability == 6) {
                  name = _("High Priestess");
                  desc = _("Remove some of the tiles on your Corruption board from the game. Choose between: either all Sobek Statues, including the Architect; or all the tiles of a single Goods type, including their Character(s).");
               } else if (tile.ability == 7) {
                  name = _("Courtesan");
                  desc = _("Add 1 or 2 tiles from your hand to a previously sold set of tiles (of the same type).");
               } else if (tile.ability == 8) {
                  name = _("Architect");
                  desc = _("Randomly draw 3 Pirogue tokens from the ones set aside at the beginning of the game. Choose one and immediately apply its ability.");
               } else if (tile.ability == 9 || tile.ability == 10) {
                  name = _("Scribe");
                  desc = _("If your opponent has more than 6 tiles in their hand, they must place tiles on their Corruption board until they only have 6 in their hand.");
               } else if (tile.ability == 11) {
                  name = _("Royal Adviser");
                  desc = _("Discard 1 Royal corruption token of your choice among the ones you have.");
               } else if (tile.ability == 12) {
                  name = _("Spy");
                  desc = _("Apply the ability of a Character tile of your choice, among the ones that have already been used for their ability during this game. So, you can not choose the ability of a character that has been sold within a set of goods.");
               }
               html = `<h3>${name}</h3>${desc}`;
               this.addTooltipHtml( element.id, html );
            }
         }
      },
      
      numPlayers: function() {
         return this.players.length
      },
      
      getTileAt: function(col, row) {
         const q = dojo.query('#tiles-holder .sprite-tile.tile-x-'+col+'.tile-y-'+row);
         if (q.length > 0)
            return q[0];
         return null;
      }
   });
});
