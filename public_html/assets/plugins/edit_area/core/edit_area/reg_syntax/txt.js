editAreaLoader.load_syntax['txt'] = {
     'COMMENT_SINGLE'         : {1 : '//', 2 : '#'}
    ,'COMMENT_MULTI'          : {'/*' : '*/'}
    ,'QUOTEMARKS'             : {1: '"'}
    ,'KEYWORD_CASE_SENSITIVE' : false
    ,'OPERATORS'              : ['+', '-', '/', '*', '=', '<', '>']
    ,'DELIMITERS'             : ['[', ']', '{', '}']
    ,'KEYWORDS'               : {'keywords' : ['modx','php','editarea']}
    ,'STYLES' : {
         'COMMENTS'   : 'color: green;'
        ,'QUOTESMARKS': 'color: blue;font-style: italic;'
        ,'OPERATORS'  : 'color: red;'
        ,'DELIMITERS' : 'color: blue;'
        ,'KEYWORDS'   : {'keywords' : 'color:red;font-weight:bold;'}
    }
};