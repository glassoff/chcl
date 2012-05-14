<?php 

// --------------------
// Snippet: ListSiteMap
// --------------------
// Version: 0.9.6
// Date: 2007.08.29 // jaredc@honeydewdesign.com
//
// This snippet was designed to show a nested
// list site map with each pagetitle being a
// link to that page. It will not include
// unpublished folders/pages OR its children,
// even if the children ARE published.
// only when option is set to see the unpublished pages the unpub pages wil be shown (not as link) 
// Added bij Dimmy:
// Option to select if pages / folders that are not in menu will be showed or not

// Config
   // $siteMapRoot [int]
   // The parent ID of your root. Default 0. Can be set in 
   // snippet call with LSM_root (to doc id 10 for example):
   // [[ListSiteMap?LSM_root=10]]
   $siteMapRoot = 0;

   // $showDescription [ 1 | 0 ]
   // Specify if you would like to include the description
   // with the page title link.
   $showDescription = 0;

   // $titleOfLinks [ string ]
   // What database field do you want the title of your links to be?
   // The default is pagetitle because it is always a valid (not empty)
   // value, but if you prefer it can be any of the following:
   // id, pagetitle, description, parent, alias, longtitle
   $titleOfLinks = 'longtitle';
   
   // $removeNewLines [ 1 | 0 ]
   // If you want new lines removed from code, set to true. This is generally
   // better for IE when lists are styled vertically. 
   $removeNewLines = 1;
   
   // $maxLevels [ int ]
   // Maximum number of levels to include. The default 0 will allow all
   // levels. Also settable with snippet variable LSM_levels:
   // [[ListSiteMap?LSM_levels=2]]
   $maxLevels = 0;
   
   // $selfAsLink [ true | false ]
   // Define if the current page should be a link (true) or not
   // (false)
   $selfAsLink = 0;
   
   // $showUnpubs [ 1 | 0 ]
   // Decide to include items in unpublished folders. This will show the
   // unpublished items as well. No links will be made for the unpublished items
   // but they will be shown in the structure. You will not likely want to do
   // this but the option is yours.
   $showUnpubs = 0;

   // $showNotInMenu [ 1 | 0 ]
   // Decide to include items that are not in menu. This will show the
   // items not in menu including there children items as well. 
   $showNotInMenu = 1;

// Styles
//
// .LSM_currentPage    span surrounding current page if $selfAsLink is false
// .LSM_description    description of page
// .LSM_N              ul style where N is the level of nested list- starting at 0
// .LSM_unpubPage span surrounding Unpub page title
// .LSM_unpubPageLI Class for the li surounding the Unpub page title

// ###########################################
// End config, the rest takes care of itself #
// ###########################################

// Initialize
$siteMapRoot = (isset($LSM_root))? $LSM_root : $siteMapRoot ;
$maxLevels = (isset($LSM_levels))? $LSM_levels : $maxLevels ;
$ie = ($removeNewLines)? '' : "\n" ;

// Overcome single use limitation on functions

if(!function_exists('MakeSiteMap')){
  function MakeSiteMap($funcModx, $listParent, $listLevel, $description, $titleOfLinks,$maxLevels,$su,$selfAsLink){
    $children = $funcModx->getAllChildren($listParent,'menuindex ASC, pagetitle','ASC','id,pagetitle,description,parent,alias,longtitle,published,deleted,hidemenu');
    //$output .= '<ul class="LSM_'.$listLevel.'">'.$ie;
    $output .= '<ul class="site_map">'.$ie;
    foreach($children as $child){
    
      // skip unpubs unless desired
      if ((!$su && !$child['published']) || ($child['deleted']) || (!$showNotInMenu && $child['hidemenu'])) continue;
      
      $descText = ($description)? ' <span class="LSM_description">'.$child['description'].'</span>' : '';
      $output .= '';
      if ((!$selfAsLink) && ($child['id'] == $funcModx->documentIdentifier)){
        $output .= '<li> <span class="LSM_currentPage">'.$child['pagetitle'].'</span>';
      } else if (!$child['published']){
        $output .= '<li class="LSM_unpubLI"> <span class="LSM_unpubPage">'.$child['pagetitle'].'</span>';
      } else {
        $output .= '<li> <a href="[~'.$child['id'].'~]" title="'.$child[$titleOfLinks].'">'.$child['pagetitle'].'</a>';
      }
      $output .= $descText;
      if ($funcModx->getAllChildren($child['id']) && (($maxLevels==0) || ($maxLevels > $listLevel+1 ))){
        $output .= MakeSiteMap($funcModx,$child['id'],$listLevel+1,$description,$titleOfLinks,$maxLevels,$su,$selfAsLink);
      }
      $output .= '</li>'.$ie;
    }
    $output .= '</ul>'.$ie;
    return $output;
  }
}
return MakeSiteMap($modx, $siteMapRoot, 0, $showDescription, $titleOfLinks,$maxLevels,$showUnpubs,$selfAsLink);

?>
