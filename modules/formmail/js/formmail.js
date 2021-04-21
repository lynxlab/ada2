/**
 * FORMMAIL MODULE.
 *
 * @package        formmail module
 * @author         Giorgio Consorti <g.consorti@lynxlab.com>
 * @copyright      Copyright (c) 2016, Lynx s.r.l.
 * @license        http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link           formmail
 * @version		   0.1
 */

var uploadedFiles = {};

function initDoc(userId)
{
	$j('input[name="subject"]').val('');
	$j('textarea[name="msgbody"]').val('');
	$j('input[name="helptype"]').val('');
	$j('input[name="sendcopy"]').attr('checked', false);

	// dropzone init
	Dropzone.autoDiscover = false;
	$j('#formmailDZ').addClass('dropzone');

	new Dropzone('#formmailDZ', $j.extend(getDropzonei18n(),
			{
				paramName : 'uploaded_file',
				maxFiles: 1,
				addRemoveLinks: true,
				url : 'ajax/upload.php' + '?userId='+userId,
				init: function() {
					var that = this;
					this.on("error", function(file, message) {
						$j.when(showHideDiv("Error", message, false)).then(function() {
							that.removeFile(file);
						});
					});

					this.on("success", function(file, responseObject) {
						if ('undefined' != typeof responseObject.attachedfile) {
							uploadedFiles[file.name] = responseObject.attachedfile;
						}
					});

					this.on("removedfile", function(file) {
						delete uploadedFiles[file.name];
					});
				}
			}
		)
	);

	// init semantic UI dropdown
	$j('.ui.selection.dropdown').dropdown();

	// remove placeholder class from select when user selects a value
	$j('input[name="helptype"]').on('change',function(){
		$j(this).parents('div').first().children('.placeholder.text').removeClass('placeholder');
	});

	// semantic UI form validation
	$j('.ui.form').form({
	    helptype: {
	      identifier  : 'helptype',
	      rules: [{
	          type   : 'empty',
	          prompt : $j('#helptypePrompt').text()
	      }]
	    },
	    subject: {
	      identifier  : 'subject',
	      rules: [{
	          type   : 'empty',
	          prompt : $j('#subjectPrompt').text()
	        }
	      ]},
	    msgbody: {
		      identifier  : 'msgbody',
		      rules: [{
		          type   : 'empty',
		          prompt : $j('#msgbodyPrompt').text()
		        }]
		}
	},{ inline: true,
		debug : false,
		performance: false,
		verbose: false,
		onSuccess: function() { ajaxSendEmail(this); }
	  }
	);
}


function ajaxSendEmail(element) {
	const helpTypeID = $j('input[name="helptype"]').val();

	if (helpTypeID.trim().length>0) {
		const recipient = $j('.helptype.item[data-value="'+helpTypeID+'"]','.selection.helptype').data('email').trim();
		const helpType  = $j('.helptype.item[data-value="'+helpTypeID+'"]','.selection.helptype').text().trim();

		if (recipient.length>0) {
			$j.ajax({
				type	:	'POST',
				url		:	'ajax/sendEmail.php',
				beforeSend: function() {
					$j(element).addClass('loading');
				},
				data	:	{
					helpType   : helpType,
					helpTypeID : helpTypeID,
					subject    : $j('input[name="subject"]').val().trim(),
					recipient  : recipient,
					msgbody    : $j('textarea[name="msgbody"]').val().trim(),
					selfSend   : $j('input[name="sendcopy"]').is(':checked') ? 1 :0,
					attachments: uploadedFiles
				}
			})
			.always(function() {
				$j(element).removeClass('loading');
			})
			.done(function(JSONObj) {
				if (JSONObj && JSONObj.status === "OK") {
					$j('#modalSentOK').modal('setting', { closable: false }).modal('show');

				} else {
					showHideDiv(JSONObj.title, JSONObj.msg, JSONObj.status === "OK");
				}
			});
		}
	}
}