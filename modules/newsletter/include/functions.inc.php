<?php
/**
 * NEWSLETTER MODULE.
 *
 * @package		newsletter module
 * @author			giorgio <g.consorti@lynxlab.com>
 * @copyright		Copyright (c) 2009, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link			newsletter
 * @version		0.1
 */
function convertFilterArrayToString ($filterArray, $dh, $futureSentence = true)
{
	$html = translateFN('La newsletter').' ';

	$html .= ($futureSentence) ? translateFN ("sar&agrave;") : translateFN("&egrave; stata");
	$html .= ' '.translateFN('inviata a');
	
	$idNewsletter = intval ($filterArray['id']);

	if (isset ($filterArray['userType']) && $filterArray['userType']>0)
	{
		if ($filterArray['userType']!=AMA_TYPE_STUDENT)
		{
			$html .= " ".translateFN("tutti")." ";
			if 	($filterArray['userType']==AMA_TYPE_AUTHOR) $html .= translateFN("gli")." ".translateFN("autori");
			else if ($filterArray['userType']==AMA_TYPE_SWITCHER) $html .= translateFN("gli")." ".translateFN("switcher");
			else if ($filterArray['userType']==AMA_TYPE_TUTOR) $html .= translateFN("i")." ".translateFN("tutor");
			else if ($filterArray['userType']==9999) $html .= translateFN("gli")." ".translateFN("utenti");
		}
		else
		{
			if ( !((isset($filterArray['userPlatformStatus']) &&  $filterArray['userPlatformStatus']!=-1) ||
					(isset($filterArray['userCourseStatus']) && $filterArray['userCourseStatus']!=-1)) )
			{
				$html .= " ".translateFN("tutti")." ";
			}
			$html .= translateFN("gli")." ".translateFN("studenti");


			if ( (isset($filterArray['userPlatformStatus']) &&  $filterArray['userPlatformStatus']!=-1) ||
			(isset($filterArray['userCourseStatus']) && $filterArray['userCourseStatus']!=-1) )
			{
				$html .= " ".translateFN("con");

				if (isset($filterArray['userPlatformStatus']) &&  $filterArray['userPlatformStatus']!=-1)
				{
					$html .= " ".translateFN("stato nella piattaforma").": <strong>";
					if ($filterArray['userPlatformStatus']==ADA_STATUS_PRESUBSCRIBED) $html .= translateFN("Non Confermato");
					else if ($filterArray['userPlatformStatus']==ADA_STATUS_REGISTERED) $html .= translateFN("Confermato");
					$html .= "</strong>";

					if (isset($filterArray['userCourseStatus']) && $filterArray['userCourseStatus']!=-1) $html .= " ".translateFN("e")." ";
				}

				if (isset($filterArray['userCourseStatus']) && $filterArray['userCourseStatus']!=-1)
				{
					$html .= " ".translateFN("stato").": <strong>";
					if ($filterArray['userCourseStatus']==ADA_SERVICE_SUBSCRIPTION_STATUS_UNDEFINED) $html .= translateFN('In visita');
					else if ($filterArray['userCourseStatus']==ADA_SERVICE_SUBSCRIPTION_STATUS_REQUESTED) $html .= translateFN('Preiscritto');
					else if ($filterArray['userCourseStatus']==ADA_SERVICE_SUBSCRIPTION_STATUS_ACCEPTED) $html .= translateFN('Iscritto');
					else if ($filterArray['userCourseStatus']==ADA_SERVICE_SUBSCRIPTION_STATUS_SUSPENDED) $html .= translateFN('Rimosso');
					else if ($filterArray['userCourseStatus']==ADA_SERVICE_SUBSCRIPTION_STATUS_COMPLETED) $html .=  translateFN('Completato');
					$html .= "</strong>";
				}
			}
		}

		if (isset($filterArray['idInstance']) && intval($filterArray['idInstance'])>0 )
		{
			$html .= " ".translateFN("dell' istanza").': <strong>';
			$instanceInfo = $dh->course_instance_get (intval ($filterArray['idInstance']));
			$html .= '<instancename></strong>';
		}
		else
		{
			$html .= " ".translateFN("di tutte le istanze");
		}

		if (isset($filterArray['idCourse']) && intval($filterArray['idCourse'])>0 )
		{
			$html .= " ".translateFN("del corso").': <strong>';
			$courseInfo = $dh->get_course(intval($filterArray['idCourse']));
			$html .= '<coursename></strong>';
		}
		else
		{
			$html .= " ".translateFN("di tutti i corsi");
		}
			
		$html = ucfirst (strtolower ($html)).'.';
		$html = str_replace('<instancename>', $instanceInfo['title'], $html);
		$html = str_replace('<coursename>', '('.$filterArray['idCourse'].')'.' '.$courseInfo['nome'].'-'.$courseInfo['titolo'], $html);			
	}
	else
	{
		$html = translateFN(DEFAULT_FILTER_SENTENCE);
	}

	return  $html;
}

function get_domain($url)
{
	$pieces = parse_url($url);
	$domain = isset($pieces['host']) ? $pieces['host'] : '';
	if (preg_match('/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', $domain, $regs)) {
		return $regs['domain'];
	}
	return '';
}
?>