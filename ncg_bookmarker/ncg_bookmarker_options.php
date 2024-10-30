<?php

define('NCG_BOOKMARKER_ID', 'ncg_bookmarker');

/** Creates default options and saves them to database. */
function ncg_bookmarker_createDefaultOptions()
{
	$deliciousOptions = get_option("ncg_delicious");
	
	$deliciousUser = "";
	$deliciousPass = "";
	$deliciousPost = "0";
	
	if ($deliciousOptions)
	{
		$deliciousUser = $deliciousOptions["username"];
		$deliciousPass = $deliciousOptions["password"];
		$deliciousPost = ($deliciousUser && $deliciousPass) ? "1" : "0";
	}
	
	$options = array(
		"delicious" => array("doPosting" => $deliciousPost, "username" => $deliciousUser, "password" => $deliciousPass),
		"simpy" => array("doPosting" => "0", "username" => "", "password" => "")
	);

	add_option(NCG_BOOKMARKER_ID, $options, "Bookmarker Options");

	return $options;
}

$options = get_option(NCG_BOOKMARKER_ID);
if (!$options) $options = ncg_bookmarker_createDefaultOptions();

if ($_POST['update'])
{
	$delicious = array("doPosting" => $_POST['delicious_doPosting'], "username" => $_POST['delicious_username'], "password" => $_POST['delicious_password']);
	$simpy = array("doPosting" => $_POST['simpy_doPosting'], "username" => $_POST['simpy_username'], "password" => $_POST['simpy_password']);

	$options['delicious'] = $delicious;
	$options['simpy'] = $simpy;
	
	update_option(NCG_BOOKMARKER_ID, $options);
}

$actionURL = $_SERVER[PHP_SELF] . '?page=ncg_bookmarker.php';

$delicious = $options['delicious'];
$simpy = $options['simpy'];
?>
<div class="wrap">
	<h2>Bookmarker Configuration</h2>
	<form method="post" action="<?= $actionURL ?>">
		<input type="hidden" name="update" value="1">
		<table border="0" cellspacing="10">
			<tr>
				<td colspan="2"><input id="delicious_doPosting" type="checkbox" name="delicious_doPosting" value="1" <?= $delicious['doPosting'] ? "checked=\"checked\"" : "" ?>/> <label for="delicious_doPosting">Post bookmark to <a href="http://del.icio.us/">del.icio.us</a></label>
			</tr>
			<tr>
				<td>&nbsp;&nbsp;User name:</td>
				<td><input type="text" name="delicious_username" value="<?= $delicious['username'] ?>"></td>
			</tr>
			<tr>
				<td>&nbsp;&nbsp;Password:</td>
				<td><input type="password" name="delicious_password" value="<?= $delicious['password'] ?>"></td>
			</tr>

			<tr>
				<td colspan="2"><input type="checkbox" id="simpy_doPosting" name="simpy_doPosting" value="1" <?= $simpy['doPosting'] ? "checked=\"checked\"" : "" ?>/> <label for="simpy_doPosting">Post bookmark to <a href="http://www.simpy.com/">Simpy</a></label>
			</tr>
			<tr>
				<td>&nbsp;&nbsp;User name:</td>
				<td><input type="text" name="simpy_username" value="<?= $simpy['username'] ?>"></td>
			</tr>
			<tr>
				<td>&nbsp;&nbsp;Password:</td>
				<td><input type="password" name="simpy_password" value="<?= $simpy['password'] ?>"></td>
			</tr>
			<tr><td colspan="2" aling="right"><p class="submit"> <input type="submit" name="submit" value="Update &raquo;" /></td></tr>
    </p> 
		</table>
	</form>
</div>
