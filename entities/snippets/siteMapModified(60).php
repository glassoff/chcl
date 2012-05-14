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
   
   //$excludeIds = '';

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
$excludeIdsArr = $excludeIds ? explode(',', $excludeIds) : array();

// Overcome single use limitation on functions

if(!function_exists('MakeSiteMap')){
  function MakeSiteMap($funcModx, $listParent, $listLevel, $description, $titleOfLinks,$maxLevels,$su,$selfAsLink,$excludeIdsArr){
    $children = $funcModx->getAllChildren($listParent,'menuindex ASC, pagetitle','ASC','id,pagetitle,description,parent,alias,longtitle,published,deleted,hidemenu');
    if(!$children)
        $children = getAllItems($listParent);
    //$output .= '<ul class="LSM_'.$listLevel.'">'.$ie;
    $output .= '<ul class="site_map">'.$ie;
    //$i = 0;
    //$childCounts = count($children);
    $childArr = array();
    foreach($children as $child){
      // skip unpubs unless desired
      if(in_array($child['parent'], $excludeIdsArr)) continue;
      if ((!$su && !$child['published']) || ($child['deleted']) || (!$showNotInMenu && $child['hidemenu'])) continue;
      
      $child['class'] = 'last';
      $child['link'] = $child['type']=='item' ? 'catalog/item?id=' . $child['id'] : '[~'.$child['id'].'~]';
      $childArr[] = $child;
      
      $clearIndex = (count($childArr)-2);
      if( $clearIndex >= 0 ){  
        $childArr[$clearIndex]['class'] = '';         
      }     
    }
    
    foreach($childArr as $child){
      $descText = ($description)? ' <span class="LSM_description">'.$child['description'].'</span>' : '';
      $output .= '';
      
      if ((!$selfAsLink) && ($child['id'] == $funcModx->documentIdentifier)){
        $output .= '<li> <span class="LSM_currentPage '.$child['class'].'">'.$child['pagetitle'].'</span>';
      } else if (!$child['published']){
        $output .= '<li class="LSM_unpubLI '.$child['class'].'"> <span class="LSM_unpubPage">'.$child['pagetitle'].'</span>';
      } else {
        $accText = '';
        $priceText = '';
        if($child['type']=='item'){//print_r($child);die();
            $accText = ' - ' . $child['acc_id'];
            $priceText = ', ' . $child['price_opt'] . ' руб/шт';
        }
        $output .= '<li class="'.$child['class'].'"> <a href="'.$child['link'].'" title="'.$child[$titleOfLinks].'">'.$child['pagetitle'].'</a>'.$accText.$priceText;
      }
      $output .= $descText;
      if (($funcModx->getAllChildren($child['id']) || getAllItems($child['id'])) && (($maxLevels==0) || ($maxLevels > $listLevel+1 ))){
        $output .= MakeSiteMap($funcModx,$child['id'],$listLevel+1,$description,$titleOfLinks,$maxLevels,$su,$selfAsLink,$excludeIdsArr);
      }
      $output .= '</li>'.$ie;
    }
    $output .= '</ul>'.$ie;
    return $output;
  }
}
function getAllItems($parent){
    global $modx;
    $items = array();
    $sql = "SELECT * FROM modx_site_ec_items WHERE parent='$parent'";
    $result = $modx->db->query($sql);
    if($modx->db->getRecordCount($result) > 0){
        while($row = $modx->db->getRow($result)){
            //id,pagetitle,description,parent,alias,longtitle,published,deleted,hidemenu
            $items[] = array(
                'id' => $row['id'],
                'pagetitle' => $row['pagetitle'],
                'description' => '',
                'parent' => $row['parent'],
                'alias' => '',
                'longtitle' => $row['pagetitle'],
                'published' => $row['published'],
                'deleted' => $row['deleted'],
                'hidemenu' => 0,
                'type' => 'item',
                'acc_id' => $row['acc_id'],
                'price_opt' => $row['price_opt'],          
            );
                
        }   
        //print_r($items);die();
        return $items;  
    }
    return false;
}
return MakeSiteMap($modx, $siteMapRoot, 0, $showDescription, $titleOfLinks,$maxLevels,$showUnpubs,$selfAsLink,$excludeIdsArr);

?>
