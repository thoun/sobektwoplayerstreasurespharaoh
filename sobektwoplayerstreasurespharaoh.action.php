<?php
/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * SobekTwoPlayersTreasuresPharaoh implementation : © <Your name here> <Your email address here>
 *
 * This code has been produced on the BGA studio platform for use on https://boardgamearena.com.
 * See http://en.doc.boardgamearena.com/Studio for more information.
 * -----
 * 
 * sobektwoplayerstreasurespharaoh.action.php
 *
 * SobekTwoPlayersTreasuresPharaoh main action entry point
 *
 *
 * In this file, you are describing all the methods that can be called from your
 * user interface logic (javascript).
 *       
 * If you define a method "myAction" here, then you can call it from your javascript code with:
 * this.ajaxcall( "/sobektwoplayerstreasurespharaoh/sobektwoplayerstreasurespharaoh/myAction.html", ...)
 *
 */
  
  
  class action_sobektwoplayerstreasurespharaoh extends APP_GameAction
  { 
    // Constructor: please do not modify
   	public function __default()
  	{
  	    if( self::isArg( 'notifwindow') )
  	    {
            $this->view = "common_notifwindow";
  	        $this->viewArgs['table'] = self::getArg( "table", AT_posint, true );
  	    }
  	    else
  	    {
            $this->view = "sobektwoplayerstreasurespharaoh_sobektwoplayerstreasurespharaoh";
            self::trace( "Complete reinitialization of board game" );
      }
  	} 
  	
  	// TODO: defines your action entry points there
  	
    public function selectMarketTile()
    {
        self::setAjaxMode();     

        // Retrieve arguments
        // Note: these arguments correspond to what has been sent through the javascript "ajaxcall" method
        $col = self::getArg( "col", AT_posint, true );
        $row = self::getArg( "row", AT_posint, true );

        // Then, call the appropriate method in your game logic, like "playCard" or "myAction"
        $this->game->selectMarketTile( $col, $row );

        self::ajaxResponse( );
    }
    
    public function answer()
    {
        self::setAjaxMode();
        $answer = self::getArg( "answer", AT_alphanum, true );
        $this->game->answer( $answer );
        self::ajaxResponse( );
    }
    
    public function sell()
    {
        self::setAjaxMode();
        $tile_ids_raw = self::getArg( "tile_ids", AT_numberlist, true );
        if( $tile_ids_raw == '' )
          $tile_ids = array();
        else
          $tile_ids = explode( ';', $tile_ids_raw );
        $this->game->sell( $tile_ids );

        self::ajaxResponse( );
    }
    
    public function playCharacter()
    {
        self::setAjaxMode();
        $tile_id = self::getArg( "tile_id", AT_posint, true );
        $this->game->playCharacter( $tile_id );
        self::ajaxResponse( );
    }
    
    public function pickPirogue()
    {
        self::setAjaxMode();
        $slot = self::getArg( "slot", AT_posint, true );
        $this->game->pickPirogue( $slot );
        self::ajaxResponse( );
    }
    
    public function refill()
    {
        self::setAjaxMode();
        $this->game->refill( );
        self::ajaxResponse( );
    }

  }
  

