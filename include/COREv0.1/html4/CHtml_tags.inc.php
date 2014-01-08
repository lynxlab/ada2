<?php

/**
 *
 * @author vito
 */
class CHtml_tags
{
    public function getTagForHtmlElement($element_class)
    {
        $core_attributes  = "%id% %class% %style% %title%";
        $i18n_attributes  = "%lang% %dir%";
        $event_attributes = "%onclick% %ondblclick% %onmousedown% %onmouseup% %onmouseover% %onmousemove% %onomuseout% %onkeypress% %onkeydown% %onkeyup%";

        $accesskey = "%accesskey%";
        $tabindex  = "%tabindex%";
        $focusable = "%onfocus% %onblur%";
        $select_element = "%disabled% %label%";
        $alignable_element = "%align% %char% %charoff% %valign%";
        $tablecell_element = "%abbr% %axis% %header% %scope% %rowspan% %colspan%";

        $table_attributes = "%summary% %width% %border% %frame% %rules% %cellspacing% %cellpadding%";


        switch($element_class)
        {
            case 'COl':
                return "<ol %start% $core_attributes $i18n_attributes $event_attributes>\n%children%\n</ol>\n";
            case 'CUl':
                return "<ul $core_attributes $i18n_attributes $event_attributes>\n%children%\n</ul>\n";
            case 'CLi':
                return "<li $core_attributes $i18n_attributes $event_attributes>\n%children%\n</li>\n";
            case 'CDl':
                return "<dl $core_attributes $i18n_attributes $event_attributes>\n%children%\n</dl>\n";
            case 'CDt':
                return "<dt $core_attributes $i18n_attributes $event_attributes>\n%children%\n</dt>\n";
            case 'CDd':
                return "<dd $core_attributes $i18n_attributes $event_attributes>\n%children%\n</dd>\n";
            case 'CTable':
                return "<table $core_attributes $i18n_attributes $event_attributes $table_attributes>\n%children%\n</table>\n";
            case 'CCaption':
                return "<caption $core_attributes $i18n_attributes $event_attributes>\n%children%\n</caption>\n";
            case 'CFieldset':
                return "<fieldset $core_attributes $i18n_attributes $event_attributes>\n%children%\n</fieldset>\n";
            case 'CSpan':
                return "<span $core_attributes $i18n_attributes $event_attributes>\n%children%\n</span>\n";
            case 'CDiv':
                return "<div $core_attributes $i18n_attributes $event_attributes>\n%children%\n</div>\n";
            case 'COptgroup':
                return "<optgroup $core_attributes $i18n_attributes $event_attributes $select_element>\n%children%\n</optgroup>\n";
            case 'COption':
                return "<option %disabled% %selected% %value% $core_attributes $i18n_attributes $event_attributes $select_element>\n%children%\n</option>\n";
            case 'CTHead':
                return "<thead $core_attributes $i18n_attributes $event_attributes $alignable_element>\n%children%\n</thead>\n";
            case 'CTFoot':
                return "<tfoot $core_attributes $i18n_attributes $event_attributes $alignable_element>\n%children%\n</tfoot>\n";
            case 'CTBody':
                return "<tbody $core_attributes $i18n_attributes $event_attributes $alignable_element>\n%children%\n</tbody>\n";
            case 'CColgroup':
                return "<colgroup %span% %width% $core_attributes $i18n_attributes $event_attributes $alignable_element>\n%children%\n</colgroup>\n";
            case 'CTr':
                return "<tr $core_attributes $i18n_attributes $event_attributes $alignable_element>\n%children%\n</tr>\n";
            case 'CTd':
                return "<td $core_attributes $i18n_attributes $event_attributes $alignable_element $tablecell_element>\n%children%\n</td>\n";
            case 'CTh':
                return "<th $core_attributes $i18n_attributes $event_attributes $alignable_element $tablecell_element>\n%children%\n</th>\n";
            case 'CA':
                return "<a %charset% %type% %name% %href% %hreflang% %rel% %rev% %shape% %coords% %target% $core_attributes $i18n_attributes $event_attributes $accesskey $tabindex>\n%children%\n</a>\n";
            case 'CTextarea':
                return "<textarea %name% %rows% %cols% %disabled% %readonly% %onselect% %onchange% $core_attributes $i18n_attributes $event_attributes $accesskey $tabindex>\n%children%\n</textarea>\n";
            case 'CButton':
                return "<button %name% %value% %type% %disabled% $core_attributes $i18n_attributes $event_attributes $accesskey $tabindex>\n%children%\n</button>\n";
            case 'CSelect':
                return "<select %name% %size% %multiple% %disabled% %onchange% $core_attributes $i18n_attributes $event_attributes $focusable_element $tabindex>\n%children%\n</select>\n";
            case 'CLabel':
                return "<label %for% $core_attributes $i18n_attributes $event_attributes $focusable_element $accesskey>\n%children%\n</label>\n";
            case 'CLegend':
                return "<legend $core_attributes $i18n_attributes $event_attributes $accesskey>\n%children%\n</legend>\n";
            case 'CTObject':
                return "<object %declare% %classid% %codebase% %data% %type% %codetype% %archive% %standby% %height% %width% %usemap% %name% $core_attributes $i18n_attributes $event_attributes $tabindex>\n%children%\n</object>\n";
            case 'CMap':
                return "<map %name% $core_attributes $i18n_attributes $event_attributes >\n%children%\n</map>\n";
            case 'CForm':
                return "<form %name% %action% %method% %enctype% %accept-charset% %accept% %onsubmit% %onreset% $core_attributes $i18n_attributes $event_attributes>\n%children%\n</form>\n";
            case 'CCol':
                return "<col %span% %width% $core_attributes $i18n_attributes $event_attributes $alignable_element>\n";
            case 'CLink':
                return "<link %charset% %type% %name% %href% %hreflang% %rel% %rev% %media% $core_attributes $i18n_attributes $event_attributes>\n";
            case 'CImg':
                return "<img %src% %alt%  %longdesc% %name% %height% %width% %usemap% %ismap% $core_attributes $i18n_attributes $event_attributes>\n";
            case 'CArea':
                return "<area %shape% %coords% %href% %nohref% %alt% $core_attributes $i18n_attributes $event_attributes>\n";
            case 'CFileInput':
            case 'CHiddenInput':
            case 'CSubmitInput':
            case 'CResetInput':
            case 'CInputText':
            case 'CInputPassword':
            case 'CButtonInput':
            case 'CCheckbox':
            case 'CRadio':
                return "<input %name% %type% %checked% %disabled% %readonly% %onselect% %size% %maxlength% %usemap% %ismap% %src% %alt% %onchange% %value% $core_attributes $i18n_attributes $event_attributes $accesskey $tabindex $focusable>\n";
            case 'CIFrame':
                return "<iframe $core_attributes %longdesc% %name% %src% %frameborder% %marginwidth% %marginheight% %noresize% %scrolling% %align% %width% %height%>\n%children%\n</iframe>\n";
            default:
                return "";
        }
    }
}
?>