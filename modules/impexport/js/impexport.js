function initDoc( maxSize )
{
	$j("#importfile").pekeUpload(
			{
//				onSubmit: true,				
				allowedExtensions:"zip",
//				onFileError:function(file,error){alert("error on file: "+file.name+" error: "+error+"");},
				onFileSuccess:function(file){ goToImportStepTwo (file); },
				btnText: "Sfoglia Files..",
//				multi: false,
				maxSize: maxSize,
				field: 'uploaded_file',
				url: 'upload.php'
			}		
	);
}

function goToImportStepTwo (file)
{
	$j('#importFileName').val(file.name);
	$j('#uploadedFileName').html (file.name);
	$j('.importFormStep1').effect ('drop', function() { $j('.importFormStep2').effect ('slide'); });
}