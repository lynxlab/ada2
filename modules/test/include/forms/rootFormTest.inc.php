<?php
/**
 *
 * @package
 * @author		Valerio Riva <valerio@lynxlab.com>
 * @copyright	Copyright (c) 2012, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link
 * @version		0.1
 */

class RootFormTest extends FormTest {
	
	protected function content() {
		//nome
		if (!empty($this->data['nome'])) {
			$defaultValue = $this->data['nome'];
		}
		else {
			$defaultValue = '';
		}
        $this->addTextInput('nome', translateFN('Nome (visualizzato nella breadcrumb)').':')
             ->setRequired()
             ->setValidator(FormValidator::NOT_EMPTY_STRING_VALIDATOR)
             ->withData($defaultValue);

		//titolo
        $this->addTextInput('titolo', translateFN('Titolo').':')
             ->setRequired()
             ->setValidator(FormValidator::NOT_EMPTY_STRING_VALIDATOR)
             ->withData($this->data['titolo']);

		//descrizione
        $this->addTextarea('testo', translateFN('Descrizione').':')
             ->withData(Node::prepareInternalLinkMediaForEditor($this->data['testo']));

		//consegna (message showed on test / survey ending)
		if (!empty($this->data['nome'])) {
			$defaultValue = Node::prepareInternalLinkMediaForEditor($this->data['consegna']);
		}
		else {
			$defaultValue = translateFN('Dati inviati correttamente.');
		}
        $this->addTextarea('consegna', translateFN('Messaggio finale').':')
             ->withData($defaultValue);

		//return
		$options = array(
			ADA_NO_TEST_RETURN => translateFN('Non mostrare link'),
			ADA_NEXT_NODE_TEST_RETURN => translateFN('Mostra link al nodo successivo del corso'),
			ADA_INDEX_TEST_RETURN => translateFN('Mostra link all\'indice del corso'),
			ADA_COURSE_INDEX_TEST_RETURN => translateFN('Mostra link all\'elenco dei corsi'),
		);
		if (isset($this->data['return'])) {
			$defaultValue = $this->data['return'];
		}
		else {
			$defaultValue = ADA_NO_TEST_RETURN;
		}
        $this->addSelect('return',translateFN('Link di ritorno').':',$options,$defaultValue);

		//ripetibile
		$radios = array(
			ADA_YES_TEST_REPETEABLE => translateFN('Si'),
			ADA_NO_TEST_REPETEABLE => translateFN('No'),
		);
		if (isset($this->data['ripetibile'])) {
			$defaultValue = $this->data['ripetibile'];
		}
		else {
			$defaultValue = ADA_YES_TEST_REPETEABLE;
		}
		$this->addRadios('ripetibile',translateFN('Ripetibile dall\'utente').':',$radios,$defaultValue);

		//suddivisione in sessioni
		$radios = array(
			ADA_SEQUENCE_TEST_MODE => translateFN('Si'),
			ADA_ONEPAGE_TEST_MODE => translateFN('No'),
		);
		if (isset($this->data['suddivisione'])) {
			$defaultValue = $this->data['suddivisione'];
		}
		else {
			$defaultValue = ADA_ONEPAGE_TEST_MODE;
		}
		$this->addRadios('suddivisione',translateFN('Suddividi per sessioni').':',$radios,$defaultValue);

		//feedback
		$options = array(
			ADA_CORRECT_TEST_INTERACTION => translateFN('Correzioni risposte, punteggio ottenuto e risposta corretta'),
			ADA_RATING_TEST_INTERACTION => translateFN('Correzioni risposte e punteggio ottenuto'),
			ADA_FEEDBACK_TEST_INTERACTION => translateFN('Correzioni risposte'),
			ADA_BLIND_TEST_INTERACTION => translateFN('Nessun feedback'),
		);
		if (isset($this->data['feedback'])) {
			$defaultValue = $this->data['feedback'];
		}
		else {
			$defaultValue = ADA_RATING_TEST_INTERACTION;
		}
        $this->addSelect('feedback',translateFN('Feedback all\'utente').':',$options,$defaultValue);

		//livello minimo
		if (isset($this->data['min_level'])) {
			$defaultValue = $this->data['min_level'];
		}
		else {
			$defaultValue = 0;
		}
        $this->addTextInput('min_level', translateFN('Livello minimo per accedere al test (0 = nessun limite)').':')
             ->setRequired()
             ->setValidator(FormValidator::NON_NEGATIVE_NUMBER_VALIDATOR)
             ->withData($defaultValue);
    }
}