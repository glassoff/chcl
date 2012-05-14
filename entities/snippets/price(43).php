<?php 
  print "<?xml version=\"1.0\" encoding=\"windows-1251\"?>";  


mysql_query("set CHARACTER SET cp1251");

$query_cat = "SELECT id, parent, pagetitle FROM  modx_site_content ";
$cat = $modx->dbQuery($query_cat);
$row_cat = mysql_fetch_assoc($cat);
$date = date("Y-m-d G:i:s");
?>

<!DOCTYPE yml_catalog SYSTEM "shops.dtd">
<yml_catalog date="<?php echo $date; ?>">
<shop>
<name>CDDISKI</name>
<company>Интернет-магазин компьютерных игр и dvd-фильмов.</company>
<url>http://www.cddiski.ru/</url>
<currencies>
    <currency id="RUR" rate="1"/>
    
</currencies>

<categories>

  <?php do { 
  

$row_cat['pagetitle']=str_replace("\"", "&quot;" , $row_cat['pagetitle']);
$row_cat['pagetitle']=str_replace("&", "&amp;" , $row_cat['pagetitle']);
$row_cat['pagetitle']=str_replace(">", "&gt;" , $row_cat['pagetitle']);
$row_cat['pagetitle']=str_replace("<", "&lt;" , $row_cat['pagetitle']);
$row_cat['pagetitle']=str_replace("'", "&apos;" , $row_cat['pagetitle']);


 ?>
 

<category id="<?php echo $row_cat['id']; ?>" parentId="<?php echo $row_cat['parent']; ?>"><?php echo $row_cat['pagetitle']; ?></category>
 <?php } while ($row_cat = mysql_fetch_assoc($cat)); ?>
</categories>

<offers>



<?php
$id = 5;
$deep = isset($deep) ? intval($deep) : 5;
$showinmenu = isset($showinmenu) ? intval($showinmenu) : 0;
$childs = $modx->getChildIds($id, $deep);
if (is_array($childs) && count($childs)>0) $childs = implode(',',$childs);
else $childs = $id;
if ($showinmenu == 1) {
    $sql = "SELECT id FROM ".$modx->getFullTableName("site_content")." WHERE id IN(".$childs.") AND hidemenu=0 AND deleted=0 AND published=1";   
    $result = $modx->dbQuery($sql);           
    $numResults = @$modx->recordCount($result);    
    $child_ = array();       
    for($i=0;$i<$numResults;$i++)  {
        $row = $modx->fetchRow($result);
        $child_[] = $row['id'];
    }
    $childs = implode(',',$child_);
}



$query_off = "SELECT 
modx_site_ec_items.id, modx_site_ec_items.parent, modx_site_ec_items.sell, modx_site_ec_items.retail_price, modx_site_ec_items.pagetitle, tmplvarid,
value, itemid

 FROM  modx_site_ec_items, modx_site_tmplvar_ec_itemvalues WHERE 
modx_site_tmplvar_ec_itemvalues.itemid=modx_site_ec_items.id and modx_site_tmplvar_ec_itemvalues.tmplvarid=40 and sell=1 and (popular=1 or new =1)
and modx_site_ec_items.parent IN  (".$childs.")

 ";
$off = $modx->dbQuery($query_off);
$row_off = mysql_fetch_assoc($off);



 do { 
 
$row_off['value']=str_replace("\"", "&quot;" , $row_off['value']);
$row_off['value']=str_replace("&", "&amp;" , $row_off['value']);
$row_off['value']=str_replace(">", "&gt;" , $row_off['value']);
$row_off['value']=str_replace("<", "&lt;" , $row_off['value']);
$row_off['value']=str_replace("'", "&apos;" , $row_off['value']);


$row_off['pagetitle']=str_replace("\"", "&quot;" , $row_off['pagetitle']);
$row_off['pagetitle']=str_replace("&", "&amp;" , $row_off['pagetitle']);
$row_off['pagetitle']=str_replace(">", "&gt;" , $row_off['pagetitle']);
$row_off['pagetitle']=str_replace("<", "&lt;" , $row_off['pagetitle']);
$row_off['pagetitle']=str_replace("'", "&apos;" , $row_off['pagetitle']);

?>

<offer id="<?php echo $row_off['id']; ?>" available="true">
      <url>http://www.cddiski.ru/catalog/item?id=<?php echo $row_off['id']; ?></url>
       <price><?php echo $row_off['retail_price']; ?></price>
       <currencyId>RUR</currencyId>
       <categoryId><?php echo $row_off['parent']; ?></categoryId>
       <picture>http://www.cddiski.ru/<?php echo $row_off['value'];  ?></picture><name><?php echo $row_off['pagetitle']; ?></name><description></description></offer><?php } while ($row_off = mysql_fetch_assoc($off)); ?>
       
  <?php     
$id = 2150;
$deep = isset($deep) ? intval($deep) : 5;
$showinmenu = isset($showinmenu) ? intval($showinmenu) : 0;
$childs = $modx->getChildIds($id, $deep);
if (is_array($childs) && count($childs)>0) $childs = implode(',',$childs);
else $childs = $id;
if ($showinmenu == 1) {
    $sql = "SELECT id FROM ".$modx->getFullTableName("site_content")." WHERE id IN(".$childs.") AND hidemenu=0 AND deleted=0 AND published=1";   
    $result = $modx->dbQuery($sql);           
    $numResults = @$modx->recordCount($result);    
    $child_ = array();       
    for($i=0;$i<$numResults;$i++)  {
        $row = $modx->fetchRow($result);
        $child_[] = $row['id'];
    }
    $childs = implode(',',$child_);
}



$query_off = "SELECT 
modx_site_ec_items.id, modx_site_ec_items.parent, modx_site_ec_items.sell, modx_site_ec_items.retail_price, modx_site_ec_items.pagetitle, tmplvarid,
value, itemid

 FROM  modx_site_ec_items, modx_site_tmplvar_ec_itemvalues WHERE 
modx_site_tmplvar_ec_itemvalues.itemid=modx_site_ec_items.id and modx_site_tmplvar_ec_itemvalues.tmplvarid=40 and sell=1 and (popular=1 or new =1)
and modx_site_ec_items.parent IN  (".$childs.")

 ";
$off = $modx->dbQuery($query_off);
$row_off = mysql_fetch_assoc($off);



 do { 
 
$row_off['value']=str_replace("\"", "&quot;" , $row_off['value']);
$row_off['value']=str_replace("&", "&amp;" , $row_off['value']);
$row_off['value']=str_replace(">", "&gt;" , $row_off['value']);
$row_off['value']=str_replace("<", "&lt;" , $row_off['value']);
$row_off['value']=str_replace("'", "&apos;" , $row_off['value']);


$row_off['pagetitle']=str_replace("\"", "&quot;" , $row_off['pagetitle']);
$row_off['pagetitle']=str_replace("&", "&amp;" , $row_off['pagetitle']);
$row_off['pagetitle']=str_replace(">", "&gt;" , $row_off['pagetitle']);
$row_off['pagetitle']=str_replace("<", "&lt;" , $row_off['pagetitle']);
$row_off['pagetitle']=str_replace("'", "&apos;" , $row_off['pagetitle']);

?>

<offer id="<?php echo $row_off['id']; ?>" available="true">
      <url>http://www.cddiski.ru/catalog/item?id=<?php echo $row_off['id']; ?></url>
       <price><?php echo $row_off['retail_price']; ?></price>
       <currencyId>RUR</currencyId>
       <categoryId><?php echo $row_off['parent']; ?></categoryId>
       <picture>http://www.cddiski.ru/<?php echo $row_off['value'];  ?></picture><name><?php echo $row_off['pagetitle']; ?></name><description></description></offer><?php } while ($row_off = mysql_fetch_assoc($off)); ?>
       
       
  <?php     
$id = 2225;
$deep = isset($deep) ? intval($deep) : 5;
$showinmenu = isset($showinmenu) ? intval($showinmenu) : 0;
$childs = $modx->getChildIds($id, $deep);
if (is_array($childs) && count($childs)>0) $childs = implode(',',$childs);
else $childs = $id;
if ($showinmenu == 1) {
    $sql = "SELECT id FROM ".$modx->getFullTableName("site_content")." WHERE id IN(".$childs.") AND hidemenu=0 AND deleted=0 AND published=1";   
    $result = $modx->dbQuery($sql);           
    $numResults = @$modx->recordCount($result);    
    $child_ = array();       
    for($i=0;$i<$numResults;$i++)  {
        $row = $modx->fetchRow($result);
        $child_[] = $row['id'];
    }
    $childs = implode(',',$child_);
}



$query_off = "SELECT 
modx_site_ec_items.id, modx_site_ec_items.parent, modx_site_ec_items.sell, modx_site_ec_items.retail_price, modx_site_ec_items.pagetitle, tmplvarid,
value, itemid

 FROM  modx_site_ec_items, modx_site_tmplvar_ec_itemvalues WHERE 
modx_site_tmplvar_ec_itemvalues.itemid=modx_site_ec_items.id and modx_site_tmplvar_ec_itemvalues.tmplvarid=40 and sell=1 
and modx_site_ec_items.parent IN  (".$childs.")

 ";
$off = $modx->dbQuery($query_off);
$row_off = mysql_fetch_assoc($off);



 do { 
 
$row_off['value']=str_replace("\"", "&quot;" , $row_off['value']);
$row_off['value']=str_replace("&", "&amp;" , $row_off['value']);
$row_off['value']=str_replace(">", "&gt;" , $row_off['value']);
$row_off['value']=str_replace("<", "&lt;" , $row_off['value']);
$row_off['value']=str_replace("'", "&apos;" , $row_off['value']);


$row_off['pagetitle']=str_replace("\"", "&quot;" , $row_off['pagetitle']);
$row_off['pagetitle']=str_replace("&", "&amp;" , $row_off['pagetitle']);
$row_off['pagetitle']=str_replace(">", "&gt;" , $row_off['pagetitle']);
$row_off['pagetitle']=str_replace("<", "&lt;" , $row_off['pagetitle']);
$row_off['pagetitle']=str_replace("'", "&apos;" , $row_off['pagetitle']);

?>

<offer id="<?php echo $row_off['id']; ?>" available="true">
      <url>http://www.cddiski.ru/catalog/item?id=<?php echo $row_off['id']; ?></url>
       <price><?php echo $row_off['retail_price']; ?></price>
       <currencyId>RUR</currencyId>
       <categoryId><?php echo $row_off['parent']; ?></categoryId>
       <picture>http://www.cddiski.ru/<?php echo $row_off['value'];  ?></picture><name><?php echo $row_off['pagetitle']; ?></name><description></description></offer><?php } while ($row_off = mysql_fetch_assoc($off)); ?>
       
       
  <?php     
$id = 2442;
$deep = isset($deep) ? intval($deep) : 5;
$showinmenu = isset($showinmenu) ? intval($showinmenu) : 0;
$childs = $modx->getChildIds($id, $deep);
if (is_array($childs) && count($childs)>0) $childs = implode(',',$childs);
else $childs = $id;
if ($showinmenu == 1) {
    $sql = "SELECT id FROM ".$modx->getFullTableName("site_content")." WHERE id IN(".$childs.") AND hidemenu=0 AND deleted=0 AND published=1";   
    $result = $modx->dbQuery($sql);           
    $numResults = @$modx->recordCount($result);    
    $child_ = array();       
    for($i=0;$i<$numResults;$i++)  {
        $row = $modx->fetchRow($result);
        $child_[] = $row['id'];
    }
    $childs = implode(',',$child_);
}



$query_off = "SELECT 
modx_site_ec_items.id, modx_site_ec_items.parent, modx_site_ec_items.sell, modx_site_ec_items.retail_price, modx_site_ec_items.pagetitle, tmplvarid,
value, itemid

 FROM  modx_site_ec_items, modx_site_tmplvar_ec_itemvalues WHERE 
modx_site_tmplvar_ec_itemvalues.itemid=modx_site_ec_items.id and modx_site_tmplvar_ec_itemvalues.tmplvarid=40 and sell=1 
and modx_site_ec_items.parent IN  (".$childs.")

 ";
$off = $modx->dbQuery($query_off);
$row_off = mysql_fetch_assoc($off);



 do { 
 
$row_off['value']=str_replace("\"", "&quot;" , $row_off['value']);
$row_off['value']=str_replace("&", "&amp;" , $row_off['value']);
$row_off['value']=str_replace(">", "&gt;" , $row_off['value']);
$row_off['value']=str_replace("<", "&lt;" , $row_off['value']);
$row_off['value']=str_replace("'", "&apos;" , $row_off['value']);


$row_off['pagetitle']=str_replace("\"", "&quot;" , $row_off['pagetitle']);
$row_off['pagetitle']=str_replace("&", "&amp;" , $row_off['pagetitle']);
$row_off['pagetitle']=str_replace(">", "&gt;" , $row_off['pagetitle']);
$row_off['pagetitle']=str_replace("<", "&lt;" , $row_off['pagetitle']);
$row_off['pagetitle']=str_replace("'", "&apos;" , $row_off['pagetitle']);

?>

<offer id="<?php echo $row_off['id']; ?>" available="true">
      <url>http://www.cddiski.ru/catalog/item?id=<?php echo $row_off['id']; ?></url>
       <price><?php echo $row_off['retail_price']; ?></price>
       <currencyId>RUR</currencyId>
       <categoryId><?php echo $row_off['parent']; ?></categoryId>
       <picture>http://www.cddiski.ru/<?php echo $row_off['value'];  ?></picture><name><?php echo $row_off['pagetitle']; ?></name><description></description></offer><?php } while ($row_off = mysql_fetch_assoc($off)); ?>
  

  <?php     
$id = 2441;
$deep = isset($deep) ? intval($deep) : 5;
$showinmenu = isset($showinmenu) ? intval($showinmenu) : 0;
$childs = $modx->getChildIds($id, $deep);
if (is_array($childs) && count($childs)>0) $childs = implode(',',$childs);
else $childs = $id;
if ($showinmenu == 1) {
    $sql = "SELECT id FROM ".$modx->getFullTableName("site_content")." WHERE id IN(".$childs.") AND hidemenu=0 AND deleted=0 AND published=1";   
    $result = $modx->dbQuery($sql);           
    $numResults = @$modx->recordCount($result);    
    $child_ = array();       
    for($i=0;$i<$numResults;$i++)  {
        $row = $modx->fetchRow($result);
        $child_[] = $row['id'];
    }
    $childs = implode(',',$child_);
}



$query_off = "SELECT 
modx_site_ec_items.id, modx_site_ec_items.parent, modx_site_ec_items.sell, modx_site_ec_items.retail_price, modx_site_ec_items.pagetitle, tmplvarid,
value, itemid

 FROM  modx_site_ec_items, modx_site_tmplvar_ec_itemvalues WHERE 
modx_site_tmplvar_ec_itemvalues.itemid=modx_site_ec_items.id and modx_site_tmplvar_ec_itemvalues.tmplvarid=40 and sell=1 
and modx_site_ec_items.parent IN  (".$childs.")

 ";
$off = $modx->dbQuery($query_off);
$row_off = mysql_fetch_assoc($off);



 do { 
 
$row_off['value']=str_replace("\"", "&quot;" , $row_off['value']);
$row_off['value']=str_replace("&", "&amp;" , $row_off['value']);
$row_off['value']=str_replace(">", "&gt;" , $row_off['value']);
$row_off['value']=str_replace("<", "&lt;" , $row_off['value']);
$row_off['value']=str_replace("'", "&apos;" , $row_off['value']);


$row_off['pagetitle']=str_replace("\"", "&quot;" , $row_off['pagetitle']);
$row_off['pagetitle']=str_replace("&", "&amp;" , $row_off['pagetitle']);
$row_off['pagetitle']=str_replace(">", "&gt;" , $row_off['pagetitle']);
$row_off['pagetitle']=str_replace("<", "&lt;" , $row_off['pagetitle']);
$row_off['pagetitle']=str_replace("'", "&apos;" , $row_off['pagetitle']);

?>

<offer id="<?php echo $row_off['id']; ?>" available="true">
      <url>http://www.cddiski.ru/catalog/item?id=<?php echo $row_off['id']; ?></url>
       <price><?php echo $row_off['retail_price']; ?></price>
       <currencyId>RUR</currencyId>
       <categoryId><?php echo $row_off['parent']; ?></categoryId>
       <picture>http://www.cddiski.ru/<?php echo $row_off['value'];  ?></picture><name><?php echo $row_off['pagetitle']; ?></name><description></description></offer><?php } while ($row_off = mysql_fetch_assoc($off)); ?>
  

  <?php     
$id = 2440;
$deep = isset($deep) ? intval($deep) : 5;
$showinmenu = isset($showinmenu) ? intval($showinmenu) : 0;
$childs = $modx->getChildIds($id, $deep);
if (is_array($childs) && count($childs)>0) $childs = implode(',',$childs);
else $childs = $id;
if ($showinmenu == 1) {
    $sql = "SELECT id FROM ".$modx->getFullTableName("site_content")." WHERE id IN(".$childs.") AND hidemenu=0 AND deleted=0 AND published=1";   
    $result = $modx->dbQuery($sql);           
    $numResults = @$modx->recordCount($result);    
    $child_ = array();       
    for($i=0;$i<$numResults;$i++)  {
        $row = $modx->fetchRow($result);
        $child_[] = $row['id'];
    }
    $childs = implode(',',$child_);
}



$query_off = "SELECT 
modx_site_ec_items.id, modx_site_ec_items.parent, modx_site_ec_items.sell, modx_site_ec_items.retail_price, modx_site_ec_items.pagetitle, tmplvarid,
value, itemid

 FROM  modx_site_ec_items, modx_site_tmplvar_ec_itemvalues WHERE 
modx_site_tmplvar_ec_itemvalues.itemid=modx_site_ec_items.id and modx_site_tmplvar_ec_itemvalues.tmplvarid=40 and sell=1 
and modx_site_ec_items.parent IN  (".$childs.")

 ";
$off = $modx->dbQuery($query_off);
$row_off = mysql_fetch_assoc($off);



 do { 
 
$row_off['value']=str_replace("\"", "&quot;" , $row_off['value']);
$row_off['value']=str_replace("&", "&amp;" , $row_off['value']);
$row_off['value']=str_replace(">", "&gt;" , $row_off['value']);
$row_off['value']=str_replace("<", "&lt;" , $row_off['value']);
$row_off['value']=str_replace("'", "&apos;" , $row_off['value']);


$row_off['pagetitle']=str_replace("\"", "&quot;" , $row_off['pagetitle']);
$row_off['pagetitle']=str_replace("&", "&amp;" , $row_off['pagetitle']);
$row_off['pagetitle']=str_replace(">", "&gt;" , $row_off['pagetitle']);
$row_off['pagetitle']=str_replace("<", "&lt;" , $row_off['pagetitle']);
$row_off['pagetitle']=str_replace("'", "&apos;" , $row_off['pagetitle']);

?>

<offer id="<?php echo $row_off['id']; ?>" available="true">
      <url>http://www.cddiski.ru/catalog/item?id=<?php echo $row_off['id']; ?></url>
       <price><?php echo $row_off['retail_price']; ?></price>
       <currencyId>RUR</currencyId>
       <categoryId><?php echo $row_off['parent']; ?></categoryId>
       <picture>http://www.cddiski.ru/<?php echo $row_off['value'];  ?></picture><name><?php echo $row_off['pagetitle']; ?></name><description></description></offer><?php } while ($row_off = mysql_fetch_assoc($off)); ?>
  

  <?php     
$id = 2471;
$deep = isset($deep) ? intval($deep) : 5;
$showinmenu = isset($showinmenu) ? intval($showinmenu) : 0;
$childs = $modx->getChildIds($id, $deep);
if (is_array($childs) && count($childs)>0) $childs = implode(',',$childs);
else $childs = $id;
if ($showinmenu == 1) {
    $sql = "SELECT id FROM ".$modx->getFullTableName("site_content")." WHERE id IN(".$childs.") AND hidemenu=0 AND deleted=0 AND published=1";   
    $result = $modx->dbQuery($sql);           
    $numResults = @$modx->recordCount($result);    
    $child_ = array();       
    for($i=0;$i<$numResults;$i++)  {
        $row = $modx->fetchRow($result);
        $child_[] = $row['id'];
    }
    $childs = implode(',',$child_);
}



$query_off = "SELECT 
modx_site_ec_items.id, modx_site_ec_items.parent, modx_site_ec_items.sell, modx_site_ec_items.retail_price, modx_site_ec_items.pagetitle, tmplvarid,
value, itemid

 FROM  modx_site_ec_items, modx_site_tmplvar_ec_itemvalues WHERE 
modx_site_tmplvar_ec_itemvalues.itemid=modx_site_ec_items.id and modx_site_tmplvar_ec_itemvalues.tmplvarid=40 and sell=1 
and modx_site_ec_items.parent IN  (".$childs.")

 ";
$off = $modx->dbQuery($query_off);
$row_off = mysql_fetch_assoc($off);



 do { 
 
$row_off['value']=str_replace("\"", "&quot;" , $row_off['value']);
$row_off['value']=str_replace("&", "&amp;" , $row_off['value']);
$row_off['value']=str_replace(">", "&gt;" , $row_off['value']);
$row_off['value']=str_replace("<", "&lt;" , $row_off['value']);
$row_off['value']=str_replace("'", "&apos;" , $row_off['value']);


$row_off['pagetitle']=str_replace("\"", "&quot;" , $row_off['pagetitle']);
$row_off['pagetitle']=str_replace("&", "&amp;" , $row_off['pagetitle']);
$row_off['pagetitle']=str_replace(">", "&gt;" , $row_off['pagetitle']);
$row_off['pagetitle']=str_replace("<", "&lt;" , $row_off['pagetitle']);
$row_off['pagetitle']=str_replace("'", "&apos;" , $row_off['pagetitle']);

?>

<offer id="<?php echo $row_off['id']; ?>" available="true">
      <url>http://www.cddiski.ru/catalog/item?id=<?php echo $row_off['id']; ?></url>
       <price><?php echo $row_off['retail_price']; ?></price>
       <currencyId>RUR</currencyId>
       <categoryId><?php echo $row_off['parent']; ?></categoryId>
       <picture>http://www.cddiski.ru/<?php echo $row_off['value'];  ?></picture><name><?php echo $row_off['pagetitle']; ?></name><description></description></offer><?php } while ($row_off = mysql_fetch_assoc($off)); ?></offers></shop></yml_catalog>
?>
