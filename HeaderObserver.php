<?php
/**
 * PHPOnix, an Onix parser and importer 
 *
 * @category	PHPOnix
 * @package	PHPOnix_HeaderObserver
 * @copyright   Bruno Rossi <brunorossiweb@gmail.com>
 * @license BSD
 */

/**
 * PHPOnix_HeaderObserver
 *
 * @category	PHPOnix
 * @package	PHPOnix_HeaderObserver
 */
class PHPOnix_HeaderObserver
implements SplObserver
{

    public function update(SplSubject $subject) 
    {
        if ('Header' === $subject->getCurrentTagName()) {
            $xml = new SimpleXMLElement($subject->getCurrentContent());
            echo 'called Header Observer' . '<br />';
            echo '<pre>';
            echo htmlentities(print_r($xml));
            echo '</pre>';
        }
    }
    
}
