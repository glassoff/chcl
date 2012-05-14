<?php
 /*
 * Ecriture de fichiers Csv (Excel)
 * Par MAMMANA Jean Charles
 *
 * Cette class simple permet d'ecrire des fichiers Excel
 * au format point virgule.
 * J'ai ecris cette petite class en PHP4 pour quelle soit
 * compatible avec tous les serveur php (non pas uniquement
 * PHP5).
 *
 *
 * -- Description des Membres --
 *
 * makeCsv(String filename) est le constructeur de la class
 * il defini le nom du fichier qui sera créé.
 *
 * addLine(String[] line, int numberLine)
 * ajoute une ligne ( une ligne est un tableau)
 * chaque element du tableau est un element de la ligne.
 * on donne le numero de la ligne que l'on souhaite
 * remplir.
 *
 * addCol(String[] line, int numberCol)
 * Idem que pour addLine() mais remplis une colonne.
 * prends en 2em parametre le numero de la colonne à
 * remplir.
 *
 * setValueAt(int x,int y,String v)
 * remplis une case aux coordonnées x,y
 *
 * maxX() et maxY()
 * retourne la taille (en nombre de cellules)
 * de lignes et de colonnes du tableau.
 *
 * createCsv()
 * Creation du fichier csv sur le serveur.
 *
 * downloadCsv()
 * Creation du fichier csv en memoire pour telechargement!
 *
 *
 *
 * -----------------------------
 *
 *
 */

 class MakeCsv {

 var $fileName;
 var $csv = array();

 function MakeCsv($fileName){
  $this->fileName = $fileName;
 }


 function addCol($array,$l){
    $this->csv[$l-1] = $array;
 }

 function addLine($array,$c){
   for($i=0;$i<count($array);$i++){
      $this->csv[$i][$c-1] = $array[$i];
   }
 }

 function setValueAt($x,$y,$v){
    $this->csv[$x-1][$y-1] = $v;
 }

 function createCsv(){
    $fp = @fopen($this->fileName,"w") or
    die('<br /><b>Fatal error</b>: Can\'t open <b>'.$this->fileName.'</b> in <b>'.__FILE__.'</b> on line <b>'.(__LINE__-1).'</b><br />');


   $maxY = $this->maxY();
   $maxX = $this->maxX();
   for($y=0;$y<$maxY;$y++){
   for($x=0;$x<$maxX;$x++){
   if(!empty($this->csv[$x][$y]))
   fwrite($fp,$this->csv[$x][$y],strlen($this->csv[$x][$y]));
   fwrite($fp,';',1);
   }
   fwrite($fp,"\r\n",2);
   }
   fclose($fp);

 }


 function downloadCsv(){
   header("Content-disposition: attachment; filename=\"$this->fileName\"");
   header("Content-Type: application/force-download");
   header("Content-Transfer-Encoding: binary");
   $maxY = $this->maxY();
   $maxX = $this->maxX();
   for($y=0;$y<$maxY;$y++){
   for($x=0;$x<$maxX;$x++){
   if(!empty($this->csv[$x][$y]))
   echo '"'.$this->csv[$x][$y].'"';
   // virgule pour W2K ou point pour Xp selon la version de Excel
   echo ';';
   }
   echo "\r\n";
   }
   exit();
 }


 function maxX(){
   $max = 0;
   while(list($k,) = each($this->csv)){
   if($k>$max) $max = $k;
   }
   reset($this->csv);
   return $max+1;
 }

 function maxY(){
   $max = 0;
   while(list($k,) = each($this->csv)){
   while(list($k2,) = each($this->csv[$k])){
   if($k2>$max) $max = $k2;
   }
   reset($this->csv[$k]);
   }
   reset($this->csv);
   return $max+1;
 }

 }
 ?>
