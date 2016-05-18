/**
 * SCORM MODULE.
 *
 * @package        scorm module
 * @author         Giorgio Consorti <g.consorti@lynxlab.com>
 * @copyright      Copyright (c) 2016, Lynx s.r.l.
 * @license        http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link           scorm
 * @version        0.1
 */
var loadOnly = true;

// executes the callback function after the script had been loaded
// but we don't need any function to be executed here
loadScript ('js/SCORMAPI-1.2.js', function() {});

function scorm_API_13(scoobject, scoid) {

	var api12 = new scorm_API_12(scoobject, scoid);

    this.Initialize = api12.LMSInitialize;
    this.Terminate = api12.LMSFinish;
    this.GetValue = api12.LMSGetValue;
    this.SetValue = api12.LMSSetValue;
    this.Commit = api12.LMSCommit;
    this.GetLastError = api12.LMSGetLastError;
    this.GetErrorString = api12.LMSGetErrorString;
    this.GetDiagnostic = api12.LMSGetDiagnostic;
    this.version = '1.0';
	this.scoobject = scoobject;
	this.scoid = scoid;

    console.log('*** SCORM v2004 RTE API loaded ***');
} ;
