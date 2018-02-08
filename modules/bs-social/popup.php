<?php 
$redirect = array_key_exists('redirect', $_GET) ? $_GET['redirect'] : '/';
if (!$redirect)
	$redirect = '/';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html dir="ltr" lang="en-US">
<head>
<meta charset="UTF-8" />
<script type="text/javascript">
opener.location.href = '<?=$redirect?>';
close();
</script>
<title>Signing into Business-Software.com</title>
</head>
<body>
</body>
</html>
