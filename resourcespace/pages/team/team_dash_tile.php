<?php
include "../../include/db.php";
include "../../include/general.php";
include "../../include/authenticate.php";
if(!((checkperm("h") && !checkperm("hdta")) || (checkperm("dta") && !checkperm("h")))){exit($lang["error-permissiondenied"]);}
include "../../include/dash_functions.php";

include "../../include/header.php";
?>
<div class="BasicsBox"> 
<p>
	<a href="<?php echo $baseurl_short?>pages/team/team_home.php" onClick="return CentralSpaceLoad(this,true);">&lt;&nbsp;<?php echo $lang["backtoteamhome"]?></a>
</p>
	<div id="HomePanelContainer">
	<?php
	get_default_dash();
	?>
	</div>
</div>
<?php
include "../../include/footer.php";
?>
