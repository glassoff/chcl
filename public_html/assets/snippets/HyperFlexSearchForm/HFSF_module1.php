/**********************************/
/* [HyperFlexSearchForm:module#1] */
/**********************************/

  // SEARCH IMPROVEMENTS
  // --------------------------

	// separator used in the comma-separated lists
	$hyperSeparator_t = isset($hyperSeparator) ? $hyperSeparator : ',';

	// name of the chunk containing the stopwords
	$hyperChunk_t = isset($hyperChunk) ? $hyperChunk : 'HyperSearchStopwords';
	// name of the chunk table
	$tbl_chunk_t = $modx->getFullTableName("site_htmlsnippets");
	// query to check the existence of the chunk
	$sql_checkChunk_t = "SELECT `id` FROM ".$tbl_chunk_t." WHERE `name`='".$modx->db->escape($hyperChunk_t)."';";
	$rs_checkChunk_i = $modx->db->query($sql_checkChunk_t);
	// if the chunk doesn't exist, we create it
	if (mysql_num_rows($rs_checkChunk_i) !== 1) {
		// if MODx manager language is French, French words
		if (strpos($modx->config['manager_language'], 'francais') === 0) {
			$stopword_at = file($modx->config['rb_base_dir'].'/snippets/HyperFlexSearchForm/stopwords.fr.txt');
		}
		// else, English words
		else {
			 $stopword_at = file($modx->config['rb_base_dir'].'/snippets/HyperFlexSearchForm/stopwords.en.txt');
		}
		// trimming and lowercasing the stop words
		$clean_f = create_function('&$item','$item = trim(strtolower($item));');
		array_walk($stopword_at, $clean_f);
		// saving the chunk
		$field_at = array();
		$field_at['name'] = $hyperChunk_t;
		$field_at['description'] = 'List of stopwords used by the HyperFlexSearchForm snippet';
		$field_at['snippet'] = implode("\n", $stopword_at);
		// words encoding
		if ($modx->config['modx_charset'] != "UTF-8") {
			$field_at['snippet'] = utf8_decode($field_at['snippet']);
		}
		$modx->putIntTableRow($field_at, 'site_htmlsnippets');
		
		// empty cache
		// inspired from "manager/processors/save_htmlsnippet.processor.php" (lines 62-67)
		include_once $modx->config['rb_base_dir']."../manager/cache_sync.class.processor.php";
		$sync = new synccache();
		$sync->setCachepath($modx->config['rb_base_dir']."cache/");
		$sync->setReport(false);
		$sync->emptyCache(); // first empty the cache
	}
	// HyperFlexSearchForm stopwords
	$stopWords_at = $modx->getChunk($hyperChunk_t);
	
	// $templateSearch [ csv ]
	// Allow searching by templates
	// Idea from xyzvisual (admin@bababu.com)
	$templateSearch_t = isset($templateSearch) ? $templateSearch : '';

	// $templateSearchMode [ 0 | 1 ]
	// Indicates whether we exclude or include the templates list
	$templateSearchMode_i = isset($templateSearchMode) ? $templateSearchMode : 1;

	// $parentSearch [ csv ]
	// Allow parent categories to be searched in
	// Idea from Wendy Novianto
	$parentSearch_t = isset($parentSearch) ? $parentSearch : '';
	
	// $parentSearchMode [ 0 | 1 ]
	// Indicates whether we exclude or include the parent categories list
	$parentSearchMode_i = isset($parentSearchMode) ? $parentSearchMode : 1;
	
	// $documentgroupSearch [ csv ]
	// Allow searching by documentgroups
	$documentgroupSearch_t = isset($documentgroupSearch) ? $documentgroupSearch : '';

	// $documentgroupSearchMode [ 0 | 1 ]
	// Indicates whether we exclude or include the documentgroups list
	$documentgroupSearchMode_i = isset($documentgroupSearchMode) ? $documentgroupSearchMode : 1;

	// $webgroupSearch [ csv ]
	// Allow searching by webgroups
	$webgroupSearch_t = isset($webgroupSearch) ? $webgroupSearch : '';

	// $webgroupSearchMode [ 0 | 1 ]
	// Indicates whether we exclude or include the webgroups list
	$webgroupSearchMode_i = isset($webgroupSearchMode) ? $webgroupSearchMode : 1;

	// $stopWords [ csv ] (from chunk)
	// List of the words to exclude from the search string
	$stopWords_at = explode("\n", $modx->getChunk($hyperChunk_t));
	
	// $showResultExtract [ true | false ]
	// Do we show the an extract of the each found pages or not ?
	$showResultExtract_b = isset($showResultExtract) ? (bool) $showResultExtract : true;
	
	// $showExtractCharCount [ true | false ]
	// Do we show the total extracts characters count or not ?
	$showExtractCharCount_b = isset($showExtractCharCount) ? (bool) $showExtractCharCount : false;


// protecting the searchString (for the searchbox value)
$_POST['search'] = str_replace("\"", "&quot;", $_POST['search']);

/***********************************/
/* [/HyperFlexSearchForm:module#1] */
/***********************************/