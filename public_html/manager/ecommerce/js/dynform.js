    function chooseoptions(){
        switch(document.getElementById('chooserender').value){
            case "DROP":
                document.getElementById('optionalspacer').show();
                document.getElementById('optionalstuff').hide();
                document.getElementById('chooselistname').show();
                document.getElementById('chooselistnamelabel').show();
                document.getElementById('specifydefaultlabel').show();
                document.getElementById('specifydefault').show();
            break;

            case "TEXT":
                document.getElementById('optionalspacer').hide();
                document.getElementById('optionalstuff').show();
                document.getElementById('specifysizelabel').innerHTML='&nbsp;&nbsp;&nbsp;Size:';
                document.getElementById('specifylengthlabel').hide();
                document.getElementById('specifylength').hide();
                document.getElementById('chooselistname').hide();
                document.getElementById('chooselistnamelabel').hide();
                document.getElementById('specifydefaultlabel').hide();
                document.getElementById('specifydefault').hide();
            break;

            case "TEXTAREA":
                document.getElementById('chooselistname').hide();
                document.getElementById('optionalspacer').hide();
                document.getElementById('optionalstuff').show();
                document.getElementById('specifysizelabel').show();
                document.getElementById('specifysize').show();
                document.getElementById('specifylength').show();
                document.getElementById('specifylengthlabel').show();

                document.getElementById('specifysizelabel').innerHTML='Rows:';
                document.getElementById('chooselistnamelabel').hide();
                document.getElementById('specifydefaultlabel').hide();
                document.getElementById('specifydefault').hide();
            break;

            case "RADIO":
                document.getElementById('optionalstuff').hide();
                document.getElementById('optionalspacer').show();
                document.getElementById('chooselistname').show();
                document.getElementById('chooselistnamelabel').show();
                document.getElementById('specifydefaultlabel').show();
                document.getElementById('specifydefault').show();
            break;

            case "CHECK":
                document.getElementById('optionalstuff').hide();
                document.getElementById('optionalspacer').show();
                document.getElementById('chooselistname').show();
                document.getElementById('chooselistnamelabel').show();
                document.getElementById('specifydefaultlabel').show();
                document.getElementById('specifydefault').show();
            break;

            case "MULTI":
                document.getElementById('optionalspacer').hide();
                document.getElementById('optionalstuff').show();
                document.getElementById('specifysizelabel').show();
                document.getElementById('specifysize').show();
                document.getElementById('specifysizelabel').innerHTML='&nbsp;&nbsp;&nbsp;Size:';
                document.getElementById('specifylengthlabel').hide();
                document.getElementById('specifylength').hide();
                document.getElementById('chooselistname').show();
                document.getElementById('chooselistnamelabel').show();
                document.getElementById('specifydefaultlabel').show();
                document.getElementById('specifydefault').show();
            break;
        }
   }

    function editchooseoptions(){
        switch(document.getElementById('editchooserender').value){
            case "DROP":
                document.getElementById('editoptionalstuff').hide();
                document.getElementById('editchooselistname').show();
                document.getElementById('editchooselistnamelabel').show();
                document.getElementById('editspecifydefaultlabel').show();
                document.getElementById('editspecifydefault').show();
            break;

            case "TEXT":
                document.getElementById('editoptionalstuff').show();
                document.getElementById('editspecifysizelabel').innerHTML='&nbsp;&nbsp;&nbsp;Size:';
                document.getElementById('editspecifylengthlabel').hide();
                document.getElementById('editspecifylength').hide();
                document.getElementById('editchooselistname').hide();
                document.getElementById('editchooselistnamelabel').hide();
                document.getElementById('editspecifydefaultlabel').hide();
                document.getElementById('editspecifydefault').hide();
            break;

            case "TEXTAREA":
                document.getElementById('editchooselistname').hide();
                document.getElementById('editoptionalstuff').show();
                document.getElementById('editspecifysizelabel').show();
                document.getElementById('editspecifysize').show();
                document.getElementById('editspecifylength').show();
                document.getElementById('editspecifylengthlabel').show();
                document.getElementById('editspecifysizelabel').innerHTML='Rows:';
                document.getElementById('editchooselistnamelabel').hide();
                document.getElementById('editspecifydefaultlabel').hide();
                document.getElementById('editspecifydefault').hide();

            break;

            case "RADIO":
                document.getElementById('editoptionalstuff').hide();
                document.getElementById('editchooselistname').show();
                document.getElementById('editchooselistnamelabel').show();
                document.getElementById('editspecifydefaultlabel').show();
                document.getElementById('editspecifydefault').show();
            break;

            case "CHECK":
                document.getElementById('editoptionalstuff').hide();
                document.getElementById('editchooselistname').show();
                document.getElementById('editchooselistnamelabel').show();
                document.getElementById('editspecifydefaultlabel').show();
                document.getElementById('editspecifydefault').show();
            break;

            case "MULTI":
                document.getElementById('editoptionalstuff').show();
                document.getElementById('editspecifysizelabel').show();
                document.getElementById('editspecifysize').show();
                document.getElementById('editspecifysizelabel').innerHTML='&nbsp;&nbsp;&nbsp;Size:';
                document.getElementById('editspecifylengthlabel').hide();
                document.getElementById('editspecifylength').hide();
                document.getElementById('editchooselistname').show();
                document.getElementById('editchooselistnamelabel').show();
                document.getElementById('editspecifydefaultlabel').show();
                document.getElementById('editspecifydefault').show();
            break;
        }
    }

