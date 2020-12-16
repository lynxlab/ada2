<?php


// Funzione di upload dei file
function upload_file($file_up_ha,$source,$dest) {
    // inizializzazione variabili
    $str[0] = "";
    $str[1] = "";

    // successo
    $success["1"] = translateFN("File inviato con successo.") ;

    // errore
    $error["1"] = translateFN("Il file non pu&ograve; essere archiviato.") ;
    $error["2"] = translateFN("Non si &egrave; selezionato nessun file, oppure il file supera la dimensione massima consentita.") ;
    $error["3"] = translateFN("&egrave; un tipo di file non consentito.") ;


    // controllo tipo file inviato, se non consentito -> stop
    $file_type = mime_content_type($source);
    $mimetypeControl = upload_tipo_stop($file_type);//$file_up_ha['file_up']['type']);
    // if php detected mimetype is not accepted, try with browser declared mimetype
    if($mimetypeControl != ADA_FILE_UPLOAD_ACCEPTED_MIMETYPE) {
        $mimetypeControl = upload_tipo_stop($file_up_ha['file_up']['type']);
    }
    if($mimetypeControl == ADA_FILE_UPLOAD_ACCEPTED_MIMETYPE) {
        if(($source != 'none') && ($source != '')) {
            if($dest != '') {
                // echo " $dest <br>";
                // pulizia del nome del file da spazi e apostrofi
                $trans = array(
                        " " => "_",
                        "\'" => "_"
                );
                $dest = strtr($dest, $trans);

                //echo " $dest<br>";
                // copia del file dalla directory temporanea di upload a quella di destinazione
                if(move_uploaded_file($source, $dest)) {
                    $str[1] .= $success["1"] ;
                    $str[0] .= "ok";
                }else {
                    $str[1] .= $error["1"] ;
                    $str[0] .= "no";
                }
            }

        }else {
            $str[0] .= "no";
            $str[1] .= $error["2"];
        }

    }else {
        $str[0] .= "no";
        $str[1] .= $file_up_ha['file_up']['type']." ".$error["3"] ;
    }

    return $str ;
} // fine funzione


function upload_tipo_stop($tipo) {
// read it from config
    $mimetypeHa = $GLOBALS['ADA_MIME_TYPE'];
    return $mimetypeHa[$tipo]['permission'];
//return $mimetypeHa[$tipo];
}


// funziona cerca i files in nella directory passata due variabili globali:
// $filelisting = array che contiene i nomi dei files
function searchdir($basedir,$addPath = false) {
    // directory no esiste reurn false
    if(@is_dir($basedir)) {
        // inizializzazione variabili
        $filelisting = array();

        // apertura della directory e ricerca dei file presenti
        $handle=opendir($basedir);
        $i = 0 ;
        while($file = readdir($handle)) {
            if($file=="." or $file==".." or filetype($basedir."/".$file)=="dir") {
            }else {
                if($addPath)
                    $filelisting[$i] = $addPath . "$file";
                else
                    $filelisting[$i] = "$file";
                $i++;
            }
        }

        return $filelisting ;
    }else {
        return false ;
    }
}
/**
 * pulizia del nome del file da spazi e apostrofi
 * @param string $filename
 * @return String $dest cleaned filename
 */
function cleanFileName($filename) {
    $trans = array(
            " " => "_",
            "\'" => "_"
    );
    $dest = strtr($filename, $trans);
    return $dest;
}
?>