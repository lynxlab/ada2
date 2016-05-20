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

// executes the callback function after the script had been loaded
// but we don't need any function to be executed here
loadScript ('js/SCORMAPI-1.2.js', function() {});

function scorm_API_13(options) {

	var api12 = new scorm_API_12(options);

    this.Initialize = api12.LMSInitialize;
    this.Terminate = api12.LMSFinish;
    this.GetValue = api12.LMSGetValue;
    this.SetValue = api12.LMSSetValue;
    this.Commit = api12.LMSCommit;
    this.GetLastError = api12.LMSGetLastError;
    this.GetErrorString = api12.LMSGetErrorString;
    this.GetDiagnostic = api12.LMSGetDiagnostic;
    this.version = '1.0';

	this.isAPI12 = api12.isAPI12;
	this.isAPI13 = api12.isAPI13;

	this.scoobject = api12.scoobject;
	this.scoid = api12.scoid;
	this.datafromlms = api12.datafromlms;
	this.masteryscore = api12.masteryscore;

    if (this.isAPI13 && debug) console.log('*** SCORM v2004 RTE API loaded ***');
} ;
