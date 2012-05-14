<?php 

$modx->regClientScript('<script type="text/javascript" src="assets/templates/kidsdream/snowfall/snowfall.jquery.js"></script>');
$modx->regClientCSS(MODX_SITE_URL.'assets/templates/kidsdream/snowfall/styles.css');
echo '
<script>
$(function(){
$(document).snowfall({flakeCount : 200, maxSpeed : 10});

});
</script>
';

?>
