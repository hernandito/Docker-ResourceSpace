<?php
/*
 * User Dash - Tile Interface
 * Page for building tiles for the homepage dash interface
 *
 */

include "../include/db.php";
include "../include/general.php";
$k=getvalescaped("k","");
include "../include/authenticate.php";
include "../include/search_functions.php";
include "../include/dash_functions.php";

if(checkperm("dtu") && !((checkperm("h") && !checkperm("hdta")) || (checkperm("dta") && !checkperm("h")))){exit($lang["error-permissiondenied"]);}
global $baseurl,$baseurl_short,$userref;
$error=false;

/* 
 * Process Submitted Tile 
 */
$submitdashtile=getvalescaped("submitdashtile",FALSE);
if($submitdashtile)
	{
	$buildurl=getvalescaped("url","");
	if ($buildurl=="")
		{
		# No URL provided - build a URL (standard title types).
		$buildurl="pages/ajax/dash_tile.php?tltype=".getvalescaped("tltype","")."&tlstyle=".getvalescaped("tlstyle","");
		$promoted_image= getvalescaped("promoted_image","");
		if(!empty($promoted_image))
			{$buildurl.="&promimg=".$promoted_image;}
		}

	if((checkperm("h") && !checkperm("hdta")) || (checkperm("dta") && !checkperm("h")))
		{
		$all_users= (getvalescaped("all_users","false")=="true")? TRUE: FALSE;
		}
	else
		{
		$all_users=FALSE;
		}

	$title=getvalescaped("title","");
	$text=getvalescaped("freetext","");
	$default_order_by=getvalescaped("default_order_by","UNSET");
	$reload_interval=getvalescaped("reload_interval_secs","");
	$resource_count=getvalescaped("resource_count",FALSE);

	$link=str_replace("&amp;","&",getvalescaped("link",""));
	if(strpos($link,$baseurl_short)===0) 
		{
		$length = strlen($baseurl_short);
		$link = substr_replace($link,"",0,$length);
		}
	$link= preg_replace("/^\//","",$link);

	$tile = create_dash_tile($buildurl,$link,$title,$reload_interval,$all_users,$default_order_by,$resource_count,$text);
	if(!$all_users)
		{
		$existing = add_user_dash_tile($userref,$tile,$default_order_by);
		if(isset($existing[0]))
			{
			$error=$lang["existingdashtilefound"];
			}
		}

	/* CREATION SUCCESSFUL */
	if(!$error)
		{
		redirect($baseurl);
		exit();
		}
	include "../include/header.php";
	?>
	<h2><?php echo $lang["createnewdashtile"];?></h2>
	<?php 
	if($error)
		{?>
		<p class="FormError" style="margin-left:5px;">
		<?php echo $error;?>
		</p>
		<?php
		}?>
	<a href="<?php echo $link;?>">&gt;&nbsp;<?php echo $lang["returntopreviouspage"];?></a>
	<?php
	include "../include/footer.php";
	exit();
	}


/* 
 * Create New Tile Form 
 */
$create=getvalescaped("create",FALSE);

if($create)
	{
	$tile_type=getvalescaped("tltype","");
	$tile_nostyle = getvalescaped("nostyleoptions",FALSE);
	$allusers=getvalescaped("all_users",FALSE);
	$url=getvalescaped("url","");
	$freetext = getvalescaped("freetext",false);
	$notitle = getvalescaped("notitle",false);
	$link=getvalescaped("link","");
	$title=getvalescaped("title","");

	if($tile_type=="srch")
		{
		$srch=getvalescaped("link","");
		$order_by=getvalescaped("order_by","");
		$sort=getvalescaped("sort","");
		$archive=getvalescaped("archive","");
		$daylimit=getvalescaped("daylimit","");
		$restypes=getvalescaped("restypes","");
		$title=getvalescaped("title","");
		$promoted_resource=getvalescaped("promoted_resource",FALSE);
		$resource_count=getvalescaped("resource_count",0,TRUE);

		$link=$srch."&order_by=" . urlencode($order_by) . "&sort=" . urlencode($sort) . "&archive=" . urlencode($archive) . "&daylimit=" . urlencode($daylimit) . "&k=" . urlencode($k) . "&restypes=" . urlencode($restypes);
		$title=preg_replace("/^.*search=/", "", $srch);
		if(substr($title,0,11)=="!collection")
			{
			include_once "../include/collections_functions.php";
			$col= get_collection(preg_replace("/^!collection/", "", $title));
			$title=$col["name"];
			}
		else if(substr($title,0,7)=="!recent")
			{$title=$lang["recent"];}
		else if(substr($title,0,5)=="!last")
			{
			$last = preg_replace("/^!last/", "", $title);
			$title= ($last!="") ? $lang["last"]." ".$last : $lang["recent"];
			}
		}
		
	include "../include/header.php";
	?>
	<h2><?php echo $lang["createnewdashtile"];?></h2>
	<form id="create_dash" name="create_dash">
		<input type="hidden" name="tltype" value="<?php echo htmlspecialchars($tile_type)?>" />
		<input type="hidden" name="link" id="previewlink" value="<?php echo htmlspecialchars($link);?>" />
		<input type="hidden" name="url" value="<?php echo htmlspecialchars($url); ?>" />
		<input type="hidden" name="submitdashtile" value="true" />

		<?php
		if(!$notitle)
			{ ?>
			<div class="Question">
				<label for="title" class="stdwidth"><?php echo $lang["dashtiletitle"];?></label> 
				<input type="text" id="previewtitle" name="title" value="<?php echo htmlspecialchars(ucfirst ($title));?>"/>
				<div class="clearerleft"></div>
			</div>
			<?php
			}
		else
			{ ?>
			<input type="hidden" name="notitle" value="1" />
			<?php
			}

		if($freetext)
			{ 
			if($freetext=="true")
				{$freetext="";}
			?>
			<div class="Question">
				<label for="freetext" class="stdwidth"><?php echo $lang["dashtiletext"];?></label> 
				<input type="text" id="previewtext" name="freetext" value="<?php echo htmlspecialchars(ucfirst($freetext));?>"/>
				<div class="clearerleft"></div>
			</div>
			<?php
			}

		if (!$tile_nostyle)
			{tileStyle($tile_type);}

		if($tile_type=="srch")
			{?>
			<div class="Question" id="showresourcecount" >
				<label for="tltype" class="stdwidth"><?php echo $lang["showresourcecount"];?></label> 
				<table>
					<tbody>
						<tr>
							<td width="10" valign="middle" >
								<input type="checkbox" id="resource_count" name="resource_count" value="1" <?php echo $resource_count? "checked":"";?>/>
							</td>
						</tr>
					</tbody>
				</table>
				<div class="clearerleft"> </div>
			</div>
			<script>
				jQuery(".tlstyle").change(function(){
					checked=jQuery(".tlstyle:checked").val();
					if(checked=="thmbs" || checked=="multi") {
						jQuery("#showresourcecount").show();
					}
					else {
						jQuery("#showresourcecount").hide();
					}
				});
			</script>
			<?php
			if($promoted_resource)
				{
				global $link,$view_title_field;
				$search_string = explode('?',$link);
				parse_str(str_replace("&amp;","&",$search_string[1]),$search_string);
				$search = isset($search_string["search"]) ? $search_string["search"] :"";
				$restypes = isset($search_string["restypes"]) ? $search_string["restypes"] : "";
				$order_by= isset($search_string["order_by"]) ? $search_string["order_by"] : "";
				$archive = isset($search_string["archive"]) ? $search_string["archive"] : "";
				$sort = isset($search_string["sort"]) ? $search_string["sort"] : "";
				$resources = do_search($search,$restypes,$order_by,$archive,-1,$sort);
				?>
				<div class="Question" id="promotedresource">
					<label for="promoted_image">
					<?php echo $lang["dashtileimage"]?></label>
					<select class="stdwidth" id="previewimage" name="promoted_image">
					<?php foreach ($resources as $resource)
						{
						?>
						<option value="<?php echo htmlspecialchars($resource["ref"]) ?>">
							<?php echo str_replace(array("%ref", "%title"), array($resource["ref"], i18n_get_translated($resource["field" . $view_title_field])), $lang["ref-title"]) ?>
						</option>
						<?php
						}
					?>
					</select>
					<div class="clearerleft"> </div>
				</div>
				<script>
					jQuery(".tlstyle").change(function(){
						checked=jQuery(".tlstyle:checked").val();
						if(checked=="thmbs") {
							jQuery("#promotedresource").show();
						}
						else {
							jQuery("#promotedresource").hide();
						}
					});
				</script>
				<?php
				}
			}

		if((checkperm("h") && !checkperm("hdta")) || (checkperm("dta") && !checkperm("h")))
			{ ?>
			<div class="Question">
				<label for="all_users" class="stdwidth"><?php echo $lang["pushtoallusers"];?></label> 
				<table>
					<tbody>
						<tr>
							<td width="10" valign="middle" >
								<input type="radio" id="all_users_false" name="all_users" value="false" <?php echo $allusers? "":"checked";?>/>
							</td>
							<td align="left" valign="middle" >
								<label class="customFieldLabel" for="all_users_false"><?php echo $lang["no"];?></label>
							</td>
							<td width="10" valign="middle" >
								<input type="radio" id="all_users_true" name="all_users" value="true" <?php echo $allusers? "checked":"";?>/>
							</td>
							<td align="left" valign="middle" >
								<label class="customFieldLabel" for="all_users_true"><?php echo $lang["yes"];?></label>
							</td>
						</tr>
					</tbody>
				</table>
				<div class="clearerleft"> </div>
			</div>
			<?php 
			} ?>
		<div class="Question">
			 <div class="Inline"><input name="Submit" type="submit" value="&nbsp;&nbsp;<?php echo $lang["create"]?>&nbsp;&nbsp;" /></div>
			<div class="clearerleft"> </div>
		</div>
	</form>
	
	<div class="HomePanel DashTile">
		<div id="previewdashtile" class="dashtilepreview HomePanelIN HomePanelDynamicDash <?php echo ($dash_tile_shadows)? "TileContentShadow":"";?>">
		</div>
	</div>
	<script>
		function updateDashTilePreview() {
			var prevstyle = jQuery(".tlstyle:checked").val();
			var width = 250;
			var height = 180;
			var pretitle = encodeURIComponent(jQuery("#previewtitle").val());
			var pretxt = encodeURIComponent(jQuery("#previewtext").val());
			var prelink= encodeURIComponent(jQuery("#previewlink").val());
			var tile = "&tllink="+prelink+"&tltitle="+pretitle+"&tltxt="+pretxt;
			<?php
			if($tile_type=="srch")
				{?>	
				var count = jQuery("#resource_count").is(':checked');
				if(count)
					{count=1;}
				else
					{count=0;}
				tile= tile+"&tlrcount="+encodeURIComponent(count);
				<?php
				if($promoted_resource)
					{ ?>
					tile = tile+"&promimg="+encodeURIComponent(jQuery("#previewimage").val()); 
					<?php
					}
				}

			#Preview URL
			if (empty($url))
				{$previewurl=$baseurl_short."pages/ajax/dash_tile_preview.php";}
			else
				{$previewurl=$baseurl_short.$url;}
			?>
			jQuery("#previewdashtile").load("<?php echo $previewurl; ?>?tltype=<?php echo urlencode($tile_type)?>&tlstyle="+prevstyle+"&tlwidth="+width+"&tlheight="+height+tile);
		}
		updateDashTilePreview();
		jQuery("#previewtitle").change(updateDashTilePreview);
		jQuery("#previewtext").change(updateDashTilePreview);
		jQuery("#resource_count").change(updateDashTilePreview);
		jQuery(".tlstyle").change(updateDashTilePreview);
		jQuery("#promotedresource").change(updateDashTilePreview);
	</script>
	<?php
	include "../include/footer.php";
	}

