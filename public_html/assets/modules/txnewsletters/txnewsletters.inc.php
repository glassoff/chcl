<?php

ini_set ('max_execution_time', 360);
date_default_timezone_set('UTC');

if (version_compare(PHP_VERSION,'5','>='))
  require_once('domxml-php4-to-php5.php'); //Load the PHP5 converter

  function xmlnode2array($node) {
    global $modx;
   if ($node->type==XML_ELEMENT_NODE) {
       if ($attrArray = $node->attributes()) {
           // parse attributes //
           foreach($attrArray AS $attr) {
               $out['ATTRIBUTE'][$attr->name] = $attr->value;
           }
       }
       if ($childArray = $node->children()) {
           // add child nodes //
           foreach($childArray AS $child) {
               if ($child->type==XML_ELEMENT_NODE) {
                   $out[$child->tagname][] = xmlnode2array($child);
               } else {
                   if ($content = xmlnode2array($child))
                       $out['CONTENT'] = $content;
               }
           }
       }

   } else {
       // this is a CONTENT NODE //
       $out = trim($node->content);
       if (!$out) return false;
   }
   return $out;
   
  }
  function getFields($fichier) {
    if(!$dom = @domxml_open_file($fichier)) {
      echo $modx->TXNewsletters['txtlang']['xml_error_read']."\n";
      exit;
    }
    $root = $dom->document_element();
    $tab = xmlnode2array($root);
    foreach($tab as $id => $value) {
      $fields = $value;
    }
    return $fields;
  }
  
////////////////////////////////////////////////////////////////////////////////

  function genCSV($mySQL,$quote=true){
    global $modx;
    
    $csv = new MakeCsv('txnewsletters.export.csv');
    
    foreach($modx->TXNewsletters['fields'] as $id => $field){
      $data = utf8_decode($field['ATTRIBUTE']['name']);
      if($quote){
        $pattern = '/(.*)(")(.*)/i';
        $replace = '$1""$3';
        $data = preg_replace($pattern,$replace,$data);
      }
      $titres[]= $data;
    }
    
    $titres[] = "Date";
    $titres[] = "Link_Unsubscribe";
    
    $csv->addLine($titres,1);
    
    $results = $modx->db->query($mySQL);
    
    while($row=mysql_fetch_assoc($results)) {
      foreach($modx->TXNewsletters['fields'] as $id => $field){
        $data = utf8_decode($row[$field['ATTRIBUTE']['name']]);
        if($quote){
        $pattern = '/(.*)(")(.*)/i';
        $replace = '$1""$3';
        $data = preg_replace($pattern,$replace,$data);
        }
        $lignes[$n][$field['ATTRIBUTE']['name']] = $data;
        if($field['ATTRIBUTE']['type']=="email"){$email = $row[$field['ATTRIBUTE']['name']];}
      }
	  $lignes[$n]['ControlMD5'] = ControlMD5($email,utf8_decode($row['Timestamp']));
      $lignes[$n]['Timestamp'] = utf8_decode($row['Timestamp']);
      $n++;
    }
    
    $j=2;
    foreach ($lignes as $n => $data){
      $linearray = false;
      foreach($modx->TXNewsletters['fields'] as $id => $field){
        $linearray[] = $data[$field['ATTRIBUTE']['name']]; 
        if($field['ATTRIBUTE']['type']=="email"){$email = $data[$field['ATTRIBUTE']['name']];}
      }
      
      $timestamp_date = date('d/m/Y', $data['Timestamp']);
      $linearray[] = $timestamp_date;
      
      $MD5 = $data['ControlMD5'];
      
      $link = 'http://'.$_SERVER['HTTP_HOST'].'/index.php?&id='.$modx->TXNewsletters['idPageUnsubscribe'].'&action=del&email='.$email.'&control='.$MD5.'';
      $linearray[] = $link;
      
      $csv->addLine($linearray,$j);
      $j++;
    }
    
    $csv->downloadCsv();
    
  
  }
  
////////////////////////////////////////////////////////////////////////////////

  function importCSVHTML(){
    global $modx;
    
    $base_path = $modx->config['base_path'];
    $error = false;
    
    if($_POST['importSend']=='ok'){
      if(@is_uploaded_file($_FILES['csvUpload']['tmp_name'])) {
        $html .='<span style="color:#ff9900;">'.$modx->TXNewsletters['txtlang']['import_labels_name'].' : '.$_FILES['csvUpload']['name']."</span><br />\n";
        $html .='<span style="color:#ff9900;">'.$modx->TXNewsletters['txtlang']['import_labels_size'].' : '.$_FILES['csvUpload']['size']." octets</span><br />\n";
      }
      else {
        $html .='<span style="color:#ff3300;">'.$modx->TXNewsletters['txtlang']['import_labels_error'].' ? : '.$_FILES['csvUpload']['error']."</span>\n";
        $error = true;
      }
  
      if(!$error){
        $tmpMODx = $base_path.'assets/upload/';
        if(!@move_uploaded_file($_FILES['csvUpload']['tmp_name'], $tmpMODx."txnewsletters.datas.csv")){
          $html .= '<span style="color:#ff3300;">'.$modx->TXNewsletters['txtlang']['import_error_upload'].' '.$tmpMODx.' ! </span><br />'."\n";
        }
        else {
          $html .= '<span style="color:#009900;">'.$modx->TXNewsletters['txtlang']['import_file_ready'].'</span><br />'."\n";
          $html .= importCSV($tmpMODx."txnewsletters.datas.csv");
        }
      }
    }
    $html .='<form method="post" enctype="multipart/form-data" action=""><div style="">'."\n";
    $html .='<br />'.$modx->TXNewsletters['txtlang']['import_labels_comment1'].'<br /><br />'."\n";
    $html .='<input name="formmenuaction" type="hidden" value="import">'."\n";
    $html .='<input name="importSend" type="hidden" value="ok">'."\n";
    $html .='<input type="file" name="csvUpload" size="30">'."\n";
    $html .='<input type="submit" name="upload" value="Uploader">'."\n";
    $html .='</div></form>'."\n";
    
    return $html;
    
  }
  
////////////////////////////////////////////////////////////////////////////////

  function importCSV($fichier_import){
    global $modx;
    
// LECTURE DU FICHIER
    $fp=fopen($fichier_import,"r" );
    
    while (!feof ($fp)) {
    
      $data =  fgets($fp,1024);
      
      if($data && $data!="" && $data!=" "){
      
        $ligne = explode(";",$data);
        
        $t = 0;
        foreach($ligne as $i => $data){
          $data = utf8_encode($data);
          $pattern = '/(")(.*)/i';
          $replace = '$2';
          $ligne[$i] = preg_replace($pattern,$replace,$data);
          $data = $ligne[$i];
          $pattern = '/(.*)(")/i';
          $replace = '$1';
          $ligne[$i] = preg_replace($pattern,$replace,$data);
          $t++;
        }

        if($t>=1){
          $datas[] = $ligne;
        }
      
      }
    
    }
    
    fclose($fp);
    
    
// RECUPERATIONS DES TITRES DE COLONNE  
    $nb = 0;
    foreach($modx->TXNewsletters['fields'] as $id => $field){
      $nb++;
      $titresBD[] = $field['ATTRIBUTE']['name'];
    }
    $titresBD[] = "Date";
    $nb++;
    
    $i = 1;
    foreach($datas[0] as $id => $data){
      if($i<=$nb){
        $titres[] = $data;
      }
      $i++;
    }
    
    
// COMPARAISON AVEC LA STRUCTURE EXISTANTE    
    $erreur = false;
    if($titresBD==$titres){
      $html .= '<span style="color:#009900;">'.$modx->TXNewsletters['txtlang']['import_msg_structure_ok'].'</span><br />';
      $html .= '<div style="padding:2px;margin:2px;border-top:1px solid #cccccc;border-bottom:1px solid #cccccc;overflow:auto;height:80px;">'."\n";    
    
// RECUPERATIONS DES LIGNES DE DONNEES
    $n = 0;
    foreach($datas as $i => $ligne){
      if($i>0){
      $t = 0;
      foreach($ligne as $id => $data){
        if($titres[$id]){
          $lignes[$n][$titres[$id]] = $data;
        }
        $t++;
      }
      $n++;
      }
    }

// RECUPERATIONS DES DONNEES DE LA BDD
    $mySQL ="SELECT * FROM `".$modx->TXNewsletters['tb_prefix']."txnewsletters_usersdata` ";
    $mySQL .=";";
    
    $results = $modx->db->query($mySQL);
    
    $n = 0;
    while($row=mysql_fetch_assoc($results)) {
      foreach($modx->TXNewsletters['fields'] as $id => $field){
        $data = $row[$field['ATTRIBUTE']['name']];
        $lignesBD[$n][$field['ATTRIBUTE']['name']] = $data;
      }
      $n++;
    }

// RETRAIT DES ENTREES EXISTANTES OU INVALIDES
     
    foreach($modx->TXNewsletters['fields'] as $id => $field){
      if($field['ATTRIBUTE']['type']=="email") {
        $champ_email = $id;
      }
    }

    $new_emails = array();
    $nblignes = 0;
    foreach($lignes as $i => $ligne){
      $nblignes ++;
      $email_invalid[$i] = true;
      foreach($ligne as $id => $data){
        if($lignes[$i]){
        if($id==$modx->TXNewsletters['fields'][$champ_email]['ATTRIBUTE']['name']){ 
// TESTE VALIDITE EMAIL                   
          unset($email_invalid[$i]);
          $valid = false;
          if ((preg_match('`^[[:alnum:]]([-_.]?[[:alnum:]])*@[[:alnum:]]([-_.]?[[:alnum:]])*\.([a-z]{2,4})$`',$data))){
          // Uncomment to activate DNS check for email domains
		  //  $domaine = substr(strstr($data, '@'),1);
          //  if (checkdnsrr($domaine)){$valid= true;}
            $valid = true;

          }
          if(!$valid){
            $html .= '<span style="color:#ff9900;">'.$modx->TXNewsletters['txtlang']['import_email'].' ('.$data.') ! -> '.$modx->TXNewsletters['txtlang']['import_labels_not_add'].' </span><br />';
            unset($lignes[$i]);
          }
// TESTE DOUBLON EMAIL DANS LE FICHIER IMPORT
          elseif(in_array($data,$new_emails)) {
            $html .= '<span style="color:#ff9900;">'.$modx->TXNewsletters['txtlang']['import_email_doubble'].' ('.$data.') ! -> '.$modx->TXNewsletters['txtlang']['import_labels_not_add'].' </span><br />';
            unset($lignes[$i]);
          }
// TESTE DOUBLON EMAIL DANS LA BDD
          elseif($lignesBD) {
            foreach($lignesBD as $j => $ligneBD){
              if($data==$ligneBD[$id]){
                $html .= '<span style="color:#ff9900;">'.$modx->TXNewsletters['txtlang']['import_email_exist'].' ('.$data.') ! -> '.$modx->TXNewsletters['txtlang']['import_labels_not_add'].' </span><br />';
                unset($lignes[$i]);
              }
            }
            
          }
          $new_email = $data;
        }
        else {
// TESTE CHAMP DATE
          if($id == "Date"){
            $date_ok = false;
            $tab = explode("/",$data);
            if(isset($tab[0]) && isset($tab[1]) &&isset($tab[2])){
            $timestamp = mktime(12,0,0,$tab[1],$tab[0],$tab[2]);
            $deadline  = mktime(0, 0, 0, 01,  01, 2000);
              if($timestamp>$deadline){
                $date_ok= true;
              }
            }
            if(!$date_ok){
              $timestamp = time();
              $html .= '<span style="color:#ff9900;">'.$modx->TXNewsletters['txtlang']['import_labels_date_adjust'].' ('.$data.') => '.date('d/m/Y', $timestamp).' ! </span><br />';
            }
			$lignes[$i]["Date"] = $timestamp;
          }
// TESTE CHAMP OBLIGATOIRE VIDE
          foreach($modx->TXNewsletters['fields'] as $j => $field){
          if($field['ATTRIBUTE']['name']==$id){
              if($field['ATTRIBUTE']['mandatory']=="yes" && $field['ATTRIBUTE']['name']==$id && isset($lignes[$i])){
                if(!$data or $data=="" or $data=="  "){
                  if($field['default'][0]['CONTENT'] && $field['default'][0]['CONTENT']!="" && $field['default'][0]['CONTENT']!=" "){
                    $lignes[$i][$id] = $field['default'][0]['CONTENT'];
                  }
                  else {
                    $html .= '<span style="color:#ff9900;">'.$modx->TXNewsletters['txtlang']['import_labels_mandatory_notok'].' : '.$field['ATTRIBUTE']['name'].' = \' '.$data.' \' ('.$ligne[$modx->TXNewsletters['fields'][$champ_email]['ATTRIBUTE']['name']].') ! -> '.$modx->TXNewsletters['txtlang']['import_labels_not_add'].' </span><br />';
                    unset($lignes[$i]);
                  }
                }
              }
              if($field['ATTRIBUTE']['type']=="list" && $field['ATTRIBUTE']['name']==$id && isset($lignes[$i])){
                if(!$data or $data=="" or $data==" "){
                  if($field['default'][0]['CONTENT'] && $field['default'][0]['CONTENT']!="" && $field['default'][0]['CONTENT']!=" "){
                    $lignes[$i][$id] = $field['default'][0]['CONTENT'];
                  }
                  else {
                    $html .= '<span style="color:#ff9900;">'.$modx->TXNewsletters['txtlang']['import_labels_mandatory_notok'].' : '.$field['ATTRIBUTE']['name'].' = \' '.$data.' \' ('.$ligne[$modx->TXNewsletters['fields'][$champ_email]['ATTRIBUTE']['name']].') ! -> '.$modx->TXNewsletters['txtlang']['import_labels_not_add'].' </span><br />';
                    unset($lignes[$i]);
                  }
                }
                else {
                  $tab = explode("||",$data);
                  if($tab && isset($lignes[$i])){
                    foreach($tab as $id => $val){
                      $tab2 = explode("|",$val);
                      $valid = false;
                      foreach($field['values'][0]['value'] as $id => $value){
                        if($value['ATTRIBUTE']['id']==$tab2[0]){
                          $valid = true;
                        }
                      }
                      if(!$valid){
                        $html .= '<span style="color:#ff9900;">'.$modx->TXNewsletters['txtlang']['import_labels_option_notok'].' : '.$field['ATTRIBUTE']['name'].' = \' '.$data.' \' ('.$ligne[$modx->TXNewsletters['fields'][$champ_email]['ATTRIBUTE']['name']].') ! -> '.$modx->TXNewsletters['txtlang']['import_labels_not_add'].' </span><br />';
                        unset($lignes[$i]);
                      }
                    }
                  }
                }              
              }
            }
          }
        }
        }  
      }
      if($email_invalid){
        foreach($email_invalid as $id => $val){
            unset($lignes[$id]);
        }
      }
      $new_emails[] = $new_email;
    }
    $html .= '</div>'."\n";
// GENERATION SQL
  
    $mySQL1 ="INSERT INTO `".$modx->TXNewsletters['tb_prefix']."txnewsletters_usersdata` ";
    
    $mySQL2 = "( ";
    foreach($modx->TXNewsletters['fields'] as $id => $field){
      $mySQL2 .= "`".$field['ATTRIBUTE']['name']."`";
      $mySQL2 .= ", ";
    }
    $mySQL2 .= "`Timestamp` ";
    $mySQL2 .= ") ";
    
    $mySQL3 = "VALUES \n";
    $mySQL4 = "; ";
    
    $n = 0;
    $nblignesok = 0;
    foreach($lignes as $i => $ligne) {
        $mySQL_in .= ' (';
        foreach($modx->TXNewsletters['fields'] as $id => $field){
          if($field['ATTRIBUTE']['name']=="Email") {
            $email = $ligne[$field['ATTRIBUTE']['name']];
          }
          $mySQL_in .= "'".delCode($ligne[$field['ATTRIBUTE']['name']])."'";
          $mySQL_in .= ", ";
          
        }
        $nblignesok ++;

        $mySQL_in .= "'".$ligne['Date']."'";
        
        $mySQL_in .= '), '."\n";
        $n++;
      if($n==10){
        $mySQL_in = substr($mySQL_in,0,strlen($mySQL_in)-3);
        $mySQL_in .= "\n";
        $sql[] = $mySQL1.$mySQL2.$mySQL3.$mySQL_in.$mySQL4;
        $n = 0;
        $mySQL_in = '';
      }
    }
    if(!$sql && $mySQL_in){
      $mySQL_in = substr($mySQL_in,0,strlen($mySQL_in)-3);
      $mySQL_in .= "\n";
      $sql[] = $mySQL1.$mySQL2.$mySQL3.$mySQL_in.$mySQL4;
    }

// EXECUTION SQL
    $nb = count($sql);
    $n = 0;
    $last_pourcent=0;
    if($sql){
    $html .= '<span style="color:#009900;"> |';
    foreach($sql as $id => $val){
      $results = $modx->db->query($val);
      if ($results){
        $pourcent = round((($n/$nb)*100), 1); 
        if($pourcent>=($last_pourcent+10)){
          $html .= '==';
          $last_pourcent=$pourcent;
        }
        $n++;
      }
      else {
        $html .= $modx->TXNewsletters['txtlang']['error_sql'].' : <br />';
        $html .= $val.'<br />';
      }
    }
    
    $pourcent = round((($n/$nb)*100), 1);
    $html .= '==> '.$pourcent.'% '.$modx->TXNewsletters['txtlang']['import_percent_done'].'</span><br />';
    
    if($pourcent==100){
      $html .= '<span style="color:#009900;">'.$modx->TXNewsletters['txtlang']['import_items_checks'].' : '.$nblignes.'</span><br />'."\n";
      $html .= '<span style="color:#009900;">'.$modx->TXNewsletters['txtlang']['import_items_add'].' : '.$nblignesok.'</span><br />'."\n";
      $html .= '<span style="color:#009900;">'.$modx->TXNewsletters['txtlang']['import_items_complete'].' </span><br />';
    }
    else {
      $html .= '<span style="color:#009900;">'.$modx->TXNewsletters['txtlang']['import_items_notcomplete'].'</span><br />';
    }
    }
    else {
      $html .= '<span style="color:#009900;">'.$modx->TXNewsletters['txtlang']['import_items_checks'].' : '.$nblignes.'</span><br />'."\n";
      $html .= '<span style="color:#009900;">'.$modx->TXNewsletters['txtlang']['import_items_complete_noadd'].'</span><br />';
    }
    
    }
    else {
      $html .= '<span style="color:#ff3300;">'.$modx->TXNewsletters['txtlang']['import_msg_structure_notok'].'</span><br />';
    }
    
    
    return $html;
  
  }
  
////////////////////////////////////////////////////////////////////////////////

  function export_html ($newslettersBaseId) {
    global $modx;
  
  $html = '<div>'.$modx->TXNewsletters['txtlang']['import_export_msg_title'].'</div><br />'."\n";
  $html = '<div>'."\n";
  $html .='<form method="post" >'."\n";
  $list = BoucleNL($newslettersBaseId,-1);
  $list = $list[$newslettersBaseId]["sub"];
  $html .='<label class="TXNewsletters_form_labelGauche" for="newsletterId">'.$modx->TXNewsletters['txtlang']['import_export_label_choose'].' : </label>';
  $html .='<select name="newsletterId" id="newsletterId" >';
  if($list){
  foreach ($list as $id => $value) {
    $html .='<option value="'.$id.'"> '.$value['pagetitle'].'&nbsp;&nbsp;&nbsp; </option>';
  }
  }
  $html .='</select> '."\n";

  $html .='<input name="formmenuaction" type="hidden" value="export_html_send">'."\n";
  $html .='<input type="submit" value="'.$modx->TXNewsletters['txtlang']['import_export_label_submit'].'" />'."\n";
  $html .='</form>'."\n";
  $html .='</div>'."\n";
  return $html;
  }
  
////////////////////////////////////////////////////////////////////////////////

  function export_html_send ($newsletterId) {
    global $modx;  
    $html = sendHTML($newsletterId);
    
    header("Content-disposition: attachment; filename=\"Newsletter.".$newsletterId.".html\"");
    header("Content-Type: application/force-download");
    header("Content-Transfer-Encoding: binary");
    header("Content-Type: application/octet-stream");
    header("Content-Type: application/download"); 
    header("Content-Type: text/html");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    
    echo $html;
    
    echo "\r\n";

    exit();
  }

////////////////////////////////////////////////////////////////////////////////

  function formHTMLMod($action,$Err=NULL) {
    global $modx;

    foreach($_POST as $id => $val){
      $pattern = '/([^"]*)(")([^"]*)/i';
      $replace = '$1&quot;$3';
      $_POST[$id] = preg_replace($pattern,$replace,$val);
      $pattern = '/([^\']*)(\')([^\']*)/i';
      $replace = '$1&apos;$3';
      $_POST[$id] = preg_replace($pattern,$replace,$_POST[$id]);
    }
    $html ='<div class="TXNewsletters_form">'."\n";
    
    $html .='<form method="post" action="http://'.$_SERVER['HTTP_HOST'].'/index.php?&id='.$modx->TXNewsletters['idPageUnsubscribe'].'" >'."\n";
    $html .='<input type="hidden" name="lastEmail" value="'.$email.'" />';
    $html .='<input type="hidden" name="formmenuaction" value="del_send" />';
    $html .='>> '.$modx->TXNewsletters['txtlang']['subscribe_mod_edit_msg1'].' <input type="submit" value="'.$modx->TXNewsletters['txtlang']['ok'].'" />'."\n";
    $html .='</form>'."\n";
    
    $html .='<br />'."\n";    
    
    foreach($modx->TXNewsletters['fields'] as $id => $field) {
      if($field['ATTRIBUTE']['type']=='subscribe'){
      
      $test=0;
      foreach($field['values'][0]['value'] as $id => $value) { $test++; }
      
      if($test>1){
    
      $html .='<form method="post" >'."\n";
      $html .='>> '.$modx->TXNewsletters['txtlang']['subscribe_mod_edit_msg2'].' : '."\n";
      $html .='<input name="TXNewsletters_form_send" type="hidden" value="ok">'."\n";
      $html .='<input name="formmenuaction" type="hidden" value="'.$action.'">'."\n";
      
      $html .='<div class="TXNewsletters_form_item">'."\n";
      
      $html .='<div class="TXNewsletters_form_list">'."\n";
      $html .='<label class="TXNewsletters_form_labelGauche" for="'.$field['ATTRIBUTE']['name'].'">'.$field['label'][0]['CONTENT'].' ';
      if ($field['ATTRIBUTE']['mandatory']=='yes' ){
      $html .= '* ';
      }
      $html .= ': </label> '."\n";

      foreach($field['values'][0]['value'] as $id => $value) {
        $html .='<input class="TXNewsletters_form_noborder" type="radio" ';
        $html .='name="'.$field['ATTRIBUTE']['name'].'" ';
        $html .='id="'.$field['ATTRIBUTE']['name'].$value['ATTRIBUTE']['id'].'" ';
        $html .='value="'.$value['ATTRIBUTE']['id'].'" ';
        if (isset($_POST[$field['ATTRIBUTE']['name']]) && $_POST[$field['ATTRIBUTE']['name']]==$value['ATTRIBUTE']['id']){
          $html .= 'checked ';
        }
        elseif ($field['default'][0]['CONTENT']==$value['ATTRIBUTE']['id']) {
          $html .= 'checked ';
        }
        $html .='/> ';
        $html .='<label for="'.$field['ATTRIBUTE']['name'].$value['ATTRIBUTE']['id'].'">'.$value['CONTENT'].'</label> ';
        $idVal = $value['ATTRIBUTE']['id'];
        if($field['values'][0]['valueother']){
        foreach ($field['values'][0]['valueother'] as $id => $value) {
          if ($value['ATTRIBUTE']['id'] == $idVal){
            $html .=' : <label for="'.$value['ATTRIBUTE']['id'].'other">'.$value['label'][0]['CONTENT'].'</label> : ';
            $html .=' <input type="text" ';
            $html .='name="'.$value['ATTRIBUTE']['id'].'other" ';
            $html .='id="'.$value['ATTRIBUTE']['id'].'other" ';
            $html .='size="'.$value['size'][0]['CONTENT'].'" ';
            $html .='maxlength="'.$value['maxlength'][0]['CONTENT'].'" ';
            $html .='value="';
            if (isset($_POST[$value['ATTRIBUTE']['id'].'other'])){
            $html .= $_POST[$value['ATTRIBUTE']['id'].'other'];
            }
            else {
            if (isset($value['default'][0]['CONTENT']) && $value['default'][0]['CONTENT']!='') {$html .= $value['default'][0]['CONTENT'];}
            }
            $html .='" ';
            $html .='/> ';
          }
        }
        }
        $html .="<br /> \n";
      }
      
    
    $html .='</div>'."\n";
    
    $html .='<input type="hidden" name="lastEmail" value="'.$email.'" />';
    $html .='<br /><div class="TXNewsletters_form_right"><div><input type="submit" value="'.$modx->TXNewsletters['txtlang']['update_btn'].'" /><br /></div></div>'."\n";
    $html .='</form>'."\n";
    
    }
    }
    }
    
    $html .='</div>'."\n";
    return $html;
  }

////////////////////////////////////////////////////////////////////////////////

  function formHTML($action,$Err=NULL) {
    global $modx;
    foreach($_POST as $id => $val){
      $pattern = '/([^"]*)(")([^"]*)/i';
      $replace = '$1&quot;$3';
      $_POST[$id] = preg_replace($pattern,$replace,$val);
      $pattern = '/([^\']*)(\')([^\']*)/i';
      $replace = '$1&apos;$3';
      $_POST[$id] = preg_replace($pattern,$replace,$_POST[$id]);
    }
    $html ='<div class="TXNewsletters_form">'."\n";
    $html .='<form method="post" >'."\n";
    $html .='<input name="TXNewsletters_form_send" type="hidden" value="ok">'."\n";
    $html .='<input name="formmenuaction" type="hidden" value="'.$action.'">'."\n";
    foreach($modx->TXNewsletters['fields'] as $id => $field) {
      $html .='<div class="TXNewsletters_form_item">'."\n";
      switch ($field['ATTRIBUTE']['type']) {
      case 'text' :
        $html .='<div class="TXNewsletters_form_text">'."\n";
        $html .='<label class="TXNewsletters_form_labelGauche" for="'.$field['ATTRIBUTE']['name'].'">'.$field['label'][0]['CONTENT'].' ';
        if ($field['ATTRIBUTE']['mandatory']=='yes' ){
        $html .= '* ';
        }
        $html .= ': </label>';
        $html .= ' <input type="text" ';
        if ($Err[$field['ATTRIBUTE']['name']]==true) {
          $html .='class="TXNewsletters_form_inputErr" ';
        }
        $html .='name="'.$field['ATTRIBUTE']['name'].'" ';
        $html .='id="'.$field['ATTRIBUTE']['name'].'" ';
        $html .='value="';
        if (isset($_POST[$field['ATTRIBUTE']['name']])){
        $html .= $_POST[$field['ATTRIBUTE']['name']];
        }
        else {
        if (isset($field['default'][0]['CONTENT']) && $field['default'][0]['CONTENT']!='') {$html .= $field['default'][0]['CONTENT'];}
        }
        $html .='" ';
        $html .='size="'.$field['size'][0]['CONTENT'].'" ';
        $html .='maxlength="'.$field['maxlength'][0]['CONTENT'].'" ';
        $html .=' /> ';
        if ($Err[$field['ATTRIBUTE']['name']]==true) {
          $html .='<br /><span class="TXNewsletters_form_right TXNewsletters_form_labelErr">'.$field['errormessage'][0]['CONTENT'].'</span>'."\n";
        }
        $html .='</div>'."\n";
      break;
      case 'email' :
        $html .='<div class="TXNewsletters_form_email">'."\n";
        $html .='<label class="TXNewsletters_form_labelGauche" for="'.$field['ATTRIBUTE']['name'].'">'.$field['label'][0]['CONTENT'].' ';
        if ($field['ATTRIBUTE']['mandatory']=='yes' ){
        $html .= '* ';
        }
        $html .= ': </label>';
        $html .=' <input type="text" ';
        if ($Err[$field['ATTRIBUTE']['name']]==true) {
          $html .='class="TXNewsletters_form_inputErr" ';
        }
        $html .='name="'.$field['ATTRIBUTE']['name'].'" ';
        $html .='id="'.$field['ATTRIBUTE']['name'].'" ';
        $html .='value="';
        if (isset($_POST[$field['ATTRIBUTE']['name']])){
        $html .= $_POST[$field['ATTRIBUTE']['name']];
        }
        else {
        if (isset($field['default'][0]['CONTENT']) && $field['default'][0]['CONTENT']!='') {$html .= $field['default'][0]['CONTENT'];}
        }
        $html .='" ';
        $html .='size="'.$field['size'][0]['CONTENT'].'" ';
        $html .='maxlength="'.$field['maxlength'][0]['CONTENT'].'" ';
        $html .=' /> ';
        if ($Err[$field['ATTRIBUTE']['name']]==true) {
          $html .='<br /><span class="TXNewsletters_form_right TXNewsletters_form_labelErr">'.$field['errormessage'][0]['CONTENT'].'</span>'."\n";
        }
        if(isset($_POST['lastEmail'])) {$html .='<input type="hidden" name="lastEmail" value="'.$_POST['lastEmail'].'" />';}
        $html .='</div>'."\n";
      break;
      case 'textarea' :
        $html .='<div class="TXNewsletters_form_textarea">'."\n";
        $html .='<label class="TXNewsletters_form_labelGauche" for="'.$field['ATTRIBUTE']['name'].'">'.$field['label'][0]['CONTENT'].' ';
        if ($field['ATTRIBUTE']['mandatory']=='yes' ){
        $html .= '* ';
        }
        $html .= ': </label>';
        $html .=' <textarea ';
        if ($Err[$field['ATTRIBUTE']['name']]==true) {
          $html .='class="TXNewsletters_form_inputErr" ';
        }
        $html .='name="'.$field['ATTRIBUTE']['name'].'" ';
        $html .='id="'.$field['ATTRIBUTE']['name'].'" ';
        $html .='cols="'.$field['cols'][0]['CONTENT'].'" ';
        $html .='rows="'.$field['rows'][0]['CONTENT'].'" ';
        $html .='>'."\n";
        if (isset($_POST[$field['ATTRIBUTE']['name']])){
        $html .= $_POST[$field['ATTRIBUTE']['name']];
        }
        else {
        if (isset($field['default'][0]['CONTENT']) && $field['default'][0]['CONTENT']!='') {$html .= $field['default'][0]['CONTENT'];}
        }
        $html .='</textarea> ';
        if ($Err[$field['ATTRIBUTE']['name']]==true) {
          $html .='<br /><span class="TXNewsletters_form_right TXNewsletters_form_labelErr">'.$field['errormessage'][0]['CONTENT'].'</span>'."\n";
        }
        $html .='</div>'."\n";
      break;
      case 'list' :
        $html .='<div class="TXNewsletters_form_list">'."\n";
        $html .='<label class="TXNewsletters_form_labelGauche" for="'.$field['ATTRIBUTE']['name'].'">'.$field['label'][0]['CONTENT'].' ';
        if ($field['ATTRIBUTE']['mandatory']=='yes' ){
        $html .= '* ';
        }
        $html .= ': </label> '."\n";
        switch ($field['listtype'][0]['CONTENT']) {
          case 'radio' :
            $html .= '<div class="TXNewsletters_form_right"><div>';
			foreach($field['values'][0]['value'] as $id => $value) {
              $html .='<input class="TXNewsletters_form_noborder" type="radio" ';
              $html .='name="'.$field['ATTRIBUTE']['name'].'" ';
              $html .='id="'.$field['ATTRIBUTE']['name'].$value['ATTRIBUTE']['id'].'" ';
              $html .='value="'.$value['ATTRIBUTE']['id'].'" ';
              if (isset($_POST[$field['ATTRIBUTE']['name']]) && $_POST[$field['ATTRIBUTE']['name']]==$value['ATTRIBUTE']['id']){
                $html .= 'checked ';
              }
              elseif ($field['default'][0]['CONTENT']==$value['ATTRIBUTE']['id']) {
                $html .= 'checked ';
              }
              $html .='/> ';
              $html .='<label for="'.$field['ATTRIBUTE']['name'].$value['ATTRIBUTE']['id'].'">'.$value['CONTENT'].'</label> ';
              $idVal = $value['ATTRIBUTE']['id'];
              if($field['values'][0]['valueother']){
              foreach ($field['values'][0]['valueother'] as $id => $value) {
                if ($value['ATTRIBUTE']['id'] == $idVal){
                  $html .=' : <label for="'.$value['ATTRIBUTE']['id'].'other">'.$value['label'][0]['CONTENT'].'</label> : ';
                  $html .=' <input type="text" ';
                  $html .='name="'.$value['ATTRIBUTE']['id'].'other" ';
                  $html .='id="'.$value['ATTRIBUTE']['id'].'other" ';
                  $html .='size="'.$value['size'][0]['CONTENT'].'" ';
                  $html .='maxlength="'.$value['maxlength'][0]['CONTENT'].'" ';
                  $html .='value="';
                  if (isset($_POST[$value['ATTRIBUTE']['id'].'other'])){
                  $html .= $_POST[$value['ATTRIBUTE']['id'].'other'];
                  }
                  else {
                  if (isset($value['default'][0]['CONTENT']) && $value['default'][0]['CONTENT']!='') {$html .= $value['default'][0]['CONTENT'];}
                  }
                  $html .='" ';
                  $html .='/> ';
                }
              }
              }
              $html .="<br /> \n";
            }
			$html .= '</div></div>';
          break;
          case 'checkbox' :
            $html .= '<div class="TXNewsletters_form_right"><div>';
            foreach($field['values'][0]['value'] as $id => $value) {
              $html .='<input class="TXNewsletters_form_noborder" type="checkbox" ';
              $html .='name="'.$field['ATTRIBUTE']['name'].'[]" ';
              $html .='id="'.$field['ATTRIBUTE']['name'].$value['ATTRIBUTE']['id'].'" ';
              $html .='value="'.$value['ATTRIBUTE']['id'].'" ';
              if($_POST[$field['ATTRIBUTE']['name']]){
              foreach($_POST[$field['ATTRIBUTE']['name']] as $i => $j) {
                if ($j==$value['ATTRIBUTE']['id']){$html .= 'checked ';}
              }    
              } 
              if ($Err[$field['ATTRIBUTE']['name']]==true) {
                $html .='class="TXNewsletters_form_inputErr" ';
              }         
              $html .=' /> ';
              $html .='<label for="'.$field['ATTRIBUTE']['name'].$value['ATTRIBUTE']['id'].'"';
              if ($Err[$field['ATTRIBUTE']['name']]==true) {
                $html .='class="TXNewsletters_form_labelErr" ';
              } 
              $html .=' >'.$value['CONTENT'].'</label> ';
              $idVal = $value['ATTRIBUTE']['id'];
              if($field['values'][0]['valueother']){
              foreach ($field['values'][0]['valueother'] as $id => $value) {
                if ($value['ATTRIBUTE']['id'] == $idVal){
                  $html .=' : <label for="'.$value['ATTRIBUTE']['id'].'other">'.$value['label'][0]['CONTENT'].'</label> : ';
                  $html .=' <input type="text" ';
                  $html .='name="'.$value['ATTRIBUTE']['id'].'other" ';
                  $html .='id="'.$value['ATTRIBUTE']['id'].'other" ';
                  $html .='size="'.$value['size'][0]['CONTENT'].'" ';
                  $html .='maxlength="'.$value['maxlength'][0]['CONTENT'].'" ';
                  $html .='value="';
                  if (isset($_POST[$value['ATTRIBUTE']['id'].'other'])){
                  $html .= $_POST[$value['ATTRIBUTE']['id'].'other'];
                  }
                  else {
                  if (isset($value['default'][0]['CONTENT']) && $value['default'][0]['CONTENT']!='') {$html .= $value['default'][0]['CONTENT'];}
                  }
                  $html .='" ';
                  $html .='/> ';
                }
              }
              }
              $html .="<br /> \n";
            }
            if ($Err[$field['ATTRIBUTE']['name']]==true) {
              $html .='<span class="TXNewsletters_form_labelErr">'.$field['errormessage'][0]['CONTENT'].'</span><br />'."\n";
            }
            $html .= '</div></div>';
          break;
          case 'list' :
            $html .=' <select ';
            $html .='name="'.$field['ATTRIBUTE']['name'].'" ';
            $html .='id="'.$field['ATTRIBUTE']['name'].'" ';
            $html .='>';
            foreach($field['values'][0]['value'] as $id => $value) {
              $html .='<option ';
              if (isset($_POST[$field['ATTRIBUTE']['name']]) && $_POST[$field['ATTRIBUTE']['name']]==$value['ATTRIBUTE']['id']){
                $html .= 'selected ';
              }
              $html .='value="'.$value['ATTRIBUTE']['id'].'" ';
              $html .='> &nbsp;&nbsp; ';
              $html .=$value['CONTENT'];
              $html .=' &nbsp;&nbsp;&nbsp; </option>';
              $html .=" \n";
            }
            $html .='</select>'." \n";
          break;
        }
        $html .='</div>'."\n";
      break;
      case 'checkbox' :
        $html .='<div class="TXNewsletters_form_item">'."\n";
        $html .=' <input class="TXNewsletters_form_noborder" type="checkbox" ';
        $html .='name="'.$field['ATTRIBUTE']['name'].'" ';
        $html .='id="'.$field['ATTRIBUTE']['name'].'" ';
        if (isset($_POST[$field['ATTRIBUTE']['name']])){
          if ($_POST[$field['ATTRIBUTE']['name']]=='on'){$html .= 'checked ';}
        }
        elseif(!isset($_POST[$field['ATTRIBUTE']['name']])) {
          if ($field['default'][0]['CONTENT']=='yes') {$html .='checked ';}
        }
        $html .='/> ';
        $html .='<label for="'.$field['ATTRIBUTE']['name'].'">'.$field['label'][0]['CONTENT'].' ';
        if ($field['ATTRIBUTE']['mandatory']=='yes' ){
        $html .= '* ';
        }
        $html .= ' </label>';
        $html .='</div>'."\n";
      break;
	  case 'subscribe' :
        $html .='<div class="TXNewsletters_form_list">'."\n";
        $html .='<label class="TXNewsletters_form_labelGauche" for="'.$field['ATTRIBUTE']['name'].'">'.$field['label'][0]['CONTENT'].' ';
        if ($field['ATTRIBUTE']['mandatory']=='yes' ){
        $html .= '* ';
        }
        $html .= ': </label> '."\n";
            $html .= '<div class="TXNewsletters_form_right"><div>';
            foreach($field['values'][0]['value'] as $id => $value) {
              $html .='<input class="TXNewsletters_form_noborder" type="checkbox" ';
              $html .='name="'.$field['ATTRIBUTE']['name'].'[]" ';
              $html .='id="'.$field['ATTRIBUTE']['name'].$value['ATTRIBUTE']['id'].'" ';
              $html .='value="'.$value['ATTRIBUTE']['id'].'" ';
              if($_POST[$field['ATTRIBUTE']['name']]){
              foreach($_POST[$field['ATTRIBUTE']['name']] as $i => $j) {
                if ($j==$value['ATTRIBUTE']['id']){$html .= 'checked ';}
              }    
              } 
              if ($Err[$field['ATTRIBUTE']['name']]==true) {
                $html .='class="TXNewsletters_form_inputErr" ';
              }         
              $html .=' /> ';
              $html .='<label for="'.$field['ATTRIBUTE']['name'].$value['ATTRIBUTE']['id'].'"';
              if ($Err[$field['ATTRIBUTE']['name']]==true) {
                $html .='class="TXNewsletters_form_labelErr" ';
              } 
              $html .=' >'.$value['CONTENT'].'</label> ';
              $idVal = $value['ATTRIBUTE']['id'];
              if($field['values'][0]['valueother']){
              foreach ($field['values'][0]['valueother'] as $id => $value) {
                if ($value['ATTRIBUTE']['id'] == $idVal){
                  $html .=' : <label for="'.$value['ATTRIBUTE']['id'].'other">'.$value['label'][0]['CONTENT'].'</label> : ';
                  $html .=' <input type="text" ';
                  $html .='name="'.$value['ATTRIBUTE']['id'].'other" ';
                  $html .='id="'.$value['ATTRIBUTE']['id'].'other" ';
                  $html .='size="'.$value['size'][0]['CONTENT'].'" ';
                  $html .='maxlength="'.$value['maxlength'][0]['CONTENT'].'" ';
                  $html .='value="';
                  if (isset($_POST[$value['ATTRIBUTE']['id'].'other'])){
                  $html .= $_POST[$value['ATTRIBUTE']['id'].'other'];
                  }
                  else {
                  if (isset($value['default'][0]['CONTENT']) && $value['default'][0]['CONTENT']!='') {$html .= $value['default'][0]['CONTENT'];}
                  }
                  $html .='" ';
                  $html .='/> ';
                }
              }
              }
              $html .="<br /> \n";
            }
            if ($Err[$field['ATTRIBUTE']['name']]==true) {
              $html .='<span class="TXNewsletters_form_labelErr">'.$field['errormessage'][0]['CONTENT'].'</span><br />'."\n";
            }
            $html .= '</div></div>';
        
        $html .='</div>'."\n";
      break;
      }
    
    $html .='</div>'."\n";
    }
    $html .='<br /><div class="TXNewsletters_form_right"><div><input type="submit" value="'.$modx->TXNewsletters['txtlang']['send_btn'].'" /><br /></div></div>'."\n";
    $html .='</form>'."\n";
    $html .='</div>'."\n";
    return $html;
  }
  
////////////////////////////////////////////////////////////////////////////////
  
  function formHTMLeditMod($email,$action,$fromDB) {
    global $modx;
    if($fromDB){
    foreach($fromDB as $id => $val){
      $pattern = '/([^"]*)(")([^"]*)/i';
      $replace = '$1&quot;$3';
      $fromDB[$id] = preg_replace($pattern,$replace,$val);
      $pattern = '/([^\']*)(\')([^\']*)/i';
      $replace = '$1&apos;$3';
      $fromDB[$id] = preg_replace($pattern,$replace,$fromDB[$id]);
    }
    }
    $html ='<div class="TXNewsletters_form">'."\n";
    
    $html .='<form method="post" action="http://'.$_SERVER['HTTP_HOST'].'/index.php?&id='.$modx->TXNewsletters['idPageUnsubscribe'].'" >'."\n";
    $html .='<input type="hidden" name="lastEmail" value="'.$email.'" />';
    $html .='<input type="hidden" name="formmenuaction" value="del_send" />';
    $html .='>> '.$modx->TXNewsletters['txtlang']['subscribe_mod_edit_msg1'].' <input type="submit" value="'.$modx->TXNewsletters['txtlang']['ok'].'" />'."\n";
    $html .='</form>'."\n";
    
    $html .='<br />'."\n";
    
    foreach($modx->TXNewsletters['fields'] as $id => $field) {
      if($field['ATTRIBUTE']['type']=='subscribe'){
      
      $test=0;
      foreach($field['values'][0]['value'] as $id => $value) { $test++; }
      
      if($test>1){
      $html .='<form method="post" action="http://'.$_SERVER['HTTP_HOST'].'/index.php?&id='.$modx->TXNewsletters['idPageUnsubscribe'].'">'."\n";
      $html .='<input name="TXNewsletters_form_send" type="hidden" value="ok">'."\n";
      $html .='<input name="formmenuaction" type="hidden" value="'.$action.'">'."\n";
      $html .= '>> '.$modx->TXNewsletters['txtlang']['subscribe_mod_edit_msg2'].' : ';
    
      
      $html .='<div class="TXNewsletters_form_item">'."\n";
              
      $html .='<div class="TXNewsletters_form_list">'."\n";
      $html .='<label class="TXNewsletters_form_labelGauche" for="'.$field['ATTRIBUTE']['name'].'">'.$field['label'][0]['CONTENT'].' ';
      if ($field['ATTRIBUTE']['mandatory']=='yes' ){
      $html .= '* ';
      }
      $html .= ': </label> '."\n";
      

      $expl = explode("||",$fromDB[$field['ATTRIBUTE']['name']]);
      $html .= '<div class="TXNewsletters_form_right"><div>';
      foreach($field['values'][0]['value'] as $id => $value) {
          $html .='<input class="TXNewsletters_form_noborder" type="checkbox" ';
          $html .='name="'.$field['ATTRIBUTE']['name'].'[]" ';
          $html .='id="'.$field['ATTRIBUTE']['name'].$value['ATTRIBUTE']['id'].'" ';
          $html .='value="'.$value['ATTRIBUTE']['id'].'" ';
          foreach($expl as $pid => $pvalue){
            list($explValue,$temp) = explode("|",$pvalue);
            if ($explValue==$value['ATTRIBUTE']['id']){
              $html .= 'checked ';
              $explValueother = $temp;
            }
          }     
          $html .=' /> ';
          $html .='<label for="'.$field['ATTRIBUTE']['name'].$value['ATTRIBUTE']['id'].'">'.$value['CONTENT'].'</label> ';
          $idVal = $value['ATTRIBUTE']['id'];
          if($field['values'][0]['valueother']){
          foreach ($field['values'][0]['valueother'] as $id => $value) {
            if ($value['ATTRIBUTE']['id'] == $idVal){
              $html .=' : <label for="'.$value['ATTRIBUTE']['id'].'other">'.$value['label'][0]['CONTENT'].'</label> : ';
              $html .='<input type="text" ';
              $html .='name="'.$value['ATTRIBUTE']['id'].'other" ';
              $html .='id="'.$value['ATTRIBUTE']['id'].'other" ';
              $html .='size="'.$value['size'][0]['CONTENT'].'" ';
              $html .='maxlength="'.$value['maxlength'][0]['CONTENT'].'" ';
              $html .='value="';
              if (isset($explValueother)){
                $html .= $explValueother;
              }
              $html .='" ';
              $html .='/> ';
            }
          }
          }
        $html .="<br /> \n";
      }
      $html .= '</div></div>';
    
    $html .='</div>'."\n";
    
    $html .='<input type="hidden" name="lastEmail" value="'.$email.'" />';
    $html .='<br /><div class="TXNewsletters_form_right"><div><input type="submit" value="'.$modx->TXNewsletters['txtlang']['update_btn'].'" /><br /><br /></div></div>'."\n";
    $html .='</form>'."\n";
    }
    }
    }

    $html .='</div>'."\n";
    return $html;
  }
  
////////////////////////////////////////////////////////////////////////////////
  
  function formHTMLedit($action,$fromDB) {
    global $modx;
    foreach($fromDB as $id => $val){
      $pattern = '/([^"]*)(")([^"]*)/i';
      $replace = '$1&quot;$3';
      $fromDB[$id] = preg_replace($pattern,$replace,$val);
      $pattern = '/([^\']*)(\')([^\']*)/i';
      $replace = '$1&apos;$3';
      $fromDB[$id] = preg_replace($pattern,$replace,$fromDB[$id]);
    }
    $html ='<div class="TXNewsletters_form">'."\n";
    $html .='<form method="post" >'."\n";
    $html .='<input name="TXNewsletters_form_send" type="hidden" value="ok">'."\n";
    $html .='<input name="formmenuaction" type="hidden" value="'.$action.'">'."\n";
    foreach($modx->TXNewsletters['fields'] as $id => $field) {
      $html .='<div class="TXNewsletters_form_item">'."\n";
      switch ($field['ATTRIBUTE']['type']) {
      case 'text' :
        $html .='<div class="TXNewsletters_form_text">'."\n";
        $html .='<label class="TXNewsletters_form_labelGauche" for="'.$field['ATTRIBUTE']['name'].'">'.$field['label'][0]['CONTENT'].' : </label>';
        $html .='<input type="text" ';
        $html .='name="'.$field['ATTRIBUTE']['name'].'" ';
        $html .='id="'.$field['ATTRIBUTE']['name'].'" ';
        $html .='value="';
        
        $html .= $fromDB[$field['ATTRIBUTE']['name']];

        $html .='" ';
        $html .='size="'.$field['size'][0]['CONTENT'].'" ';
        $html .='maxlength="'.$field['maxlength'][0]['CONTENT'].'" ';
        $html .=' /> ';
        $html .='</div>'."\n";
      break;
      case 'email' :
        $html .='<div class="TXNewsletters_form_email">'."\n";
        $html .='<label class="TXNewsletters_form_labelGauche" for="'.$field['ATTRIBUTE']['name'].'">'.$field['label'][0]['CONTENT'].' : </label>';
        $html .='<input type="text" ';
        $html .='name="'.$field['ATTRIBUTE']['name'].'" ';
        $html .='id="'.$field['ATTRIBUTE']['name'].'" ';
        $html .='value="';
        $html .= $fromDB[$field['ATTRIBUTE']['name']];
        $html .='" ';
        $html .='size="'.$field['size'][0]['CONTENT'].'" ';
        $html .='maxlength="'.$field['maxlength'][0]['CONTENT'].'" ';
        $html .=' /> ';
        $html .='<input type="hidden" name="lastEmail" value="'.$fromDB[$field['ATTRIBUTE']['name']].'" />';
        $html .='</div>'."\n";
      break;
      case 'textarea' :
        $html .='<div class="TXNewsletters_form_textarea">'."\n";
        $html .='<label class="TXNewsletters_form_labelGauche" for="'.$field['ATTRIBUTE']['name'].'">'.$field['label'][0]['CONTENT'].' : </label>';
        $html .='<textarea ';
        $html .='name="'.$field['ATTRIBUTE']['name'].'" ';
        $html .='id="'.$field['ATTRIBUTE']['name'].'" ';
        $html .='cols="'.$field['cols'][0]['CONTENT'].'" ';
        $html .='rows="'.$field['rows'][0]['CONTENT'].'" ';
        $html .='>'."\n";
        $html .= $fromDB[$field['ATTRIBUTE']['name']];
        $html .='</textarea> ';
        if ($Err[$field['ATTRIBUTE']['name']]==true) {
          $html .='<br /><span class="TXNewsletters_form_right TXNewsletters_form_labelErr">'.$field['errormessage'][0]['CONTENT'].'</span>'."\n";
        }
        $html .='</div>'."\n";
      break;
      case 'list' :
        
        $html .='<div class="TXNewsletters_form_list">'."\n";
        $html .='<label class="TXNewsletters_form_labelGauche" for="'.$field['ATTRIBUTE']['name'].'">'.$field['label'][0]['CONTENT'].' ';
        if ($field['ATTRIBUTE']['mandatory']=='yes' ){
        $html .= '* ';
        }
        $html .= ': </label> '."\n";
        
        switch ($field['listtype'][0]['CONTENT']) {
          case 'radio' :
            $html .= '<div class="TXNewsletters_form_right"><div>';
			foreach($field['values'][0]['value'] as $id => $value) {
              list($explValue, $explValueother) = explode("|",$fromDB[$field['ATTRIBUTE']['name']]);
              $html .='<input class="TXNewsletters_form_noborder" type="radio" ';
              $html .='name="'.$field['ATTRIBUTE']['name'].'" ';
              $html .='id="'.$field['ATTRIBUTE']['name'].$value['ATTRIBUTE']['id'].'" ';
              $html .='value="'.$value['ATTRIBUTE']['id'].'" ';
              if ($explValue==$value['ATTRIBUTE']['id']){
                $html .= 'checked ';
              }
              $html .='/> ';
              $html .='<label for="'.$field['ATTRIBUTE']['name'].$value['ATTRIBUTE']['id'].'">'.$value['CONTENT'].'</label> ';
              $idVal = $value['ATTRIBUTE']['id'];
              if($field['values'][0]['valueother']){
              foreach ($field['values'][0]['valueother'] as $id => $value) {
                if ($value['ATTRIBUTE']['id'] == $idVal){
                  $html .=' : <label for="'.$value['ATTRIBUTE']['id'].'other">'.$value['label'][0]['CONTENT'].'</label> : ';
                  $html .='<input type="text" ';
                  $html .='name="'.$value['ATTRIBUTE']['id'].'other" ';
                  $html .='id="'.$value['ATTRIBUTE']['id'].'other" ';
                  $html .='size="'.$value['size'][0]['CONTENT'].'" ';
                  $html .='maxlength="'.$value['maxlength'][0]['CONTENT'].'" ';
                  $html .='value="';
                  if (isset($explValueother)){
                  $html .= $explValueother;
                  }

                  $html .='" ';
                  $html .='/> ';
                }
              }
              }
              $html .="<br /> \n";
            }
			$html .= '</div></div>';
          break;
          case 'checkbox' :
            $expl = explode("||",$fromDB[$field['ATTRIBUTE']['name']]);
            $html .= '<div class="TXNewsletters_form_right"><div>';
            foreach($field['values'][0]['value'] as $id => $value) {
                $html .='<input class="TXNewsletters_form_noborder" type="checkbox" ';
                $html .='name="'.$field['ATTRIBUTE']['name'].'[]" ';
                $html .='id="'.$field['ATTRIBUTE']['name'].$value['ATTRIBUTE']['id'].'" ';
                $html .='value="'.$value['ATTRIBUTE']['id'].'" ';
                foreach($expl as $pid => $pvalue){
                  list($explValue,$temp) = explode("|",$pvalue);
                  if ($explValue==$value['ATTRIBUTE']['id']){
                    $html .= 'checked ';
                    $explValueother = $temp;
                  }
                }     
                $html .=' /> ';
                $html .='<label for="'.$field['ATTRIBUTE']['name'].$value['ATTRIBUTE']['id'].'">'.$value['CONTENT'].'</label> ';
                $idVal = $value['ATTRIBUTE']['id'];
                if($field['values'][0]['valueother']){
                foreach ($field['values'][0]['valueother'] as $id => $value) {
                  if ($value['ATTRIBUTE']['id'] == $idVal){
                    $html .=' : <label for="'.$value['ATTRIBUTE']['id'].'other">'.$value['label'][0]['CONTENT'].'</label> : ';
                    $html .='<input type="text" ';
                    $html .='name="'.$value['ATTRIBUTE']['id'].'other" ';
                    $html .='id="'.$value['ATTRIBUTE']['id'].'other" ';
                    $html .='size="'.$value['size'][0]['CONTENT'].'" ';
                    $html .='maxlength="'.$value['maxlength'][0]['CONTENT'].'" ';
                    $html .='value="';
                    if (isset($explValueother)){
                      $html .= $explValueother;
                    }
                    $html .='" ';
                    $html .='/> ';
                  }
                }
                }
              $html .="<br /> \n";
            }
            $html .= '</div></div>';
          break;
          case 'list' :

            $html .='<select ';
            $html .='name="'.$field['ATTRIBUTE']['name'].'" ';
            $html .='id="'.$field['ATTRIBUTE']['name'].'" ';
            $html .='>';
            foreach($field['values'][0]['value'] as $id => $value) {
              $html .='<option ';
              if ($fromDB[$field['ATTRIBUTE']['name']]==$value['ATTRIBUTE']['id']){
                $html .= 'selected ';
              }
              $html .='value="'.$value['ATTRIBUTE']['id'].'" ';
              $html .='> &nbsp;&nbsp; ';
              $html .=$value['CONTENT'];
              $html .=' &nbsp;&nbsp;&nbsp; </option>';
              $html .=" \n";
            }
            $html .='</select>'." \n";
          break;
        }
        $html .='</div>'."\n";
      break;
      case 'checkbox' :
        $html .='<div class="TXNewsletters_form_item">'."\n";
        $html .='<input class="TXNewsletters_form_noborder" type="checkbox" ';
        $html .='name="'.$field['ATTRIBUTE']['name'].'" ';
        $html .='id="'.$field['ATTRIBUTE']['name'].'" ';
        if ($fromDB[$field['ATTRIBUTE']['name']]=='1'){$html .= 'checked ';}
        $html .='/> ';
        $html .='<label for="'.$field['ATTRIBUTE']['name'].'">'.$field['label'][0]['CONTENT'].'</label>';
        $html .='</div>'."\n";
      break;
	  case 'subscribe' :
        $html .='<div class="TXNewsletters_form_list">'."\n";
        $html .='<label class="TXNewsletters_form_labelGauche" for="'.$field['ATTRIBUTE']['name'].'">'.$field['label'][0]['CONTENT'].' ';
        if ($field['ATTRIBUTE']['mandatory']=='yes' ){
        $html .= '* ';
        }
        $html .= ': </label> '."\n";
            $expl = explode("||",$fromDB[$field['ATTRIBUTE']['name']]);
            $html .= '<div class="TXNewsletters_form_right"><div>';
            foreach($field['values'][0]['value'] as $id => $value) {
                $html .='<input class="TXNewsletters_form_noborder" type="checkbox" ';
                $html .='name="'.$field['ATTRIBUTE']['name'].'[]" ';
                $html .='id="'.$field['ATTRIBUTE']['name'].$value['ATTRIBUTE']['id'].'" ';
                $html .='value="'.$value['ATTRIBUTE']['id'].'" ';
                foreach($expl as $pid => $pvalue){
                  list($explValue,$temp) = explode("|",$pvalue);
                  if ($explValue==$value['ATTRIBUTE']['id']){
                    $html .= 'checked ';
                    $explValueother = $temp;
                  }
                }     
                $html .=' /> ';
                $html .='<label for="'.$field['ATTRIBUTE']['name'].$value['ATTRIBUTE']['id'].'">'.$value['CONTENT'].'</label> ';
                $idVal = $value['ATTRIBUTE']['id'];
                if($field['values'][0]['valueother']){
                foreach ($field['values'][0]['valueother'] as $id => $value) {
                  if ($value['ATTRIBUTE']['id'] == $idVal){
                    $html .=' : <label for="'.$value['ATTRIBUTE']['id'].'other">'.$value['label'][0]['CONTENT'].'</label> : ';
                    $html .='<input type="text" ';
                    $html .='name="'.$value['ATTRIBUTE']['id'].'other" ';
                    $html .='id="'.$value['ATTRIBUTE']['id'].'other" ';
                    $html .='size="'.$value['size'][0]['CONTENT'].'" ';
                    $html .='maxlength="'.$value['maxlength'][0]['CONTENT'].'" ';
                    $html .='value="';
                    if (isset($explValueother)){
                      $html .= $explValueother;
                    }
                    $html .='" ';
                    $html .='/> ';
                  }
                }
                }
              $html .="<br /> \n";
            }
            $html .= '</div></div>';

        $html .='</div>'."\n";
      break;
      }
    
    $html .='</div>'."\n";
    }
    $html .='<br /><div class="TXNewsletters_form_right"><div><input type="submit" value="'.$modx->TXNewsletters['txtlang']['update_btn'].'" /><br /></div></div>'."\n";
    $html .='</form>'."\n";
    $html .='</div>'."\n";
    return $html;
  }
  
////////////////////////////////////////////////////////////////////////////////

  function checkForm () {
    global $modx;
    $unValid = NULL;
    $html ='';
    if (isset($_POST['TXNewsletters_form_send']) && $_POST['TXNewsletters_form_send']!='ok') {
      $html = $modx->TXNewsletters['txtlang']['postdata_error'];
      return $false;
    }
    else {
      foreach($modx->TXNewsletters['fields'] as $id => $field) {
      if ($field['ATTRIBUTE']['type']!='checkbox'){
        $t = false;
        foreach($_POST as $id => $value) {
          if ($id == $field['ATTRIBUTE']['name']) {
          
            if ($field['ATTRIBUTE']['type']=='email') {
              $Email = $_POST[$id];
              if ((preg_match('`^[[:alnum:]]([-_.]?[[:alnum:]])*@[[:alnum:]]([-_.]?[[:alnum:]])*\.([a-z]{2,4})$`',$Email))){
                $domaine = substr(strstr($Email, '@'),1);
                if (checkdnsrr($domaine)){$t= true;}
              }
            }

            elseif ($field['ATTRIBUTE']['type']=='subscribe') {
                $Subscribes = $_POST[$field['ATTRIBUTE']['name']];
                foreach($Subscribes as $n => $value){
                  $t = true;
                }
            }

            else {
              if ($field['ATTRIBUTE']['mandatory']=='yes' && $_POST[$id]!='') {
                $t= true;
              }
              elseif ($field['ATTRIBUTE']['mandatory']=='no') {
                $t= true;
              }
            }
            
            
          }
          
        }
        if ($t== false) {$unValid[$field['ATTRIBUTE']['name']]=true;}
      }
    }
    }
    return $unValid;
  }
  
////////////////////////////////////////////////////////////////////////////////

  function showHTML ($rows,$orderBy,$currentPage,$nbPerPage) {
    global $modx;
    $pair=0;
    $html ='';
    $html .= '<div style="text-align:center;"><table style="margin:auto;text-align:left;" width="99%" cellspacing="1" cellpadding="2">';
    $html .= '<tr>';
    foreach($modx->TXNewsletters['fields'] as $id => $field){
      if ($field['ATTRIBUTE']['display']=='yes') {
        $html .= '<th class="TXNewsletters_form_th">'."\n";
        $html .= ' <a href="#" onclick="javascript:menuSubmit(\'show\',\''.$field['ATTRIBUTE']['name'].'|ASC\');">';
        $html .= '<img src="'.$modx->TXNewsletters['path_url'].'images/sortUp.jpg" style="vertical-align:middle;" />';
        $html .= '</a> ';
        $html .= '<a href="#" onclick="javascript:menuSubmit(\'show\',\''.$field['ATTRIBUTE']['name'].'|DESC\');">';
        $html .= '<img src="'.$modx->TXNewsletters['path_url'].'images/sortDown.jpg" style="vertical-align:middle;" />';
        $html .= '</a> &nbsp;';
        $html .= ' '.$field['label'][0]['CONTENT'].' ';
        $html .= '</th>'."\n";
      }
    }
    $html .= '</tr>';
    
    $t=1;
    $pagesStart[]=0;
    if($rows) {
    foreach ($rows as $i => $value) {
        if ($t==$nbPerPage){$pagesStart[]=$i;$t=1;}
        else{$t++;}
    }
    }
    
    if(isset($pagesStart[$currentPage-1]) ){
      $nbFirst = $pagesStart[$currentPage-1];
    }
    else {$nbFirst=0;}
    
    for ($i=0;$i<$nbPerPage;$i++) {
      if($rows[$nbFirst]){$selectedRows[$i]=$rows[$nbFirst];}
      $nbFirst++;
    }
    if($selectedRows) {
    foreach($selectedRows as $i => $user) {
    $html .= '<tr ';
    if ($pair==1) {$html .= 'style="font-size:10px;background-color:#cccccc;" ';}
    $html .= '>'."\n";
    foreach($user as $champ => $value){
      foreach($modx->TXNewsletters['fields'] as $id => $field){
        if ($champ==$field['ATTRIBUTE']['name'] && $field['ATTRIBUTE']['display']=='yes') {
          $html .= '<td class="TXNewsletters_form_td">'."\n";
          if ($empl){$value = substr($value,0,$empl);}
          $html .= $value;
          if ($value==''){$html .= '&nbsp;';}
          $html .= '</td>'."\n";
          if($field['ATTRIBUTE']['type']=='email') {
            $idFiche=$value;
          }
        }
      }
    }
    $html .= '<td style="font-size:10px;padding:4px;border:1px solid #cccccc;">'."\n";
    $html .= '<a href="#" onclick="javascript:menuSubmit(\'edit\',\''.$idFiche.'\');"><img src="'.$modx->TXNewsletters['path_url'].'images/context_view.gif" style="vertical-align:middle;" /></a>&nbsp; ';
    $html .= '<a href="#" onclick="javascript:menuSubmit(\'del\',\''.$idFiche.'\');"><img src="'.$modx->TXNewsletters['path_url'].'images/delete.jpg" style="vertical-align:middle;" /></a> ';
    $html .= '</td>'."\n";
    $html .= '</tr>'."\n";
    if ($pair==0) {$pair=1;}
    elseif ($pair==1) {$pair=0;}
    }
    }
    $html .= '</table></div><br />'."\n";
    
    $html .='<div style="text-align:center;">'."\n";
    $html .='<form name="Pages" method="post" >'."\n";
    if (isset($pagesStart[$currentPage-2])) { $html .='&nbsp;<span class="coolButton" onclick="javascript:document.formmenu.formmenunewPage.value=\''.($currentPage-1).'\';menuSubmit(\'show\',\''.$orderBy.'\');"> < '.$modx->TXNewsletters['txtlang']['page_previous'].' </span>&nbsp;'."\n";}
    $html .= $modx->TXNewsletters['txtlang']['page_msg1'].' '.$currentPage.' '.$modx->TXNewsletters['txtlang']['page_msg2'].' '.count($pagesStart)."\n";
    if (isset($pagesStart[$currentPage])) { $html .='&nbsp;<span class="coolButton" onclick="javascript:document.formmenu.formmenunewPage.value=\''.($currentPage+1).'\';menuSubmit(\'show\',\''.$orderBy.'\');"> '.$modx->TXNewsletters['txtlang']['page_next'].' > </span>&nbsp;'."\n";}
    $html .='</form>'."\n";
    $html .='</div>'."\n";
    
    return $html;
  }
 
////////////////////////////////////////////////////////////////////////////////

  function BoucleNL ($id,$level) {
    global $modx;
    $ItemData = $modx->getTemplateVarOutput(array('pagetitle','published'), $id, 1);
    if ($ItemData['published']==1) {
    
    if(!isset($order) || $order == '') $order='menuindex';
    
    $children = $modx->getActiveChildren($id, $order);
    $ItemData['pagetitle']= htmlspecialchars($ItemData['pagetitle']);
    if($children == false)
     {
     $decal = '';
     for ($i=0;$i<$level;$i++) {
     $decal .= '&nbsp;&nbsp;';
     }
     
     $list['pagetitle'] = $decal.' | '.$ItemData['pagetitle'];
    }
    else {
     $decal = '';
     for ($i=0;$i<$level;$i++) {
     $decal .= '&nbsp;&nbsp;';
     }
     
     $list['pagetitle'] = $decal.' | '.$ItemData['pagetitle'];
     $level++;
     $childrenCount = count($children);
     for($i=0; $i<$childrenCount; $i++) {
     $newid = $children[$i]['id'];
     $list[$id]["sub"][$newid]= BoucleNL($newid,$level);
     
     }
    }
    }
    return $list;
  } 
////////////////////////////////////////////////////////////////////////////////

  function formHTMLsend ($emails,$action,$emailFrom,$newslettersBaseId) {
    global $modx;  
  $html ='<div>'.$modx->TXNewsletters['txtlang']['send_msg1'].'  <strong>'.count($emails).'</strong> '.$modx->TXNewsletters['txtlang']['send_msg2'].' </div><br />'."\n";
  $html .='<div>'."\n";
  $html .='<form name="formSend" method="post">'."\n";
  $html .='<input name="formmenuaction" type="hidden" value="'.$action.'">'."\n";
  $html .='<label class="TXNewsletters_form_labelGauche" for="sendSubject" style="">'.$modx->TXNewsletters['txtlang']['send_subject'].' : </label>';
  $html .='<input type="text" style="width:300px;" name="sendSubject" id="sendSubject" value="" /> <br /><br />'."\n";
  $html .='<label class="TXNewsletters_form_labelGauche" for="sendEmailForm">'.$modx->TXNewsletters['txtlang']['send_fromfield'].' : </label>';
  $html .='<input type="text" style="width:300px;border:0;" name="sendEmailForm" id="sendEmailForm" value="'.$emailFrom.'" readonly /> <br /><br />'."\n";
  
  $list = BoucleNL($newslettersBaseId,-1);
  $list = $list[$newslettersBaseId]["sub"];
  $html .='<label class="TXNewsletters_form_labelGauche" for="newsletterId">'.$modx->TXNewsletters['txtlang']['send_label_choose'].' : </label>';
  $html .='<select name="newsletterId" id="newsletterId" >';
  if($list){
  foreach ($list as $id => $value) {
    $html .='<option value="'.$id.'"> '.$value['pagetitle'].'&nbsp;&nbsp;&nbsp; </option>';
  }
  }
  $html .='</select> ';
  $html .= '&nbsp; <a href="#" onClick="javascript:window.open(\'/index.php?id=\'+document.formSend.newsletterId.value,\'Newsletter\',\'width=800,height=600,menubar=no,scrollbars=no,status=no,toolbar=no\');"><img src="'.$modx->TXNewsletters['path_url'].'images/context_view.gif" style="vertical-align:middle;" /> '.$modx->TXNewsletters['txtlang']['send_label_preview'].' </a>&nbsp;<br /><br />'."\n";
  $html .='';
  $html .='<input type="submit" value="'.$modx->TXNewsletters['txtlang']['send_label_submit'].'" />'."\n";
  $html .='</form>'."\n";
  $html .='</div>'."\n";
  return $html;
  }

////////////////////////////////////////////////////////////////////////////////

  function sendHTML ($newsletterId) {
    global $modx;
    
    ini_set('default_socket_timeout', 120);
    $url = 'http://'.$_SERVER['HTTP_HOST'].$modx->makeUrl($newsletterId);
    $html = file_get_contents($url);    
    $httpDom = 'http://'.$_SERVER['HTTP_HOST'];
    $html = preg_replace('#(<img[^>]*src=\")([^\"]*/assets)#', '${1}'.$httpDom.'/assets', $html);
    $html = preg_replace('#(<a[^>]*href=\")([^\"]*/assets)#', '${1}'.$httpDom.'/assets', $html);
    $html = preg_replace('#(onclick=\"window\.open\(\')([^\"]*/assets)#', '${1}'.$httpDom.'/assets', $html);
     
    return $html;
  }
  
////////////////////////////////////////////////////////////////////////////////

  function listEmails ($rows) {
    global $modx;
	$n =0;
    if($rows){
      foreach($rows as $i => $user) {
        foreach($user as $champ => $value){
          foreach($modx->TXNewsletters['fields'] as $id => $field){
            if ($champ==$field['ATTRIBUTE']['name']) {
              if($field['ATTRIBUTE']['type']=='email') {
                $emails[$n]['val']=$value;
              }
            }
          }	
          if ($champ=='Timestamp') {
            $emails[$n]['timestamp']=$value;
          }
        }
		$n++;
      }
    }
    return $emails;
  }
  
////////////////////////////////////////////////////////////////////////////////

  function sendNewsletterMail ($emails,$emailFrom,$newsletterId,$subject) {
    global $modx;
          
    $html = sendHTML($newsletterId);
    foreach ($emails as $id => $datas) {
      
      $email = $datas['val'];
      $timestamp = $datas['timestamp'];
      $MD5 = ControlMD5($email,$timestamp);
      $link = 'http://'.$_SERVER['HTTP_HOST'].'/index.php?&id='.$modx->TXNewsletters['idPageUnsubscribe'].'&action=del&email='.$email.'&control='.$MD5;
      $send_html = preg_replace('#(\{link_unsubscribe\})#', $link, $html);
      
      $headers  = 'From: ' . $emailFrom . "\n";
      $headers .= 'To: ' . $email . "\n";
      $headers .= 'Return-Path: ' . $emailFrom . "\n";
      $headers .= 'MIME-Version: 1.0' ."\n";
      
      $headers .= 'Content-Type: text/html; charset=UTF-8' ."\n";
      $headers .= 'Content-Transfer-Encoding: 8bit'. "\n\n";
      $headers .= $send_html . "\n";
      
      $result=mail('', $subject,'', $headers);

    }
    return $result;
  }

////////////////////////////////////////////////////////////////////////////////

  function clearFilters () {
    $_SESSION['TXNewslettersFilters']=NULL;
  }

////////////////////////////////////////////////////////////////////////////////

  function delFilter ($delFilter) {
    list ($action,$filterName)= explode("|",$delFilter);
    unset ($_SESSION['TXNewslettersFilters'][$filterName]);
  }

////////////////////////////////////////////////////////////////////////////////

  function newFilter () {
    if(isset($_POST['TXNewslettersFilter']) && isset($_POST['TXNewslettersFilterValue']) && $_POST['TXNewslettersFilter']!='' && $_POST['TXNewslettersFilterValue']!=''){
      $_SESSION['TXNewslettersFilters'][$_POST['TXNewslettersFilter']]=$_POST['TXNewslettersFilterValue'];
    }
  }
    
////////////////////////////////////////////////////////////////////////////////

  function formFilterHTML () {
    global $modx;
    $html ='<div style="border:1px solid #cccccc;padding:2px;margin:4px;">'."\n";
    $html .= '<form name="TXNewslettersFilters" id="TXNewslettersFilters" method="post" >'."\n";
    $html .= '<div style="padding:2px;margin:4px;">'."\n";
    $html .= $modx->TXNewsletters['txtlang']['filter_msg1'].' : <select name="TXNewslettersFilter" id="TXNewslettersFilter">'."\n";
     foreach($modx->TXNewsletters['fields'] as $id => $field){
      if ($field['ATTRIBUTE']['filter']=='yes') {
        $exist=false;
        if ($_SESSION['TXNewslettersFilters']){
        foreach ($_SESSION['TXNewslettersFilters'] as $filterName => $filterValue) {
          if ($filterName==$field['ATTRIBUTE']['name']) {$exist=true;}
        }
        }
        if($exist==false){$html .=  '<option value="'.$field['ATTRIBUTE']['name'].'"> &nbsp;&nbsp;'.$field['label'][0]['CONTENT'].'&nbsp;&nbsp;&nbsp; </option>'."\n";}      
      }
    }
    $html .='</select> '."\n";
    $html .=' '.$modx->TXNewsletters['txtlang']['filter_msg2'].' : <input type="text" name="TXNewslettersFilterValue" id="TXNewslettersFilterValue" value="" /> '."\n";
    $html .='<input type="submit" name="TXNewslettersFilterSubmit" id="TXNewslettersFilterSubmit" value="'.$modx->TXNewsletters['txtlang']['filter_add'].'" /> <br />'."\n";
    $html .= '</div>'."\n";
    if ($_SESSION['TXNewslettersFilters']){
    $html .= '<div style="border-bottom:1px solid #cccccc;padding:2px;margin:4px;">';
    $html .= $modx->TXNewsletters['txtlang']['filter_actifs_msg1'].' :<br />'."\n";
    $html .= '</div> <div style="padding:2px;margin:4px;">';
    foreach ($_SESSION['TXNewslettersFilters'] as $filterName => $filterValue) {
		foreach($modx->TXNewsletters['fields'] as $id => $field){
			if($field['ATTRIBUTE']['name']==$filterName){$filterCaption=$field['label'][0]['CONTENT'];}
		}
	  $html .='<div> <span class="coolButton" onclick="javascript:menuSubmit(\'show_delfilter\',\'delFilter|'.$filterName.'\');"> '.$modx->TXNewsletters['txtlang']['filter_del'].' </span> ';
      $html .='<label>&nbsp;&nbsp; " <b>'.$filterCaption.'</b> " '.$modx->TXNewsletters['txtlang']['filter_actifs_msg2'].' : </label> ';
      $html .=' " <b>'.$filterValue.'</b> " ';
      $html .='</div> <div class="clear">&nbsp;</div> '."\n";
    }
    $html .= '</div>'."\n";
    }
    $html .= '</form></div><br />'."\n";
    return $html;
  }
  
////////////////////////////////////////////////////////////////////////////////  

  function delCode ($value) {
    // Stripslashes
   if (get_magic_quotes_gpc()) {
      $value = stripslashes($value);
   }
   $value = strip_tags($value);
   // Protect not int numbers
   if (!is_numeric($value)) {
      $value = mysql_real_escape_string($value);
   }
   return $value;
  }
////////////////////////////////////////////////////////////////////////////////  

  function checkPost () {
    global $modx;

  foreach($modx->TXNewsletters['fields'] as $id => $field) {
    switch ($field['ATTRIBUTE']['type']) {
      case 'text' :
        $xmlPost[$field['ATTRIBUTE']['name']] = delCode($_POST[$field['ATTRIBUTE']['name']]);
      break;
      case 'email' :
        $xmlPost[$field['ATTRIBUTE']['name']] = delCode($_POST[$field['ATTRIBUTE']['name']]);
        $xmlPost['lastEmail']=$_POST['lastEmail'];
      break;
      case 'textarea' :
        $xmlPost[$field['ATTRIBUTE']['name']] = delCode($_POST[$field['ATTRIBUTE']['name']]);
      break;
      case 'list' :       
        switch ($field['listtype'][0]['CONTENT']) {
          case 'radio' :
            $xmlPost[$field['ATTRIBUTE']['name']] = delCode($_POST[$field['ATTRIBUTE']['name']]);
            if($field['values'][0]['value']){
            foreach($field['values'][0]['value'] as $id => $value) {
              $idVal = $value['ATTRIBUTE']['id'];
              if($field['values'][0]['valueother']){
              foreach ($field['values'][0]['valueother'] as $pid => $pvalue) {
                if ($pvalue['ATTRIBUTE']['id'] == $idVal){
                  if (isset($_POST[$pvalue['ATTRIBUTE']['id'].'other']) && $_POST[$pvalue['ATTRIBUTE']['id'].'other']!=""){
                    $xmlPost[$field['ATTRIBUTE']['name']] .= '|'.delCode($_POST[$pvalue['ATTRIBUTE']['id'].'other']);
                  }
                }
              }
              }
            }
            }
          break;
          case 'checkbox' :
            $xmlPost[$field['ATTRIBUTE']['name']] ='';
            foreach($field['values'][0]['value'] as $id => $value) {
              $checked=0;
			  if($_POST[$field['ATTRIBUTE']['name']]){
              foreach($_POST[$field['ATTRIBUTE']['name']] as $pid => $pvalue) {
                if($pvalue == $value['ATTRIBUTE']['id']) {
                $xmlPost[$field['ATTRIBUTE']['name']] .= delCode($pvalue);
                $idVal = $value['ATTRIBUTE']['id'];
                $checked=1;
                }
              }
			  }
                  if($field['values'][0]['valueother']){
                  foreach ($field['values'][0]['valueother'] as $id2 => $value2) {
                    if ($value2['ATTRIBUTE']['id'] == $idVal){
                      if (isset($_POST[$value2['ATTRIBUTE']['id'].'other']) && $_POST[$value2['ATTRIBUTE']['id'].'other']!=""){
                        $xmlPost[$field['ATTRIBUTE']['name']] .= '|'.delCode($_POST[$value2['ATTRIBUTE']['id'].'other']);
                      }
                    }
                  }
                  }
            if($checked==1){ $xmlPost[$field['ATTRIBUTE']['name']] .= '||';}
            }
            $xmlPost[$field['ATTRIBUTE']['name']] = substr($xmlPost[$field['ATTRIBUTE']['name']],0,strlen($xmlPost[$field['ATTRIBUTE']['name']])-2);
          break;
          case 'list' :
            $xmlPost[$field['ATTRIBUTE']['name']] = delCode($_POST[$field['ATTRIBUTE']['name']]);
          break;
        }        
      break;
      case 'checkbox' :
        if (isset($_POST[$field['ATTRIBUTE']['name']])) {$xmlPost[$field['ATTRIBUTE']['name']] = 1;}
        else {$xmlPost[$field['ATTRIBUTE']['name']]=0;}
      break;
	  case 'subscribe' :       
          case 'checkbox' :
            $xmlPost[$field['ATTRIBUTE']['name']] ='';
            foreach($field['values'][0]['value'] as $id => $value) {
              $checked=0;
              foreach($_POST[$field['ATTRIBUTE']['name']] as $pid => $pvalue) {
                if($pvalue == $value['ATTRIBUTE']['id']) {
                $xmlPost[$field['ATTRIBUTE']['name']] .= delCode($pvalue);
                $idVal = $value['ATTRIBUTE']['id'];
                $checked=1;
                }
              }
                  if($field['values'][0]['valueother']){
                  foreach ($field['values'][0]['valueother'] as $id2 => $value2) {
                    if ($value2['ATTRIBUTE']['id'] == $idVal){
                      if (isset($_POST[$value2['ATTRIBUTE']['id'].'other']) && $_POST[$value2['ATTRIBUTE']['id'].'other']!=""){
                        $xmlPost[$field['ATTRIBUTE']['name']] .= '|'.delCode($_POST[$value2['ATTRIBUTE']['id'].'other']);
                      }
                    }
                  }
                  }
            if($checked==1){ $xmlPost[$field['ATTRIBUTE']['name']] .= '||';}
            }
            $xmlPost[$field['ATTRIBUTE']['name']] = substr($xmlPost[$field['ATTRIBUTE']['name']],0,strlen($xmlPost[$field['ATTRIBUTE']['name']])-2);
      break;
      }
  }

  return $xmlPost;
  }
  
////////////////////////////////////////////////////////////////////////////////  
  
  function genSQLTable () {
    global $modx;
  $mySQL ="CREATE TABLE `".$modx->TXNewsletters['tb_prefix']."txnewsletters_usersdata` (";
  $mySQL .="\n";
  foreach($modx->TXNewsletters['fields'] as $id => $field) {
      switch ($field['ATTRIBUTE']['type']) {
      case 'text' :
        $mySQL .="`".$field['ATTRIBUTE']['name']."` varchar(".$field['maxlength'][0]['CONTENT'].") NOT NULL default '',";
        $mySQL .="\n";
      break;
      case 'email' :
        $primary = $field['ATTRIBUTE']['name'];
        $mySQL .="`".$field['ATTRIBUTE']['name']."` varchar(".$field['maxlength'][0]['CONTENT'].") NOT NULL default '',";
        $mySQL .="\n";
      break;
      case 'textarea' :
        $mySQL .="`".$field['ATTRIBUTE']['name']."` text character set utf8 collate utf8_bin NOT NULL default '',";
        $mySQL .="\n";
      break;
      case 'list' :
        $mySQL .="`".$field['ATTRIBUTE']['name']."` varchar(255) NOT NULL default '',";
        $mySQL .="\n";
      break;
      case 'checkbox' :
        $mySQL .= "`".$field['ATTRIBUTE']['name']."` tinyint(1) NOT NULL default '0',";
        $mySQL .="\n";
      break;
	  case 'subscribe' :
        $mySQL .="`".$field['ATTRIBUTE']['name']."` varchar(64) NOT NULL default '',";
        $mySQL .="\n";
      break;
      }
    }
    
  $mySQL .="`Timestamp` varchar(255) NOT NULL default '',";
  $mySQL .="\n";
  $mySQL .="PRIMARY KEY  (`".$primary."`)";
  $mySQL .="\n";
  $mySQL .=") ENGINE=MyISAM DEFAULT CHARSET=utf8;";
  
  return $mySQL;
  }
  
////////////////////////////////////////////////////////////////////////////////  

  function genSQLdel ($email) {
    global $modx;
  foreach($modx->TXNewsletters['fields'] as $id => $field) {
    switch ($field['ATTRIBUTE']['type']) {
      case 'email' :
        $champ = $field['ATTRIBUTE']['name'];
      break;
    }
  }
  $mySQL ="DELETE FROM `".$modx->TXNewsletters['tb_prefix']."txnewsletters_usersdata` WHERE CONVERT(`".$champ."` USING utf8) = '".$email."' LIMIT 1;";
  return $mySQL;
  }
  
////////////////////////////////////////////////////////////////////////////////  
  function ControlMD5($email,$timestamp) {
    $chaine = 'Email:'.$email.':TXNewsletters:'.$timestamp;
    $ControlMD5 = md5($chaine);
    return $ControlMD5;
  }
  
  
//////////////////////////////////////////////////////////////////////////////// 

  function checkControl($email,$MD5) {
	global $modx;
  
	foreach($modx->TXNewsletters['fields'] as $id => $field) {
		switch ($field['ATTRIBUTE']['type']) {
		  case 'email' :
			$champ = $field['ATTRIBUTE']['name'];
		  break;
		}
	}
  
    $sql = "
			SELECT * FROM `".$modx->TXNewsletters['tb_prefix']."txnewsletters_usersdata`
			WHERE CONVERT(`".$champ."` USING utf8) = '".$email."'
			LIMIT 1;
	";
	$results = $modx->db->query($sql);
	while($row = mysql_fetch_assoc($results)){
		$timestamp = $row['Timestamp'];
	}

    $thisControlMD5 = ControlMD5($email,$timestamp);
    if($thisControlMD5==$MD5){
      return true;
    }
    else {
      return false;
    }
  }

//////////////////////////////////////////////////////////////////////////////// 

  function genSQLinsert ($xmlPost) {
    global $modx;
  $mySQL ="INSERT INTO `".$modx->TXNewsletters['tb_prefix']."txnewsletters_usersdata` ";
  $mySQL1 = "( ";
  $mySQL2 = "VALUES ( ";
  foreach($modx->TXNewsletters['fields'] as $id => $field) {
    switch ($field['ATTRIBUTE']['type']) {
      case 'text' :
        $mySQL1 .= "`".$field['ATTRIBUTE']['name']."` , ";
        $mySQL2 .= "'".$xmlPost[$field['ATTRIBUTE']['name']]."' , ";
      break;
      case 'email' :
        $mySQL1 .= "`".$field['ATTRIBUTE']['name']."` , ";
        $mySQL2 .= "'".$xmlPost[$field['ATTRIBUTE']['name']]."' , ";
        $email = $xmlPost[$field['ATTRIBUTE']['name']];
      break;
      case 'textarea' :
        $mySQL1 .= "`".$field['ATTRIBUTE']['name']."` , ";
        $mySQL2 .= "'".$xmlPost[$field['ATTRIBUTE']['name']]."' , ";
      break;
      case 'list' :
        $mySQL1 .= "`".$field['ATTRIBUTE']['name']."` , ";        
        switch ($field['listtype'][0]['CONTENT']) {
          case 'radio' :
            $mySQL2 .= "'".$xmlPost[$field['ATTRIBUTE']['name']]."' , ";
          break;
          case 'checkbox' :
            $mySQL2 .= "'".$xmlPost[$field['ATTRIBUTE']['name']]."' , ";
          break;
          case 'list' :
            $mySQL2 .= "'".$xmlPost[$field['ATTRIBUTE']['name']]."' , ";
          break;
        }        
      break;
      case 'checkbox' :
        $mySQL1 .= "`".$field['ATTRIBUTE']['name']."` , ";
        $mySQL2 .= $xmlPost[$field['ATTRIBUTE']['name']]." , ";
      break;
	  case 'subscribe' :
        $mySQL1 .= "`".$field['ATTRIBUTE']['name']."` , ";        
        $mySQL2 .= "'".$xmlPost[$field['ATTRIBUTE']['name']]."' , "; 
      break;
      }
  }
  
  $mySQL1 .= "`Timestamp` , ";
  $timestamp = time();
  $mySQL2 .= "'".$timestamp."' , ";  
  
  $mySQL1 = substr($mySQL1,0,strlen($mySQL1)-2);
  $mySQL1 .= " ) ";
  $mySQL2 = substr($mySQL2,0,strlen($mySQL2)-2);
  $mySQL2 .= " );";
  
  $mySQL .= $mySQL1;
  $mySQL .= $mySQL2;
  
  return $mySQL;
  }
  ////////////////////////////////////////////////////////////////////////////////  

  function genSQLupdate ($xmlPost) {
    global $modx;
  
  foreach($modx->TXNewsletters['fields'] as $id => $field) {
    switch ($field['ATTRIBUTE']['type']) {
      case 'email' :
        $champ = $field['ATTRIBUTE']['name'];
      break;
    }
  }
  $mySQL ="UPDATE `".$modx->TXNewsletters['tb_prefix']."txnewsletters_usersdata` SET ";
  foreach($modx->TXNewsletters['fields'] as $id => $field) {
        $mySQL .= "`".$field['ATTRIBUTE']['name']."` = ";
        $mySQL .= "'".$xmlPost[$field['ATTRIBUTE']['name']]."' , "; 
  }
  $mySQL = substr($mySQL,0,strlen($mySQL)-2);
  $mySQL .= " WHERE `".$champ."` = '".$xmlPost['lastEmail']."' ";
  
  return $mySQL;
  }

  ////////////////////////////////////////////////////////////////////////////////  

  function genSQLupdateMod ($xmlPost) {
    global $modx;
  
  foreach($modx->TXNewsletters['fields'] as $id => $field) {
    switch ($field['ATTRIBUTE']['type']) {
      case 'email' :
        $champ = $field['ATTRIBUTE']['name'];
      break;
	  case 'subscribe' :
        $subscribe = $field['ATTRIBUTE']['name'];
      break;
    }
  }
  $mySQL ="UPDATE `".$modx->TXNewsletters['tb_prefix']."txnewsletters_usersdata` SET ";
  
        $mySQL .= "`".$subscribe."` = ";
        $mySQL .= "'".$xmlPost[$subscribe]."' "; 

  $mySQL .= " WHERE `".$champ."` = '".$xmlPost['lastEmail']."' ";
  
  return $mySQL;
  }
  
////////////////////////////////////////////////////////////////////////////////  
  
  function genSQLshow ($orderBy) {
    global $modx;
  
  $mySQL ="SELECT * FROM `".$modx->TXNewsletters['tb_prefix']."txnewsletters_usersdata` ";
  if (count($_SESSION['TXNewslettersFilters'])>0){
    $mySQL .= "WHERE ";
    foreach ($_SESSION['TXNewslettersFilters'] as $filterName => $filterValue) {
        $mySQL .= "`".$filterName."` LIKE '%".$filterValue."%' ";
        $mySQL .="AND ";
    }
    $mySQL = substr($mySQL,0,strlen($mySQL)-4);
  }
  
  if (isset($orderBy) && $orderBy!='') {
    $orderBy = explode("|",$orderBy);
    $mySQL .= "ORDER BY ".$orderBy[0]." ".$orderBy[1]." ";
  }
  $mySQL .=";";

  return $mySQL;
  }

////////////////////////////////////////////////////////////////////////////////  
  
  function genSQLedit ($email) {
    global $modx;
  foreach($modx->TXNewsletters['fields'] as $id => $field) {
    switch ($field['ATTRIBUTE']['type']) {
      case 'email' :
        $champ = $field['ATTRIBUTE']['name'];
      break;
    }
  }
  $mySQL ="SELECT * FROM `".$modx->TXNewsletters['tb_prefix']."txnewsletters_usersdata` WHERE CONVERT(`".$champ."` USING utf8) = '".$email."' LIMIT 1;";
  return $mySQL;
  
  return $mySQL;
  }
////////////////////////////////////////////////////////////////////////////////  
  
  function testEmail ($email=false) {
  $exist = false;
  global $modx;
  $lastEmail = $email;
  if(!$lastEmail){$lastEmail = $_POST['lastEmail'];}
  foreach($modx->TXNewsletters['fields'] as $id => $field) {
    switch ($field['ATTRIBUTE']['type']) {
      case 'email' :
        $champ = $field['ATTRIBUTE']['name'];
        if(!$email){$email = $_POST[$field['ATTRIBUTE']['name']];}
      break;
    }
  }
  $mySQL ="SELECT * FROM `".$modx->TXNewsletters['tb_prefix']."txnewsletters_usersdata` WHERE CONVERT(`".$champ."` USING utf8) = '".$email."' LIMIT 1;";
  $results = $modx->db->query($mySQL);
  if($results){
    while($row = mysql_fetch_assoc($results)){
	  $exist = true;
	}
  }
  
  return $exist;
  }

////////////////////////////////////////////////////////////////////////////////  
  
  function genSQLadd () {
    global $modx;
  $mySQL ="CREATE TABLE `".$modx->TXNewsletters['tb_prefix']."txnewsletters_usersdata` (";
  $mySQL .="\n";
  foreach($modx->TXNewsletters['fields'] as $id => $field) {
      switch ($field['ATTRIBUTE']['type']) {
      case 'text' :
        $mySQL .="`".$field['ATTRIBUTE']['name']."` varchar(".$field['maxlength'][0]['CONTENT'].") NOT NULL default '',";
        $mySQL .="\n";
      break;
      case 'email' :
        $primary = $field['ATTRIBUTE']['name'];
        $mySQL .="`".$field['ATTRIBUTE']['name']."` varchar(".$field['maxlength'][0]['CONTENT'].") NOT NULL default '',";
        $mySQL .="\n";
      break;
      case 'textarea' :
        $mySQL .="`".$field['ATTRIBUTE']['name']."` text character set utf8 collate utf8_bin NOT NULL default '',";
        $mySQL .="\n";
      break;
      case 'list' :
        $mySQL .="`".$field['ATTRIBUTE']['name']."` varchar(255) NOT NULL default '',";
        $mySQL .="\n";
      break;
      case 'checkbox' :
        $mySQL .= "`".$field['ATTRIBUTE']['name']."` tinyint(1) NOT NULL default '0',";
        $mySQL .="\n";
      break;
      }
    }
  $mySQL .="PRIMARY KEY  (`".$primary."`)";
  $mySQL .="\n";
  $mySQL .=") ENGINE=MyISAM DEFAULT CHARSET=utf8;";
  
  return $mySQL;
  }

  function checkTable() {
    global $modx;
    $exist = false;
    $sql = "SHOW TABLES ;";
    $results = $modx->db->query($sql);
    while ($row = mysql_fetch_row($results)) {
     if ($row[0] == ($modx->TXNewsletters['tb_prefix'].'txnewsletters_usersdata')) { $exist = true; };
    }
    return $exist;
  }
  
  function createTable () {
    global $modx;
    if (checkTable() == false) {
    $sql = genSQLTable($tb_prefix);
    $results = $modx->db->query($sql);
    }
    return $results;
  }

?>
