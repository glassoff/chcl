<?php 

$imgWidth = (isset ($imgWidth)) ? $imgWidth : 18;
$rating = (isset ($rating)) ? intval($rating) : 0; //see if document is set
$votes = (isset ($votes)) ? intval($votes) : 0; //see if document is set
$output = '';
if ($votes > 0) {
    $currentStarValue = $rating / $votes;    
} else {
    $currentStarValue = 0;    
}
$width = $currentStarValue * $imgWidth;
$output = "<ul class='star-rating'><li class='current-rating' style='width:" . $width . "px;'></li>
<li class='one-star'>1</li>
<li class='two-stars'>2</li>
<li class='three-stars'>3</li>
<li class='four-stars'>4</li>
<li class='five-stars'>5</li>
</ul>";
return $output;

?>
