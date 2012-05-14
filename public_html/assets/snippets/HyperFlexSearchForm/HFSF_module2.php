/**********************************/
/* [HyperFlexSearchForm:module#2] */
/**********************************/

    // cleaning of the search string

	$searchString_copy_t = str_replace("&quot;", "\"", $searchString);

	// letters to replace
	$input_t  = "ÀÁÂÃÄÅàáâãäåÒÓÔÕÖØòóôõöøéÉÊéééêéÇçÌêÎÏìêîïÙÚÛÜùúûüÿÑñ";
	// replacement letters
	$output_t = "aaaaaaaaaaaaooooooooooooeeeeeeeecciiiiiiiiuuuuuuuuynn";
	// removing accents from search string and lowercasing
	$searchString_copy_t = strtolower(strtr($searchString_copy_t, $input_t, $output_t));

	// quotes management

	// extraction of the search string segments between quotes
	//$mask_t = '`(")(.*?)(\\\(?<!\\\)("))`s';
	$mask_t = '`("[^"]+")`';
	preg_match_all($mask_t, $searchString_copy_t, $array_at);
	
	// we loop through each found segment
	foreach ($array_at[0] as $result_t)
	{
		// if empty result
		if (empty($result_t)) {
			continue;
		}
		// searching of the segment position
	    $pos_i = @strpos($searchString_copy_t, $result_t);
	    // length of the segment
		$length_i = strlen($result_t);
		// deletion of the found segment from the search string
		$searchString_copy_t = substr_replace($searchString_copy_t, '', $pos_i, $length_i);
		// we get rid of the quotes
		$segment_t = substr($result_t, 1, $length_i-2);
		// if the found segment is not an empty string, we add it to the list
		if (!empty($segment_t)) $keywords_at[] = $segment_t;
	}

	// stop words management
	
	$words_at = $stopWords_at;
	// if the stop words list is not empty
	if (!empty($words_at))
	{
		// trimming and lowercasing the stop words
		$clean_f = create_function('&$item','$item = trim(strtolower($item));');
		array_walk($words_at, $clean_f);
		
		// we loop through each word of the search string
		foreach (explode(' ', $searchString_copy_t) as $searchTerm_t)
		{			
			// trimming of the word
			$searchTerm_t = trim($searchTerm_t);
			// if the word is empty, we loop again
			if (empty($searchTerm_t)) continue;
			// we check wether the word is a stop word
			$test_i = array_search($searchTerm_t, $words_at);
			
			// "null" and "false" for PHP compatibility reasons
			// if the first letter is a "+", we keep the word even if it is in the black list
			if ( $test_i === null || $test_i === false || $searchTerm_t{0} == '+' )
			{
				// if the word begins with a "+"
				if ($searchTerm_t{0} == "+") {
					// we get rid of that "+"
					$searchTerm_t = substr($searchTerm_t, 1);
				}
				// we add the word to the list
				$keywords_at[] = $searchTerm_t;
			}
			// si le mot est à exclure
			elseif (is_int($test_i))
			{
				// we add it to the black list
				$stopwords_at[] = $searchTerm_t;
			}
		}
		
		// if the list of words to search is empty (every words excluded)
		if (count($keywords_at) == 0)
		{
			// we go back to the original search string
			$keywords_at = explode(" ", $searchString);
			// no warning
			$searchWarning_t = '';
		}
		// else, if the stop words list is not empty
		elseif (count($stopwords_at) > 0)
		{
			// if the stop words list is just composed by one word
			if (count($stopwords_at) == 1) {
				// warning message
				$searchWarning_t = '<strong>"'.$stopwords_at[0].'"</strong>';
				$searchWarning_t .= ' is a very common word, so it was ignored for this search.';
			}
			// else, if many words in the stop words list
			else {
				// warning message
				$searchWarning_t = 'The following words are very common, so they were ignored for this search :';
				$searchWarning_t .= ' <strong>'.implode(' ', $stopwords_at).'</strong>.';
			}
		}

	} // endif the stop words list is not empty

	// management of the final query

	// tables names for further query
	$tbl_cnt_t = $modx->getFullTableName("site_content");
	$tbl_dgp_t = $modx->getFullTableName("document_groups");
	$tbl_dgn_t = $modx->getFullTableName("documentgroup_names");
	$tbl_wga_t = $modx->getFullTableName("webgroup_access");
	$tbl_wgn_t = $modx->getFullTableName("webgroup_names");
	$tbl_tpl_t = $modx->getFullTableName("site_templates");
	$tbl_pgn_t = $modx->getFullTableName("site_plugins");

	// searching in the parent folders

	// if the categories list is not empty
	if (!empty($parentSearch_t))
	{
		// parent folders query segment
		$sql_t = "SELECT `id`, `parent`, `isfolder`, `pagetitle` FROM ".$tbl_cnt_t." WHERE `parent` <> '0' OR `isfolder` = '1';";
		$rs_i = $modx->dbQuery($sql_t);

		$folders_at = array();
		// we loop through the results to create a list
		while ($doc_at = mysql_fetch_object($rs_i)) {
			// we save the results by parent folder id
			$folders_at[$doc_at->parent][] = array("id"=>$doc_at->id, "isfolder"=>$doc_at->isfolder);
		}

		// this function returns the list of subfolders
		$list_at = array();
		// parent folder id, list of folders
		function getSubfolders($parentId_i, $folders_at)
		{
			global $list_at;
	
			// if the folder has subfolders
			if (is_array($folders_at[$parentId_i])) {
				// we loop through each subfolders
				foreach ($folders_at[$parentId_i] as $item_at) {
					// if the subfolder is not empty
					if ($item_at["isfolder"]) {
						// we get the parent id
						$list_at[] = (int) $item_at["id"];
						// and we search again with this id
						getSubfolders($item_at["id"], $folders_at);
					}
				}
				// list of subfolders
				$list_at[] = (int) $parentId_i;
			}
			
			// we return the list of subfolders found
			return $list_at;
		}

		// subfolders searching
		
		// we loop through each id in the list
		foreach (explode($hyperSeparator_t, $parentSearch_t) as $parentId_i)
		{
			$parentId_i = (int) $parentId_i;

			// we search subfolders in every given folders
			$subfolders_at[] = getSubfolders($parentId_i, $folders_at);
		}
		
		// we make a one dimension array from 2D array
		$subfolders_1D_at = array();
		foreach ($subfolders_at as $subfolders_arr_at) {
			foreach ($subfolders_arr_at as $sf_t) {
				$subfolders_1D_at[] = $sf_t;
			}
		}
		
		// we make result array unique
		$parentSearchArr_at = array_unique($subfolders_1D_at);
		
	}

	// building of the query

	$sql_t  = "SELECT ";
	$sql_t .= "cnt.`id`, cnt.`pagetitle`, cnt.`description`, cnt.`content`";
	
	// if the template list is not empty
	if (!empty($templateSearch_t))
	{
		$sql_t .= ", cnt.`parent`";
	}

	// if the document group list or the webgroup list is not empty
	if ( (!empty($documentgroupSearch_t)) || (!empty($webgroupSearch_t)) )
	{
		$sql_t .= ", dgn.`name` AS documentgroup";	
		// if the webgroup list is not empty
		if (!empty($webgroupSearch_t))
		{
			$sql_t .= ", wgn.`name` AS webgroup";
		}
	}

	// if the template list is not empty
	if (!empty($templateSearch_t))
	{
		$sql_t .= ", stp.`templatename` AS template";
	}

	// table to search in
	$sql_t .= " FROM $tbl_cnt_t AS cnt";

	// if the document group list or the webgroup list is not empty
	if ( (!empty($documentgroupSearch_t)) || (!empty($webgroupSearch_t)) )
	{
		$sql_t .= " LEFT JOIN ".$tbl_dgp_t." AS dgp ON dgp.`document` = cnt.`id`";
		$sql_t .= " LEFT JOIN ".$tbl_dgn_t." AS dgn ON dgn.`id` = dgp.`document_group`";

		// if the webgroup list is not empty
		if ( !empty($webgroupSearch_t) )
		{
			$sql_t .= " LEFT JOIN ".$tbl_wga_t." AS wga ON wga.`documentgroup` = dgn.`id`";
			$sql_t .= " LEFT JOIN ".$tbl_wgn_t." AS wgn ON wgn.`id` = wga.`webgroup`";
		}
	}

	// if the template list is not empty
	if ( (!empty($templateSearch_t)) )
	{
		$sql_t .= " LEFT JOIN ".$tbl_tpl_t." AS stp ON stp.`id` = cnt.`template`";
	}


	// search condition
	$sql_t .= " WHERE ";

	// basic filters
	$sql_t .= "cnt.`published`='1' AND cnt.`searchable`='1' AND cnt.`deleted`='0'";

	// this function return a query segement from a csv list

	// field name, list, search mode
	if (!function_exists("csvToQuery"))
	{
		function csvToQuery($name_t, $alias_t, $csv_t, $mode_i) // $mode_i = 0|1
		{
			$hyperSeparator_t = ","; // should use the global variable but it doesn't seem to work !
					
			// inclusion or exclusion depending on the search mode
			$compStr_t = ($mode_i) ? "=" : "<>";
			$joinStr_t = ($mode_i) ? "OR" : "AND";
		
			// we loop through each item of the list
			foreach (explode($hyperSeparator_t, $csv_t) as $val_t)
			{
				$group_t = trim($val_t);
		
				// raw query segment
				$fieldName_t = $alias_t.".`".$name_t."`";
				$query_t = $fieldName_t.$compStr_t."'".$val_t."'";
				$q_at[] = $query_t;
			}
		
			// final query segment
			$sql_t = " AND (" . implode(" ".$joinStr_t." ", $q_at) . ") ";
		
			// we return the query segment
			return $sql_t;
		}
	}

	// filter on templates

	// if the template list is not empty
	if (!empty($templateSearch_t))
	{
		// we build a query segment
		$sql_t .= csvToQuery("templatename", "stp", $templateSearch_t, $templateSearchMode_i);
	}

	// filter on parent folders

	// if the parent folders list is not empty
	if (!empty($parentSearch_t))
	{
		// we get folders and subfolders ids
		$parentSearch_at = implode(",", $parentSearchArr_at);

		// we build a query segment
		$sql_t .= csvToQuery("parent", "cnt", $parentSearch_t, $parentSearchMode_i);
	}

	// filter on the document groups

	// if the document groups list is not empty
	if (!empty($documentgroupSearch_t))
	{
		// we build a query segment
		$sql_t .= csvToQuery("name", "dgn", $documentgroupSearch_t, $documentgroupSearchMode_i);
	}

	// filter on the webgroups

	// if the webgroups list is not empty
	if (!empty($webgroupSearch_t))
	{
		// we build a query segment
		$sql_t .= csvToQuery("name", "wgn", $webgroupSearch_t, $webgroupSearchMode_i);
	}

	// filter on the keywords (it would be a pity to forget them)

	// this function escapes the strings in the query
	if (!function_exists("smartEscape"))
	{
		function smartEscape($value_t)
		{
			if (get_magic_quotes_gpc()) {
				// stripslashes
				$value_t = stripslashes($value_t);
			}
		
			if (!is_numeric($value)) {
				// escaping of the string
			    $value_t = "'". mysql_real_escape_string($value_t) . "'";
			}
		
			# we return the escaped string
			return $value_t;
		}
	}


	// we loop through each words
	foreach ($keywords_at as $searchTerm_t)
	{
		// if the first letter of the word is the sign "-" (word ignored)
		if ($searchTerm_t{0} == "-")
		{
			// inclusion or exclusion depending on the search mode
			$compStr_t = ($searchStyle == 'partial') ? "NOT LIKE" : "<>" ;
			$joinStr_t = "AND";

			// we get rid of the "-"
			$searchTerm_t = substr($searchTerm_t, 1);
		}

		// else, if not "-"
		else
		{
			// inclusion or exclusion depending on the search mode			
			$compStr_t = ($searchStyle == 'partial') ? "LIKE" : "=";
			$joinStr_t = "OR";
		}

		// depending on the search mode, we include the word between "%"		
		$searchTerm_t = ($searchStyle == 'partial') ? '%'.$searchTerm_t.'%' : $searchTerm_t;

		// we escape the string
		$searchTerm_t = smartEscape($searchTerm_t);

		// query segments
		$q1_t = " (LOWER(cnt.`pagetitle`) ".$compStr_t." ".$searchTerm_t." ".$joinStr_t;
		$q1_t .= " LOWER(cnt.`description`) ".$compStr_t." ".$searchTerm_t." ".$joinStr_t;
		$q1_t .= " LOWER(cnt.`content`) ".$compStr_t." ".$searchTerm_t.") ";
		// query segment (with html entities encoding)
		$q2_t = " (LOWER(cnt.`pagetitle`) ".$compStr_t." ".htmlentities($searchTerm_t)." ".$joinStr_t;
		$q2_t .= " LOWER(cnt.`description`) ".$compStr_t." ".htmlentities($searchTerm_t)." ".$joinStr_t;
		$q2_t .= " LOWER(cnt.`content`) ".$compStr_t." ".htmlentities($searchTerm_t).") ";
		// if the two strings are different
		if ($q1_t != $q2_t) {
			// we build the query segment with both of them
			$q_at[] = '('.$q1_t.'OR'.$q2_t.')';
		}
		// else
		else {
			// we just keep the first one
			$q_at[] = $q1_t;
		}
	}

	// do we use all words or not ?
	$joinStr_t = ($useAllWords) ? "AND" : "OR" ;

	// if there is only one word
	if (count($q_at) == 1)
	{
		$sql_t .= " AND (" . $q_at[0] . ") ";
	}
	// else, if many words
	elseif (count($q_at) > 1)
	{
		$sql_t .= " AND (" . implode(" ".$joinStr_t." ", $q_at) . ") ";
	}

	// ordering the results
	$sql_t .= "ORDER BY `editedon` DESC, `createdon` DESC LIMIT 200;";
	
	// giving back the query to FlexSearchForm
	$sql = $sql_t;

	// protecting the search string
	$searchString = str_replace("\"", "&quot;", $searchString);

/***********************************/
/* [/HyperFlexSearchForm:module#2] */
/***********************************/