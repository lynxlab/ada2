/**
 * SCORM MODULE.
 *
 * @package scorm module
 * @author Giorgio Consorti <g.consorti@lynxlab.com>
 * @copyright Copyright (c) 2016, Lynx s.r.l.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link scorm
 * @version 0.1
 */

/*
 *
 * VS SCORM - RTE API FOR SCORM 1.2 Rev 1.0 - Sunday, May 31, 2009 Copyright (C)
 * 2009, Addison Robson LLC
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the License, or (at your option) any later
 * version.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE. See the GNU General Public License for more
 * details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, write to the Free Software Foundation, Inc., 51
 * Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 *
 */

var debug = false;
var latestCommitTS = 0;
var hasFinished = false;
var hasInitialized = false;
var errorCode = '0';
var cache = new Object();

var supportedElements = new Object();

function isSupported(element) {
	return element in supportedElements;
}

function isReadable(element) {
	return isSupported(element) && (supportedElements[element].indexOf("R")!=-1);
}

function isWritable(element) {
	return isSupported(element) && (supportedElements[element].indexOf("W")!=-1);
}

function scorm_API_12(options) {

	this.scoobject = options.SCOObject;
	this.scoid = options.SCOid;

	if ('undefined' != typeof options.datafromlms)  this.datafromlms = options.datafromlms;
	else this.datafromlms = null;
	if ('undefined' != typeof options.masteryscore) this.masteryscore = options.masteryscore;
	else this.masteryscore = null;

	// ------------------------------------------
	// SCORM RTE Functions - Initialization
	// ------------------------------------------
	function LMSInitialize(dummyString) {

		if ((hasInitialized) || (hasFinished)) {
			if (this.isAPI13) errorCode = (hasFinished ? '104' : '103');
			return "false";
		}

		if (debug) {
			console.log('*** LMSInitialize ***');
			console.log('*** API v1.2: '+this.isAPI12+' API v1.3: '+this.isAPI13+' ***')
		}

		// create request object
		var req = createRequest();
		// set up request parameters - uses GET method
		req.open('GET', 'ajax/doSCORM.php?action=initialize&scoobject='
				+ urlencode(this.scoobject) + '&scoid=' + urlencode(this.scoid) +
				'&datafromlms=' + (this.datafromlms != null ? urlencode(this.datafromlms) : '')  +
				'&apiversion=' + (this.isAPI12 ? '1.2' : '2004') +
				'&ts=' + Math.round(new Date().getTime()/1000), false);
		// submit to the server for processing
		req.send(null);
		// process returned data - error condition
		hasInitialized = true;
		if (req.status != 200) {
			console.log('Problem with doSCORM Request');
			return "false";
		} else {
			return req.responseText;
		}
	}

	// ------------------------------------------
	// SCORM RTE Functions - Getting and Setting Values
	// ------------------------------------------
	function LMSGetValue(varname) {

		if (debug) {
			console.log('*** LMSGetValue varname=' + varname + ' ***');
		}

		if ((!hasInitialized) || (hasFinished)) {
			if (debug) {
				console.log('*** LMSGetValue notInitialized or Finished ***');
			}
			if (this.isAPI12) errorCode = '301';
			else if (this.isAPI13) errorCode = (hasFinished ? '123' : '122');
			return '';
		}

		if (!isSupported(varname)) {
			if (debug) {
				console.log('*** LMSGetValue varname=' + varname + ' is NOT supported ***');
			}
			errorCode = '401';
			return '';
		}

		if (!isReadable(varname)) {
			if (debug) {
				console.log('*** LMSGetValue varname=' + varname + ' is NOT readable ***');
			}
			errorCode = '404';
			return '';
		}

		var varvalue = '';
		if ('undefined' === typeof cache[varname]) {
			// create request object
			var req = createRequest();
			// set up request parameters - uses GET method
			req.open('GET', 'ajax/doSCORM.php?action=getValue&scoobject='
					+ urlencode(this.scoobject) + '&scoid=' + urlencode(this.scoid) + '&varname='
					+ urlencode(varname) + '&ts=' + Math.round(new Date().getTime()/1000), false);
			// submit to the server for processing
			req.send(null);
			// process returned data - error condition
			if (req.status != 200) {
				errorCode = '101';
				console.log('Problem with doSCORM Request');
			} else {
				errorCode = '0';
				cache[varname] = req.responseText;
				varvalue = cache[varname];
			}
		} else {
			errorCode = '0';
			varvalue = cache[varname];
		}

		return varvalue;
	}

	function LMSSetValue(varname, varvalue, forceRequest) {

		forceRequest = forceRequest || false;

		if (debug) {
			console.log('*** LMSSetValue varname=' + varname + ' varvalue='
					+ varvalue + ' forceRequest='+forceRequest+' ***');
		}

		if ((!hasInitialized) || (hasFinished)) {
			if (debug) {
				console.log('*** LMSSetValue notInitialized or Finished ***');
			}
			if (this.isAPI12) errorCode = '301';
			else if (this.isAPI13) errorCode = (hasFinished ? '133' : '132');
			return 'false';
		}

		if (!isSupported(varname)) {
			if (debug) {
				console.log('*** LMSSetValue varname=' + varname + ' is NOT supported ***');
			}
			errorCode = '401';
			return 'false';
		}

		if (!isWritable(varname)) {
			if (debug) {
				console.log('*** LMSSetValue varname=' + varname + ' is NOT writable ***');
			}
			errorCode = '403';
			return 'false';
		}

		var retval = "false";
		if (forceRequest) {
			// create request object
			var req = createRequest();
			// set up request parameters - uses GET method
			req.open('POST', 'ajax/doSCORM.php', false);
			var postParams = 'action=setValue&scoobject=' + urlencode(this.scoobject)
			+ '&scoid=' + urlencode(this.scoid) + '&varname=' + urlencode(varname)
			+ '&varvalue=' + urlencode(varvalue) + '&ts='
			+ ((latestCommitTS>0) ? latestCommitTS : Math.round(new Date().getTime()/1000));

			req.setRequestHeader("Content-type",
			"application/x-www-form-urlencoded");
			// submit to the server for processing
			req.send(postParams);

			// process returned data - error condition
			if (req.status != 200) {
				errorCode = '101';
				console.log('Problem with doSCORM Request');
			} else {
				return req.responseText;
			}
		} else {
			errorCode = '0';
			cache[varname] = varvalue;
			retval = 'true';
		}

		return retval;
	}

	function LMSCommit(dummyString) {

		if ((!hasInitialized) || (hasFinished)) {
			if (this.isAPI13) errorCode = (hasFinished ? '143' : '142');
			return "false";
		}

		if (debug) {
			console.log('*** LMSCommit '+dummyString+' ***');
		}

		currentTS = Math.round(new Date().getTime()/1000);
		// prevents double commits having same timestamps
		if (latestCommitTS < currentTS) {
			if (debug) {
				console.log (latestCommitTS + ' < ' + currentTS + ' committing to the DB');
			}

			latestCommitTS = currentTS;
			// Save all cache values
			for (key in cache) {
				if (cache.hasOwnProperty(key)) {
					if (this.isAPI12) this.LMSSetValue(key, cache[key], true);
					else if (this.isAPI13) this.SetValue(key, cache[key], true);
				}
			}

			// create request object
			var req = createRequest();
			// set up request parameters - uses GET method
			req.open('GET', 'ajax/doSCORM.php?action=commit&scoobject='
					+ urlencode(this.scoobject) + '&scoid=' + urlencode(this.scoid) + '&ts='
					+ latestCommitTS, false);
			// submit to the server for processing
			req.send(null);
			// process returned data - error condition
			if (req.status != 200) {
				console.log('Problem with doSCORM Request');
				return "false";
			} else {
				return "true";
			}
		} else {
			return "true";
		}
	}

	// ------------------------------------------
	// SCORM RTE Functions - Closing The Session
	// ------------------------------------------
	function LMSFinish(dummyString) {

		if ((!hasInitialized) || (hasFinished)) return "true";


		if (debug) {
			console.log('*** LMSFinish '+dummyString+' ***');
		}

		if (this.isAPI12) this.LMSCommit('from LMSFinish');
		else if (this.isAPI13) this.Commit('from LMSFinish');

		// create request object
		var req = createRequest();
		// set up request parameters - uses GET method
		req.open('GET', 'ajax/doSCORM.php?action=finish&scoobject='
				+ urlencode(this.scoobject) + '&scoid=' + urlencode(this.scoid) +
				'&masteryscore=' + (this.masteryscore != '0' ? urlencode(this.masteryscore) : '')  +
				'&apiversion=' + (this.isAPI12 ? '1.2' : '2004') +
				'&ts=' + Math.round(new Date().getTime()/1000), false);
		// submit to the server for processing
		req.send(null);
		// process returned data - error condition
		hasFinished = true;
		if (req.status != 200) {
			console.log('Problem with doSCORM Request');
			return "false";
		} else {
			return req.responseText;
		}
	}

	// ------------------------------------------
	// SCORM RTE Functions - Error Handling
	// ------------------------------------------
	function LMSGetLastError() {
		if (debug) {
			console.log('*** LMSGetLastError ***');
		}
		return errorCode;
	}

	function LMSGetDiagnostic(errorCode) {
		if (debug) {
			console.log('*** LMSGetDiagnostic errorCode=' + errorCode + ' ***');
		}
		if (this.isAPI12) return this.LMSGetErrorString(errorCode);
		else if (this.isAPI13) return this.GetErrorString(errorCode);

	}

	function LMSGetErrorString(errorCode) {
		if (errorCode != "") {
			var errorMessages = new Object();
			errorMessages['0'] = 'No Error';
			errorMessages['101'] = 'General Exception';
			// API13
			errorMessages['103'] = 'Call to Initialize failed because Initialize was already called';
		    errorMessages['104'] = 'Call to Initialize failed because Terminate was already called';
			errorMessages['122'] = 'Call to GetValue failed because it was made before the call to Initialize';
			errorMessages['123'] = 'Call to GetValue failed because it was made after the call to Terminate';
			errorMessages['132'] = 'Call to SetValue failed because it was made before the call to Initialize';
			errorMessages['133'] = 'Call to SetValue failed because it was made after the call to Terminate';
			errorMessages['142'] = 'Call to Commit failed because it was made before the call to Initialize';
			errorMessages['143'] = 'Call to Commit failed because it was made after the call to Terminate';
			// end API13
			errorMessages['201'] = 'Invalid Argument';
			errorMessages['202'] = 'Element Cannot Have Children';
			errorMessages['203'] = 'Element Not an Array - Cannot Have Children';
			errorMessages['301'] = 'API Not Initialized';
			errorMessages['401'] = 'Data Model Element Not Implemented';
			errorMessages['402'] = 'Invalid Set Value - Element is a Keyword';
			errorMessages['403'] = 'Invalid Set Value - Element is Read Only';
			errorMessages['404'] = 'Invalid Get Value - Element is Write Only';
			errorMessages['405'] = 'Invalid Set Value - Incorrect Data Type';
			if (debug) {
				console.log('*** LMSGetErrorString errorCode=' + errorCode
						+ ' ***');
			}
			return errorMessages[errorCode];
		} else {
			return "";
		}
	}

	function setSupportedElements(that) {

		// common supported elements
		supportedElements['cmi.suspend_data'] = 'RW';
		supportedElements['cmi.launch_data'] = 'RO';
		if (that.isAPI12) {
			if (debug) console.log ('### enabling supported elements for v1.2 ###');
			supportedElements['cmi.core._children'] = 'RO';
			supportedElements['cmi.core.student_id'] = 'RO';
			supportedElements['cmi.core.student_name'] = 'RO';
			supportedElements['cmi.core.lesson_location'] = 'RW';
			supportedElements['cmi.core.credit'] = 'RO';
			supportedElements['cmi.core.lesson_status'] = 'RW';
			supportedElements['cmi.core.entry'] = 'RO';
			supportedElements['cmi.core.exit'] = 'WO';
			supportedElements['cmi.core.score._children'] = 'RO';
			supportedElements['cmi.core.score.raw'] = 'RW';
			supportedElements['cmi.core.score.max'] = 'RW';
			supportedElements['cmi.core.score.min'] = 'RW';
			supportedElements['cmi.core.total_time'] = 'RO';
			supportedElements['cmi.core.session_time'] = 'WO';
		} else if (that.isAPI13) {
			if (debug) console.log ('### enabling supported elements for v1.3 ###');
			supportedElements['cmi.location'] = 'RW';
			supportedElements['cmi.credit'] = 'RO';
			supportedElements['cmi.completion_status'] = 'RW';
			supportedElements['cmi.entry'] = 'RO';
			supportedElements['cmi.exit'] = 'WO';
			supportedElements['cmi.score._children'] = 'RO';
			supportedElements['cmi.score.raw'] = 'RW';
			supportedElements['cmi.score.max'] = 'RW';
			supportedElements['cmi.score.min'] = 'RW';
			supportedElements['cmi.total_time'] = 'RO';
			supportedElements['cmi.session_time'] = 'WO';
		}
	}

	this.LMSInitialize = LMSInitialize;
	this.LMSFinish = LMSFinish;
	this.LMSGetValue = LMSGetValue;
	this.LMSSetValue = LMSSetValue;
	this.LMSCommit = LMSCommit;
	this.LMSGetLastError = LMSGetLastError;
	this.LMSGetErrorString = LMSGetErrorString;
	this.LMSGetDiagnostic = LMSGetDiagnostic;
	this.isAPI12 = options.SCOversion=='1.2';
	this.isAPI13 = !this.isAPI12;

	setSupportedElements(this);

	if (this.isAPI12 && debug) console.log('*** SCORM v1.2 RTE API loaded ***');
};
