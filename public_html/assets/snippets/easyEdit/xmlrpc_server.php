<?php
ini_set("error_reporting", E_ALL ^ E_NOTICE);

//mysql_query("SET NAMES utf8");

require_once('IXR_Library.inc.php');

class modx_xmlrpc_server extends IXR_Server {
	public $charset;
	
    function modx_xmlrpc_server() {
    	global $modx;
    	$this->charset = $modx->config['modx_charset'];
    	
		$this->methods = array(
			"modx.easyEdit.getPages" => "this:getPages",
			"modx.easyEdit.getPagesIds" => "this:getPagesIds",
			"modx.easyEdit.getPage" => "this:getPage",
			"modx.easyEdit.getParents" => "this:getParents",
			"modx.easyEdit.getPageTitle" => "this:getPageTitle",
			"modx.easyEdit.savePage" => "this:savePage",
		
			"modx.easyEdit.getSnippetsIds" => "this:getSnippetsIds",
			"modx.easyEdit.getSnippet" => "this:getSnippet",
			"modx.easyEdit.getCategoryName" => "this:getCategoryName",
			"modx.easyEdit.saveSnippet" => "this:saveSnippet",
		
			"modx.easyEdit.getTemplatesIds" => "this:getTemplatesIds",
			"modx.easyEdit.getTemplate" => "this:getTemplate",
			"modx.easyEdit.saveTemplate" => "this:saveTemplate",
		
			"modx.easyEdit.getChunksIds" => "this:getChunksIds",
			"modx.easyEdit.getChunk" => "this:getChunk",
			"modx.easyEdit.saveChunk" => "this:saveChunk",
		
			"modx.easyEdit.getModulesIds" => "this:getModulesIds",
			"modx.easyEdit.getModule" => "this:getModule",
			"modx.easyEdit.saveModule" => "this:saveModule",		
		
			'demo.sayHello' => 'this:sayHello'
		);

		$this->IXR_Server($this->methods);
    }
    function sayHello($args) {
        return 'привет!';
    }
    
    function setCharsetGet(){
    	if ($this->charset != 'UTF-8'){
    		mysql_query("SET NAMES utf8");
    	}
    }
    
    function encodeForSet($string){
    	return iconv("UTF-8", $this->charset, $string);
    }
    
	function getPages() {
		global $modx;
		
		$result = $modx->db->select("id, type, contentType, pagetitle, content, parent, isfolder", $modx->getFullTableName('site_content'), "", "parent");
		$pages = array();
		while( $row = $modx->db->getRow( $result ) ) {
			$rows = array($row['id'], $row['type'], $row['contentType'], $row['pagetitle'], $row['content'], $row['parent'], $row['isfolder']);
			$pages[] = $rows;
		}
	
		return $pages;
		
	} 

	function getPagesIds(){
		global $modx;
		
		$result = $modx->db->select("id", $modx->getFullTableName('site_content'), "", "parent");
		$ids = array();
		while( $row = $modx->db->getRow( $result ) ) {
			$ids[] = $row['id'];
		}
		return $ids;
	}
	
	function getPage($id){
		global $modx;
		
		$this->setCharsetGet();

		$result = $modx->db->select("id, type, contentType, pagetitle, content, parent, isfolder", $modx->getFullTableName('site_content'), "id = $id", "");
		$row = $modx->db->getRow( $result );
		$rows = array($row['id'], $row['type'], $row['contentType'], $row['pagetitle'], $row['content'], $row['parent'], $row['isfolder']);
	
		return $rows;		
	}
	
	function getParents($id){
		global $modx;
		
		$parents = $modx->getParentIds($id);
		return array_values($parents);
	}
	
	function getPageTitle($id){
		global $modx;
		
		$this->setCharsetGet();
		
		$result = $modx->db->select("pagetitle", $modx->getFullTableName('site_content'), "id = $id", "");
		$row = $modx->db->getRow( $result );
		
		return $row['pagetitle'];
	}
	
	function savePage($args){
		$id = $args[0];
		$content = $args[1];
		#return;
		global $modx;
		
		$fields = array('content' => $this->encodeForSet($content));
		$result = $modx->db->update( $fields, $modx->getFullTableName('site_content'), 'id = "' . $id . '"' );
		$this->clearCache();
	}
	
	function getSnippetsIds(){
		global $modx;
		
		$result = $modx->db->select("id", $modx->getFullTableName('site_snippets'), "", "category");
		$ids = array();
		while( $row = $modx->db->getRow( $result ) ) {
			$ids[] = $row['id'];
		}
		return $ids;	
	}
	
	function getSnippet($id){
		global $modx;

		$this->setCharsetGet();
		
		$result = $modx->db->select("id, name, category, snippet", $modx->getFullTableName('site_snippets'), "id = $id", "");
		$row = $modx->db->getRow( $result );
		$rows = array($row['id'], $row['name'], $row['category'], $row['snippet']);
	
		return $rows;		
	}	
	
	function getCategoryName($category_id){
		global $modx;
		
		$this->setCharsetGet();
		
		$result = $modx->db->select("category", $modx->getFullTableName('categories'), "id = $category_id", "");
		$row = $modx->db->getRow( $result );
		
		return $row['category'];
	}
	
	function saveSnippet($args){
		$id = $args[0];
		$content = $args[1];
		#return;
		global $modx;
		
		$fields = array('snippet' => $this->encodeForSet($content));
		$result = $modx->db->update( $fields, $modx->getFullTableName('site_snippets'), 'id = "' . $id . '"' );	
		$this->clearCache();
	}
    
	function getTemplatesIds(){
		global $modx;
		
		$result = $modx->db->select("id", $modx->getFullTableName('site_templates'), "", "category");
		$ids = array();
		while( $row = $modx->db->getRow( $result ) ) {
			$ids[] = $row['id'];
		}
		return $ids;	
	}
	
	function getTemplate($id){
		global $modx;

		$this->setCharsetGet();
		
		$result = $modx->db->select("id, templatename, category, content", $modx->getFullTableName('site_templates'), "id = $id", "");
		$row = $modx->db->getRow( $result );
		$rows = array($row['id'], $row['templatename'], $row['category'], $row['content']);
	
		return $rows;	
	}
	
	function saveTemplate($args){
		$id = $args[0];
		$content = $args[1];
		#return;
		global $modx;
		
		$fields = array('content' => $this->encodeForSet($content));
		$result = $modx->db->update( $fields, $modx->getFullTableName('site_templates'), 'id = "' . $id . '"' );
		$this->clearCache();	
	}	
	
	function getChunksIds(){
		global $modx;
		
		$result = $modx->db->select("id", $modx->getFullTableName('site_htmlsnippets'), "", "category");
		$ids = array();
		while( $row = $modx->db->getRow( $result ) ) {
			$ids[] = $row['id'];
		}
		return $ids;	
	}
	
	function getChunk($id){
		global $modx;

		$this->setCharsetGet();
		
		$result = $modx->db->select("id, name, category, snippet", $modx->getFullTableName('site_htmlsnippets'), "id = $id", "");
		$row = $modx->db->getRow( $result );
		$rows = array($row['id'], $row['name'], $row['category'], $row['snippet']);
	
		return $rows;	
	}	
	
	function saveChunk($args){
		$id = $args[0];
		$content = $args[1];
		#return;
		global $modx;
		
		//$this->setCharsetSet();
		
		$fields = array('snippet' => $this->encodeForSet($content));
		$result = $modx->db->update( $fields, $modx->getFullTableName('site_htmlsnippets'), 'id = "' . $id . '"' );	
		$this->clearCache(); 
	}	
	
	function getModulesIds(){
		global $modx;
		
		$result = $modx->db->select("id", $modx->getFullTableName('site_modules'), "", "category");
		$ids = array();
		while( $row = $modx->db->getRow( $result ) ) {
			$ids[] = $row['id'];
		}
		return $ids;	
	}

	function getModule($id){
		global $modx;

		$this->setCharsetGet();
		
		$result = $modx->db->select("id, name, category, modulecode", $modx->getFullTableName('site_modules'), "id = $id", "");
		$row = $modx->db->getRow( $result );
		$rows = array($row['id'], $row['name'], $row['category'], $row['modulecode']);
	
		return $rows;	
	}	

	function saveModule($args){
		$id = $args[0];
		$content = $args[1];
		#return;
		global $modx;
		
		$fields = array('modulecode' => $this->encodeForSet($content));
		$result = $modx->db->update( $fields, $modx->getFullTableName('site_modules'), 'id = "' . $id . '"' );	
		$this->clearCache();
	}
		
	/**
	 * Полная очистка кэша
	 */
	function clearCache() {
	    global $modx;
	    
	    $modx->clearCache();
	    
	    include_once MODX_BASE_PATH . 'manager/processors/cache_sync.class.processor.php';
	    $sync = new synccache();
	    $sync->setCachepath(MODX_BASE_PATH . "assets/cache/");
	    $sync->setReport(false);
	    $sync->emptyCache();
	}	
		
}

$server = new modx_xmlrpc_server();

?>