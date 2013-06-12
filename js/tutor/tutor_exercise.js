document.write("<script type='text/javascript' src='../js/include/basic.js'></script>");
document.write("<script type='text/javascript' src='../js/include/menu_functions.js'></script>");

/**
 * Used by the tutor/tutor_exercise.php module, updates the contents of the current
 * exercise accordingly with answers given by the selected student.
 *
 * The css class names used are defined in exercise_player.css.
 */
function updateExerciseWithStudentAnswer() {
    /*
     * The element containing a json string with the answer given by a student
     * to this exercise.
     *
     * It's defined in tutor_exercise.tpl as a div with style display:none.
     */
    var jsonContainer = 'jsonResponse';

    if($(jsonContainer)) {

        var jsonResponse = $(jsonContainer).innerHTML;
        var responseObject = jsonResponse.evalJSON();
        var answersCount = responseObject.answers.length;
        for(var i = 0; i< answersCount; i++) {

            var element = responseObject.answers[i].id;
            var userAnswer = responseObject.answers[i].userAnswer;
            var correctAnswer = responseObject.answers[i].correctAnswer;
            var correctness = responseObject.answers[i].correctness;

            var elementAsString = $(element).toString();
            var ancestors = $(element).ancestors();
            var firstAncestor = ancestors[0];
            var cssClass = '';

            if (elementAsString == '[object HTMLInputElement]') {

                if(correctness) {
                    cssClass = 'correct-image';
                } else {
                    cssClass = 'wrong-image';
                }

                var inputType = $(element).getAttribute('type');
                switch (inputType) {
                   case 'checkbox':
                       $(firstAncestor).addClassName(cssClass);
                       if (userAnswer == 'true') {
                           $(element).setAttribute('checked', true);
                       }
                       break;
                   case 'radio':
                       if (userAnswer == 'true') {
                           $(element).setAttribute('checked', true);
                           $(firstAncestor).addClassName(cssClass);
                       }
                       break;
                   case 'text':
                       $(element).addClassName(cssClass);
                       $(element).setAttribute('value', userAnswer);
                       break;
                }
                /*
                 * By default all the input elements are disabled, since these
                 * are needed only to show how the student answered to the
                 * questions.
                 */
                $(element).setAttribute('disabled', true);

            } else if((elementAsString == '[object HTMLSpanElement]') || (elementAsString == '[object HTMLElement]')) {

                if(correctness) {
                    cssClass = 'correct';
                } else {
                    cssClass = 'wrong';
                }

                if (firstAncestor.toString() == '[object HTMLTableCellElement]') {
                    var tableCell = firstAncestor;
                    var tableRow = ancestors[1];

                    $(element).remove();

                    $(tableCell).insert(userAnswer);
                    $(tableCell).addClassName(cssClass);
                } else {
                    $(element).insert(userAnswer);
                    $(element).addClassName(cssClass);
                }
            }else if (elementAsString == '[object HTMLElement]') {

            }
        }

        updateExerciseWithCorrection(responseObject.answers);
    }
}

function dataTablesExec() {
//	$j('#container').css('width', '99%');
	var datatable = $j('#exercise_table').dataTable( {
//		'sScrollX': '100%',
                'aoColumns': [
                                null,
                                null,
                                { "sType": "date-euro" },                                
                                null,
                                null
                            ],
                'bLengthChange': false,
		//'bScrollCollapse': true,
//		'iDisplayLength': 50,
                "bFilter": false,
                "bInfo": false,
                "bSort": true,
                "bAutoWidth": true,
//		'bProcessing': true,
		'bDeferRender': true,
                "aaSorting": [[ 2, "desc" ],[ 3, "desc" ]],
                'bPaginate': false
//		'sPaginationType': 'full_numbers'
	}).show();
}

