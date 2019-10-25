/*
funzioni di creazione di opzioni con la definizione del default selected
new Option([text[, value[, defaultSelected[, selected]]]])
*/
var adultAge = 18;

function isAdult(dateString) {
    // First check for the pattern
    if(!/^\d{2}\/\d{2}\/\d{4}$/.test(dateString))
        return -1;

    // Parse the date parts to integers
    var parts = dateString.split("/");
    var day = parseInt(parts[0], 10);
    var month = parseInt(parts[1], 10);
    var year = parseInt(parts[2], 10);
    var today = new Date();

    // Check the ranges of month and year
    if(year < 1900 || year > today.getFullYear() || month == 0 || month > 12)
        return -1;

    var monthLength = [ 31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31 ];

    // Adjust for leap years
    if(year % 400 == 0 || (year % 100 != 0 && year % 4 == 0))
        monthLength[1] = 29;

    // Check the range of the day
    var dateOK = day > 0 && day <= monthLength[month - 1];

    if (dateOK) {
    	var birthDate = new Date(parts[2]+"/"+parts[1]+"/"+parts[0]);
	    var age = today.getFullYear() - birthDate.getFullYear();
	    var m = today.getMonth() - birthDate.getMonth();
	    if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
	        age--;
	    }
	    if (age<adultAge) return -2; // -2 means is not adult
    } else {
    	return -1; // -1 is invalid date
    }
    return 0;
}

function initRegistration() {
	$j('form[name="registration"]').submit(function(e) {
		var isAdultCheck = isAdult($j('#birthdate').val());
		if (isAdultCheck<0) {
			e.preventDefault();
			if (isAdultCheck == -1) $j('.alertMSG','#registrationError').html($j('#invalidDate').html());
			else if (isAdultCheck == -2) $j('.alertMSG','#registrationError').html($j('#notAdult').html());
			$j('#registrationError').modal('show');
			return;
		}
		if (!checkAllPoliciesAccepted()) {
			e.preventDefault();
			$j('#acceptPoliciesMSG').modal('show');
			return;
		}
	});

	if ($j('input[name="uname"]', 'form[name="registration"]').length>0) {
		var usercheckTimeout, lastCheckedUname = '';
		$j('form[name="registration"]')
			.on('change', 'input[name="nome"], input[name="cognome"]', function() {
				var stripname = $j('input[name="nome"]').val().replace(/[^A-Za-z0-9]/g, '');
				var stripsurname = $j('input[name="cognome"]').val().replace(/[^A-Za-z0-9]/g, '');
				var conj = (stripname.length>0 && stripsurname.length>0) ? '.' : '';
				$j('input[name="uname"]', 'form[name="registration"]').val((stripname+conj+stripsurname).toLowerCase().substring(0,255)).trigger('change');
			})
			.on('keyup change', 'input[name="uname"][data-isusername="true"]', function() {
				var that = $j(this);
				var containerel = that.parents('li.form').first();
				var messageDivId = "unamecheckstatus";
				that.val(that.val().trim());
				clearTimeout(usercheckTimeout);

				if (that.val().length>0 && lastCheckedUname !== that.val()) {
					$j('#'+messageDivId).remove();
					$j('<div id="'+messageDivId+'"></div>').appendTo(containerel);
					$j('#'+messageDivId).html($j('#unameCheckProgress').html());
					usercheckTimeout = setTimeout(
						function() {
							$j.when(ajaxCheckUname(that.val()))
								.done(function(result) {
									var showLabel = 'Fail';
									if ('unameok' in result) {
										showLabel = result.unameok ? 'Ok' : 'Exists';
									}
									$j('#'+messageDivId).html($j('#unameCheck'+showLabel).html());
								})
								.fail(function(result){
									$j('#'+messageDivId).html($j('#unameCheckFail').html());
								})
								.always(function() {
									lastCheckedUname = that.val();
								});
						}
						,500
					);
				}

			});
	}
}

function ajaxCheckUname(value, containerel, messageDivId) {
	return $j.ajax({
		'type': 'POST',
		'url': MODULES_SECRETQUESTION_HTTP + '/ajax/checkUname.php',
		'cache': false,
		'data': { uname : value }
	});
}

function CreateProvince() {
 var Bulgaria = 1;
 var Romania = 5;
 var Espana = 2;
 var Italia = 4;
 var Iceland = 3;

  var Primary = document.services_request.country.selectedIndex;

  if ((Primary == null) || (Primary == 0)) return;

  if (Primary == Bulgaria) {
  var Bu_provinces = new Array(
	  "Burgas",
	  "Dobrich",
	  "Gabrovo",
	  "Haskovo",
	  "Kardzhali",
	  "Kyustendil",
	  "Lovech",
	  "Montana",
	  "Pazardzhik",
	  "Pernik",
	  "Pleven",
	  "Plovdiv",
	  "Razgrad",
	  "Ruse",
	  "Shumen",
	  "Silistra",
	  "Sliven",
	  "Smolyan",
	  "Sofia",
	  "Stara_Zagora",
	  "Targovishte",
	  "Varna",
	  "Veliko_Tarnovo",
	  "Vidin",
	  "Vratsa",
	  "Yambol"
		  );


  var Province = new Array;
  for (i = 0; i<25; i++){
	  Province[i] = new Option(Bu_provinces[i],Bu_provinces[i]);
  }
  }

  if (Primary == Espana) {
	  var Es_provinces = new Array(
 "Álava",
"Albacete",
"Alicante",
"Almería",
"Asturias",
"Ávila",
"Badajoz",
"Baleares",
"Barcelona",
"Vizcaya",
"Burgos",
"Cáceres",
"Cádiz",
"Cantabria",
"Castellón",
"Ciudad_Real",
"Córdoba",
"Cuenca",
"Gerona",
"Granada",
"Guadalajara",
"Guipúzcoa",
"Huelva",
"Huesca",
"Jaén",
"La_Coruña",
"La_Rioja",
"León",
"Lérida",
"Lugo",
"Madrid",
"Málaga",
"Murcia",
"Navarra",
"Orense",
"Palencia",
"Las_Palmas",
"Pontevedra",
"Salamanca",
"SantaCruz",
"Segovia",
"Sevilla",
"Soria",
"Tarragona",
"Teruel",
"Toledo",
"Valencia",
"Valladolid",
"Zamora",
"Zaragoza"
);


  var Province = new Array;

  for (i = 0; i<49; i++){
	  Province[i] = new Option(Es_provinces[i],Es_provinces[i]);
  }
  }

  if (Primary == Iceland) {

	  /* FIXME: sono queste?
     * Árnessýsla
    * Austur-Barðastrandarsýsla
    * Austur-Húnavatnssýsla
    * Austur-Skaftafellssýsla
    * Borgarfjarðarsýsla
    * Dalasýsla
    * Eyjafjarðarsýsla
    * Gullbringusýsla
    * Kjósarsýsla
    * Mýrasýsla
    * Norður-Ísafjarðarsýsla
    * Norður-Múlasýsla
    * Norður-Þingeyjarsýsla
    * Rangárvallasýsla
    * Skagafjarðarsýsla
    * Snæfellsnes-og Hnappadalssýsla
    * Strandasýsla
    * Suður-Múlasýsla
    * Suður-Þingeyjarsýsla
    * Vestur-Barðastrandarsýsla
    * Vestur-Húnavatnssýsla
    * Vestur-Ísafjarðarsýsla
    * Vestur-Skaftafellssýsla

 */
 var Is_provinces = new Array("Reykyavik");

  var Province = new Array;
  for (i = 0; i<1; i++){
	  Province[i] = new Option(Is_provinces[i],Is_provinces[i]);
  }
 }


  if (Primary == Italia) {
  var It_provinces = new Array(
		  "AGRIGENTO",
		  "ALESSANDRIA",
		  "ANCONA",
		  "AOSTA",
		  "AREZZO",
		  "ASCOLI_PICENO",
		  "ASTI",
		  "AVELLINO",
		  "BARI",
		  "BELLUNO",
		  "BENEVENTO",
		  "BERGAMO",
		  "BIELLA",
		  "BOLOGNA",
		  "BOLZANO",
		  "BRESCIA",
		  "BRINDISI",
		  "BARLETTA-ANDRIA-TRANI",
		  "CAGLIARI",
		  "CALTANISSETTA",
		  "CAMPOBASSO",
		  "CASERTA",
		  "CATANIA",
		  "CATANZARO",
		  "CHIETI",
		  "COMO",
		  "COSENZA",
		  "CREMONA",
		  "CROTONE",
		  "CUNEO",
		  "ENNA",
		  "FERRARA",
		  "FIRENZE",
		  "FOGGIA",
		  "FORLI-CESENA",
		  "FERMO",
		  "FROSINONE",
		  "GENOVA",
		  "GORIZIA",
		  "GROSSETO",
		  "IMPERIA",
		  "ISERNIA",
		  "LA_SPEZIA",
		  "AQUILA",
		  "LATINA",
		  "LECCE",
		  "LECCO",
		  "LIVORNO",
		  "LODI",
		  "LUCCA",
		  "MONZA_E_DELLA_BRIANZA",
		  "MACERATA",
		  "MANTOVA",
		  "MASSA-CARRARA",
		  "MATERA",
		  "MESSINA",
		  "MILANO",
		  "MODENA",
		  "NAPOLI",
		  "NOVARA",
		  "NUORO",
		  "ORISTANO",
		  "PADOVA",
		  "PALERMO",
		  "PARMA",
		  "PAVIA",
		  "PERUGIA",
		  "PESARO_E_URBINO",
		  "PESCARA",
		  "PIACENZA",
		  "PISA",
		  "PISTOIA",
		  "PORDENONE",
		  "POTENZA",
		  "PRATO",
		  "RAGUSA",
		  "RAVENNA",
		  "REGGIO_DI_CALABRIA",
		  "REGGIO_EMILIA",
		  "RIETI",
		  "RIMINI",
		  "ROMA",
		  "ROVIGO",
		  "SALERNO",
		  "SASSARI",
		  "SAVONA",
		  "SIENA",
		  "SIRACUSA",
		  "SONDRIO",
		  "TARANTO",
		  "TERAMO",
		  "TERNI",
		  "TORINO",
		  "TRAPANI",
		  "TRENTO",
		  "TREVISO",
		  "TRIESTE",
		  "UDINE",
		  "VARESE",
		  "VENEZIA",
		  "VERBANO-CUSIO-OSSOLA",
		  "VERCELLI",
		  "VERONA",
		  "VIBO_VALENTIA",
		  "VICENZA",
		  "VITERBO"
		  );
  var Province = new Array;
  for (i = 0; i<105; i++){
	  Province[i] = new Option(It_provinces[i],It_provinces[i]);
  }
 }

if (Primary == Romania) {
 var Ro_provinces = new Array(
		 "Alba",
		 "Arad",
		 "Argeş",
		 "Bacău",
		 "Bihor",
		 "Bistriţa-Năsăud",
		 "Botoşani",
		 "Braşov",
		 "Brăila",
		 "Bucureşti",
		 "Buzău",
		 "Caraş-Severin",
		 "Călăraşi",
		 "Cluj",
		 "Constanţa",
		 "Covasna",
		 "Dâmboviţa",
		 "Dolj",
		 "Galaţi",
		 "Giurgiu",
		 "Gorj",
		 "Harghita",
		 "Hunedoara",
		 "Ialomiţa",
		 "Iaşi",
		 "Ilfov",
		 "Maramureş",
		 "Mehedinţi",
		 "Mureş",
		 "Neamţ",
		 "Olt",
		 "Prahova",
		 "Satu_Mare",
		 "Sălaj",
		 "Sibiu",
		 "Suceava",
		 "Teleorman",
		 "Timiş",
		 "Tulcea",
		 "Vaslui",
		 "Vâlcea",
		 "Vrancea"
 );
  var Province = new Array;
  for (i = 0; i<41; i++){
	  Province[i] = new Option(Ro_provinces[i],Ro_provinces[i]);
  }
  }

  for (i=document.services_request.Province.options.length; i>0; i--) {
   document.services_request.Province.options[i] = null;
  }

  for(i=0; i<Province.length; i++) {
  document.services_request.Province.options[i] = Province[i];
  }

  document.services_request.Province.options[0].selected = true;

}

/* only for  testing use */

function CreateProvider() {

  var Service = document.services_request.service_type.value;

  var erogatori = new Array();
  erogatori["jaen"]= new Array("JaÃ©n Provincial Authority (Spain)","Chamber of Commerce of JaÃ©n (Spain)");
  erogatori["torino"]= new Array("Coop. Orso (Italy)");
  erogatori["oristano"]= new Array("Cooperativa Studio Progetto 2 (Italy)");
  erogatori["sofia"]= new Array("iCentres (Bulgaria)");
  erogatori["reykjavik"]= new Array("The Research Liaison Office of the University of Iceland (Iceland)");
  erogatori["iasi"]= new Array("City Hall of Iasi (Romania)");



  if ((Service == null) || (Service == 0)) return;

  if (Service == "2_1") { /* Educational guidance advice. The User can choose from all Pilot that provide the service*/

  var pilot = new Array;
  pilot[0] = new Option("iCentres (Bulgaria)");
  pilot[1] = new Option("The Research Liaison Office of the University of Iceland (Iceland)");
  pilot[2] = new Option("Coop. Orso (Italy)");
  pilot[3] = new Option("Cooperativa Studio Progetto 2 (Italy)");
  pilot[4] = new Option("City Hall of Iasi (Romania)");
  pilot[5] = new Option("Vila-Real City Council (Spain)");

  }

  if (Service == "2_2") { /* Vocational guidance advice. The User can choose from all Pilot that provide the service*/

  var pilot = new Array;
  pilot[0] = new Option("iCentres (Bulgaria)");
  pilot[1] = new Option("The Research Liaison Office of the University of Iceland (Iceland)");
  pilot[2] = new Option("Coop. Orso (Italy)");
  pilot[3] = new Option("Arezzo Provincial Government (Italy)");
  pilot[4] = new Option("Cooperativa Studio Progetto 2 (Italy)");
  pilot[5] = new Option("City Hall of Iasi (Romania)");
  pilot[6] = new Option("JaÃ©n Provincial Authority (Spain)");
  pilot[7] = new Option("Chamber of Commerce of JaÃ©n (Spain)");

  }

  if ((Service == "3_1") || (Service == "3_2")) {

  var provincia_sel = document.services_request.Province.value;
//  alert(isProvinceService(provincia_sel));

  if ((isProvinceService(provincia_sel)< 0 )) {
    alert ("Sorry, no service provider in your Province");
    return;
  }
  if (document.services_request.fiscal_code.value == "") {
    alert ("Sorry, You have to fill the fiscal code");
    return;
  }

/*  alert(provincia_sel);
  alert (erogatori[provincia_sel][0]);
  alert (erogatori[provincia_sel].length);
*/
  var pilot = new Array;


  for (i=0; i<erogatori[provincia_sel].length; i++) {
//  alert (erogatori[provincia_sel][i]);
  pilot[i] = new Option(erogatori[provincia_sel][i]);
  }
//  alert (pilot[0]);
//  alert (pilot.length);

  var Province = new Array;
  Province[0] = new Option("Ãlava/Araba");
  Province[1] = new Option("Albacete");
  Province[2] = new Option("Alicante/Alacant");
  Province[3] = new Option("AlmerÃ­a");
  Province[4] = new Option("Barcellona");
  Province[5] = new Option("JaÃ©n");
  Province[6] = new Option("Madrid");
  Province[7] = new Option("MÃ¡laga");
  Province[8] = new Option("etc...");

  }

  if (Service == 3) {

  var Province = new Array;
  Province[0] = new Option("Reykyavik");
  Province[1] = new Option("Etc..");

  }
  if (Service == 4) {

  var Province = new Array;
  Province[0] = new Option("Arezzo");
  Province[1] = new Option("Oristano");
  Province[2] = new Option("Torino");
  Province[3] = new Option("Bologna");
  Province[4] = new Option("Etc...");

  }

  if (Service == 5) {

  var Province = new Array;
  Province[0] = new Option("Iasi");
  Province[1] = new Option("Bucarest");
  Province[2] = new Option("etc...");

  }

  for (i=document.services_request.Pilot.options.length; i>0; i--) {
//  alert('ciao');
  document.services_request.Pilot.options[i] = null;
  }

  for(i=0; i<pilot.length; i++) {
  document.services_request.Pilot.options[i] = pilot[i];
  }

  document.services_request.Pilot.options[0].selected = true;

}

function isArray(obj) {
  return (obj.constructor.toString().indexOf("Array") != -1);
}

function isProvinceService(province) {

var prov_provider = new Array;
  prov_provider[0] = "jaen";
  prov_provider[1] = "arezzo";
  prov_provider[2] = "torino";
  prov_provider[3] = "iasi";
  prov_provider[4] = "sofia";
  prov_provider[5] = "oristano";
  prov_provider[6] = "reykjavik";

  var i;
  for (i in prov_provider) {
    if (prov_provider[i] == province)
      return i;
    }
    return -1;
}