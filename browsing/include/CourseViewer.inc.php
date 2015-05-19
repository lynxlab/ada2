<?php
/**
 * class CourseViewer
 *
 * @author vito
 */
class CourseViewer
{
  /**
   * function displayInternalLinkSelector: used to display a course index menu
   * with a specified javascript callback associated to each node.
   *
   * @param integer $id_course
   * @param array $callback_params
   * @return string - the html code for the generated course index
   */
  public static function displayInternalLinkSelector($id_course, $callback_params=array()) {
    $dh = $GLOBALS['dh'];
    if (isset($callback_params['id_edited_node'])) {
        $info_node = $dh->get_node_info($callback_params['id_edited_node']);
        if (!AMA_DataHandler::isError($info_node)) {
            if ($info_node['type'] == ADA_GROUP_WORD_TYPE || $info_node['type'] == ADA_LEAF_WORD_TYPE) {
                $course_data = $dh->get_glossary_data($id_course);
            } else {
                $course_data = $dh->get_course_data($id_course);
            }
        } else {
            return new CText('');
        }

    }else {
        $course_data = $dh->get_course_data($id_course);
    }
    if (AMA_DataHandler::isError($course_data)) {
      return $course_data;
    }

    $id_toc = $id_course.'_'.ADA_DEFAULT_NODE;

    $link_selector = CDOMElement::create('div');
    $link_selector->addChild(self::struct($course_data, $id_toc, 0, 'internalLinkSelector', $callback_params));

    return $link_selector;
  }

  /**
   * function displayMainIndex: used to display the main index for an ADA course instance.
   *
   * @param  object  $userObj
   * @param  integer $id_course
   * @param  integer $expand_index
   * @param  string  $order
   * @param  integer $id_course_instance
   * @param  string  $container_div_name
   * @return string  -
   */
  public static function displayMainIndex($userObj, $id_course, $expand_index, $order, $id_course_instance=NULL, $container_div_name=NULL){
    $dh = $GLOBALS['dh'];

    // vito, 3 ottobre 2008
    $container_div = "";
    if($container_div_name != NULL) {
      $container_div = $container_div_name;
    }
    else {
      $container_div = 'id_main_index';
    }

    $callback_params = array('container_div'=>$container_div);

    $order_by_name = FALSE;
    if ( $order == 'alfa' ) {
      $order_by_name = TRUE;
    }

    /*
     * Necessario per gestire il caso di visualizzazione dell'indice quando si
     * naviga un corso pubblico: in questo caso non è presente una istanza corso,
     * quindi dobbiamo comportarci  come se l'utente fosse un autore.
     */
    if(is_null($id_course_instance)) {
      $user_type = AMA_TYPE_AUTHOR;
    }
    else {
      $user_type = $userObj->tipo;
    }

    switch($user_type) {// sarebbe meglio $userObj->getType()
      case AMA_TYPE_AUTHOR:
      case AMA_TYPE_VISITOR:
        $callback = 'authorCallback';
        $course_data = $dh->get_course_data($id_course, 1, $order_by_name);
        break;

      case AMA_TYPE_TUTOR:
        $callback    = 'tutorCallback';
        $course_data = $dh->get_course_data($id_course, 2, $order_by_name, $id_course_instance);
        $callback_params['id_course_instance'] = $id_course_instance;
        $callback_params['user_id'] = $userObj->id_user;
        break;

      case AMA_TYPE_STUDENT:
        $callback    = 'studentCallback';
        $course_data = $dh->get_course_data($id_course, 3, $order_by_name, $id_course_instance, $userObj->id_user, $userObj->livello); //sarebbe meglio $userObj->getId()
        $callback_params['user_level'] = $userObj->livello;
        $callback_params['user_id'] = $userObj->id_user;
        break;
    }


    if (AMA_DataHandler::isError($course_data)) {
      return $course_data;
    }
    else { //retrieving data about nodes' visits
		//first: retrieve visits data about all nodes of selected course and arrange them into an associative array
		$someone_there_data = array();
		$tmp_someone_there_data = ADALoggableUser::is_someone_there_courseFN($id_course_instance);
		if (!empty($tmp_someone_there_data)) {
			foreach($tmp_someone_there_data as $v) {
				$someone_there_data[$v['id_nodo']][] = $v;
			}
		}
		unset($tmp_someone_there_data); //free memory

		foreach($course_data as $k=>$v) //foreach node...
		{
			//let's check if there are records for the node
			if (isset($someone_there_data[$v['id_nodo']])) {
				$course_data[$k]['is_someone_there'] = (count($someone_there_data[$v['id_nodo']])>=1);
			}
			else {
				$course_data[$k]['is_someone_there'] = false;
			}
		}
		unset($someone_there_data); //free memory
	}

    $id_toc = $id_course.'_'.ADA_DEFAULT_NODE;

    $index = CDOMElement::create('div', "id:$container_div");

    if ( $order == 'alfa' ) {
      $index->addChild(self::ordered($course_data, $callback, $callback_params,$id_toc));
    }
    else {
      $index->addChild(self::struct($course_data, $id_toc, $expand_index, $callback, $callback_params));
    }

    return $index;
  }

  /**
   * function displayGlossaryIndex: used to display the glossary index for an ADA course.
   *
   * @param  object  $userObj
   * @param  integer $id_course
   * @param  integer $expand_index
   * @param  string  $order
   * @param  integer $id_course_instance
   * @param  string  $container_div_name
   * @return string  -
   */
  function displayGlossaryIndex($userObj, $id_course, $expand_index, $order, $id_course_instance=NULL, $container_div_name=NULL){
    $dh = $GLOBALS['dh'];

    // vito, 3 ottobre 2008
    $container_div = "";
    if($container_div_name != NULL) {
      $container_div = $container_div_name;
    }
    else {
      $container_div = 'id_main_index';
    }

    $callback_params = array('container_div'=>$container_div);

    $order_by_name = FALSE;
    if ( $order == 'alfa' ) {
      $order_by_name = TRUE;
    }

    /*
     * Necessario per gestire il caso di visualizzazione dell'indice quando si
     * naviga un corso pubblico: in questo caso non è presente una istanza corso,
     * quindi dobbiamo comportarci  come se l'utente fosse un autore.
     */
    if(is_null($id_course_instance)) {
      $user_type = AMA_TYPE_AUTHOR;
    }
    else {
      $user_type = $userObj->tipo;
    }

    switch($user_type) {// sarebbe meglio $userObj->getType()
      case AMA_TYPE_AUTHOR:
      case AMA_TYPE_VISITOR:
        $callback = 'authorCallback';
        $course_data = $dh->get_glossary_data($id_course, 1, $order_by_name);
        break;

      case AMA_TYPE_TUTOR:
        $callback    = 'tutorCallback';
        $course_data = $dh->get_glossary_data($id_course, 2, $order_by_name, $id_course_instance);
        $callback_params['id_course_instance'] = $id_course_instance;
        $callback_params['user_id'] = $userObj->id_user;
        break;

      case AMA_TYPE_STUDENT:
        $callback    = 'studentCallback';
        $course_data = $dh->get_glossary_data($id_course, 3, $order_by_name, $id_course_instance, $userObj->id_user); //sarebbe meglio $userObj->getId()
        $callback_params['user_level'] = $userObj->livello;
        $callback_params['user_id'] = $userObj->id_user;
        break;
    }


    if (AMA_DataHandler::isError($course_data)) {
      return $course_data;
    }

    $id_toc = $id_course.'_'.ADA_DEFAULT_NODE;

    $index = CDOMElement::create('div', "id:$container_div");

    if ( $order == 'alfa' ) {
      $index->addChild(self::ordered($course_data, $callback, $callback_params,$id_toc));
    }
    else {
      $index->addChild(self::struct($course_data, $id_toc, $expand_index, $callback, $callback_params));
    }

    return $index;
  }


  /**
   * function displayForumIndex
   *
   * @param  object  $userObj
   * @param  integer $id_course
   * @param  integer $expand_index
   * @param  string  $order
   * @param  integer $id_course_instance
   * @param  string  $container_div_name
   * @return string  -
   */
  public static function displayForumIndex($userObj, $id_course, $expand_index, $order, $id_course_instance, $with_icons=NULL, $container_div_name=NULL) {
    $dh = $GLOBALS['dh'];
    $show_visits   = !$GLOBALS['hide_visits'];
    /**
     *
     */
    if (!isset($id_course_instance)) {
      //return "";
      return new CText('');
    }
    // vito, 3 ottobre 2008
    $container_div = "";
    if($container_div_name != NULL) {
      $container_div = $container_div_name;
    }
    else {
      $container_div = 'id_forum_index';
    }

    $show_icons = FALSE;
    if ($with_icons) {
      $show_icons = TRUE;
    }

    $callback_params = array('container_div'=>$container_div);

	if ($order == 'chrono' || $order == 'struct') {
		$order_by_date = TRUE;
	}
	else {
		$order_by_date = FALSE;
	}

    /*
     * Get tutor id for this course instance
     */
    $class_tutor_id = $dh->course_instance_tutor_get($id_course_instance);
    if (AMA_DataHandler::isError($class_tutor_id)) {
      return $class_tutor_id;
    }

	$callback_params['user_id'] = $userObj->getId();
	$callback_params['class_tutors_ids'] = $class_tutor_id;
	$callback_params['show_icons'] = $show_icons;
	$forum_data = $dh->get_notes_for_this_course_instance($id_course_instance, $userObj->id_user, $order_by_date, $show_visits);
    switch( $userObj->tipo ) {// sarebbe meglio $userObj->getType()
      case AMA_TYPE_AUTHOR:
        $callback = 'forumAuthorCallback';
        break;

      case AMA_TYPE_TUTOR:
        $callback    = 'forumTutorCallback';
	$callback_params['id_course_instance'] = $id_course_instance;
        break;

      case AMA_TYPE_STUDENT:
        $callback    = 'forumStudentCallback';
	$callback_params['id_course_instance'] = $id_course_instance;
        break;
    }

    if (AMA_DataHandler::isError($forum_data)) {
      return $forum_data;
    }
    else { //retrieving data about nodes' visits
		//first: retrieve visits data about all nodes of selected course and arrange them into an associative array
		$someone_there_data = array();
		$tmp_someone_there_data = ADALoggableUser::is_someone_there_courseFN($id_course_instance);
		if (!empty($tmp_someone_there_data)) {
			foreach($tmp_someone_there_data as $v) {
				$someone_there_data[$v['id_nodo']][] = $v;
			}
		}
		unset($tmp_someone_there_data); //free memory

		foreach($forum_data as $k=>$v) //foreach node...
		{
			//let's check if there are records for the node
			if (isset($someone_there_data[$v['id_nodo']])) {
				$forum_data[$k]['is_someone_there'] = (count($someone_there_data[$v['id_nodo']])>=1);
			}
			else {
				$forum_data[$k]['is_someone_there'] = false;
			}
		}
		unset($someone_there_data); //free memory
	}
    /*
     * Attach the subtrees containing notes to the root node for this course instance.
     * This is required in order to display the index.
     */
    $forum_root_node = $id_course.'_'.ADA_DEFAULT_NODE;
    $notes_parent_nodes = array();

    // First, save all the parent ids for the notes in the forum
    foreach($forum_data as $note) {
      $notes_parent_nodes[$note['id_nodo']] = TRUE;
    }
    // Then, if a note has a parent id which is not a note, attach it to
    // forum_root_node
    for ($i = 0; $i < count($forum_data); $i++) {
      if(!isset($notes_parent_nodes[$forum_data[$i]['id_nodo_parent']])) {
        $forum_data[$i]['id_nodo_parent'] = $forum_root_node;
      }
    }

    $index = CDOMElement::create('div', "id:$container_div");

    if ($order == 'chrono') {
      $index->addChild(self::ordered($forum_data, $callback, $callback_params, $forum_root_node));
    }
    else {
      $index->addChild(self::struct($forum_data, $forum_root_node, $expand_index, $callback, $callback_params));
    }
    return $index;
  }

  /* ***************
   *
   * PRIVATE METHODS
   *
   * ***************
   */

  /**
   * function ordered
   *
   * @param unknown_type $course_data
   * @param unknown_type $callback
   * @param unknown_type $callback_params
   * @return unknown
   */
  public static function ordered($course_data, $callback, $callback_params=array(),$id_toc) {
    $list = CDOMElement::create('ul');
    /*
     * Ottiene le informazioni sul nodo principale
     */
    $dh = $GLOBALS['dh'];
    $node_info = $dh->get_node_info($id_toc);
    if(!AMA_DataHandler::isError($node_info)) {
      $principale = array('id_nodo' => $id_toc, 'id_nodo_parent' => $id_toc, 'nome' => $node_info['name']/*translateFN('Principale')*/, 'tipo' => ADA_GROUP_TYPE, 'icona'=> $node_info['icon']/*'group.png'*/, 'livello'=>$node_info['level']);
    }
    else {
      $principale = array('id_nodo' => $id_toc, 'id_nodo_parent' => $id_toc, 'nome' => translateFN('Principale'), 'tipo' => ADA_GROUP_TYPE, 'icona'=> 'group.png', 'livello'=>0);
    }

    if (($r = self::$callback(array('node'=>$principale, 'show_hide_span' => FALSE), $callback_params)) != NULL) {
        $list_element = CDOMElement::create('li','class:courseNode');
        $list_element->addChild($r);
        $list->addChild($list_element);
    }
    foreach ($course_data as $course_node) {
      if (($r = self::$callback(array('node'=>$course_node, 'show_hide_span' => FALSE), $callback_params)) != NULL) {
        $list_element = CDOMElement::create('li','class:courseNode');
        $list_element->addChild($r);
        $list->addChild($list_element);
      }
    }
    return $list;
  }

  /**
   * function struct:
   *
   * @param unknown_type $course_data
   * @param unknown_type $id_toc
   * @param unknown_type $expand_index
   * @param unknown_type $callback
   * @param unknown_type $callback_params
   * @return unknown
   */
  public static function struct($course_data, $id_toc, $expand_index, $callback, $callback_params=array()) {

    $lda = self::buildLda($course_data);
    $s   = array();
    $list = array();

    /*
     * Ottiene le informazioni sul nodo principale
     */
    $dh = $GLOBALS['dh'];
    $node_info = $dh->get_node_info($id_toc);
    if(!AMA_DataHandler::isError($node_info)) {
      $principale = array('id_nodo' => $id_toc, 'id_nodo_parent' => $id_toc, 'nome' => $node_info['name']/*translateFN('Principale')*/, 'tipo' => ADA_GROUP_TYPE, 'icona'=> $node_info['icon']/*'group.png'*/,'root'=>true, 'livello'=>$node_info['level']);
    }
    else {
      $principale = array('id_nodo' => $id_toc, 'id_nodo_parent' => $id_toc, 'nome' => translateFN('Principale'), 'tipo' => ADA_GROUP_TYPE, 'icona'=> 'group.png','root'=>true, 'livello'=>0);
    }
      // vito 13 gennaio 2009
    if (isset($lda[$id_toc]) && count($lda[$id_toc]) > 0 ) {
      $show_hide_span = TRUE;
    }
    else {
      $show_hide_span = FALSE;
    }
    $r = self::$callback(array('node' => $principale, 'level'=>sizeof($s), 'expand_index'=>$expand_index, 'show_hide_span' => $show_hide_span), $callback_params);
    $ul = CDOMElement::create('ul');
    //vito 16 gennaio 2009
    $li = CDOMElement::create('li','class:courseNode');
    $li->addChild($r);
    array_push($list,$ul);
    array_push($list,$li);

//    $ul_1 = CDOMElement::create('ul', 'id:root');
    $ul_1 = CDOMElement::create('ul', "id:{$principale['id_nodo']}");
    $ul_1->setAttribute('class', $callback_params['container_div']);
    if ($expand_index == 0) {
      $ul_1->setAttribute('style', 'display: none');
    }

    array_push($list, $ul_1);
    array_push($s,$id_toc);

    while (!empty($s)) {
      $top_node = end($s);

      if (empty($lda[$top_node])) {
        array_pop($s);

        /*
         * ci sono sempre almeno 3 elementi nella pila
         */
        $current_ul = array_pop($list);
        $current_li = array_pop($list);

        $current_li->addChild($current_ul);

        $parent_ul = array_pop($list);
        $parent_ul->addChild($current_li);
        /*
         * se nella pila non ci sono altri elementi, allora
         * ho terminato di visitare l'albero, quindi restituisco
         * l'oggetto CORE ul
         */
        if (count($list) == 0) {
          return $parent_ul;
        }
        else {
          array_push($list,$parent_ul);
        }
      }
      else {
        $nodo = array_shift($lda[$top_node]);
        $level = sizeof($s);

        // vito 13 gennaio
        if(isset($lda[$nodo['id_nodo']]) && count($lda[$nodo['id_nodo']]) > 0) {
          $show_hide_span = TRUE;
        }
        else {
          $show_hide_span = FALSE;
        }

        if (($r = self::$callback(array('node'=>$nodo, 'level'=> $level, 'expand_index' => $expand_index, 'show_hide_span' => $show_hide_span), $callback_params)) != NULL) {
          // vito 16 gennaio 2009
          $li = CDOMElement::create('li','class:courseNode');
          $li->addChild($r);
          array_push($list, $li);

          $ul = CDOMElement::create('ul', 'id:'.$nodo['id_nodo'].', class:'.$callback_params['container_div']);

          if ($level >= $expand_index) {
           $ul->setAttribute('style','display: none');
          }
        }
        array_push($s, $nodo['id_nodo']);

        array_push($list, $ul);
      }
    }
    return $html;
  }

  /**
   * function buildLda: used to build an adjacency list for the course nodes passed in $result
   *
   * @param  array $result
   * @return array
   */
  public static function buildLda($result = array()) {
    $lda = array();
    if(count($result)){
      foreach ($result as $item) {
        $lda[$item['id_nodo_parent']][] = $item;
      }
    }
    return $lda;
  }

  /**
   * function internalLinkSelector
   *
   * @param  array  $params          - an array of parameters
   * @param  array  $external_params - an array with additional parameters
   * @return string $list_item       - an html string for a course index item
   */
  public static function internalLinkSelector($params = array(), $external_params=array()) {
    $css_classname = self::getClassNameForNodeType($params['node']['tipo']);
    $list_item = CDOMElement::create('span', "class:$css_classname");
    $list_item->addChild(self::getDisclosureElement($params, $external_params));
    $node_selector = CDOMElement::create('span');
    /*
     *  vito, 22 apr 2009:
     *  in case we are displaying the internal link selector to change the parent node
     *  of the currently edited node, we do not allow moving the edited node as a child
     *  of one of its children or as a child of itself.
     */
    if ($external_params['action'] == 1 && isset($external_params['id_edited_node'])
       && ($external_params['id_edited_node'] == $params['node']['id_nodo_parent'] || $external_params['id_edited_node'] == $params['node']['id_nodo'])) {
      // do nothing here
    }
    else {
      $node_selector->setAttribute('onclick',"executeAction('{$external_params['action']}','{$params['node']['id_nodo']}');");
      $node_selector->setAttribute('class','selectable');
    }
    $node_selector->addChild(new CText($params['node']['nome']));
    $list_item->addChild($node_selector);

    return $list_item;
  }

  /**
   * function nodeSelector
	* vito, 24 nov 2008: questo metodo non sembra essere utilizzato
	* valerio, 31 lug 2012: questo metodo non è utilizzato
   *
   * @param  array  $params          - an array of parameters
   * @param  array  $external_params - an array with additional parameters
   * @return string $list_item       - an html string for a course index item
   */
  function nodeSelector($params = array(), $external_params=array()) {

    $http_root_dir = $GLOBALS['http_root_dir'];

    if ( $params['node']['tipo'] == ADA_GROUP_TYPE ) {
      //vito, 3 ottobre 2008
      //$list_item  = "<span title=\"{$params['node']['id_nodo']}\" class=\"esplodi\" onclick=\"toggleVisibilityByClassName('{$external_params['container_div']}','{$params['node']['id_nodo']}');\">+</span>";
      $span_css_classname = '';
      $list_item  = "<span title=\"{$external_params['container_div']}{$params['node']['id_nodo']}\" class=\"$span_css_classname\" onclick=\"toggleVisibilityByClassName('{$external_params['container_div']}','{$params['node']['id_nodo']}');\">+</span>";
    }
    //vito 12 gennaio 2009
    //$list_item .= "<img src=\"img/{$params['node']['icona']}\"  /><a href=\"$http_root_dir/browsing/view.php?id_node={$params['node']['id_nodo']}\">{$params['node']['nome']}</a>";
    return $list_item;
  }

  /**
   * function authorCallback
   *
   * @param  array  $params          - an array of parameters
   * @param  array  $external_params - an array with additional parameters
   * @return string $list_item       - an html string for a course index item
   */
  public static function authorCallback($params = array(), $external_params=array()) {
    $http_root_dir = $GLOBALS['http_root_dir'];
    $show_visits   = !$GLOBALS['hide_visits'];

    $css_classname = self::getClassNameForNodeType($params['node']['tipo']);

    $list_item = CDOMElement::create('span',"class:$css_classname");
    $list_item->addChild(self::getDisclosureElement($params, $external_params));
    //vito 12 gennaio 2009
    //$icon = CDOMElement::create('img', "src:img/{$params['node']['icona']}");
    //$list_item->addChild($icon);
      preg_match("/^([0-9]+)_/", $params['node']['id_nodo'], $match);
      $id_course = $match[1];
      $node_type_family = $params['node']['tipo'][0];
      if ($node_type_family >= ADA_STANDARD_EXERCISE_TYPE AND $node_type_family <= ADA_OPEN_UPLOAD_EXERCISE_TYPE) {
          $node_element = CDOMElement::create('a', "href:$http_root_dir/browsing/exercise.php?id_node={$params['node']['id_nodo']}");
      }elseif ($node_type_family == ADA_PERSONAL_EXERCISE_TYPE) {
          $node_element = CDOMElement::create('a', "href:$http_root_dir/browsing/exercise_player.php?id_node={$params['node']['id_nodo']}");
      }else {
         $node_element = CDOMElement::create('a', "href:$http_root_dir/browsing/view.php?id_node={$params['node']['id_nodo']}&id_course={$id_course}");
      }
//      $node_element = CDOMElement::create('a', "href:$http_root_dir/browsing/view.php?id_node={$params['node']['id_nodo']}");
      $node_element->addChild(new CText($params['node']['nome']));
      $list_item->addChild($node_element);

    if ( isset($show_visits) && $show_visits == TRUE ) {
      $visits = 0;

      if (isset($params['node']['numero_visite']) && $params['node']['numero_visite'] > 0) {
        $visits = $params['node']['numero_visite'];
      }
      $list_item->addChild(new CText(translateFN("Visite") . " $visits"));
    }

    if (isset($params['node']['is_someone_there']) && $params['node']['is_someone_there'] >= 1) {
      $icon = CDOMElement::create('img', 'src:img/_student.png');
      $icon->setAttribute('name',translateFN('altri'));
      $icon->setAttribute('alt',translateFN('altri'));
      $list_item->addChild($icon);
    }
    return $list_item;
  }

  /**
   * function tutorCallback
   *
   * @param  array  $params          - an array of parameters
   * @param  array  $external_params - an array with additional parameters
   * @return string $list_item       - an html string for a course index item
   */
  public static function tutorCallback($params = array(), $external_params=array()) {
    $http_root_dir = $GLOBALS['http_root_dir'];
    $show_visits   = !$GLOBALS['hide_visits'];

    $css_classname = self::getClassNameForNodeType($params['node']['tipo']);

    $list_item = CDOMElement::create('span',"class:$css_classname");
    $list_item->addChild(self::getDisclosureElement($params, $external_params));
    //vito 12 gennaio 2009
    //$icon = CDOMElement::create('img', "src:img/{$params['node']['icona']}");
    //$list_item->addChild($icon);
      preg_match("/^([0-9]+)_/", $params['node']['id_nodo'], $match);
      $id_course = $match[1];
      $node_type_family = $params['node']['tipo'][0];
      if ($node_type_family >= ADA_STANDARD_EXERCISE_TYPE AND $node_type_family <= ADA_OPEN_UPLOAD_EXERCISE_TYPE) {
          $node_element = CDOMElement::create('a', "href:$http_root_dir/browsing/exercise.php?id_node={$params['node']['id_nodo']}");
      }elseif ($node_type_family == ADA_PERSONAL_EXERCISE_TYPE) {
          $node_element = CDOMElement::create('a', "href:$http_root_dir/browsing/exercise_player.php?id_node={$params['node']['id_nodo']}");
      }else {
         $node_element = CDOMElement::create('a', "href:$http_root_dir/browsing/view.php?id_node={$params['node']['id_nodo']}&id_course={$id_course}");
      }
      $node_element->addChild(new CText($params['node']['nome']));
      $list_item->addChild($node_element);

      if (isset($show_visits) && $show_visits == TRUE) {
      $visits = 0;

      if (isset($params['node']['numero_visite']) && $params['node']['numero_visite'] > 0) {
        $visits = $params['node']['numero_visite'];
      }
      $list_item->addChild(new CText(translateFN("Visite") . " $visits"));
    }

    if (isset($params['node']['is_someone_there']) && $params['node']['is_someone_there'] >= 1) {
      $icon = CDOMElement::create('img', 'src:img/_student.png');
      $icon->setAttribute('name',translateFN('altri'));
      $icon->setAttribute('alt',translateFN('altri'));
      $list_item->addChild($icon);
    }
    return $list_item;
  }

  /**
   * function studentCallback, used to generete an html string for a node
   *
   * @param array $params				- an array of parameters passed to this function from function struct()
   * @param array $external_params	- an array of parameters passed to this function from struct()'s caller
   * @return string
   */
  public static function studentCallback($params = array(), $external_params=array()) {
    $http_root_dir = $GLOBALS['http_root_dir'];
    $show_visits   = !$GLOBALS['hide_visits'];

    $css_classname = self::getClassNameForNodeType($params['node']['tipo']);

//    /*
//     * If current node can be viewed by the student, create a link to the node,
//     * else display only node name.
//     */
//    /*
//     * Display student visits to this node if required.
//     */


    $list_item = CDOMElement::create('span',"class:$css_classname");
    $list_item->addChild(self::getDisclosureElement($params, $external_params));
    //vito 12 gennaio 2009
    //$icon = CDOMElement::create('img', "src:img/{$params['node']['icona']}");
    //$list_item->addChild($icon);

    if (isset($params['node']['livello']) && $external_params['user_level'] >= $params['node']['livello']) {
      preg_match("/^([0-9]+)_/", $params['node']['id_nodo'], $match);
      $id_course = $match[1];

      $node_type_family = $params['node']['tipo'][0];
      if ($node_type_family >= ADA_STANDARD_EXERCISE_TYPE AND $node_type_family <= ADA_OPEN_UPLOAD_EXERCISE_TYPE) {
          $node_element = CDOMElement::create('a', "href:$http_root_dir/browsing/exercise.php?id_node={$params['node']['id_nodo']}");
      }elseif ($node_type_family == ADA_PERSONAL_EXERCISE_TYPE) {
          $node_element = CDOMElement::create('a', "href:$http_root_dir/browsing/exercise_player.php?id_node={$params['node']['id_nodo']}");
      }else {
         $node_element = CDOMElement::create('a', "href:$http_root_dir/browsing/view.php?id_node={$params['node']['id_nodo']}&id_course={$id_course}");
      }
//      $node_element = CDOMElement::create('a', "href:$http_root_dir/browsing/view.php?id_node={$params['node']['id_nodo']}");
      $node_element->addChild(new CText($params['node']['nome']));
      $list_item->addChild($node_element);
    }
    else {
      $list_item->addChild(new CText($params['node']['nome']));
    }

    if (isset($show_visits) && $show_visits == TRUE) {
      $visits = 0;

      if (isset($params['node']['numero_visite']) && $params['node']['numero_visite'] > 0) {
        $visits = $params['node']['numero_visite'];
      }
      $list_item->addChild(new CText(translateFN("Visite") . " $visits"));
    }

    $id_bk = Bookmark::is_node_bookmarkedFN($external_params['user_id'],$params['node']['id_nodo']);

    if ($id_bk) {
      // vito 13 gennaio 2009
      //  $link = CDOMElement::create('a', "href: bookmarks.php?op=zoom&id_bk=$id_bk");
      $link = CDOMElement::create('a', "href: tags.php?op=zoom&id_bk=$id_bk");
      $icon = CDOMElement::create('img', 'name:bookmark, alt:bookmark, src:img/check.png, border:0');
      $link->addChild($icon);
      $list_item->addChild($link);
    }

    if (isset($params['node']['is_someone_there']) && $params['node']['is_someone_there'] >= 1) {
      $icon = CDOMElement::create('img', 'src:img/_student.png');
      $icon->setAttribute('name',translateFN('altri'));
      $icon->setAttribute('alt',translateFN('altri'));
      $list_item->addChild($icon);
    }
    return $list_item;
  }

  /**
   * function forumStudentCallback
   *
   * @param  array  $params          - an array of parameters
   * @param  array  $external_params - an array with additional parameters
   * @return string $list_item       - an html string for a course index item
   */
  public static function forumStudentCallback($params = array(), $external_params=array()) {
    return self::forumCommonCallback($params, $external_params);
  }

  /**
   * function forumTutorCallback
   *
   * @param  array  $params          - an array of parameters
   * @param  array  $external_params - an array with additional parameters
   * @return string $list_item       - an html string for a course index item
   */
  public static function forumTutorCallback($params = array(), $external_params=array()) {
    return self::forumCommonCallback($params, $external_params);
  }

  /**
   * function forumAuthorCallback
   *
   * @param  array  $params          - an array of parameters
   * @param  array  $external_params - an array with additional parameters
   * @return string $list_item       - an html string for a course index item
   */
  public static function forumAuthorCallback($params = array(), $external_params=array()) {
    return self::forumCommonCallback($params, $external_params);
  }

  public static function forumCommonCallback($params=array(), $external_params=array()) {
    $http_root_dir = $GLOBALS['http_root_dir'];

    $show_visits   = !$GLOBALS['hide_visits'];

    $css_classname = self::getClassNameForNote($params['node'], $external_params['user_id'], isset($external_params['class_tutors_ids']) ? $external_params['class_tutors_ids'] : null);

    $list_item = CDOMElement::create('span');
    $list_item->addChild(self::getDisclosureElement($params, $external_params));
    if ($external_params['show_icons'] == TRUE) {
//      $note_icon = self::forumGetNoteIcon($params['node'], $external_params['class_tutor_id']);
//      $icon = CDOMElement::create('img');
//      $icon->setAttribute('src',"img/$note_icon");
//      $list_item->addChild($icon);
      $list_item->setAttribute('class',$css_classname);
    }

    if (isset($params['node']['username'])) {
    	$username = CDOMElement::create('span', 'class:username');
    	$username->addChild(new CText($params['node']['username']));
    }
    if (isset($params['node']['nome_nodo'])) {
    	$textlink = $params['node']['nome_nodo'];
    	$link_to_note = CDOMElement::create('a',"href:$http_root_dir/browsing/view.php?id_node={$params['node']['id_nodo']}");
    } else if (isset($params['node']['nome'])) {
    	$textlink = $params['node']['nome'];
    	$link_to_note = CDOMElement::create('span');
    }
    if (isset($link_to_note)) $link_to_note->addChild(new CText($textlink));    
    
    if (isset($link_to_note)) $list_item->addChild($link_to_note);
    if (isset($username)) $list_item->addChild($username);

	if (!empty($params['node']['testo'])) {
		$link_zoom = CDOMElement::create('a');
		$link_zoom->setAttribute('href','javascript:void(0);');
		$link_zoom->setAttribute('onclick',"$('messagePreview".$params['node']['id_nodo']."').toggle();");
		$link_zoom->setAttribute('title',translateFN('Anteprima Messaggio'));
		$zoom = CDOMElement::create('img','src:img/zoom.png, width:16, height:16');
		$link_zoom->addChild($zoom);
		$list_item->addChild($link_zoom);
	}
	/*
     * Display student visits to this node if required.
     */
    if (isset($show_visits) && $show_visits == TRUE) {
      $visits = 0;

      if (isset($params['node']['numero_visite']) && $params['node']['numero_visite'] > 0) {
        $visits = $params['node']['numero_visite'];
      }
      $list_item->addChild(new CText(translateFN("Visite") . " $visits"));
    }

    if (isset($params['node']['is_someone_there']) && $params['node']['is_someone_there'] >= 1) {
      $image = CDOMElement::create('img','name:altri, src:img/_student.png');
      $list_item->addChild($image);
    }

    if (!empty($params['node']['testo'])) {
		$div_text = CDOMElement::create('div', 'id:messagePreview'.$params['node']['id_nodo']);
		$div_text->setAttribute('class', 'preview_forum');
		$div_text->setAttribute('style', 'display:none;');
		$char_limit = 525;
		$text = strip_tags($params['node']['testo']);
		if (strlen($text)>$char_limit) {
			$add_link = true;
			$text = substr_gentle($text,$char_limit);
		}
		else {
			$add_link = false;
		}
		$div_text->addChild(new CText($text));

		if ($add_link) {
			$link_to_note = CDOMElement::create('a',"href:$http_root_dir/browsing/view.php?id_node={$params['node']['id_nodo']}");
			$link_to_note->setAttribute('title',translateFN('Visualizza messaggio completo'));
			$link_to_note->addChild(new CText(translateFN('(leggi tutto)')));
			$div_text->addChild(new CText(' '));
			$div_text->addChild($link_to_note);
		}
		$list_item->addChild($div_text);
	}
    return $list_item;
  }

  public static function displayForumMenu($op, $userObj) {
    $menu = CDOMElement::create('span','id:forum_menu, class:right_menu');
    $ul   = CDOMElement::create('ul');
    $export_all = CDOMElement::create('li');
    $export_all_link  = CDOMElement::create('a', "href:main_index.php?op=$op&order=chrono&list_mode=export_all");
    $export_all_link->addChild(new CText(translateFN("Esporta tutto")));
    $export_all->addChild($export_all_link);
    $ul->addChild($export_all);

    if ($userObj->tipo == AMA_TYPE_STUDENT) {
      $export_student_notes = CDOMElement::create('li');
      $export_student_notes_link = CDOMElement::create('a', "href:main_index.php?op=$op&order=chrono&list_mode=export_single&id_student=".$userObj->getId());
      $export_student_notes_link->addChild(new CText(translateFN("Esporta note studente")));
      $export_student_notes->addChild($export_student_notes_link);
      $ul->addChild($export_student_notes);
    }

    $menu->addChild($ul);

    return $menu->getHtml();
  }

  /**
   * function forumGetNoteIcon
   *
   * @param array   $node
   * @param integer $class_tutor_id
   * @return string
   */
  function forumGetNoteIcon($node, $class_tutor_id) {
    if (!isset($node['id_utente'])) {
      return "_nota.png";
    }

    if ($node['tipo'] == ADA_PRIVATE_NOTE_TYPE) {
      return "_nota_pers.png";
    }

    if ($node['id_utente'] == $class_tutor_id) {
      return "_nota_tutor.png";
    }

    return "_nota.png";
  }

  /**
   * function getClassNameForForumUserName
   *
   * @param  array  $params          - an array of parameters
   * @param  array  $external_params - an array with additional parameters
   * @return string $list_item       - an html string for a course index item
   */
  function getClassNameForForumUserName($node, $id_user) {
    if(!isset($node['id_utente']) || $node['id_utente'] != $id_user) {
      return "not_your_note";
    }
    else {
      return "your_note";
    }
  }

  /**
   * function getClassNameForNodeType
   *
   * @param unknown_type $node_type
   * @return string
   */
  public static function getClassNameForNodeType($node_type) {
      $node_type_family = $node_type[0];
      switch($node_type) {
      case ADA_NOTE_TYPE:
        $classNameForNodeType = "ADA_NOTE_TYPE";
        break;
//        return "ADA_NOTE_TYPE";
      case ADA_PRIVATE_NOTE_TYPE:
        $classNameForNodeType = "ADA_PRIVATE_NOTE_TYPE";
        break;
//        return "ADA_PRIVATE_NOTE_TYPE";

      case ADA_GROUP_TYPE:
        $classNameForNodeType = "ADA_GROUP_TYPE";
        break;
        //return "ADA_GROUP_TYPE";

      case ADA_LEAF_TYPE:
        $classNameForNodeType = "ADA_LEAF_TYPE";
        break;
      case ADA_GROUP_WORD_TYPE:
        $classNameForNodeType = "ADA_GROUP_WORD_TYPE";
        break;
        //return "ADA_GROUP_TYPE";
      case ADA_LEAF_WORD_TYPE:
        $classNameForNodeType = "ADA_LEAF_WORD_TYPE";
        break;
      default:
        $classNameForNodeType = "ADA_LEAF_TYPE";
        //return "ADA_LEAF_TYPE";
    }
    if (($node_type_family >= 3 AND $node_type_family <= 7) OR $node_type_family == 9) {
        $classNameForNodeType = "ADA_EXERCISE";
    }

    return $classNameForNodeType;
  }

  /**
   * function getClassNameForNote
   *
   * @param array $node
   * @param integer $user_id
   * @param integer $tutor_id
   * @return string
   */
  public static function getClassNameForNote($node, $user_id, $tutor_id) {
    $classname = 'ADA_NOTE_TYPE ';

    if($node['tipo'] == ADA_GROUP_TYPE) {
        $classname = 'ADA_GROUP_TYPE';
    }
    if (isset($node['id_utente'])) {
      if($node['id_utente'] == $tutor_id) {
        $classname .= 'TUTOR_NOTE ';
      }

      if ($node['id_utente'] == $user_id) {
        $classname .= 'YOUR_NOTE ';
      }

      if($node['tipo'] == ADA_PRIVATE_NOTE_TYPE) {
        $classname .= 'ADA_PRIVATE_NOTE_TYPE';
      }
    }

    return $classname;
  }

  /**
   * function getCSSClassNameForExerciseType($exercise_typ, $executed)
   *
   * @param integer $exercise_type
   * @param boolean $executed
   * @return string
   */
  public static function getCSSClassNameForExerciseType($exercise_type, $executed=false) {

    if ($executed) {
      $executed_classname = 'ADA_EXECUTED_EXERCISE';
    }
    else {
      $executed_classname = '';
    }

    switch($exercise_type) {
      case ADA_STANDARD_EXERCISE_TYPE:
        return "ADA_STANDARD_EXERCISE_TYPE $executed_classname";

      case ADA_OPEN_MANUAL_EXERCISE_TYPE:
        return "ADA_OPEN_MANUAL_EXERCISE_TYPE $executed_classname";

      case ADA_OPEN_AUTOMATIC_EXERCISE_TYPE:
        return "ADA_OPEN_AUTOMATIC_EXERCISE_TYPE $executed_classname";

      case ADA_CLOZE_EXERCISE_TYPE:
        return "ADA_CLOZE_EXERCISE_TYPE $executed_classname";

      case ADA_OPEN_UPLOAD_EXERCISE_TYPE:
        return "ADA_OPEN_UPLOAD_EXERCISE_TYPE $executed_classname";

      default:
        return '';
    }
  }

  /**
   * function getDisclosureElement
   *
   * @param  array  $params          - an array of parameters
   * @param  array  $external_params - an array with additional parameters
   * @return string $list_item       - an html string for a course index item
   */
  public static function getDisclosureElement($params = array(), $external_params = array()) {
    // vito 13 gennaio 2009
    if (!isset($params['show_hide_span']) || $params['show_hide_span'] == FALSE) {
      $list_item = CDOMElement::create('span');
      return $list_item;
    }

    if ($params['expand_index'] == 0 || $params['level'] >= $params['expand_index']) {
      $hide_span = TRUE;
    }
    else {
      $hide_span = FALSE;
    }


    if (($params['node']['tipo'] == ADA_GROUP_TYPE && $hide_span)
         || ($params['node']['tipo'] == ADA_NOTE_TYPE && $hide_span)) {
      $span_css_classname = 'hideNodeChildren selectable';
      $disclosure_element = '+';
    }
    else if(!$hide_span) {
      $span_css_classname = 'viewNodeChildren selectable';
      $disclosure_element = '-';
    }

	$list_item = CDOMElement::create('span','id:s'.$params['node']['id_nodo']);
	$list_item->setAttribute('class',$external_params['container_div'].' '.$span_css_classname);
//	if (!$params['node']['root'])
//	{

		$list_item->setAttribute('onclick',"toggleVisibilityByClassName('".$external_params['container_div']."','".$params['node']['id_nodo']."');");
//	}
    $list_item->addChild(new CText($disclosure_element));

    return $list_item;
  }
}
