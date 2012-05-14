/**********************************/
/* [HyperFlexSearchForm:module#4] */
/**********************************/
	
        $SearchForm .= '<strong><a class="FSF_resultLink" href="[~'.$SearchFormsrc['id'].'~]'.$stabiloLink_t.'"';
        $SearchForm .= ' title="' . $SearchFormsrc['pagetitle'] . '">';
        $SearchForm .= $SearchFormsrc['pagetitle'] . "</a></strong><br />".$newline;
        
        // if an extract of the result is asked
        if ($showResultExtract_b)
        {
			// content to show with the link
			$content_t = trim(strip_tags($SearchFormsrc['content']));
			// we strip any modx sensitive tags
			foreach ($modRegExArray as $mReg){
				$content_t = preg_replace($mReg, '', $content_t);
			}
			// we get the first lines
			$extractLength_i = 200;
			if (strlen($content_t) > $extractLength_i) {
				while ($content_t{$extractLength_i} != ' ') {
					$extractLength_i--;
				}
			} else {
				$extractLength_i = strlen($content_t);
			}
			// content extract 
			$contentExtract_t = substr($content_t, 0, $extractLength_i).'&nbsp;...';
	        $SearchForm .= '<span class="FSF_resultDescription">' . $contentExtract_t . "</span><br />".$newline;
        }
        
		$SearchForm .= '<span class="FSF_resultLink">';
		$SearchForm .= '<a href="[~'.$SearchFormsrc['id'].'~]"?search='.urlencode($searchString).'> ';
		$SearchForm .= 'http://'.$_SERVER['SERVER_NAME'].'/[~'. $SearchFormsrc['id'] .'~]</a>';
		
		// do we show the total characters count ?
		if ($showExtractCharCount_b) {
			$SearchForm .= '	- '.strlen($SearchFormsrc['content']).'&nbsp;car.';
		}
		
		$SearchForm .= '</span><br /><br />'.$newline;
			
/***********************************/
/* [/HyperFlexSearchForm:module#4] */
/***********************************/