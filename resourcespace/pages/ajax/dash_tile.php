<?php
/*
 * User Tile Ajax Interface
 * Ajax functions for the homepage dash interface
 *
 */

include "../../include/db.php";
include "../../include/general.php";
include "../../include/authenticate.php";
include "../../include/search_functions.php";
include "../../include/collections_functions.php";
include "../../include/dash_functions.php";


global $userref,$baseurl_short;
/* Tile */
$rawtile=getvalescaped("tile",null,TRUE);
if(isset($rawtile) && !empty($rawtile))
	{
	if(!is_numeric($rawtile)){exit($lang["invaliddashtile"]);}
	$tile=get_tile($rawtile);
	if(!$tile){exit($lang["nodashtilefound"]);}
	}

/* User Tile */
$user_rawtile=getvalescaped("user_tile",null,TRUE);
if(isset($user_rawtile) && !empty($user_rawtile))
	{
	if(!is_numeric($user_rawtile)){exit($lang["invaliddashtile"]);}
	$usertile=get_user_tile($user_rawtile,$userref);
	if(!$usertile){exit($lang["nodashtilefound"]);}
	}

/* 
 * Reorder Tile
 */
$index=getvalescaped("new_index","",TRUE);
if(!empty($index) && isset($usertile))
	{
	$index= $index-10;
	if($index > $usertile["order_by"])
		{$index+=5;}
	else 
		{$index-=5;}
	update_user_dash_tile_order($userref,$usertile["ref"],$index);
	reorder_user_dash($userref);
	exit("Tile ".$usertile["ref"]." at index: ".($index));
	}
if(!empty($index) && isset($tile) && !isset($usertile))
	{
	$index= $index-10;
	if($index > $tile["default_order_by"])
		{$index+=5;}
	else 
		{$index-=5;}
	update_default_dash_tile_order($tile["ref"],$index);
	reorder_default_dash();
	echo "Tile ".$tile["ref"]." at index: ".($index);
	exit();
	}

/* 
 * Delete Tile 
 */
$delete=getvalescaped("delete",false);
if($delete && isset($usertile))
	{
	if(checkperm("dtu") && !((checkperm("h") && !checkperm("hdta")) || (checkperm("dta") && !checkperm("h")))){exit($lang["error-permissiondenied"]);}
	delete_user_dash_tile($usertile["ref"],$userref);
	reorder_user_dash($userref);
	echo "Deleted ".$usertile['ref'];
	exit();
	}
if($delete && isset($tile) && !isset($usertile))
	{
	if(!((checkperm("h") && !checkperm("hdta")) || (checkperm("dta") && !checkperm("h")))){exit($lang["error-permissiondenied"]);}
	delete_dash_tile($tile["ref"]);
	reorder_default_dash();
	echo "Deleted ".$tile['ref'];
	exit();
	}



/* 
 * Generating Tiles 
 */
$tile_type=getvalescaped("tltype","");
$tile_style=getvalescaped("tlstyle","");
$tile_id= (isset($usertile)) ? "contents_user_tile".$usertile["ref"] : "contents_tile".$tile["ref"];
$tile_width = getvalescaped("tlwidth","");
$tile_height = getvalescaped("tlheight","");
if(!is_numeric($tile_width) || !is_numeric($tile_height)){exit($lang["error-missingtileheightorwidth"]);}
include "../../include/dash_tile_generation.php";

tile_select($tile_type,$tile_style,$tile,$tile_id,$tile_width,$tile_height);
exit($lang["nodashtilefound"]);
