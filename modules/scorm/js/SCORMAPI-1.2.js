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

var debug = true;

function scorm_API_12(scoobject, scoid) {

	this.scoobject = scoobject;
	this.scoid = scoid;

	// ------------------------------------------
	// SCORM RTE Functions - Initialization
	// ------------------------------------------
	function LMSInitialize(dummyString) {
		if (debug) {
			console.log('*** LMSInitialize ***');
		}

		// create request object
		var req = createRequest();
		// set up request parameters - uses GET method
		req.open('GET', 'ajax/doSCORM.php?method=initialize&scoobject='+this.scoobject+
				'&scoid='+this.scoid + '&code='+ Math.random(), false);
		// submit to the server for processing
		req.send(null);
		// process returned data - error condition
		if (req.status != 200) {
			console.log('Problem with doSCORM Request');
			return "false";
		} else {
			return "true";
		}
	}

	// ------------------------------------------
	// SCORM RTE Functions - Getting and Setting Values
	// ------------------------------------------
	function LMSGetValue(varname) {
		if (debug) {
			console.log('*** LMSGetValue varname=' + varname + '***');
		}

		// create request object
		var req = createRequest();
		// set up request parameters - uses GET method
		req.open('GET', 'ajax/doSCORM.php?method=getValue&scoobject='+this.scoobject+
				'&scoid='+this.scoid +'&varname=' + urlencode(varname) +
				'&code='+ Math.random(), false);
		// submit to the server for processing
		req.send(null);
		// process returned data - error condition
		if (req.status != 200) {
			console.log('Problem with doSCORM Request');
			return "";
		} else {
			return req.responseText;
		}

	}

	function LMSSetValue(varname, varvalue) {
		if (debug) {
			console.log('*** LMSSetValue varname=' + varname + ' varvalue='
					+ varvalue + ' ***');
		}

		// create request object
		var req = createRequest();
		// set up request parameters - uses GET method
		req.open('GET', 'ajax/doSCORM.php?method=setValue&scoobject='+this.scoobject+
				'&scoid='+this.scoid +'&varname=' + urlencode(varname) +
				'&varvalue='+ urlencode (varvalue) +
				'&code='+ Math.random(), false);
		// submit to the server for processing
		req.send(null);
		// process returned data - error condition
		if (req.status != 200) {
			console.log('Problem with doSCORM Request');
			return "false";
		} else {
			return "true";
		}

	}

	function LMSCommit(dummyString) {
		if (debug) {
			console.log('*** LMSCommit ***');
		}

		// create request object
		var req = createRequest();
		// set up request parameters - uses GET method
		req.open('GET', 'ajax/doSCORM.php?method=commit&scoobject='+this.scoobject+
				'&scoid='+this.scoid + '&code='+ Math.random(), false);
		// submit to the server for processing
		req.send(null);
		// process returned data - error condition
		if (req.status != 200) {
			console.log('Problem with doSCORM Request');
			return "false";
		} else {
			return "true";
		}

	}

	// ------------------------------------------
	// SCORM RTE Functions - Closing The Session
	// ------------------------------------------
	function LMSFinish(dummyString) {
		if (debug) {
			console.log('*** LMSFinish ***');
		}

		// create request object
		var req = createRequest();
		// set up request parameters - uses GET method
		req.open('GET', 'ajax/doSCORM.php?method=finish&scoobject='+this.scoobject+
				'&scoid='+this.scoid + '&code='+ Math.random(), false);
		// submit to the server for processing
		req.send(null);
		// process returned data - error condition
		if (req.status != 200) {
			console.log('Problem with doSCORM Request');
			return "false";
		} else {
			return "true";
		}
	}

	// ------------------------------------------
	// SCORM RTE Functions - Error Handling
	// ------------------------------------------
	function LMSGetLastError() {
		if (debug) {
			console.log('*** LMSGetLastError ***');
		}
		return 0;
	}

	function LMSGetDiagnostic(errorCode) {
		if (debug) {
			console.log('*** LMSGetDiagnostic errorCode=' + errorCode + ' ***');
		}
		return "diagnostic string";
	}

	function LMSGetErrorString(errorCode) {
		if (errorCode != "") {
			var errorString = new Array();
			errorString["0"] = "No error";
			errorString["101"] = "General exception";
			errorString["201"] = "Invalid argument error";
			errorString["202"] = "Element cannot have children";
			errorString["203"] = "Element not an array - cannot have count";
			errorString["301"] = "Not initialized";
			errorString["401"] = "Not implemented error";
			errorString["402"] = "Invalid set value, element is a keyword";
			errorString["403"] = "Element is read only";
			errorString["404"] = "Element is write only";
			errorString["405"] = "Incorrect data type";
			if (debug) {
				console.log('*** LMSGetErrorString errorCode=' + errorCode
						+ ' ***');
			}
			return errorString[errorCode];
		} else {
			return "";
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
	console.log('*** SCORM v1.2 RTE API loaded ***');
};
