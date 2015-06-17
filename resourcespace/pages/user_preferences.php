<?php
include "../include/db.php";

$password_reset_mode=false;
$resetvalues=getvalescaped("rp","");
if($resetvalues!="")
    {
    $rplength=strlen($resetvalues);
    $resetuserref=substr($resetvalues,0,$rplength-15);
    $resetkey=substr($resetvalues,$rplength-15);
    $resetvaliduser=sql_query("select ref, username, fullname, usergroup, password, password_reset_hash from user where ref='" . escape_check($resetuserref)  . "'",""); 

    $resetuser=$resetvaliduser[0];
    
    $keycheck=array();
    $keycheck[]=substr(hash('sha256', date("Ymd") .  $resetuser["password_reset_hash"] . $resetuser["username"] . $scramble_key),0,15); 
    $keycheck[]=substr(hash('sha256', date("Ymd", time() + 60 * 60 * 24 ) .  $resetuser["password_reset_hash"] . $resetuser["username"] . $scramble_key),0,15); 

    if(in_array($resetkey, $keycheck))
	{
	$userref=$resetuser["ref"];
	$username=$resetuser["username"];
	$userfullname=$resetuser["fullname"];
	$usergroup=$resetuser["usergroup"];
	$userpassword=$resetuser["password"];
	$password_reset_mode=true;
	}
    else
	{
	redirect ($baseurl . "/login.php?error=passwordlinkexpired");     
	}
    }


include "../include/general.php";
if(!$password_reset_mode)
    {
    include "../include/authenticate.php"; if (checkperm("p")) {exit("Not allowed.");}
    }
   
hook("preuserpreferencesform");

if (getval("save","")!="")
	{
	if (hook('saveadditionaluserpreferences'))
		{
		# The above hook may return true in order to prevent the password from being updated
		}
	else if (!$password_reset_mode && hash('sha256', md5("RS" . $username . getvalescaped("currentpassword","")))!=$userpassword)
		{
		$error3=$lang["wrongpassword"];
		}
	else {
        if (getval("password","")!=getval("password2","")) {$error2=true;}
    	else
	    	{
		    $message=change_password(getvalescaped("password",""));
    		if ($message===true)
	    		{
		    	redirect($baseurl_short."pages/" . ($use_theme_as_home?'themes.php':$default_home_page));
			    }
    		else
	    		{
		    	$error=true;
			    }
		    }
		}
	}
include "../include/header.php";
?>
<div class="BasicsBox"> 
	<?php if ($userpassword=="b58d18f375f68d13587ce8a520a87919" || $userpassword=="b58d18f375f68d13587ce8a520a87919"){?><div class="FormError" style="margin:0;"><?php echo $lang['secureyouradminaccount'];?></div><p></p><?php } ?>
	<?php if (!hook("replaceuserpreferencesheader")) { ?>
	<h1><?php echo $lang["changeyourpassword"]?></h1>
	<?php } ?> <!-- End hook("replaceuserpreferencesheader") -->

    <p><?php echo text("introtext")?></p>

	<?php if (getval("expired","")!="") { ?><div class="FormError">!! <?php echo $lang["password_expired"]?> !!</div><?php } ?>

	<form method="post" action="<?php echo $baseurl_short?>pages/user_preferences.php">
	<input type="hidden" name="expired" value="<?php echo htmlspecialchars(getvalescaped("expired",""))?>">
	<?php hook('additionaluserpreferences');
	
	if(!$password_reset_mode)
	    {?>
	    <div class="Question">
	    <label for="password"><?php echo $lang["currentpassword"]?></label>
	    <input type="password" class="stdwidth" name="currentpassword" id="currentpassword" value="<?php if ($userpassword=="b58d18f375f68d13587ce8a520a87919"){?>admin<?php } ?>"/>
	    <div class="clearerleft"> </div>
	    <?php if (isset($error3)) { ?><div class="FormError">!! <?php echo $error3?> !!</div><?php } ?>
	    </div>
	    <?php
	    }
	else
	    {?>
	    <input type="hidden" name="rp" id="resetkey" value="<?php echo htmlspecialchars($resetuserref) . htmlspecialchars($resetkey)  ?>" />    
	    
	    <?php
	    }
	    ?>
	<div class="Question">
	<label for="password"><?php echo $lang["newpassword"]?></label>
	<input type="password" name="password" id="password" class="stdwidth">
	<?php if (isset($error)) { ?><div class="FormError">!! <?php echo $message?> !!</div><?php } ?>
	<div class="clearerleft"> </div>
	</div>

	<div class="Question">
	<label for="password2"><?php echo $lang["newpasswordretype"]?></label>
	<input type="password" name="password2" id="password2" class="stdwidth">
	<?php if (isset($error2)) { ?><div class="FormError">!! <?php echo $lang["passwordnotmatch"]?> !!</div><?php } ?>
	<div class="clearerleft"> </div>
	</div>



	<div class="QuestionSubmit">
	<label for="buttons"> </label>
	<input name="save" type="submit" value="&nbsp;&nbsp;<?php echo $lang["save"]?>&nbsp;&nbsp;" /><div class="clearerleft"> </div>
	</div>
	</form>

<?php

if(!$password_reset_mode)
    {
    hook("afteruserpreferencesform");
    }
    ?>

</div>
<?php
include "../include/footer.php";
?>
