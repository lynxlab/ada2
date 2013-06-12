/**
 *
 *
 * @package   comunica
 * @author    Vito Modena <vito@lynxlab.com>
 * @copyright Copyright (c) 2009-2011, Lynx s.r.l.
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link
 * @version   0.1
 */

/*
 * id of the div containing the addressees list
 */
var ADDRESSEES_SELECT = 'js_destinatari_sel';
/*
 * id of the divs containing the users list based on user type
 */
var SWITCHERS_SELECT     = 'js_switcher_sel';
var PRACTITIONERS_SELECT = 'js_practitioner_sel';
var USERS_SELECT         = 'js_user_sel';
/*
 * id of the buttons used to switch between the users list
 */
var SWITCHERS_BUTTON     = 'js_switcher_bt';
var PRACTITIONERS_BUTTON = 'js_practitioner_bt';
var USERS_BUTTON         = 'js_user_bt';
/*
 * used to handle switching between lists
 */
var SELECTS = new Array(SWITCHERS_SELECT, PRACTITIONERS_SELECT, USERS_SELECT);
var BUTTONS = new Array(SWITCHERS_BUTTON, PRACTITIONERS_BUTTON, USERS_BUTTON);
/*
 * class name used to hide from CSS all the selects
 */
var CSS_HIDE_CLASSNAME = 'hidden_element';

/**
 * Adds a new addressee to the addresses' list.
 *
 * @param  select
 * @return void
 */
function add_addressee(select) {
  /*
   * Addressee's username
   */
  var addressee = select.getValue();

  if(addressee == null) {
      return;
  }
  /*
   * Deselect the selected element
   */
  $(select).selectedIndex = -1;
  /*
   * If the user has already added an addressee, do not
   * add him/her again.
   */
  if($(addressee)) {
    return;
  }
  /*
   * Add a div with id equal to the addressee username containing a checkbox
   * and the username
   */
  var div = new Element('div',{'id':addressee});

  var checkbox = new Element('input',{
      'name':'destinatari[]',
      'type':'checkbox',
      'value':addressee,
      'checked':'true',
      'onclick':"remove_addressee('"+addressee+"')"});

  $(div).insert(checkbox);
  $(div).insert(addressee);

  $(ADDRESSEES_SELECT).insert(div);
}

/**
 * Removes an addressee from the addressees' list.
 *
 * @param  addressee
 * @return void
 */
function remove_addressee(addressee) {
  $(addressee).remove();
}

/**
 * Handles switching between the various selects
 * of the address book.
 *
 * @param  string control
 * @return void
 */
function showMeHideOthers(control) {
  var index = SELECTS.indexOf(control);

  var to_hide = SELECTS.without(control);

  var i = 0;
  var max = to_hide.length;
  var element;


  for (i = 0; i < max; i++) {
    element = to_hide[i];
    if($(element)) {
      $(element).hide();
    }
  }

  for(i = 0; i < BUTTONS.length; i++) {
    element = BUTTONS[i];
    if(i == index && $(element)) {
      $(element).addClassName('selected');
    }
    else if($(element) && $(element).hasClassName('selected')) {
      $(element).removeClassName('selected');
    }
  }

  if($(control).hasClassName(CSS_HIDE_CLASSNAME)) {
    $(control).removeClassName(CSS_HIDE_CLASSNAME);
  }

  if(!$(control).visible()) {
    $(control).show();
  }
}

function load_addressbook() {
  var i = 0;
  var max = SELECTS.length;
  var select;

  for (i = 0; i < max; i++) {
    select = SELECTS[i];
    button = BUTTONS[i];
    if($(select)) {
      if($(select).hasClassName(CSS_HIDE_CLASSNAME)) {
        $(select).removeClassName(CSS_HIDE_CLASSNAME);
      }
      $(select).show();
      $(button).addClassName('selected');
      break;
    }
  }
}