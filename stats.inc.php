<?php

$stats_type = array(

    // Statistics global to table
    "table" => array(

        "turns_number" => array("id"=> 10,
                    "name" => totranslate("Number of turns"),
                    "type" => "int" ),

/*
        Examples:


        "table_teststat1" => array(   "id"=> 10,
                                "name" => totranslate("table test stat 1"), 
                                "type" => "int" ),
                                
        "table_teststat2" => array(   "id"=> 11,
                                "name" => totranslate("table test stat 2"), 
                                "type" => "float" )
*/  
    ),
    
    // Statistics existing for each player
    "player" => array(

        "turns_number" => array("id"=> 10,
                    "name" => totranslate("Number of turns"),
                    "type" => "int" ),
        "points_sets" => array("id"=> 11,
            "name" => totranslate("Points for sets"),
            "type" => "int" ),
        "points_deben" => array("id"=> 12,
            "name" => totranslate("Points for Debens"),
            "type" => "int" ),
        "points_pirogue" => array("id"=> 13,
            "name" => totranslate("Points for Pirogues"),
            "type" => "int" ),
        "corruption" => array("id"=> 14,
            "name" => totranslate("Corruption"),
            "type" => "int" ),
    
/*
        Examples:    
        
        
        "player_teststat1" => array(   "id"=> 10,
                                "name" => totranslate("player test stat 1"), 
                                "type" => "int" ),
                                
        "player_teststat2" => array(   "id"=> 11,
                                "name" => totranslate("player test stat 2"), 
                                "type" => "float" )

*/    
    )

);
