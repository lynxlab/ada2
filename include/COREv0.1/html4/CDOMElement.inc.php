<?php
class CDOMElement
{
    public static function create($element_name, $a_list_of_attribute_values_pairs=NULL)
    {
        $element_name = strtolower($element_name);
        
        switch($element_name)
        {
            case 'ol':
                $element = new COl();
                break;
            case 'ul':
                $element = new CUl();
                break;
            case 'li':
                $element = new CLi();
                break;
            case 'dl':
                $element = new CDl();
                break;
            case 'dt':
                $element = new CDt();
                break;
            case 'dd':
                $element = new CDd();
                break;
            case 'table':
                $element = new CTable();
                break;
            case 'caption':
                $element = new CCaption();
                break;
            case 'fieldset':
                $element = new CFieldset();
                break;
            case 'span':
                $element = new CSpan();
                break;
            case 'div':
                $element = new CDiv();
                break;
            case 'optgroup':
                $element = new COptgroup();
                break;
            case 'option':
                $element = new COption();
                break;
            case 'thead':
                $element = new CTHead();
                break;
            case 'tfoot':
                $element = new CTFoot();
                break;
            case 'tbody':
                $element = new CTBody();
                break;
            case 'colgroup':
                $element = new CColgroup();
                break;
            case 'tr':
                $element = new CTr();
                break;
            case 'td':
                $element = new CTd();
                break;
            case 'th':
                $element = new CTh();
                break;
            case 'a':
                $element = new CA();
                break;
            case 'textarea':
                $element = new CTextarea();
                break;
            case 'button':
                $element = new CButton();
                break;
            case 'select':
                $element = new CSelect();
                break;
            case 'label':
                $element = new CLabel();
                break;
           case 'legend':
                $element = new CLegend();
                break;
           case 'object':
                $element = new CTObject();
                break;
           case 'map':
                $element = new CMap();
                break;
           case 'form':
                $element = new CForm();
                break;
           case 'col':
                $element = new CCol();
                break;
           case 'link':
                $element = new CLink();
                break;
           case 'img':
                $element = new CImg();
                break;
           case 'area':
                $element = new CArea();
                break;
           case 'file':
                $element = new CFileInput();
                break;
           case 'hidden':
                $element = new CHiddenInput();
                break;
           case 'submit':
                $element = new CSubmitInput();
                break;
           case 'reset':
                $element = new CResetInput();
                break;
           case 'text':
                $element = new CInputText();
                break;
           case 'password':
                $element = new CInputPassword();
                break;
           case 'input_button':
                 $element = new CButtonInput();
                 break;
           case 'checkbox':
                $element = new CCheckbox();
                break;
           case 'radio':
                $element = new CRadio();
                break;
           case 'iframe':
                $element = new CIFrame();
                break;
            default:
                return NULL;
        }
        
        if($element instanceof CBaseElement)
        {
            $element->setAttributes($a_list_of_attribute_values_pairs);
            return $element;
        }
        
        return NULL;
        
        /* //funziona dal 5.3?
        $element_class = 'Element_'.$element_name;
        $element = new $element_class();
        $element->setAttributes($a_list_of_attribute_values_pairs);
        return $element;
		*/
    }
}
?>